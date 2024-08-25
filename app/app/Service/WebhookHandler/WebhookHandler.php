<?php

namespace App\Service\WebhookHandler;

use DefStudio\Telegraph\Handlers\WebhookHandler as AbstractWebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;

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
        $this->chat->message(__('telegram_bot.menu.header'))
            ->keyboard(fn(Keyboard $keyboard) => $keyboard
                ->buttons([
                    Button::make(__('telegram_bot.menu.weather_today'))->action('weather_today'),
                    Button::make(__('telegram_bot.menu.weather_3d'))->action('weather_3d'),
                    Button::make(__('telegram_bot.menu.weather_week'))->action('weather_week'),
                ]))
            ->replyKeyboard(fn(ReplyKeyboard $replyKeyboard) => $replyKeyboard
                ->button(__('telegram_bot.menu.set_location'))->requestLocation())
            ->send();
        if (!is_null($this->callbackQuery)) {
            $this->reply('Готово!');
        }
    }
}
