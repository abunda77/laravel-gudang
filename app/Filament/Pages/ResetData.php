<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class ResetData extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-trash';

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Reset Data';

    protected static ?int $navigationSort = 99;

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        return $user && $user->hasRole('super_admin');
    }

    public static function getNavigationLabel(): string
    {
        return 'Reset Data';
    }

    protected function getViewData(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Reset Data';
    }

    public function getView(): string
    {
        return 'filament.pages.reset-data';
    }

    public function resetAllData(): void
    {
        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Daftar tabel yang akan direset (kecuali users dan tabel sistem)
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
                
                // Master data
                'product_variants',
                'products',
                'product_categories',
                'customers',
                'suppliers',
                'drivers',
                'vehicles',
                
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
                ->title('Data berhasil direset')
                ->success()
                ->body('Semua data telah dihapus kecuali data user.')
                ->send();

        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            Notification::make()
                ->title('Gagal mereset data')
                ->danger()
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->send();
        }
    }
}
