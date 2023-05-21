<?php

namespace Database\Seeders;

use App\Models\Card;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    private array $stack;

    const suits = [
        '♦',
        '♥',
        '♣',
        '♠'
    ];

    const faces = [
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        '10',
        'J',
        'Q',
        'K'
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::suits as $suit) {
            foreach (self::faces as $face) {
                $this->stack[] = ['suit' => $suit, 'face' => $face];
            }
        }
        foreach ($this->stack as $card) {
            $cardModel = new Card();
            $cardModel->suit = $card['suit'];
            $cardModel->face = $card['face'];
            $cardModel->save();
        }
    }
}
