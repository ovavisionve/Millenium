<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Millennium — los dumps usan varchar(250); Laravel inicialmente usa 100.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE parroquias MODIFY nombre_parroquia VARCHAR(250) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE parroquias MODIFY nombre_parroquia VARCHAR(100) NOT NULL');
    }
};
