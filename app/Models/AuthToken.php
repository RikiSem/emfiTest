<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AuthToken extends Model
{
    protected $fillable = [
        'access_token',
        'refresh_token',
        'expires_at'
    ];

    public function isActive(): bool
    {
        return $this->expires_at > Carbon::now()->timestamp;
    }
}
