<?php

namespace Scryba\LaravelScheduleTelegramOutput;

use Illuminate\Console\Scheduling\Event;
use Scryba\LaravelScheduleTelegramOutput\TelegramNotifier;

class TelegramEvent extends Event
{
    /**
     * Ensure that the command output is being captured.
     */
    protected function ensureOutputIsBeingCaptured(): void
    {
        if (is_null($this->output) || $this->output == $this->getDefaultOutput()) {
            $this->sendOutputTo(storage_path('logs/schedule-'.sha1($this->mutexName()).'.log'));
        }
    }

    /**
     * Send the captured output to Telegram.
     *
     * @param string|null $chatId
     * @return $this
     * @throws \LogicException
     */
    public function sendOutputToTelegram($chatId = null): self
    {
        $this->ensureOutputIsBeingCaptured();

        $text = is_file($this->output) ? file_get_contents($this->output) : '';

        if (empty($text)) {
            return $this;
        }

        // Use provided chat ID or default from config
        $chatId = $chatId ?? config('schedule-telegram-output.default_chat_id');
        
        if (!$chatId) {
            throw new \LogicException('Chat ID is required. Either pass it to sendOutputToTelegram() or set TELEGRAM_DEFAULT_CHAT_ID in your .env file.');
        }

        return $this->then(function () use($chatId, $text) {
            $this->sendTelegramMessage($chatId, $text);
        });
    }

    /**
     * Format and send the message to Telegram via HTTP request.
     *
     * @param string $chatId
     * @param string $text
     */
    protected function sendTelegramMessage($chatId, $text): void
    {
        try {
            $commandName = $this->command;
            if (str_contains($commandName, 'artisan')) {
                $parts = explode(' ', $commandName);
                $commandName = end($parts);
            }
            
            TelegramNotifier::sendMessage($chatId, $text, $commandName);
        } catch (\Exception $e) {
            // Log the error but don't fail the scheduled task
            \Log::error('Failed to send Telegram message: ' . $e->getMessage());
        }
    }
}
