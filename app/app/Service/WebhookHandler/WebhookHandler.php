<?php

namespace App\Service\WebhookHandler;

use DefStudio\Telegraph\DTO\InlineQuery;
use DefStudio\Telegraph\DTO\User;
use DefStudio\Telegraph\Handlers\WebhookHandler as AbstractWebhookHandler;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Stringable;

class WebhookHandler extends AbstractWebhookHandler
{
    public function start()
    {
        $this->chat->message(__('telegram_bot.welcome'))
            ->keyboard(fn(Keyboard $keyboard) => $keyboard
                ->button('Главное меню')
                ->action('menu'))->send();
    }

    public function menu()
    {
        $this->chat->message('Меню!')->send();
    }

    protected function handleChatMemberJoined(User $member): void
    {
        // .. do nothing
    }

    protected function handleChatMemberLeft(User $member): void
    {
        // .. do nothing
    }

    protected function handleChatMessage(Stringable $text): void
    {
        // .. do nothing
    }

    protected function handleInlineQuery(InlineQuery $inlineQuery): void
    {
        // .. do nothing
    }
}
