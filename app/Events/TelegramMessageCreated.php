<?php

namespace App\Events;

use App\Models\TelegramMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TelegramMessageCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public TelegramMessage $message;

    public function __construct(TelegramMessage $message)
    {
        $this->message = $message;
        \Log::info('Broadcasting TelegramMessageCreated', [
        'id' => $message->id,
        'chat_id' => $message->telegram_chat_id,
        ]);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('telegram.chat.' . $this->message->telegram_chat_id);
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->message->id,
            'chat_id'    => $this->message->telegram_chat_id,
            'text'       => $this->message->text,
            'direction'  => $this->message->direction,
            'from_role'  => $this->message->from_role,
            'sent_at'    => $this->message->sent_at?->toIso8601String(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'TelegramMessageCreated';
    }
}
