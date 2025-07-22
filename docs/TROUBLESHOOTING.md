# Troubleshooting & FAQ

This guide covers common issues and frequently asked questions for Laravel Schedule Telegram Output.

## Common Errors

### 1. 400 Bad Request: can't parse entities

- **Cause:** Your message contains a special character (like `.`) that is not escaped for MarkdownV2.
- **Solution:** Ensure all dynamic values are passed through the package's escaping logic. Update to the latest version for bulletproof escaping.

### 2. preg_replace(): Compilation failed

- **Cause:** Regex error in escaping logic (fixed in v1.1.3+).
- **Solution:** Update to the latest version.

### 3. No message sent

- **Cause:** Wrong bot token, chat ID, or bot not a member of the chat/group.
- **Solution:**
  - Double-check your `.env` values.
  - Make sure your bot is not blocked and is a member of the chat/group.
  - Use the [Telegram Setup Guide](TELEGRAM_SETUP.md) to get your chat ID.

### 4. Message truncated

- **Cause:** Telegram has a 4096 character limit per message.
- **Solution:** The package truncates messages at 4000 characters by default. You can adjust this in the config.

## FAQ

**Q: Can I send to multiple chats?**
A: Yes, call `sendOutputToTelegram` with different chat IDs, or use the trait for programmatic control.

**Q: Can I use multiple bots?**
A: Yes, configure multiple bots in the config and switch tokens as needed.

**Q: How do I change the message format?**
A: Set `parse_mode` to `HTML` or `MarkdownV2` in the config or at runtime.

**Q: How do I debug message formatting?**
A: Enable debug logging in the config or `.env` to see the raw message and errors in your logs.

---

For more, see the [Getting Started Guide](GETTING_STARTED.md) and the main [README](../README.md).
