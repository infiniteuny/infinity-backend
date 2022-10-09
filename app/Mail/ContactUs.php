<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUs extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.contact-us')
            ->from($this->data['email'], $this->data['name'] . ' (via Contact Us Form)')
            ->subject($this->data['subject'])
            ->with([
                'name' => $this->data['name'],
                'email' => $this->data['email'],
                'messages' => $this->data['message'],
            ]);
    }
}
