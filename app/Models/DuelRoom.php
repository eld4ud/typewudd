<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DuelRoom extends Model
{
    protected $fillable = [
        'code',
        'host_id',
        'guest_id',
        'typing_content_id',
        'difficulty',
        'language',
        'duration',
        'status',
        'host_ready',
        'guest_ready',
        'starts_at',
        'ends_at',
        'host_progress',
        'guest_progress',
        'host_result',
        'guest_result',
    ];

    protected function casts(): array
    {
        return [
            'host_ready' => 'boolean',
            'guest_ready' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'host_result' => 'array',
            'guest_result' => 'array',
        ];
    }

    public function content()
    {
        return $this->belongsTo(TypingContent::class, 'typing_content_id');
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function guest()
    {
        return $this->belongsTo(User::class, 'guest_id');
    }
}
