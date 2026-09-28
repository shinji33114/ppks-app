<?php

namespace App\Notifications;

use App\Models\Client;
use Illuminate\Notifications\Notification;

class KlienBaruMasuk extends Notification
{
    public function __construct(protected Client $client)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'client_id' => $this->client->id,
            'nama_lengkap' => $this->client->nama_lengkap,
            'message' => "Klien baru \"{$this->client->nama_lengkap}\" perlu di-assessment.",
            'url' => route('clients.show', $this->client->id),
        ];
    }
}