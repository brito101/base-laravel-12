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
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';

        // Alterar model_has_permissions para suportar UUID
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($pivotPermission) {
            // Remover foreign key antes de alterar a primary key
            $table->dropForeign([$pivotPermission]);
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropIndex('model_has_permissions_model_id_model_type_index');
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($columnNames, $tableNames, $pivotPermission) {
            $table->uuid($columnNames['model_morph_key'])->change();
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');

            // Recriar foreign key
            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
        });

        // Alterar model_has_roles para suportar UUID
        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($pivotRole) {
            // Remover foreign key antes de alterar a primary key
            $table->dropForeign([$pivotRole]);
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropIndex('model_has_roles_model_id_model_type_index');
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($columnNames, $tableNames, $pivotRole) {
            $table->uuid($columnNames['model_morph_key'])->change();
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');

            // Recriar foreign key
            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';

        // Reverter model_has_permissions
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($pivotPermission) {
            $table->dropForeign([$pivotPermission]);
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropIndex('model_has_permissions_model_id_model_type_index');
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($columnNames, $tableNames, $pivotPermission) {
            $table->unsignedBigInteger($columnNames['model_morph_key'])->change();
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
        });

        // Reverter model_has_roles
        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($pivotRole) {
            $table->dropForeign([$pivotRole]);
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropIndex('model_has_roles_model_id_model_type_index');
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($columnNames, $tableNames, $pivotRole) {
            $table->unsignedBigInteger($columnNames['model_morph_key'])->change();
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');
        });
    }
};
