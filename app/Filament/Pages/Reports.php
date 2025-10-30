<?php

namespace App\Filament\Pages;

use App\Jobs\GenerateMonthlyReport;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class Reports extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string | \UnitEnum | null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.reports';

    protected static ?string $title = 'Generate Reports';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateMonthlyReport')
                ->label('Generate Monthly Report')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->form([
                    Select::make('report_type')
                        ->label('Report Type')
                        ->options([
                            'sales' => 'Sales Report',
                            'purchase' => 'Purchase Report',
                            'stock_valuation' => 'Stock Valuation Report',
                            'low_stock' => 'Low Stock Report',
                        ])
                        ->required()
                        ->default('sales')
                        ->helperText('Select the type of report to generate'),
                    DatePicker::make('month')
                        ->label('Month')
                        ->displayFormat('F Y')
                        ->format('Y-m-01')
                        ->default(now()->subMonth()->startOfMonth())
                        ->maxDate(now())
                        ->required()
                        ->helperText('Select the month for the report'),
                ])
                ->action(function (array $data): void {
                    $month = Carbon::parse($data['month']);
                    $reportType = $data['report_type'];

                    // Dispatch the job
                    GenerateMonthlyReport::dispatch(
                        auth()->user(),
                        $month,
                        $reportType
                    );

                    // Show success notification
                    Notification::make()
                        ->title('Report Generation Started')
                        ->body('Your monthly report is being generated. You will be notified when it is ready.')
                        ->success()
                        ->icon('heroicon-o-check-circle')
                        ->send();
                })
                ->modalHeading('Generate Monthly Report')
                ->modalDescription('Generate a comprehensive monthly report. The report will be generated in the background and you will be notified when it is ready.')
                ->modalSubmitActionLabel('Generate Report')
                ->modalWidth('md'),
        ];
    }
}
