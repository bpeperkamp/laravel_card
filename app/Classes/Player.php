<?php

namespace App\Classes;

class Player
{
    private array $playerCards;
    private string $playerName;

    public function setPlayerName(string $playerName): void
    {
        $this->playerName = $playerName;
    }

    public function getPlayerName(): string
    {
        return $this->playerName;
    }

    public function setPlayerCard($card): void
    {
        $this->playerCards[] = $card;
    }

    public function getPlayerCards(): array
    {
        return $this->playerCards;
    }
}
