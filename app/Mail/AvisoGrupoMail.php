<?php

namespace App\Mail;

use App\Models\Aviso;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AvisoGrupoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Aviso $aviso) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[{$this->aviso->grupo->nome}] {$this->aviso->titulo}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.aviso-grupo',
        );
    }
}
