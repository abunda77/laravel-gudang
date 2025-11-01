<?php

namespace App\Console\Commands;

use App\Models\SalesOrder;
use Illuminate\Console\Command;

class FixSalesOrderTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:sales-order-totals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate and fix total_amount for all sales orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing sales order totals...');

        $salesOrders = SalesOrder::with('items')->get();
        $count = 0;

        foreach ($salesOrders as $so) {
            $oldTotal = $so->total_amount;
            $so->updateTotalAmount();
            $newTotal = $so->fresh()->total_amount;

            if ($oldTotal != $newTotal) {
                $this->line("SO {$so->so_number}: {$oldTotal} -> {$newTotal}");
                $count++;
            }
        }

        $this->info("Fixed {$count} sales orders.");

        return Command::SUCCESS;
    }
}
