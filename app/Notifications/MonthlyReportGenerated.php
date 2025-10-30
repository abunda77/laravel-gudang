<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class MonthlyReportGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $filePath,
        public string $reportType,
        public Carbon $month
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $reportTypeName = ucwords(str_replace('_', ' ', $this->reportType));
        $monthName = $this->month->format('F Y');

        return (new MailMessage)
            ->subject("Monthly {$reportTypeName} Report - {$monthName}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your monthly {$reportTypeName} report for {$monthName} has been generated successfully.")
            ->line('You can download the report from the system or use the link below.')
            ->action('Download Report', url('/storage/' . $this->filePath))
            ->line('Thank you for using our Warehouse Management System!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Monthly Report Generated',
            'message' => sprintf(
                'Your %s report for %s is ready',
                ucwords(str_replace('_', ' ', $this->reportType)),
                $this->month->format('F Y')
            ),
            'file_path' => $this->filePath,
            'report_type' => $this->reportType,
            'month' => $this->month->format('Y-m'),
            'download_url' => Storage::url($this->filePath),
        ];
    }
}
