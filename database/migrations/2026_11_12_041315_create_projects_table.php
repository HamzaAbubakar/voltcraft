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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('customer_name')->nullable();
            $table->string('site_location')->nullable();
            $table->unsignedInteger('system_size_watts');
            $table->decimal('customer_rate_per_watt', 8, 2);
            $table->decimal('outsourced_rate_per_watt', 8, 2);
            $table->foreignId('assigned_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('status', 20)->default('active');
            $table->date('start_date');
            $table->date('completed_date')->nullable();
            $table->timestamps();

            $table->index(['status', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
