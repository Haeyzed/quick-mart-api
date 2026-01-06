<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Daily Quote Command
 *
 * Sends a daily inspirational quote via email to a specified user.
 * This command can be scheduled to run daily (e.g., via cron or Laravel scheduler).
 *
 * @package App\Console\Commands
 */
class DailyQuote extends Command
{
    /**
     * Collection of inspirational quotes.
     *
     * @var array<string, string>
     */
    private const QUOTES = [
        'Mahatma Gandhi' => 'Live as if you were to die tomorrow. Learn as if you were to live forever.',
        'Friedrich Nietzsche' => 'That which does not kill us makes us stronger.',
        'Theodore Roosevelt' => 'Do what you can, with what you have, where you are.',
        'Oscar Wilde' => 'Be yourself; everyone else is already taken.',
        'William Shakespeare' => 'This above all: to thine own self be true.',
        'Napoleon Hill' => 'If you cannot do great things, do small things in a great way.',
        'Milton Berle' => 'If opportunity doesn\'t knock, build a door.',
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:daily
                            {--user=1 : User ID to send the quote to}
                            {--from= : From email address (optional)}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a daily inspirational quote via email';

    /**
     * Execute the console command.
     *
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function handle(): int
    {
        try {
            $userId = (int)$this->option('user');
            $user = User::find($userId);

            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return Command::FAILURE;
            }

            if (empty($user->email)) {
                $this->error("User {$userId} does not have an email address.");
                return Command::FAILURE;
            }

            $quote = $this->getRandomQuote();
            $fromEmail = $this->option('from') ?? config('mail.from.address');

            $this->sendQuoteEmail($user, $quote, $fromEmail);

            $this->info("Successfully sent daily quote to {$user->email}.");
            return Command::SUCCESS;
        } catch (Exception $e) {
            Log::error('DailyQuote: Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error("Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Get a random quote from the collection.
     *
     * @return array<string, string> Quote with author and text
     */
    private function getRandomQuote(): array
    {
        $author = array_rand(self::QUOTES);
        $text = self::QUOTES[$author];

        return [
            'author' => $author,
            'text' => $text,
        ];
    }

    /**
     * Send the quote email to the user.
     *
     * @param User $user The user to send the quote to
     * @param array<string, string> $quote The quote data
     * @param string $fromEmail The from email address
     * @return void
     */
    private function sendQuoteEmail(User $user, array $quote, string $fromEmail): void
    {
        try {
            Mail::raw(
                "{$quote['author']} -> {$quote['text']}",
                function ($message) use ($user, $quote, $fromEmail) {
                    $message->from($fromEmail)
                        ->to($user->email)
                        ->subject('Daily New Quote!');
                }
            );
        } catch (Exception $e) {
            Log::error('DailyQuote: Failed to send email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

