<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = config('visitor.table_name', 'shetabit_visits');

        Schema::table($tableName, function (Blueprint $table) {
            // Alterar apenas as colunas _id para UUID
            $table->uuid('visitable_id')->nullable()->change();
            $table->uuid('visitor_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('visitor.table_name', 'shetabit_visits');

        Schema::table($tableName, function (Blueprint $table) {
            // Reverter para unsignedBigInteger
            $table->unsignedBigInteger('visitable_id')->nullable()->change();
            $table->unsignedBigInteger('visitor_id')->nullable()->change();
        });
    }
};
