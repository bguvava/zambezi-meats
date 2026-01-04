<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when a customer cancels their support ticket.
 *
 * @requirement NOTIF-001 Email notifications for ticket cancellation
 */
class TicketCancelledByCustomerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public SupportTicket $ticket
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Support Ticket Cancelled - #' . $this->ticket->id)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A customer has cancelled their support ticket.')
            ->line('**Ticket ID:** #' . $this->ticket->id)
            ->line('**Subject:** ' . $this->ticket->subject)
            ->line('**Customer:** ' . $this->ticket->user->name . ' (' . $this->ticket->user->email . ')')
            ->line('**Cancelled At:** ' . $this->ticket->cancelled_at->format('F j, Y, g:i a'))
            ->line('The ticket has been marked as cancelled by the customer.')
            ->action('View Ticket', url('/staff/messages'))
            ->line('Thank you for your attention to this matter.');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'subject' => $this->ticket->subject,
            'customer_name' => $this->ticket->user->name,
            'customer_email' => $this->ticket->user->email,
            'cancelled_at' => $this->ticket->cancelled_at->toIso8601String(),
            'message' => 'Customer cancelled support ticket #' . $this->ticket->id,
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'ticket_cancelled';
    }
}
