<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Send Notification
 *
 * Database notification for sending reminders and messages.
 * This notification is stored in the database and can be retrieved
 * by the recipient through the API.
 *
 * @package App\Notifications
 */
class SendNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param array<string, mixed> $data Notification data containing:
     *   - 'sender_id': int - ID of the user sending the notification
     *   - 'receiver_id': int - ID of the user receiving the notification
     *   - 'reminder_date': string - Date for the reminder
     *   - 'document_name': string - Name of the related document
     *   - 'message': string - Notification message content
     */
    public function __construct(
        private readonly array $data
    )
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable The notifiable entity
     * @return array<string> Array of delivery channels
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable The notifiable entity
     * @return MailMessage Mail message instance
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable The notifiable entity
     * @return array<string, mixed> Array representation for database storage
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'sender_id' => $this->data['sender_id'] ?? null,
            'receiver_id' => $this->data['receiver_id'] ?? null,
            'reminder_date' => isset($this->data['reminder_date'])
                ? date('Y-m-d', strtotime($this->data['reminder_date']))
                : null,
            'document_name' => $this->data['document_name'] ?? null,
            'message' => $this->data['message'] ?? null,
        ];
    }
}

