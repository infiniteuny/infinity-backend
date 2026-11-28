<?php

namespace Tests\Unit\Mail;

use App\Mail\ContactUs;
use Tests\TestCase;

class ContactUsTest extends TestCase
{
    public function test_declares_data_property_and_builds_subject_and_reply_to(): void
    {
        $mailable = new ContactUs([
            'subject' => 'Partnership Inquiry',
            'name' => 'Alice',
            'email' => 'alice@infiniteuny.id',
            'message' => 'Hello INFINITE',
        ]);

        $built = $mailable->build();

        $this->assertSame('Partnership Inquiry', $built->subject);
        $this->assertSame('alice@infiniteuny.id', $mailable->data['email']);
    }
}
