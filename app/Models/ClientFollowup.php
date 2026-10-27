<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientFollowup extends Model
{
    protected $fillable = ['client_id', 'user_id', 'tanggal_tindakan', 'catatan_tindakan'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}