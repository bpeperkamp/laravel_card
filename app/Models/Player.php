<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Player extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name'
    ];

    /**
     * Get the cards for the player
     * @return HasMany
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Get the cards for the player in random order
     * @return HasMany
     */
    public function cardsInRandomOrder(): HasMany
    {
        return $this->hasMany(Card::class)->inRandomOrder();
    }

    /**
     * @param Card $card
     * @return false|mixed
     */
    public function hasSimilarCard(Card $card): mixed
    {
        foreach ($this->cards as $playerCard) {
            if ($playerCard->suit == $card->suit or $playerCard->face == $card->face) {
                $cardShouldBePlayed = $playerCard;
                break;
            }
        }
        return $cardShouldBePlayed ?? false;
    }
}
