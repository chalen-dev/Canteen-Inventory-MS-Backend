<?php

namespace App\Console\Commands;

use App\Models\InventoryLog;
use Illuminate\Console\Command;

class UpdateExpiredInventory extends Command
{
    protected $signature = 'inventory:update-expired';
    protected $description = 'Mark inventory logs as unavailable when they expire';

    public function handle()
    {
        $updated = InventoryLog::where('expiry_date', '<', now())
            ->where('is_available', true)
            ->update(['is_available' => false]);

        $this->info("{$updated} inventory logs marked as unavailable due to expiry.");
    }
}
