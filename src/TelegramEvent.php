<?php

namespace Scryba\LaravelScheduleTelegramOutput;

use Illuminate\Console\Scheduling\Event;

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
            $maxLength = config('schedule-telegram-output.message_format.max_length', 4000);
            $parseMode = config('schedule-telegram-output.message_format.parse_mode', 'MarkdownV2');
            $commandName = $this->command;
            if (str_contains($commandName, 'artisan')) {
                $parts = explode(' ', $commandName);
                $commandName = end($parts);
            }
            $contents = '';
            $truncated = false;
            if (strtolower($parseMode) === 'html') {
                $outputClean = str_replace('`', '', $text);
                $outputHtml = e($outputClean);
                $outputPre = '<pre>' . $outputHtml . '</pre>';
                $contents = "<b>🤖 Scheduled Job Output</b><br><br>";
                $contents .= "<b>Project:</b> " . e(config('app.name') ?: 'Laravel App') . "<br>";
                $contents .= "<b>Environment:</b> " . e(config('app.env') ?: 'unknown') . "<br>";
                $contents .= "<b>URL:</b> " . e(config('app.url') ?: 'N/A') . "<br>";
                $contents .= "<b>Server:</b> " . e(gethostname() ?: 'unknown') . "<br>";
                $contents .= "<b>Command:</b> <code>" . e($commandName) . "</code><br>";
                $contents .= "<b>Time:</b> " . e(now()->format('Y-m-d H:i:s T')) . "<br><br>";
                $contents .= "<b>Output:</b><br>" . $outputPre;
                if (strlen($contents) > $maxLength) {
                    $contents = substr($contents, 0, $maxLength - 40) . "<br><br>...<br>[Output truncated]</pre>";
                    $truncated = true;
                }
            } else {
                $outputClean = str_replace('`', '', $text);
                $escapeMarkdown = function($string) {
                    $specialChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
                    foreach ($specialChars as $char) {
                        $string = str_replace($char, '\\' . $char, $string);
                    }
                    $string = preg_replace('/\//', '\\', $string); // escape backslashes
                    return $string;
                };
                $outputMd = $escapeMarkdown($outputClean);
                $contents = "*🤖 Scheduled Job Output*\n\n";
                $contents .= "*Project:* " . $escapeMarkdown(config('app.name') ?: 'Laravel App') . "\n";
                $contents .= "*Environment:* " . $escapeMarkdown(config('app.env') ?: 'unknown') . "\n";
                $contents .= "*URL:* " . $escapeMarkdown(config('app.url') ?: 'N/A') . "\n";
                $contents .= "*Server:* " . $escapeMarkdown(gethostname() ?: 'unknown') . "\n";
                $contents .= "*Command:* `" . $escapeMarkdown($commandName) . "`\n";
                $contents .= "*Time:* " . $escapeMarkdown(now()->format('Y-m-d H:i:s T')) . "\n\n";
                $contents .= "*Output:*\n" . $outputMd;
                if (strlen($contents) > $maxLength) {
                    $contents = substr($contents, 0, $maxLength - 40) . "\n\n...\n[Output truncated]";
                    $truncated = true;
                }
            }
            $shouldDebug = config('schedule-telegram-output.debug', config('app.debug'));
            if ($shouldDebug) {
                \Log::info('[ScheduleTelegramOutput] Telegram message content', [
                    'message' => $contents,
                    'parse_mode' => $parseMode
                ]);
            }
            $botToken = config('schedule-telegram-output.bots.default.token');
            $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
            $params = [
                'chat_id' => $chatId,
                'text' => $contents,
                'parse_mode' => $parseMode,
            ];
            $url = $apiUrl . '?' . http_build_query($params);
            file_get_contents($url);
            if ($shouldDebug) {
                \Log::info('[ScheduleTelegramOutput] Sent Telegram message chunk (HTTP)', [
                    'chat_id' => $chatId,
                    'length' => strlen($contents),
                    'truncated' => $truncated,
                    'parse_mode' => $parseMode
                ]);
            }
        } catch (\Exception $e) {
            // Log the error but don't fail the scheduled task
            \Log::error('Failed to send Telegram message: ' . $e->getMessage());
        }
    }
}
