# BHFE Chatbot WordPress Plugin

A WordPress plugin that adds an AI chatbot to your site using OpenAI Assistants API.

## Installation

### Option 1: Upload ZIP (Recommended)

1. **Create a ZIP file** of the `wordpress-plugin` folder:
   - Right-click on the `wordpress-plugin` folder
   - Select "Compress" or "Send to" → "Compressed (zipped) folder"
   - Name it `bhfe-chatbot.zip`

2. **Upload to WordPress:**
   - Go to WordPress Admin → Plugins → Add New
   - Click "Upload Plugin"
   - Choose the `bhfe-chatbot.zip` file
   - Click "Install Now"
   - Click "Activate Plugin"

### Option 2: Manual Installation

1. **Upload the plugin folder:**
   - Upload the entire `wordpress-plugin` folder to `/wp-content/plugins/`
   - Rename it to `bhfe-chatbot` (without the `wordpress-plugin` prefix)
   - The path should be: `/wp-content/plugins/bhfe-chatbot/`

2. **Activate the plugin:**
   - Go to WordPress Admin → Plugins
   - Find "BHFE AI Chatbot"
   - Click "Activate"

## Configuration

1. **Go to Settings:**
   - WordPress Admin → Settings → BHFE Chatbot
   - Or WordPress Admin → Plugins → BHFE Chatbot → Settings

2. **Configure:**
   - **Enable Chatbot**: Check to enable the chatbot widget
   - **Middleware Server URL**: Enter your middleware server URL
     - For local testing: `http://localhost:3000`
     - For production: `https://your-middleware-server.com`

3. **Save Settings**

## Testing on Staging Site

Since your staging site is at `https://bhfestaging.wpenginepowered.com/`, you have two options:

### Option A: Use Local Middleware (for testing)

1. **Start your middleware server locally:**
   ```bash
   cd middleware
   npm start
   ```

2. **Make it accessible from staging:**
   - Use a tunneling service like **ngrok**: `ngrok http 3000`
   - This will give you a public URL like: `https://abc123.ngrok.io`
   - Use this URL in the plugin settings

3. **Configure plugin:**
   - Middleware URL: `https://abc123.ngrok.io` (your ngrok URL)

### Option B: Deploy Middleware (recommended for testing)

1. **Deploy middleware to a hosting service:**
   - Render.com (free tier available)
   - Fly.io (free tier available)
   - Heroku (if you have an account)

2. **Update plugin settings:**
   - Use your deployed middleware URL

## Usage

Once installed and configured:

1. The chatbot widget will appear in the bottom-right corner of your site
2. Visitors can click the chat bubble to open the chatbot
3. The chatbot will communicate with your middleware server
4. The assistant can search Dropbox and retrieve WordPress data

## Troubleshooting

### Widget not appearing

- Check that "Enable Chatbot" is checked in settings
- Verify the middleware URL is correct
- Check browser console for errors (F12)
- Make sure the middleware server is running

### Connection errors

- Verify the middleware URL is accessible from your WordPress site
- Check CORS settings on the middleware server
- For localhost, use ngrok or similar tunneling service

### Assistant not responding

- Check middleware server logs
- Verify OpenAI API key is correct
- Check that Assistant ID is correct

## Plugin Files

- `bhfe-chatbot.php` - Main plugin file
- `assets/chat-widget.js` - Chat widget JavaScript
- `README.md` - This file

## Support

For issues or questions:
- Check the main project README.md
- Review server logs
- Check browser console for errors

