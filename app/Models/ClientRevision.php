<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientRevision extends Model
{
    protected $fillable = ['client_id', 'user_id', 'changes', 'alasan'];

    protected $casts = [
        'changes' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}