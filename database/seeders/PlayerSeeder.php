<?php

namespace Database\Seeders;

use App\Models\Player;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $players = [
            'Bob',
            'Natasha',
            'Marijke',
            'Bart'
        ];

        // Create the players in the DB
        foreach ($players as $player) {
            $newPlayer = new Player();
            $newPlayer->name = $player;
            $newPlayer->save();
        }
    }
}
