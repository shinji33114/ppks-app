<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ASSESSMENT = 'assessment';
    public const STATUS_PENANGANAN = 'penanganan';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DIRUJUK = 'dirujuk';

    protected $fillable = [
        'status',
        'tanggal_masuk',
        'sumber_aduan',
        'tkp_kronologi',
        'kondisi_fisik',
        'problem_awal',
        'nama_lengkap',
        'nama_alias',
        'foto_klien',
        'data_dukung',
        'is_archived',
        'archived_at',
        'archived_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
            'tanggal_keluar' => 'date',
            'is_archived' => 'boolean',
            'archived_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_DRAFT => 'Terregistrasi / Menunggu Assessment',
            self::STATUS_ASSESSMENT => 'Siap Ditangani',
            self::STATUS_PENANGANAN => 'Sedang Ditangani',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_DIRUJUK => 'Dirujuk',
        ][$this->status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return in_array($this->status, [self::STATUS_SELESAI, self::STATUS_DIRUJUK], true)
            ? 'success'
            : 'pending';
    }
    public function assessment()
    {
        return $this->hasOne(ClientAssessment::class);
    }
    public function isStageOneEditable(): bool
{
    // Sudah diarsipkan atau sudah lewat dari tahap draft — tidak bisa diedit lagi
    if ($this->is_archived || $this->status !== self::STATUS_DRAFT) {
        return false;
    }

    // Peksos sudah mulai assessment — data Tahap 1 otomatis terkunci
    if ($this->locked_at !== null) {
        return false;
    }

    // Sudah lewat 24 jam sejak diregistrasi, meski Peksos belum mulai assessment
    if ($this->created_at->addDay()->isPast()) {
        return false;
    }

    return true;
}
    public function followups()
{
    return $this->hasMany(ClientFollowup::class)->latest();
}

    public function exitDocuments()
    {
        return $this->hasMany(ClientExitDocument::class);
    }

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }
}