# Laravel Schedule Telegram Output

[![Latest Version on Packagist](https://img.shields.io/packagist/v/scryba/laravel-schedule-telegram-output.svg?style=flat-square)](https://packagist.org/packages/scryba/laravel-schedule-telegram-output)
[![Total Downloads](https://img.shields.io/packagist/dt/scryba/laravel-schedule-telegram-output.svg?style=flat-square)](https://packagist.org/packages/scryba/laravel-schedule-telegram-output)
[![License](https://img.shields.io/packagist/l/scryba/laravel-schedule-telegram-output.svg?style=flat-square)](https://packagist.org/packages/scryba/laravel-schedule-telegram-output)
[![GitHub Stars](https://img.shields.io/github/stars/scryba/laravel-schedule-telegram-output?style=flat-square)](https://github.com/scryba/laravel-schedule-telegram-output/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/scryba/laravel-schedule-telegram-output?style=flat-square)](https://github.com/scryba/laravel-schedule-telegram-output/network)

A Laravel package to send scheduled job outputs to Telegram with robust formatting and flexible configuration.

## Features

- Sends scheduled command output to Telegram
- Supports both **MarkdownV2** (default) and **HTML** formatting
- Handles message truncation for Telegram limits
- Debug logging, configurable per environment
- Easy integration with Laravel's scheduler

## Installation

1. **Require the package via Composer:**

   ```bash
   composer require scryba/laravel-schedule-telegram-output
   ```

2. **Publish the config (optional, for customization):**

   ```bash
   php artisan vendor:publish --provider="Scryba\LaravelScheduleTelegramOutput\ScheduleTelegramOutputServiceProvider" --tag=schedule-telegram-output-config
   ```

## Manual Installation (VCS Repository)

You can install it directly from GitHub as a VCS repository:

1. Add the repository to your `composer.json`:

   ```json
   "repositories": [
       {
           "type": "vcs",
           "url": "git@github.com:scryba/laravel-schedule-telegram-output.git"
       }
   ]
   ```

2. Require the package:

   ```bash
   composer require scryba/laravel-schedule-telegram-output
   ```

3. **Manually register the service provider** in your `config/app.php` (since auto-discovery does not work for VCS installs):

   ```php
   'providers' => [
       // ...
       Scryba\LaravelScheduleTelegramOutput\ScheduleTelegramOutputServiceProvider::class,
   ],
   ```

## Configuration

The config file is `config/schedule-telegram-output.php`. Key options:

```php
return [
    'default_bot' => env('TELEGRAM_BOT_NAME', 'default'),
    'bots' => [
        'default' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'certificate_path' => env('TELEGRAM_CERTIFICATE_PATH', ''),
            'webhook_url' => env('TELEGRAM_WEBHOOK_URL', ''),
            'commands' => [],
        ],
    ],
    'default_chat_id' => env('TELEGRAM_DEFAULT_CHAT_ID'),
    // Debug logging (default: uses APP_DEBUG, can override with SCHEDULE_TELEGRAM_OUTPUT_DEBUG)
    'debug' => env('SCHEDULE_TELEGRAM_OUTPUT_DEBUG', env('APP_DEBUG', false)),
    'message_format' => [
        // Default is MarkdownV2. Set to 'HTML' to use HTML formatting.
        'parse_mode' => env('SCHEDULE_TELEGRAM_OUTPUT_PARSE_MODE', 'MarkdownV2'),
        'max_length' => 4000,
        'include_timestamp' => true,
        'include_command_name' => true,
        // Max number of characters to send as output snippet (default: 500)
        'snippet_max_length' => 500,
    ],
];
```

- **snippet_max_length**: Maximum number of characters to send as the output snippet in Telegram messages. Default is 500. You can override this in your config file.

### .env Example

```env
TELEGRAM_BOT_TOKEN=your-telegram-bot-token
TELEGRAM_DEFAULT_CHAT_ID=your-chat-id
SCHEDULE_TELEGRAM_OUTPUT_DEBUG=true # or false
SCHEDULE_TELEGRAM_OUTPUT_PARSE_MODE=MarkdownV2 # or HTML
```

## Usage

By default, only a snippet of the output (first 10 lines or up to 500 characters) is sent to Telegram. You can change the maximum snippet length in your config:

```php
// config/schedule-telegram-output.php
'message_format' => [
    'snippet_max_length' => 1000, // Send up to 1000 characters
],
```

In your `App\Console\Kernel` or any scheduled command:

```php
$schedule->command('your:command')->sendOutputToTelegram();
```

You can also specify a chat ID:

```php
$schedule->command('your:command')->sendOutputToTelegram('123456789');
```

---

### Advanced Usage: Using TelegramScheduleTrait

The package provides a trait `Scryba\LaravelScheduleTelegramOutput\TelegramScheduleTrait` for more advanced or programmatic use cases. This allows you to add Telegram output to any scheduled event, not just via the macro.

**Example:**

```php
use Scryba\LaravelScheduleTelegramOutput\TelegramScheduleTrait;

class Kernel extends ConsoleKernel
{
    use TelegramScheduleTrait;

    protected function schedule(Schedule $schedule)
    {
        $event = $schedule->command('your:command');
        // Add Telegram output to the event
        $this->addOutputToTelegram($event, '123456789'); // Optional: specify chat ID
    }
}
```

This is useful if you want to add Telegram output conditionally or as part of a custom scheduling workflow.

---

## Examples Folder

See the `examples/` folder in the package for more real-world usage patterns, including:

- Example `ConsoleKernel.php` files
- Advanced scheduling and notification scenarios
- Trait-based usage

---

## Formatting Modes

### MarkdownV2 (default)

- Most robust for code, bold, and multi-line output.
- Handles all escaping for Telegram's MarkdownV2.

### HTML

- Use only allowed tags: `<b>`, `<i>`, `<a>`, `<code>`, `<pre>`, etc.
- Output is wrapped in `<pre>` for multi-line blocks.
- **Do not use backticks or Markdown-style formatting in HTML mode.**

## Debug Logging

- By default, debug logs are written if `APP_DEBUG=true`.
- You can override this with `SCHEDULE_TELEGRAM_OUTPUT_DEBUG=true` or `false` in your `.env` or config.
- When debug is off, no `[ScheduleTelegramOutput]` logs are written.

## Troubleshooting

- **400 Bad Request:**
  - For HTML mode, ensure only allowed tags are used and all tags are properly closed.
  - For MarkdownV2, ensure all special characters are escaped.
  - Check the log for `[ScheduleTelegramOutput] Telegram message content` to see the exact message being sent.
- **No message sent:**
  - Check your bot token and chat ID.
  - Ensure your bot is not blocked and is a member of the chat/group.

## Example Output

**MarkdownV2:**

```
*🤖 Scheduled Job Output*

*Project:* your-project
*Environment:* local
*URL:* http://your-project.test
*Server:* your-server
*Command:* `your:command`
*Time:* 2025-07-18 10:43:29 UTC

*Output:*
Your command output here...
```

**HTML:**

```
<b>🤖 Scheduled Job Output</b><br><br>
<b>Project:</b> your-project<br>
<b>Environment:</b> local<br>
<b>URL:</b> http://your-project.test<br>
<b>Server:</b> your-server<br>
<b>Command:</b> <code>your:command</code><br>
<b>Time:</b> 2025-07-18 10:43:29 UTC<br><br>
<b>Output:</b><br><pre>Your command output here...</pre>
```

## Advanced

- You can extend or override the macro in your own service provider if you need custom logic.
- The package is compatible with Laravel 8, 9, 10+.

## License

MIT

## Documentation & Onboarding

- [Getting Started Guide](docs/GETTING_STARTED.md)
- [How to get your Telegram Bot Token and Chat ID](docs/TELEGRAM_SETUP.md)
- [Configuration Reference](docs/CONFIGURATION.md)
- [Advanced Usage](docs/ADVANCED_USAGE.md)
- [Troubleshooting & FAQ](docs/TROUBLESHOOTING.md)
- [Examples](docs/EXAMPLES.md)
