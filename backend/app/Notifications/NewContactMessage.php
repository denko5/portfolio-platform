<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

/** Tells the site owner about a new enquiry. Queued, so a slow mail server never blocks the visitor. */
class NewContactMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactMessage $message) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->message;

        return (new MailMessage)
            ->subject('New enquiry: '.$message->subject)
            ->replyTo($message->email, $message->name)
            ->line('From: '.$message->name.' ('.$message->email.')')
            ->line('Organisation: '.($message->organisation ?: 'not given'))
            ->line('Type: '.$message->enquiry_type->getLabel())
            ->line('Reference: '.$message->reference)
            ->line(new HtmlString('<pre style="white-space: pre-wrap; font-family: inherit;">'.e($message->message).'</pre>'))
            ->action('Open in the admin', url('/admin/enquiries/'.$message->getKey().'/edit'));
    }
}
