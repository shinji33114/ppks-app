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

    /** Daftar Kategori PPKS, dipisah dari problem_awal (field 6 dokumen = teks bebas)
     *  supaya tetap bisa dipakai untuk grafik breakdown di halaman Statistik. */
    public const KATEGORI_PPKS = [
        'Lansia Terlantar', 'Anak Jalanan', 'Anak Terlantar', 'Disabilitas Terlantar',
        'Korban Kekerasan', 'Gelandangan / Pengemis', 'ODGJ Terlantar', 'Lainnya',
    ];

    protected $fillable = [
        'status',
        'tanggal_masuk',
        'sumber_aduan',
        'tkp_kronologi',
        'kondisi_fisik',
        'problem_awal',
        'kategori_ppks',
        'nama_lengkap',
        'nama_alias',
        'foto_klien',
        'data_dukung',
        'is_archived',
        'archived_at',
        'archived_by',
        // A5: kolom ini sebelumnya tidak ada di $fillable sehingga update() diam-diam membuangnya
        'locked_at',
        'rujukan',
        'tanggal_keluar',
        'no_hp_penanggung_jawab',
        'status_akhir',
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
    // C3: nama status disamakan persis dengan diagram alur di dokumen panduan.
    return [
        self::STATUS_DRAFT => 'Draft / Diterima',
        self::STATUS_ASSESSMENT => 'Proses Assessment',
        self::STATUS_PENANGANAN => 'Dalam Penanganan',
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

    // Admin sudah mulai assessment — data Tahap 1 otomatis terkunci
    if ($this->locked_at !== null) {
        return false;
    }

    // Sudah lewat 24 jam sejak diregistrasi, meski assessment belum dimulai
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

    public function revisions()
    {
        return $this->hasMany(ClientRevision::class)->latest();
    }

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }
}