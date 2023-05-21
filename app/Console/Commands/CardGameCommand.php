<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Models\Player;
use icanhazstring\SymfonyConsoleSpinner\SpinnerProgress;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Rahul900Day\LaravelConsoleSpinner\Spinner;
use Symfony\Component\Console\Helper\Table;

class CardGameCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'play:cards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Play the game!';

    private int $counter = 0;

    public function createAndSeedDB(): void
    {
        // Migrate and seed the database (file)
        $this->line(now() . ' - <fg=white>Migrating the database</>');
        Artisan::call('migrate:fresh');
        $this->line(now() . ' - <fg=white>Seeding the database</>');
        Artisan::call('db:seed');
        $this->line(now() . ' - <fg=white>Done!</>');
    }

    /*
     * Assign the cards to players and return in the dealt order
     */
    public function assignCardsToPlayers(): Collection
    {
        // Get all (4) players
        $players = Player::all();

        // Reset the cards to be assigned to nobody
        foreach (Card::all() as $card) {
            $card->player_id =  null;
            $card->save();
        }

        // Assign 7 random cards for each player
        foreach ($players as $player) {
            for ($count = 0; $count <= 6; $count++) {
                $card = Card::inRandomOrder()->whereNull('player_id')->first();

                $player->cards()->save($card);

                // Update the card to be unavailable for new assignment
                $card->player_id = $player->id;
                $card->save();
            }

            $player->setRelation('cards', $player->cardsInRandomOrder);
        }

        return $players;
    }

    /*
     * Show a (fake) spinner, pretending some heavy calculation
     */
    public function showSpinner($time = 2500): void
    {
        $spinner = new Spinner($this->output);
        for($i = 0; $i < 100; $i++) {
            usleep($time);
            $spinner->advance();
        }
        $spinner->finish();
        $this->newLine();
    }

    // Check if a card is red, just for layout/visual purpose
    public function isRed($card): bool
    {
        return match ($card->suit) {
            '♦', '♥' => true,
            default => false
        };
    }

    /*
     * Show the dealt cards in a table
     */
    public function showAssignedCardsTable($players): void
    {
        $tableDealt = new Table($this->output);

        $tableDealt->setHeaders([
            'Player:', 'Dealt cards:'
        ]);

        // Show the cards assigned to players
        foreach ($players as $player) {
            $cardString = '';
            foreach ($player->cards as $card) {
                $cardString .= ($this->isRed($card) ? '<fg=red>' . $card->suit . '</>' : $card->suit) . ' ' . $card->face . ' ';
            }
            $tableDealt->addRow([$player->name, $cardString]);
        }

        $tableDealt->render();
    }

    /*
     * Show remaining card info
     */
    public function remainingCards()
    {
        $remainingCards = Card::whereNotNull('player_id')->inRandomOrder()->get();

        $this->newLine();
        $this->line(now() . ' - <fg=green>Cards remaining: </>' . $remainingCards->count());
    }

    /**
     * A simple notification
     */
    public function startGameNotification(): void
    {
        $this->line(now() . ' - <fg=green>Starting the game!</>');
        $this->newLine();
    }

    public function addCardToPlayer($player, $card): void
    {
        $player->cards()->save($card);
    }

    protected function stop() {
        exit;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->createAndSeedDB();

        $playersWithCards = $this->assignCardsToPlayers();

        $this->showSpinner();

        $this->startGameNotification();

        $this->showAssignedCardsTable($playersWithCards);

        $this->remainingCards();

        $remainingCards = Card::whereNotNull('player_id')->inRandomOrder()->get();
        $remainingCards = collect($remainingCards);

        $topCard = $remainingCards->first();

        $this->newLine();
        $this->line(now() . ' - <fg=green>The top card is:</> ' . ($this->isRed($topCard) ? '<fg=red>'. $topCard->suit . '</>' : $topCard->suit) . ' ' . $topCard->face);
        $this->newLine();

        $this->counter = count($remainingCards);

        while ($this->counter > 0) {
            foreach ($playersWithCards as $player) {

                $playedCard = $player->hasSimilarCard($topCard);

                if ($playedCard) {
                    $this->line(now() . ' - ' . $player->name . ' plays: ' . ($this->isRed($playedCard) ? '<fg=red>' . $playedCard->suit . '</>' : $playedCard->suit) . ' ' . $playedCard->face);

                    // Remove the played card from the player
                    $id = $playedCard->id;
                    $key = $player->cards->search(function($i) use($id) {
                        return $i->id === $id;
                    });
                    $player->cards->forget($key);

                    // Update the top card
                    $topCard = $playedCard;

                    // In this case a player has won
                    if (count($player->cards) === 0) {
                        $this->line(now() . ' - <fg=green>' . $player->name . ' has won the game!</>');
                        $this->stop();
                    }

                } else {
                    if ($this->counter > 0) {
                        $removeFromDeck = $remainingCards->random();
                        $key = $remainingCards->search(function ($i) use ($removeFromDeck) {
                            return $i->id === $removeFromDeck->id;
                        });
                        $player->cards->push($removeFromDeck);
                        $remainingCards = $remainingCards->forget($key);

                        $this->counter = count($remainingCards);
                        $this->line(now() . ' - The top card is now: ' . ($this->isRed($topCard) ? '<fg=red>' . $topCard->suit . '</>' : $topCard->suit) . ' ' . $topCard->face);
                        $this->line(now() . ' - <fg=red>' . $player->name . ' no suitable card. </>');
                    }
                    // The final step, the one with the least left cards
                    else {
                        $collection = collect();
                        foreach ($playersWithCards as $finalsPlayer) {
                            $collection->push(['name' => $finalsPlayer->name, 'count' => count($finalsPlayer->cards)]);
                        }
                        $winner = $collection->sortBy('count')->first();
                        $this->line(now() . ' - <fg=green>Final clause ' . $winner['name'] . ' has won the game!</>');
                        $this->stop();
                    }
                }
            }
        }
    }
}
