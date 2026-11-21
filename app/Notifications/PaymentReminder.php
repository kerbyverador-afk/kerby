<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReminder extends Notification
{
    public function __construct(public Payment $payment) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment reminder for '.$this->payment->month_year)
            ->view(
                ['emails.payment-reminder', 'emails.payment-reminder-text'],
                $this->reminderData($notifiable)
            );
    }

    public function toArray(User $notifiable): array
    {
        $data = $this->reminderData($notifiable);

        return [
            'title' => 'Payment reminder: '.$data['monthYear'],
            'message' => 'Total due: ₱'.$data['totalDue'].'. Confirmed paid: ₱'.$data['amountPaid'].'. Outstanding balance: ₱'.$data['balance'].'. Due date: '.$data['dueDate'].'.',
            'month_year' => $data['monthYear'],
            'amount_due' => $data['totalDue'],
            'amount_paid' => $data['amountPaid'],
            'balance' => $data['balance'],
            'due_date' => $data['dueDate'],
            'url' => $data['dashboardUrl'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function reminderData(User $notifiable): array
    {
        return [
            'tenantName' => $notifiable->name,
            'monthYear' => $this->payment->month_year,
            'totalDue' => number_format((float) $this->payment->amount, 2),
            'amountPaid' => number_format((float) $this->payment->amount_paid, 2),
            'balance' => number_format((float) $this->payment->balance, 2),
            'dueDate' => $this->payment->due_date?->format('F j, Y') ?? 'Not set',
            'dashboardUrl' => route('tenant.dashboard'),
        ];
    }
}
