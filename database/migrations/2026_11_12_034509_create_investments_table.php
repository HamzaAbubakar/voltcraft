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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->string('investor_name');
            $table->string('contact_info')->nullable();
            $table->decimal('principal_amount', 12, 2);
            $table->date('received_date');
            $table->date('maturity_date');
            $table->decimal('profit_share_percentage', 5, 2);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->decimal('profit_paid', 12, 2)->default(0);
            $table->date('settlement_date')->nullable();
            $table->foreignId('initial_cash_transaction_id')
                ->unique()
                ->constrained('cash_transactions')
                ->restrictOnDelete();
            $table->foreignId('settlement_cash_transaction_id')
                ->nullable()
                ->unique()
                ->constrained('cash_transactions')
                ->restrictOnDelete();
            $table->timestamps();

            $table->index(['status', 'maturity_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
