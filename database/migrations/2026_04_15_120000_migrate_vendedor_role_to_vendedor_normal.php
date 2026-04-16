<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'vendedor')
            ->update(['role' => 'vendedor_normal']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'vendedor_normal')
            ->update(['role' => 'vendedor']);
    }
};
