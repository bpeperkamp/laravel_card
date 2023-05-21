<?php

namespace App\Classes;

class Cards
{
    private array $deck;
    private array $newDeck;

    const suitCharacters = [
        '&diams;',
        '&hearts;',
        '&clubs;',
        '&spades;'
    ];

    const suitNames = [
        // <fg /> is for color formatting in a terminal application
        '<fg=white;bg=red>Diamonds</>',
        '<fg=white;bg=red>Hearts</>',
        '<fg=default;bg=black>Clubs</>',
        '<fg=default;bg=black>Spades</>'
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
        'Jack',
        'Queen',
        'King'
    ];

    public function getCompleteDeck(): array
    {
        // Create 4 sets of cards containing the right cards (card 1-10 + 1 Jack/1 Queen/1 King)
        foreach (self::suitNames as $suit) {
            foreach (self::faces as $face) {
                $this->deck[] = $face . " - " . $suit;
            }
        }

        return $this->deck;
    }

    public function toArray(): array
    {
        return (array)$this;
    }

    public function removeFromDeck($removedCard): array
    {
        $deck = array_diff($this->deck, [$removedCard]);
        $this->newDeck = $deck;

        return $this->newDeck;
    }

    public function setNewDeck($newDeck): void
    {
        $this->newDeck = $newDeck;
    }

    public function getNewDeck(): array
    {
        return $this->newDeck;
    }

    public function setDeck($deck): void
    {
        $this->deck = $deck;
    }

    public function getDeck(): array
    {
        if (empty($this->deck)) {
            return $this->getCompleteDeck();
        } else {
            return $this->deck;
        }
    }
}
