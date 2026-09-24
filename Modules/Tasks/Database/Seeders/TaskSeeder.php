<?php

namespace Modules\Tasks\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Fields\Models\Field;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskCorrelative;
use Modules\Users\Models\User;

/**
 * Seeds a realistic distribution of tasks for the dashboard counters.
 *
 * The dashboard reads 3 globals from TasksStatsProvider::taskCounters():
 *
 *   - pending_tasks      → status = 'overdued'
 *   - tasks_in_progress  → status IN ('started', 'overdued')
 *   - tasks_totals       → status IN ('to_begin', 'started', 'stopped', 'overdued')
 *
 * Without seed data, all three read 0 and the cards are empty. The
 * distribution here mirrors what a real Chilean truffery would look like
 * after 2-3 years of operations:
 *
 *   - 12-18 overdued (delayed, end_date past)
 *   - 6-10 started (in progress, end_date in the future)
 *   - 4-6 stopped (paused mid-season)
 *   - 8-12 to_begin (scheduled, not started)
 *   - 10-15 finished (closed; intentionally excluded from totals)
 *
 * This pattern follows AGENTS.md §4: Tasks owns its own data. We don't
 * import Fields services — we read fields directly via the model, which
 * is acceptable for seeders because seeders are one-shot, app-internal
 * fixtures.
 */
class TaskSeeder extends Seeder
{
    /**
     * State distribution per field. The dashboard reads globals, but
     * attaching tasks to real fields gives a coherent demo for the rest
     * of the Tasks UI as well.
     */
    private const TASKS_PER_FIELD = [
        'overdued' => [3, 5],     // 3 to 5 overdued per field
        'started' => [2, 4],
        'stopped' => [1, 2],
        'to_begin' => [2, 4],
        'finished' => [3, 5],
    ];

    /**
     * Realistic truffle-farming tasks. Mix of operational (irrigation,
     * harvest prep) and administrative (auditoría, planificación).
     */
    private const TASK_NAMES = [
        'overdued' => [
            'Riego de mantenimiento por goteo',
            'Poda sanitaria en cuarteles adultos',
            'Reparación de cerco perimetral',
            'Control de maleza pre-temporada',
            'Aplicación de compost orgánico',
            'Calibración de sistema de riego',
            'Inventario de herramientas',
            'Limpieza de canales de drenaje',
            'Fumigación preventiva contra hongos',
            'Reemplazo de árboles secos',
            'Capacitación de personal',
            'Auditoría interna de producción',
        ],
        'started' => [
            'Cosecha turno matinal',
            'Entrenamiento de perros detectores',
            'Revisión de sistema de bombeo',
            'Selección y分类 de truffles',
            'Embalaje y despacho a importador',
            'Control de calidad post-cosecha',
            'Mantenimiento de vehículos',
            'Actualización de registros',
            'Reunión con equipo técnico',
        ],
        'stopped' => [
            'Reparación de bodega de acopio',
            'Negociación con nuevo importador',
            'Estudio de suelo en cuarteles nuevos',
            'Poda de formación (pausada por lluvia)',
            'Compra de insumos (presupuesto pendiente)',
        ],
        'to_begin' => [
            'Planificación temporada 2026',
            'Renovación de contrato de arriendo',
            'Solicitud de permisos municipales',
            'Preparación de balance anual',
            'Diseño de nuevos cuarteles',
            'Selección de variedades para invernadero',
            'Cotización de sistema de riego nuevo',
            'Inscripción en registro SAG',
            'Reclutamiento de personal temporal',
            'Calendario de fertilización',
            'Actualización de cartografía digital',
            'Evaluación de nuevas tecnologías',
        ],
        'finished' => [
            'Cosecha temporada 2024',
            'Liquidación anual con importador A',
            'Cierre contable trimestre 1',
            'Renovación de permisos de exportación',
            'Capacitación anual de seguridad',
            'Mantenimiento mayor de tractores',
            'Poda de invierno 2024',
            'Análisis de suelo post-temporada',
            'Cierre de balance 2024',
            'Pago de contribuciones',
            'Limpieza profunda de bodega',
            'Inventario anual',
            'Auditoría externa 2024',
            'Renovación de seguros',
            'Implementación de software agrícola',
        ],
    ];

    /**
     * Priorities used in the seeded data. Weighted random pick — tasks
     * with status 'overdued' skew 'urgent'/'important' because they were
     * missed.
     */
    private const PRIORITIES = ['when_possible', 'routine', 'important', 'urgent'];

    public function run(): void
    {
        if (Task::exists()) {
            return;
        }

        $fields = Field::with('quarters')->orderBy('id')->get();

        if ($fields->isEmpty()) {
            $this->command?->warn('TaskSeeder: no fields found, skipping. Run FieldSeeder first.');

            return;
        }

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command?->warn('TaskSeeder: no users found, skipping. Run UserSeeder first.');

            return;
        }

