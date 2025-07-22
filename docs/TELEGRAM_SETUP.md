# How to Get Your Telegram Bot Token and Chat ID

This guide will help you obtain the required values for:

- `TELEGRAM_BOT_TOKEN`
- `TELEGRAM_DEFAULT_CHAT_ID`

## 1. Create a Telegram Bot

1. Open Telegram and search for `@BotFather`.
2. Start a chat and send `/newbot`.
3. Follow the prompts to name your bot and get a username.
4. BotFather will reply with your **bot token**:

   ```
   123456789:ABCdefGhIJKlmNoPQRsTuvWxYZ1234567890
   ```

5. Copy this value and set it as `TELEGRAM_BOT_TOKEN` in your `.env` file.

## 2. Get Your Chat ID

### For a Personal Chat

1. Start a chat with your bot (search for your bot username and click "Start").
2. Visit this URL in your browser (replace `YOUR_BOT_TOKEN`):

   ```
   https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates
   ```

3. Send a message to your bot in Telegram.
4. Refresh the URL above. Look for a JSON object like:

   ```json
   {
     "update_id": ...,
     "message": {
       "chat": {
         "id": 123456789,
         ...
       },
       ...
     }
   }
   ```

5. The `id` value is your `TELEGRAM_DEFAULT_CHAT_ID`.

### For a Group Chat

1. Add your bot to the group.
2. Send a message in the group.
3. Visit the same `getUpdates` URL as above.
4. Look for a `chat` object with a negative ID (e.g., `-123456789`). Use this as your chat ID.

## 3. Troubleshooting

- Make sure your bot is not blocked and is a member of the chat/group.
- If you don't see updates, make sure you have sent a message to the bot or group after creating the bot.

## 4. Example .env

```
TELEGRAM_BOT_TOKEN=123456789:ABCdefGhIJKlmNoPQRsTuvWxYZ1234567890
TELEGRAM_DEFAULT_CHAT_ID=123456789
```

---

For more advanced usage, see the [Getting Started Guide](GETTING_STARTED.md) and the main [README](../README.md).
