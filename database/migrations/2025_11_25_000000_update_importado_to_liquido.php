<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mediciones')
            ->where('tipo_superficie', 'Importado')
            ->update(['tipo_superficie' => 'Líquido']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mediciones')
            ->where('tipo_superficie', 'Líquido')
            ->update(['tipo_superficie' => 'Importado']);
    }
};
