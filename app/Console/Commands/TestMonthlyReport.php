<?php

namespace App\Console\Commands;

use App\Jobs\GenerateMonthlyReport;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class TestMonthlyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:test-monthly 
                            {user_id? : The ID of the user to generate the report for}
                            {--type=sales : The type of report (sales, purchase, stock_valuation, low_stock)}
                            {--month= : The month to generate the report for (Y-m format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test monthly report generation by dispatching a job to the queue';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Get or prompt for user
        $userId = $this->argument('user_id');
        if (!$userId) {
            $users = User::all();
            if ($users->isEmpty()) {
                $this->error('No users found in the database.');
                return self::FAILURE;
            }

            $userChoices = $users->mapWithKeys(function ($user) {
                return [$user->id => "{$user->name} ({$user->email})"];
            })->toArray();

            $userId = $this->choice('Select a user', $userChoices, $users->first()->id);
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return self::FAILURE;
        }

        // Get report type
        $type = $this->option('type');
        $validTypes = ['sales', 'purchase', 'stock_valuation', 'low_stock'];
        if (!in_array($type, $validTypes)) {
            $this->error("Invalid report type. Must be one of: " . implode(', ', $validTypes));
            return self::FAILURE;
        }

        // Get month
        $monthInput = $this->option('month');
        if ($monthInput) {
            try {
                $month = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
            } catch (\Exception $e) {
                $this->error('Invalid month format. Use Y-m format (e.g., 2024-01)');
                return self::FAILURE;
            }
        } else {
            $month = now()->subMonth()->startOfMonth();
        }

        // Dispatch the job
        $this->info("Dispatching monthly {$type} report job...");
        $this->info("User: {$user->name} ({$user->email})");
        $this->info("Month: {$month->format('F Y')}");

        GenerateMonthlyReport::dispatch($user, $month, $type);

        $this->info('✓ Job dispatched successfully!');
        $this->line('');
        $this->comment('The report will be generated in the background.');
        $this->comment('Run the queue worker to process the job:');
        $this->line('  php artisan queue:work');
        $this->line('');
        $this->comment('Or process it immediately:');
        $this->line('  php artisan queue:work --once');

        return self::SUCCESS;
    }
}
