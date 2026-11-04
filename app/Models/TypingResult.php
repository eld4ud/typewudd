<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypingResult extends Model
{
    protected $fillable = [
        'user_id',
        'typing_content_id',
        'mode',
        'difficulty',
        'language',
        'input_method',
        'target',
        'elapsed_ms',
        'total_chars',
        'correct_chars',
        'errors',
        'wpm',
        'raw_wpm',
        'accuracy',
        'correct_words',
        'consistency',
        'is_competitive',
    ];

    protected function casts(): array
    {
        return ['is_competitive' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function content()
    {
        return $this->belongsTo(TypingContent::class, 'typing_content_id');
    }
}
