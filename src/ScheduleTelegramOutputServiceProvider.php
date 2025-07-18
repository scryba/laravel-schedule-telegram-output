<?php

namespace Scryba\LaravelScheduleTelegramOutput;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\Event;

class ScheduleTelegramOutputServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/schedule-telegram-output.php', 'schedule-telegram-output'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/schedule-telegram-output.php' => config_path('schedule-telegram-output.php'),
            ], 'schedule-telegram-output-config');

            $this->commands([
                // Add any console commands here if needed
            ]);
        }

        // Add macro to Event class to support telegram output
        Event::macro('sendOutputToTelegram', function ($chatId = null) {
            // Always call sendOutputTo to ensure output is captured
            $this->sendOutputTo(app('path.storage').'/logs/schedule-telegram-'.sha1($this->command).'.log');

            $chatId = $chatId ?? config('schedule-telegram-output.default_chat_id');
            if (!$chatId) {
                throw new \LogicException('Chat ID is required. Either pass it to sendOutputToTelegram() or set TELEGRAM_DEFAULT_CHAT_ID in your .env file.');
            }

            return $this->then(function () use ($chatId) {
                $output = is_file($this->output) ? file_get_contents($this->output) : '';
                if (empty($output)) {
                    return;
                }
                try {
                    // Extract clean command name (remove full path)
                    $commandName = $this->command;
                    if (str_contains($commandName, 'artisan')) {
                        $parts = explode(' ', $commandName);
                        $commandName = end($parts); // Get the last part (the actual command)
                    }
                    
                    $parseMode = config('schedule-telegram-output.message_format.parse_mode', 'MarkdownV2');
                    $maxLength = config('schedule-telegram-output.message_format.max_length', 4000);
                    
                    if (strtolower($parseMode) === 'html') {
                        // HTML logic
                        $outputClean = str_replace('`', '', $output);
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
                        $truncated = false;
                        if (strlen($contents) > $maxLength) {
                            $contents = substr($contents, 0, $maxLength - 40) . "<br><br>...<br>[Output truncated]</pre>";
                            $truncated = true;
                        }
                    } else {
                        // MarkdownV2 logic
                        $outputClean = str_replace('`', '', $output);
                        $escapeMarkdown = function($string) {
                            $specialChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
                            foreach ($specialChars as $char) {
                                $string = str_replace($char, '\\' . $char, $string);
                            }
                            $string = preg_replace('/\\//', '\\\\', $string); // escape backslashes
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
                        $truncated = false;
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
                    $chatId = $chatId;
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
                    return;
                } catch (\Exception $e) {
                    \Log::error('Failed to send Telegram message: ' . $e->getMessage());
                }
            });
        });
    }
} 