<?php

namespace App\Mail;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GroupInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Group $group,
        public string $acceptUrl,
        public string $roleLabel,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Einladung zur Gruppe :name', ['name' => $this->group->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.group-invitation',
        );
    }
}
