<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE organizations MODIFY card_style TEXT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE organizations ALTER COLUMN card_style TYPE TEXT');
            DB::statement('ALTER TABLE organizations ALTER COLUMN card_style DROP NOT NULL');
        } elseif ($driver !== 'sqlite') {
            throw new RuntimeException("Unsupported database driver for card style migration: {$driver}");
        }
    }

    public function down(): void
    {
        // Keep the wider column to avoid truncating saved card styles.
    }
};
