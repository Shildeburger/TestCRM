<?php

namespace App\Http\Webhooks;

use App\Models\Contact;
use App\Models\TelegramChat;
use App\Models\TelegramMessage;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Stringable;

class TgHandler extends WebhookHandler
{
    protected function handleChatMessage(Stringable $text): void //, Функция для обработки входящих сообщений в чатах
    {
        // 1) Найти/создать контакт и чат
        [$contact, $crmChat] = $this->resolveContactAndChat(); // Метод для поиска или создания контакта
        //  и связанного с ним чата в CRM, возвращающий массив с контактами и чатами. Если контакт не найден,
        //  он будет создан на основе данных из Telegram, а затем будет создан чат, связанный с этим контактом.

        // 2) Если контакт ещё не онборден — продолжить/запустить анкету
        if (! $this->isOnboarded($contact)) {
            $this->handleOnboarding($contact, $crmChat, $text);

            return; // Если контакт не прошёл онбординг, мы обрабатываем его в методе handleOnboarding\
            //  и завершаем выполнение функции, чтобы не обрабатывать входящее сообщение дальше.
        }
        // 3) Обычное входящее сообщение в уже привязанном чате
        $this->storeIncomingMessage($crmChat, $text);
        $this->broadcastIncoming($crmChat);
    }

    protected function resolveContactAndChat(): array
    {
        // Telegram-юзер из апдейта
        $from = $this->message->from();   // user object
        $userId = $from?->id();
        $username = $from?->username();
        $firstName = $from?->firstName();
        $lastName = $from?->lastName();

        // 1) Найти или создать TelegraphChat (из пакета)
        /** @var TelegraphChat $telegraphChat */
        $telegraphChat = $this->chat; // текущий чат, уже загружен Telegraph'ом

        $ownerUserId = (int) config('services.telegram.default_contact_owner', 1);

        $fullName = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));

        // 2) Найти или создать Contact по telegram_user_id
        $contact = Contact::firstOrCreate(
            ['telegram_user_id' => $userId],
            [
                'user_id' => $ownerUserId,
                'name'    => $fullName !== '' ? $fullName : ($username ?? 'Telegram user '.$userId),
                'telegram_username' => $username,
            ]
        );

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

    protected function isOnboarded(Contact $contact): bool
    {
        return (bool) $contact->is_onboarded;
    }

    protected function setOnboardingStep(Contact $contact, string $step): void
    {
        $contact->onboarding_step = $step;
        $contact->save();
    }

    protected function handleOnboarding(Contact $contact, TelegramChat $crmChat, Stringable $text): void
    {
        $step = $contact->onboarding_step ?? 'start';

        switch ($step) {
            case 'start':
                $this->chat->message("Здравствуйте! Давай познакомимся!\nКак вас зовут? (имя)")->send();
                $this->setOnboardingStep($contact, 'ask_first_name');
                break;

            case 'ask_first_name':
                $contact->first_name = $text->toString();
                $contact->save();

                $this->chat->message("Спасибо! Теперь напишите фамилию.")->send();
                $this->setOnboardingStep($contact, 'ask_last_name');
                break;

            case 'ask_last_name':
                $contact->last_name = $text->toString();
                $contact->save();

                $fullName = trim($contact->first_name.' '.$contact->last_name);
                $this->chat->message("Проверим: *{$fullName}* — всё верно? Напиши `да` или `нет`.")->send();
                $this->setOnboardingStep($contact, 'confirm');
                break;

            case 'confirm':
                $answer = mb_strtolower(trim($text->toString()));
                if (in_array($answer, ['да', 'yes', 'y'])) {
                    $contact->is_onboarded = true;
                    $contact->onboarding_step = null;
                    $contact->save();

                    $this->chat->message("Отлично! Я создал вашу карточку в нашей системе. Менеджер скоро ответит.")->send();

                    // здесь можно сгенерировать событие для CRM "новый контакт"
                    $this->notifyCrmAboutNewContact($contact, $crmChat);
                } else {
                    $this->chat->message("Ок, давайте ещё раз. Как вас зовут? (имя)")->send();
                    $this->setOnboardingStep($contact, 'ask_first_name');
                }
                break;
        }
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
    }

    protected function notifyCrmAboutNewContact(Contact $contact, TelegramChat $chat): void
    {
        // Event::dispatch(new NewTelegramContactCreated($contact, $chat));
        // или просто пока лог/почта, если WebSocket ещё не настроен
        \Log::info("Новый контакт из Telegram: {$contact->name} (ID: {$contact->id}), чат ID: {$chat->id}");
    }

    public function start(): void
    {
        [$contact, $crmChat] = $this->resolveContactAndChat();
        $this->handleOnboarding($contact, $crmChat, str('')); // пустой текст как триггер первого шага
    }
}