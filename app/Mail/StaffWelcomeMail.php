<?php

namespace App\Mail;

use App\Models\Staff;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StaffWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Staff $staff, public string $token)
    {
    }

    public function build(): self
    {
        return $this->subject('Welcome to '.config('app.name'))
            ->view('emails.staff-welcome', [
                'staff' => $this->staff,
                'setPasswordUrl' => route('password.reset', [
                    'token' => $this->token,
                    'email' => $this->staff->email,
                ]),
            ]);
    }
}
