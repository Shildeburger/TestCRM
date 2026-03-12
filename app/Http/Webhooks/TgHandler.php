<?php

namespace App\Http\Webhooks;

use App\Models\Contact;
use App\Models\TelegramChat;
use App\Models\TelegramMessage;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Stringable;
use DefStudio\Telegraph\DTO\User as TelegramUser;
use App\Events\TelegramMessageCreated;


class TgHandler extends WebhookHandler
{
    protected function handleChatMessage(Stringable $text): void //, Функция для обработки входящих сообщений в чатах
    {
            /** @var TelegramUser|null $from */
        $from = $this->message->from(); // объект юзера из Telegram [web:520][web:526]

        $telegramUserId = $from?->id();
        $telegramUsername = $from?->username();

        [$contact, $crmChat] = $this->resolveContactAndChat();

        // авто-заполнение только ID и username
        if ($telegramUserId !== null) {
            $contact->telegram_user_id = $telegramUserId;
        }
        if ($telegramUsername !== null) {
            $contact->telegram_username = $telegramUsername;
        }
        $contact->is_onboarded = true;
        $contact->onboarding_step = null;
        $contact->save();

        $this->storeIncomingMessage($crmChat, $text);
    }

    protected function resolveContactAndChat(): array
    {
        // Telegram-юзер из апдейта
        $from = $this->message->from();   // user object
        $userId = $from?->id();
        $username = $from?->username();

        // 1) Найти или создать TelegraphChat (из пакета)
        /** @var TelegraphChat $telegraphChat */
        $telegraphChat = $this->chat; // текущий чат, уже загружен Telegraph'ом

        $ownerUserId = (int) config('services.telegram.default_contact_owner', 1);

        // 2) Найти или создать Contact по telegram_user_id
        $contact = Contact::firstOrCreate(
            ['telegram_user_id' => $userId],
            [
                'user_id' => $ownerUserId,
                'name'    => $username ? '@'.$username : 'Telegram user '.$userId,
                'telegram_username' => $username,
            ]
        );

        if ($contact->wasRecentlyCreated) {
            // Вызываем событие, которое отправит данные во Vue
            event(new \App\Events\TelegramContactCreated($contact));
        }

        // 3) Найти или создать нашу обёртку TelegramChat
        $crmChat = TelegramChat::firstOrCreate(
            [
                'contact_id'        => $contact->id,
                'telegraph_chat_id' => $telegraphChat->id,
            ],
            [
                'type'           => 'private',
                'chat_title'     => $telegraphChat->name,
                'chat_username'  => $username,
                'chat_external_id' => (string) $telegraphChat->chat_id,
                'is_primary'     => true,
            ]
        );

        return [$contact, $crmChat];
    }

    protected function storeIncomingMessage(TelegramChat $crmChat, Stringable $text): void
    {
        $message = TelegramMessage::create([
            'telegram_chat_id' => $crmChat->id,
            'direction'        => 'incoming',
            'from_role'        => 'client',
            'text'             => $text->toString(),
            'sent_at'          => now(),
        ]);

        $crmChat->last_message_at = now();
        $crmChat->unread_count = $crmChat->unread_count + 1;
        $crmChat->save();

        \Log::info('storeIncomingMessage', ['chat_id' => $crmChat->id]);
        event(new TelegramMessageCreated($message));
    }

    protected function notifyCrmAboutNewContact(Contact $contact, TelegramChat $chat): void
    {
        \Log::info("Новый контакт из Telegram: {$contact->name} (ID: {$contact->id}), чат ID: {$chat->id}");
    }

    public function start(): void
    {
        [$contact, $crmChat] = $this->resolveContactAndChat();
    }
}