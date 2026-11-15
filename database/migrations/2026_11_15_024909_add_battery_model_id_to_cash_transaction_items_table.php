<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add battery_model_id to cash_transaction_items so battery-sale line
     * items can be linked to a battery model via foreign key instead of
     * matching by item_name string.
     */
    public function up(): void
    {
        Schema::table('cash_transaction_items', function (Blueprint $table): void {
            $table->foreignId('battery_model_id')
                ->nullable()
                ->constrained('battery_models')
                ->nullOnDelete()
                ->after('inventory_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_transaction_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('battery_model_id');
        });
    }
};