        DB::transaction(function () use ($fields, $users) {
            $correlativeByYear = [];

            foreach ($fields as $field) {
                $fieldQuarters = $field->quarters;
                $fieldQuarterIds = $fieldQuarters->pluck('id')->all();

                foreach (self::TASKS_PER_FIELD as $status => $range) {
                    $count = rand($range[0], $range[1]);
                    $names = self::TASK_NAMES[$status] ?? [];
                    if (empty($names)) {
                        continue;
                    }

                    for ($i = 0; $i < $count; $i++) {
                        $name = $names[array_rand($names)];

                        [$startDate, $endDate] = $this->dateRangeForStatus($status);

                        $year = (int) Carbon::parse($startDate)->year;
                        $correlative = $this->nextCorrelative($correlativeByYear, $year);

                        $priority = $this->priorityForStatus($status);

                        $task = Task::create([
                            'name' => $name,
                            'status' => $status,
                            'repeat_number' => '0',
                            'repeat_type' => '',
                            'priority' => $priority,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'field_id' => $field->id,
                            // quarter_id is NOT a column on tasks: the
                            // 2024_08_28_040921 migration dropped it in favour
                            // of the belongsToMany pivot table `quarter_task`.
                            // We attach via $task->quarters() below.
                            'plant_id' => null,
                            'responsible_id' => $users->random()->id,
                            'note' => null,
                            // `comments` is not a column on tasks — comments
                            // live in the related `task_comments` table via the
                            // TaskComment model. We don't seed comments here.
                            'correlative' => $correlative,
                            'rows' => [],  // JSON column, no rows in seed
                        ]);

                        // Attach 1-2 quarters via the pivot so the task isn't
                        // a "naked" row in the task_quarter view of the UI.
                        if (! empty($fieldQuarterIds)) {
                            $attachCount = min(count($fieldQuarterIds), rand(1, 2));
                            $picked = (array) array_rand(array_flip($fieldQuarterIds), $attachCount);
                            $task->quarters()->attach($picked);
                        }
                    }
                }
            }
        });

        $this->command?->info(sprintf('TaskSeeder: %d tasks created.', Task::count()));
    }

    /**
     * Build a (start, end) date pair consistent with the task status:
     *   - overdued  → end_date 7-30 days ago, start_date 14-60 days ago
     *   - started   → start_date 0-10 days ago, end_date 1-21 days in future
     *   - stopped   → end_date 1-10 days ago, started earlier this season
     *   - to_begin  → start_date 1-30 days in the future
     *   - finished  → both dates 14-180 days ago
     *
     * @return array{0: string, 1: string}
     */
    private function dateRangeForStatus(string $status): array
    {
        $today = Carbon::today();

        return match ($status) {
            'overdued' => [
                $today->copy()->subDays(rand(14, 60))->toDateString(),
                $today->copy()->subDays(rand(7, 30))->toDateString(),
            ],
            'started' => [
                $today->copy()->subDays(rand(0, 10))->toDateString(),
                $today->copy()->addDays(rand(1, 21))->toDateString(),
            ],
            'stopped' => [
                $today->copy()->subDays(rand(15, 60))->toDateString(),
                $today->copy()->subDays(rand(1, 10))->toDateString(),
            ],
            'to_begin' => [
                $today->copy()->addDays(rand(1, 14))->toDateString(),
                $today->copy()->addDays(rand(15, 60))->toDateString(),
            ],
            'finished' => [
                $today->copy()->subDays(rand(30, 180))->toDateString(),
                $today->copy()->subDays(rand(14, 29))->toDateString(),
            ],
            default => [
                $today->toDateString(),
                $today->copy()->addDays(7)->toDateString(),
            ],
        };
    }

    /**
     * Priority distribution by status. Overdued tasks skew urgent/important;
     * to_begin skews routine; finished skews routine/historical.
     */
    private function priorityForStatus(string $status): string
    {
        return match ($status) {
            'overdued' => $this->pick(['urgent' => 0.5, 'important' => 0.3, 'routine' => 0.15, 'when_possible' => 0.05]),
            'started' => $this->pick(['urgent' => 0.2, 'important' => 0.4, 'routine' => 0.3, 'when_possible' => 0.1]),
            'stopped' => $this->pick(['important' => 0.4, 'routine' => 0.4, 'urgent' => 0.1, 'when_possible' => 0.1]),
            'to_begin' => $this->pick(['routine' => 0.5, 'when_possible' => 0.3, 'important' => 0.15, 'urgent' => 0.05]),
            'finished' => $this->pick(['routine' => 0.4, 'when_possible' => 0.3, 'important' => 0.2, 'urgent' => 0.1]),
            default => 'routine',
        };
    }

    /**
     * Weighted random pick. Same algorithm as LiquidationSeeder::weightedPick,
     * duplicated here to keep each seeder independent (they don't share
     * runtime utilities per AGENTS.md §4.5 anti-pattern note about
     * "centralized helpers" replacing module-owned logic).
     */
    private function pick(array $weights): string
    {
        $total = array_sum($weights);
        $rand = mt_rand() / mt_getrandmax() * $total;
        $acc = 0;
        foreach ($weights as $key => $weight) {
            $acc += $weight;
            if ($rand <= $acc) {
                return $key;
            }
        }

        return array_key_first($weights);
    }

    /**
     * Generate the next correlative code in the "NNN_YY" format used by
     * TaskService::getCorrelative(). Also keeps TaskCorrelative in sync so
     * a freshly-created task through the UI doesn't collide.
     */
    private function nextCorrelative(array &$cache, int $year): string
    {
        if (! isset($cache[$year])) {
            $cache[$year] = (int) (TaskCorrelative::where('year', $year)->value('correlative') ?? -1);
        }
        $cache[$year]++;

        TaskCorrelative::updateOrCreate(
            ['year' => $year],
            ['correlative' => $cache[$year]],
        );

        return str_pad($cache[$year], 3, '0', STR_PAD_LEFT).'_'.substr((string) $year, -2);
    }
}
