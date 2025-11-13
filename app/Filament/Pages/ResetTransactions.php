<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Filament\Notifications\Notification;
use BackedEnum;
use UnitEnum;

class ResetTransactions extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrow-path';

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Reset Transactions';

    protected static ?int $navigationSort = 100;

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        return $user && $user->hasRole('super_admin');
    }

    public static function getNavigationLabel(): string
    {
        return 'Reset Transactions';
    }

    protected function getViewData(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Reset Transactions';
    }

    public function getView(): string
    {
        return 'filament.pages.reset-transactions';
    }

    public function resetTransactions(): void
    {
        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Only transaction tables (all master data will be kept)
            $tablesToReset = [
                // Transaction tables (child tables first)
                'invoice_items',
                'invoices',
                'delivery_orders',
                'outbound_operation_items',
                'outbound_operations',
                'inbound_operation_items',
                'inbound_operations',
                'sales_order_items',
                'sales_orders',
                'purchase_order_items',
                'purchase_orders',
                'stock_opname_items',
                'stock_opnames',
                'stock_movements',
                
                // Reports
                'generated_reports',
            ];

            foreach ($tablesToReset as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            Notification::make()
                ->title('Transaksi berhasil direset')
                ->success()
                ->body('Semua data transaksi telah dihapus. Data master (produk, customer, supplier, dll) tetap tersimpan. Semua stok produk sekarang 0.')
                ->send();

        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            Notification::make()
                ->title('Gagal mereset transaksi')
                ->danger()
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->send();
        }
    }
}
