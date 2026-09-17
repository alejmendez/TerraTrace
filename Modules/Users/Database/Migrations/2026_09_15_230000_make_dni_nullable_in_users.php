<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make `dni` nullable so that Breeze-style registration flows
     * (which only collect name/email/password) don't crash on insert.
     *
     * PostgreSQL treats NULL as distinct in UNIQUE indexes, so the
     * constraint still prevents duplicate DNIs when populated — only
     * the "must provide one" enforcement is relaxed. This matches the
     * UX intent: DNI is collected later in the user profile, not at
     * sign-up.
     *
     * Schema::change() can't drop+recreate a unique constraint on a
     * column with existing data, so we use raw SQL guarded by the
     * doctrine driver (PostgreSQL in this project).
     */
    public function up(): void
    {
        // The doctrine schema builder's change() emits ADD CONSTRAINT
        // which collides with the existing users_dni_unique index.
        // Drop the index, change the column, recreate it.
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_dni_unique');
        DB::statement('ALTER TABLE users ALTER COLUMN dni DROP NOT NULL');
        DB::statement('CREATE UNIQUE INDEX users_dni_unique ON users (dni) WHERE dni IS NOT NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_dni_unique');
        DB::statement('ALTER TABLE users ALTER COLUMN dni SET NOT NULL');
        DB::statement('CREATE UNIQUE INDEX users_dni_unique ON users (dni)');
    }
};
