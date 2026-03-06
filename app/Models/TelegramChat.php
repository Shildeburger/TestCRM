<?php

namespace App\Models;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelegramChat extends Model
{
    protected $fillable = [
        'contact_id',
        'telegraph_chat_id',
        'type',
        'chat_title',
        'chat_username',
        'chat_external_id',
        'is_primary',
        'last_message_at',
        'unread_count',
    ];

    protected $casts = [
        'is_primary'      => 'boolean',
        'last_message_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function telegraphChat(): BelongsTo
    {
        return $this->belongsTo(TelegraphChat::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TelegramMessage::class);
    }
}