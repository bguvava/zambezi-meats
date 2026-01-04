<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent to customer when they cancel their support ticket.
 *
 * @requirement NOTIF-001 Email confirmation for ticket cancellation
 */
class TicketCancellationConfirmation extends Notification implements ShouldQueue
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Support Ticket Cancelled - #' . $this->ticket->id)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your support ticket has been successfully cancelled.')
            ->line('**Ticket ID:** #' . $this->ticket->id)
            ->line('**Subject:** ' . $this->ticket->subject)
            ->line('**Cancelled At:** ' . $this->ticket->cancelled_at->format('F j, Y, g:i a'))
            ->line('If you need further assistance, please don\'t hesitate to create a new support ticket.')
            ->action('Create New Ticket', url('/customer/support'))
            ->line('Thank you for choosing Zambezi Meats!');
    }
}
