<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `liquidation_number` is referenced by LiquidationService::collection()
     * (select / search / sort) and rendered in Modules\Fields\Resources\Pages\
     * Liquidations\List.vue as the user-facing identifier, but the column
     * was never added by any prior migration. We add it now, backfill each
     * existing row with its `id` so the field has a stable, sortable value
     * out of the box, then enforce NOT NULL.
     *
     * A UNIQUE constraint is deliberately NOT added here — `id` is already
     * unique and acts as the canonical key. If a future workflow wants a
     * separate business-number sequence (e.g. "LIQ-{year}-{seq}"), add a
     * dedicated service and tighten the constraint in a follow-up migration.
     */
    public function up(): void
    {
        Schema::table('liquidations', function (Blueprint $table) {
            $table->integer('liquidation_number')->nullable()->after('id');
        });

        DB::table('liquidations')->update(['liquidation_number' => DB::raw('id')]);

        Schema::table('liquidations', function (Blueprint $table) {
            $table->integer('liquidation_number')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('liquidations', function (Blueprint $table) {
            $table->dropColumn('liquidation_number');
        });
    }
};
