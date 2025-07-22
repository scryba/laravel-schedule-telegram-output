# Configuration Reference

This document describes all configuration options available in `config/schedule-telegram-output.php`.

## Bots

- **default_bot**: The default bot name (default: `default`).
- **bots**: Array of bot configurations. Each bot can have:
  - `token`: The Telegram bot token (required)
  - `certificate_path`: Path to certificate (optional)
  - `webhook_url`: Webhook URL (optional)
  - `commands`: Array of custom bot commands (optional)

## Default Chat ID

- **default_chat_id**: The default chat ID to send messages to. Can be overridden per command.

## Debug Logging

- **debug**: If true, logs detailed debug info for Telegram message sending. Defaults to `APP_DEBUG`.

## Message Format

- **message_format**: Array of options for message formatting:
  - `parse_mode`: `MarkdownV2` (default) or `HTML`.
  - `max_length`: Maximum message length (default: 4000).
  - `include_timestamp`: Whether to include the time in the message (default: true).
  - `include_command_name`: Whether to include the command name (default: true).
  - `show_url`: Whether to show the app URL in the message (default: false).

## Example

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
    'debug' => env('SCHEDULE_TELEGRAM_OUTPUT_DEBUG', env('APP_DEBUG', false)),
    'message_format' => [
        'parse_mode' => 'MarkdownV2',
        'max_length' => 4000,
        'include_timestamp' => true,
        'include_command_name' => true,
        'show_url' => false,
    ],
];
```

---

For more, see the [Getting Started Guide](GETTING_STARTED.md) and the main [README](../README.md).
