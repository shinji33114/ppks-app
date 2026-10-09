<?php

namespace App\Notifications;

use App\Models\Client;
use Illuminate\Notifications\Notification;

class AssessmentSelesai extends Notification
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
            'message' => "Assessment klien \"{$this->client->nama_lengkap}\" telah selesai dan siap ditangani.",
            'url' => route('clients.show', $this->client->id),
        ];
    }
}