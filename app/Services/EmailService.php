<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\GenericNotificationMail;

class EmailService
{
    /**
     * Send a transactional or notification email to a user.
     */
    public function sendTransactionalEmail(User $user, string $subject, string $view, array $data = [])
    {
        // Check local unsubscribe/consent rules first
        if ($user->unsubscribed) {
            return false;
        }

        // Dispatch via Laravel's native Mail facade (routed through Brevo SMTP currently)
        Mail::to($user->email)->send(new GenericNotificationMail($subject, $view, $data));

        return true;
    }
}