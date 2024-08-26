<?php

namespace App\Models;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $chat_id
 * @property string $lat
 * @property string $lon
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Location forChat(\DefStudio\Telegraph\Models\TelegraphChat $chat)
 * @method static Builder|Location newModelQuery()
 * @method static Builder|Location newQuery()
 * @method static Builder|Location query()
 * @method static Builder|Location whereChatId($value)
 * @method static Builder|Location whereCreatedAt($value)
 * @method static Builder|Location whereId($value)
 * @method static Builder|Location whereLat($value)
 * @method static Builder|Location whereLon($value)
 * @method static Builder|Location whereUpdatedAt($value)
 * @property string $name
 * @method static Builder|Location whereName($value)
 * @mixin \Eloquent
 */
class Location extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_writable' => 'boolean',
    ];

    public function scopeForChat(Builder $builder, TelegraphChat $chat): Builder
    {
        return $builder->where('chat_id', $chat->id);
    }
}
