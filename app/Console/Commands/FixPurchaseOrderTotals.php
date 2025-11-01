<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use Illuminate\Console\Command;

class FixPurchaseOrderTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:purchase-order-totals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate and fix total_amount for all purchase orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing purchase order totals...');

        $purchaseOrders = PurchaseOrder::with('items')->get();
        $count = 0;

        foreach ($purchaseOrders as $po) {
            $oldTotal = $po->total_amount;
            $po->updateTotalAmount();
            $newTotal = $po->fresh()->total_amount;

            if ($oldTotal != $newTotal) {
                $this->line("PO {$po->po_number}: {$oldTotal} -> {$newTotal}");
                $count++;
            }
        }

        $this->info("Fixed {$count} purchase orders.");

        return Command::SUCCESS;
    }
}
