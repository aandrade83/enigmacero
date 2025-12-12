<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rol del usuario: admin, employee, client
            $table->enum('role', ['admin', 'employee', 'client'])
                  ->default('employee')
                  ->after('email');

            // Si el usuario está activo o no
            $table->boolean('is_active')
                  ->default(true)
                  ->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
