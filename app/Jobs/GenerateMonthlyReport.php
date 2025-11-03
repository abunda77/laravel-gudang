<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\MonthlyReportGenerated;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateMonthlyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public Carbon $month,
        public string $reportType = 'sales'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ReportService $reportService): void
    {
        $user = User::find($this->userId);
        if (!$user) {
            throw new \Exception("User not found: {$this->userId}");
        }

        $startDate = $this->month->copy()->startOfMonth();
        $endDate = $this->month->copy()->endOfMonth();

        // Generate the appropriate report based on type
        $reportData = match ($this->reportType) {
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
            default => throw new \InvalidArgumentException("Invalid report type: {$this->reportType}"),
        };

        // Generate PDF
        $pdf = Pdf::loadView("reports.monthly-{$this->reportType}", [
            'reportData' => $reportData,
            'month' => $this->month,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now(),
            'generatedBy' => $user->name,
        ]);

        // Store the PDF
        $filename = sprintf(
            'reports/%s/%s-%s.pdf',
            $this->reportType,
            $this->reportType,
            $this->month->format('Y-m')
        );

        Storage::put($filename, $pdf->output());

        // Notify the user
        $user->notify(new MonthlyReportGenerated(
            $filename,
            $this->reportType,
            $this->month
        ));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Log the failure or notify administrators
        Log::error('Monthly report generation failed', [
            'user_id' => $this->userId,
            'month' => $this->month->format('Y-m'),
            'report_type' => $this->reportType,
            'error' => $exception->getMessage(),
        ]);
    }
}
