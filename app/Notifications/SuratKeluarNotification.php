<?php

namespace App\Notifications;

use App\Models\ClientExitDocument;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class SuratKeluarNotification extends Notification
{
    public const DIAJUKAN = 'diajukan';
    public const DISETUJUI = 'disetujui';
    public const DITOLAK = 'ditolak';

    public function __construct(
        protected ClientExitDocument $document,
        protected string $event
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $client = $this->document->client;
        $type = $this->document->type === 'rujukan' ? 'Surat rujukan' : 'Surat keluar';

        $message = match ($this->event) {
            self::DIAJUKAN => "{$type} untuk {$client->nama_lengkap} diajukan Admin RPS dan menunggu persetujuan.",
            self::DISETUJUI => "{$type} untuk {$client->nama_lengkap} telah disetujui Kepala RPS.",
            self::DITOLAK => "{$type} untuk {$client->nama_lengkap} ditolak Kepala RPS. Catatan: "
                .Str::limit((string) $this->document->catatan_penolakan, 120)
                .' Mohon diperbaiki dan diajukan ulang.',
        };

        return [
            'document_id' => $this->document->id,
            'client_id' => $client->id,
            'event' => $this->event,
            'message' => $message,
            'url' => $this->event === self::DIAJUKAN
                ? route('exit-documents.show', $this->document)
                : route('exit-documents.mine'),
        ];
    }
}