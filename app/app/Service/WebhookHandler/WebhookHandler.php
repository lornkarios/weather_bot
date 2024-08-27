<?php

namespace App\Service\WebhookHandler;

use App\Models\Location as LocationModel;
use App\Service\Location\LocationRepository;
use App\Service\LocationApi\Dto\Location;
use App\Service\LocationApi\LocationApiClient;
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
        private LocationApiClient $locationApiClient,
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
            $this->sendFullMenu($location);
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
        $telegraphLocation = $this->message?->location();
        $text = $this->message?->text();
        if (is_null($telegraphLocation) && empty($text)) {
            return;
        }
        if (is_null($telegraphLocation)) {
            $location = $this->locationApiClient->findLocation($text);
            if (is_null($location)) {
                $this->chat->message(__('telegram_bot.location_not_found'))->send();
                return;
            }
        } else {
            $location = new Location(
                $telegraphLocation->longitude(),
                $telegraphLocation->latitude(),
                $telegraphLocation->latitude() . ' ' . $telegraphLocation->longitude(),
            );
        }

        $locationModel = $this->locationRepository->saveLocation($this->chat, $location);
        $this->chat->message(__('telegram_bot.location_saved'))->send();
        $this->sendFullMenu($locationModel);
    }

    public function weather_today()
    {
        $location = $this->locationRepository->forChatFirst($this->chat);
        $weatherForDay = $this->weatherApiClient->oneDayFormat($location, 1);
        $this->messageWithKeyboardMenuSend(
            $this->chat
                ->message(view('today', ['weather' => $weatherForDay->first()])->render())
        );
        $this->reply('');
    }

    public function weather_3d()
    {
        $location = $this->locationRepository->forChatFirst($this->chat);
        $weatherCollection = $this->weatherApiClient->oneDayFormat($location, 3);
        $this->messageWithKeyboardMenuSend(
            $this->chat
                ->message(view('3days', ['weatherCollection' => $weatherCollection])->render())
        );
        $this->reply('');
    }

    public function weather_week()
    {
        $location = $this->locationRepository->forChatFirst($this->chat);
        $weatherCollection = $this->weatherApiClient->manyDayFormat($location, 7);
        $this->messageWithKeyboardMenuSend(
            $this->chat
                ->message(view('week', ['weatherCollection' => $weatherCollection])->render())
        );
        $this->reply('');
    }

    public function weather_2weeks()
    {
        $location = $this->locationRepository->forChatFirst($this->chat);
        $weatherCollection = $this->weatherApiClient->manyDayFormat($location, 14);
        $this->messageWithKeyboardMenuSend(
            $this->chat
                ->message(view('week', ['weatherCollection' => $weatherCollection])->render())
        );
        $this->reply('');
    }

    private function sendFullMenu(LocationModel $location): void
    {
        $this->messageWithKeyboardMenuSend(
            $this->chat->message(__('telegram_bot.menu.header', ['location' => $location->name]))
        );
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
            ->row([
                Button::make(__('telegram_bot.menu.weather_2weeks'))->action('weather_2weeks'),
            ])
        )
            ->send();
    }

    private function sendRequestLocationMenu(): void
    {
        $this->chat->message(__('telegram_bot.request_location'))
            ->replyKeyboard(fn(ReplyKeyboard $replyKeyboard) => $replyKeyboard
                ->button(__('telegram_bot.menu.send_location'))->requestLocation()
                ->resize())
            ->send();
    }
}
