<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'company',
        'country',
        'job_title',
        'telegram_user_id',
        'telegram_username',
        'notes',
        'is_favorite'
    ];

    protected $casts = [
        'is_favorite' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

     public function telegramChats(): HasMany
    {
        return $this->hasMany(TelegramChat::class);
    }

    public function primaryTelegramChat()
    {
        return $this->hasOne(TelegramChat::class)->where('is_primary', true);
    }
}
