<?php

namespace App\Filament\Pages;

use App\Models\GeneratedReport;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Reports extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string | \UnitEnum | null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.reports';

    protected static ?string $title = 'Generate Reports';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateMonthlyReport')
                ->label('Generate Sekarang')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->fillForm([
                    'report_type' => 'sales',
                    'month' => now()->subMonth()->startOfMonth(),
                ])
                ->form([
                    Select::make('report_type')
                        ->label('Tipe Report')
                        ->options([
                            'sales' => 'Sales Report',
                            'purchase' => 'Purchase Report',
                            'stock_valuation' => 'Stock Valuation Report',
                            'low_stock' => 'Low Stock Report',
                        ])
                        ->required()
                        ->helperText('Pilih tipe report yang akan di-generate'),
                    DatePicker::make('month')
                        ->label('Bulan')
                        ->displayFormat('F Y')
                        ->format('Y-m-01')
                        ->maxDate(now())
                        ->required()
                        ->helperText('Pilih bulan untuk report'),
                ])
                ->action(function (array $data): void {
                    $user = Auth::user();
                    if (!$user) {
                        Notification::make()
                            ->title('Authentication Required')
                            ->body('You must be logged in to generate reports.')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        $month = Carbon::parse($data['month']);
                        $reportType = $data['report_type'];
                        
                        // Generate report langsung tanpa queue
                        $this->generateReport($user, $month, $reportType);

                        Notification::make()
                            ->title('Report Berhasil Di-generate')
                            ->body('Report telah berhasil dibuat dan siap didownload.')
                            ->success()
                            ->icon('heroicon-o-check-circle')
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal Generate Report')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->modalHeading('Generate Monthly Report')
                ->modalDescription('Generate report bulanan. Report akan langsung tersedia untuk didownload setelah proses selesai.')
                ->modalSubmitActionLabel('Generate Report')
                ->modalWidth('md'),
        ];
    }

    protected function generateReport($user, Carbon $month, string $reportType): void
    {
        $reportService = app(ReportService::class);
        
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        // Generate the appropriate report based on type
        $reportData = match ($reportType) {
            'sales' => $reportService->getSalesReport([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]),
            'purchase' => $reportService->getPurchaseReport([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]),
            'stock_valuation' => $reportService->getStockValuationReport(),
            'low_stock' => [
                'items' => $reportService->getLowStockProducts(),
            ],
            default => throw new \InvalidArgumentException("Invalid report type: {$reportType}"),
        };

        // Generate PDF
        $pdf = Pdf::loadView("reports.monthly-{$reportType}", [
            'reportData' => $reportData,
            'month' => $month,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now(),
            'generatedBy' => $user->name,
        ]);

        // Store the PDF
        $filename = sprintf(
            'reports/%s/%s-%s.pdf',
            $reportType,
            $reportType,
            $month->format('Y-m')
        );

        Storage::put($filename, $pdf->output());

        // Save to database
        $reportTypeLabels = [
            'sales' => 'Sales Report',
            'purchase' => 'Purchase Report',
            'stock_valuation' => 'Stock Valuation Report',
            'low_stock' => 'Low Stock Report',
        ];

        GeneratedReport::create([
            'user_id' => $user->id,
            'report_type' => $reportType,
            'report_name' => $reportTypeLabels[$reportType] . ' - ' . $month->format('F Y'),
            'file_path' => $filename,
            'report_month' => $month,
            'status' => 'completed',
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(GeneratedReport::query()->with('user')->latest())
            ->columns([
                Tables\Columns\TextColumn::make('report_name')
                    ->label('Nama File')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('report_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'sales' => 'success',
                        'purchase' => 'info',
                        'stock_valuation' => 'warning',
                        'low_stock' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sales' => 'manual',
                        'purchase' => 'manual',
                        'stock_valuation' => 'manual',
                        'low_stock' => 'manual',
                        default => 'manual',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn() => 'success'),
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Ukuran')
                    ->getStateUsing(fn($record) => $record->file_size),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->actions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('warning')
                    ->action(function (GeneratedReport $record) {
                        $fullPath = Storage::path($record->file_path);
                        
                        if (!file_exists($fullPath)) {
                            Notification::make()
                                ->danger()
                                ->title('File Tidak Ditemukan')
                                ->body('File report tidak dapat ditemukan di: ' . $record->file_path)
                                ->send();
                            return null;
                        }

                        return response()->download(
                            $fullPath,
                            $record->report_name . '.pdf'
                        );
                    }),
                DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Report')
                    ->modalDescription('Apakah Anda yakin ingin menghapus report ini?')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->successNotificationTitle('Report Dihapus')
                    ->before(function (GeneratedReport $record) {
                        $record->deleteFile();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->modalHeading('Hapus Report Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus report yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->successNotificationTitle('Report Dihapus')
                        ->before(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->deleteFile();
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum Ada Report')
            ->emptyStateDescription('Generate report pertama Anda dengan klik tombol "Generate Sekarang" di atas.')
            ->emptyStateIcon('heroicon-o-document-chart-bar');
    }
}
