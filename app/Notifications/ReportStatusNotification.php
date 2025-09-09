<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportStatusNotification extends Notification
{
    use Queueable;

    public $status;
    public $report;

    public function __construct($status, $report)
    {
        $this->status = $status;
        $this->report = $report;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // both email + database notifications
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Your report has been {$this->status}")
            ->line("Your report titled '{$this->report->title}' has been {$this->status}.")
            ->line('Thank you for contributing!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'report_id' => $this->report->id,
            'title'     => $this->report->title,
            'status'    => $this->status,
        ];
    }
}
