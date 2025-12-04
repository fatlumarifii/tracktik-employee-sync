<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_syncs', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->index();
            $table->string('provider_employee_id')->index();
            $table->string('tracktik_employee_id')->nullable()->index();
            $table->string('sync_status')->index();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_syncs');
    }
};
