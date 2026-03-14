<?php

use App\Enums\InventoryStatus;
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
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')
                ->constrained('menu_items')
                ->cascadeOnDelete()
                ->restrictOnDelete();
            $table->integer('quantity_in_stock');
            $table->date('date_acquired');
            $table->date('expiry_date');
            $table->enum('inventory_status', InventoryStatus::cases())
                ->default(InventoryStatus::IN_STOCK);
            $table->boolean('is_available')
                ->default(false);
            $table->boolean('is_archived')
                ->default(false);
            $table->text('description')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
