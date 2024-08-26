<?php

namespace App\Service\WebhookHandler;

use App\Service\Location\LocationRepository;
use App\Service\WeatherApi\WeatherApiClient;
use DefStudio\Telegraph\Handlers\WebhookHandler as AbstractWebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use DefStudio\Telegraph\Telegraph;
use Illuminate\Support\Stringable;

class WebhookHandler extends AbstractWebhookHandler
{
    public function __construct(
        private LocationRepository $locationRepository,
        private WeatherApiClient $weatherApiClient,
    ) {
        parent::__construct();
    }

    public function start()
    {
        $this->chat->message(__('telegram_bot.welcome'))
            ->keyboard(fn(Keyboard $keyboard) => $keyboard
                ->button('Главное меню')
                ->action('menu'))->send();
    }

    public function menu()
    {
        $location = $this->locationRepository->forChatFirst($this->chat);
        if ($location) {
            $this->sendFullMenu();
        } else {
            $this->sendRequestLocationMenu();
        }

        if (!is_null($this->callbackQuery)) {
            $this->reply('Готово!');
        }
    }

    public function request_location()
    {
        $this->sendRequestLocationMenu();
        if (!is_null($this->callbackQuery)) {
            $this->reply('Готово!');
        }
    }

    protected function handleChatMessage(Stringable $text): void
    {
        $location = $this->message?->location();
        if (is_null($location)) {
            return;
        }
        $this->locationRepository->saveLocation($this->chat, $location);
        $this->chat->message(__('telegram_bot.location_saved'))->send();
        $this->sendFullMenu();
    }

    public function weather_today()
    {
        $location = $this->locationRepository->forChatFirst($this->chat);
        $weatherForDay = $this->weatherApiClient->today($location);
        $this->messageWithKeyboardMenuSend(
            $this->chat
                ->message(view('today', ['weather' => $weatherForDay])->render())
        );
        $this->reply('');
    }

    public function weather_3d()
    {
        $location = $this->locationRepository->forChatFirst($this->chat);
        $weatherCollection = $this->weatherApiClient->for3d($location);
        $this->messageWithKeyboardMenuSend(
            $this->chat
                ->message(view('3days', ['weatherCollection' => $weatherCollection])->render())
        );
        $this->reply('');
    }

    public function weather_week()
    {
        $location = $this->locationRepository->forChatFirst($this->chat);
        $weatherCollection = $this->weatherApiClient->forWeek($location);
        $this->messageWithKeyboardMenuSend(
            $this->chat
                ->message(view('week', ['weatherCollection' => $weatherCollection])->render())
        );
        $this->reply('');
    }

    private function sendFullMenu(): void
    {
        $this->messageWithKeyboardMenuSend($this->chat->message(__('telegram_bot.menu.header')));
    }

    private function messageWithKeyboardMenuSend(Telegraph $message): void
    {
        $message->keyboard(fn(Keyboard $keyboard) => $keyboard
            ->row([
                Button::make(__('telegram_bot.menu.change_location'))->action('request_location'),
                Button::make(__('telegram_bot.menu.weather_today'))->action('weather_today'),
            ])
            ->row([
                Button::make(__('telegram_bot.menu.weather_3d'))->action('weather_3d'),
                Button::make(__('telegram_bot.menu.weather_week'))->action('weather_week'),
            ])
        )
            ->send();
    }

    private function sendRequestLocationMenu(): void
    {
        $this->chat->message(__('telegram_bot.request_location'))
            ->replyKeyboard(fn(ReplyKeyboard $replyKeyboard) => $replyKeyboard
                ->button(__('telegram_bot.menu.send_location'))->requestLocation())
            ->send();
    }
}
