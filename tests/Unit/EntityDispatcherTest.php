<?php

namespace Tests\Unit;

use Modules\Core\Registry\EntityDispatcher;
use RuntimeException;
use Tests\TestCase;

/**
 * Coverage for `EntityDispatcher`, the central named-entity
 * resolver. Booting the container is required because the
 * dispatcher resolves services via `app()`, so this test extends
 * `Tests\TestCase` (the project base that boots Laravel) rather
 * than `PHPUnit\Framework\TestCase` directly.
 */
class EntityDispatcherTest extends TestCase
{
    public function test_catalogue_includes_all_expected_entities(): void
    {
        $catalogue = EntityDispatcher::catalogue();

        // Service-backed entries we rely on across the app.
        $this->assertArrayHasKey('field', $catalogue);
        $this->assertArrayHasKey('quarter', $catalogue);
        $this->assertArrayHasKey('plant', $catalogue);
        $this->assertArrayHasKey('harvest', $catalogue);
        $this->assertArrayHasKey('category_products', $catalogue);
        $this->assertArrayHasKey('user', $catalogue);
        $this->assertArrayHasKey('role', $catalogue);

        // Three Users aliases share one method.
        $this->assertSame(
            $catalogue['user'],
            $catalogue['responsible']
        );
        $this->assertSame(
            $catalogue['user'],
            $catalogue['couple']
        );

        // Static entries.
        $this->assertSame('static', $catalogue['scale_type']);
        $this->assertSame('static', $catalogue['genders']);
        $this->assertSame('static', $catalogue['is_commercial_options']);

        // Catalogue is alphabetically sorted (helper invariant).
        $this->assertSame(
            array_keys($catalogue),
            collect(array_keys($catalogue))->sort()->values()->all()
        );
    }

    public function test_dispatch_returns_array_for_service_backed_entity(): void
    {
        // `field` resolves through FieldService::forSelect(), which
        // returns an array of {value, text} rows. Test DB has no
        // rows so the result is empty — the contract is the shape.
        $this->assertSame([], EntityDispatcher::dispatch('field'));
    }

    public function test_dispatch_returns_translated_options_for_static_lists(): void
    {
        $this->assertSame(
            [
                ['value' => 'weight', 'text' => trans('quarter.show.statistics.scale_type.options.weight')],
                ['value' => 'quantity', 'text' => trans('quarter.show.statistics.scale_type.options.quantity')],
            ],
            EntityDispatcher::dispatch('scale_type')
        );

        $this->assertSame(
            [
                ['value' => 'M', 'text' => trans('dog.form.gender.options.male')],
                ['value' => 'F', 'text' => trans('dog.form.gender.options.female')],
            ],
            EntityDispatcher::dispatch('genders')
        );
    }

    public function test_dispatch_is_commercial_options_has_three_rows(): void
    {
        $options = EntityDispatcher::dispatch('is_commercial_options');

        $this->assertCount(3, $options);
        $this->assertNull($options[0]['value']);
        $this->assertTrue($options[1]['value']);
        $this->assertFalse($options[2]['value']);
    }

    public function test_dispatch_throws_for_unknown_entity(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Entity 'totally_bogus' is not registered in EntityDispatcher.");

        EntityDispatcher::dispatch('totally_bogus');
    }

    public function test_dispatch_many_returns_one_entry_per_input(): void
    {
        $result = EntityDispatcher::dispatchMany([
            'field' => [],
            'genders' => [],
            'scale_type' => [],
        ]);

        $this->assertSame(['field', 'genders', 'scale_type'], array_keys($result));
        $this->assertCount(2, $result['genders']);
        $this->assertCount(2, $result['scale_type']);
        $this->assertSame([], $result['field']);
    }

    public function test_dispatch_many_tolerates_empty_filter_arrays(): void
    {
        // The dispatcher's filter parameter is currently unused by
        // any registered service method, but the contract documents
        // that it's accepted and forwarded. Passing `[]` must not
        // throw.
        $this->assertSame(
            ['field' => []],
            EntityDispatcher::dispatchMany(['field' => []])
        );
    }
}
