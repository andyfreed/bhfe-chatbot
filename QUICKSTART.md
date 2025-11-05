# Quick Start Guide

Get your chatbot up and running in 5 minutes!

## 🚀 Quick Setup

### 1. Install Dependencies

```bash
cd middleware
npm install
```

### 2. Set Up Environment Variables

Copy the example file:

```bash
cp env.example .env
```

Edit `.env` and add your credentials:

```env
OPENAI_API_KEY=sk-...
OPENAI_ASSISTANT_ID=asst_...
DROPBOX_ACCESS_TOKEN=...
WORDPRESS_API_URL=https://your-site.com/wp-json
WORDPRESS_API_SECRET=...
```

### 3. Create Your Assistant

Run the helper script:

```bash
npm run create-assistant
```

This will create your assistant and print the Assistant ID. Copy it to your `.env` file.

### 4. Get Your Credentials

#### OpenAI API Key
- Go to https://platform.openai.com/api-keys
- Create a new API key
- Copy to `OPENAI_API_KEY` in `.env`

#### Dropbox Access Token
- Go to https://www.dropbox.com/developers/apps
- Create a new app
- Generate an access token
- Copy to `DROPBOX_ACCESS_TOKEN` in `.env`

#### WordPress API Secret
- Go to WordPress Admin → Users → Your Profile
- Scroll to "Application Passwords"
- Create a new application password
- Use format: `username:password` in `WORDPRESS_API_SECRET`

### 5. Start the Server

```bash
npm start
```

You should see:
```
🚀 Middleware server running on port 3000
```

### 6. Add Widget to WordPress

1. Upload `frontend/chat-widget.js` to your theme directory
2. Add this to your theme's `footer.php` (before `</body>`):

```php
<script>
  const CHATBOT_MIDDLEWARE_URL = 'http://localhost:3000'; // Change to your deployed URL
</script>
<script src="<?php echo get_template_directory_uri(); ?>/chat-widget.js"></script>
```

### 7. Test It!

1. Visit your WordPress site
2. Click the chat bubble in the bottom-right corner
3. Type a message like: "Search Dropbox for my presentation"
4. The assistant should respond!

## 🐛 Troubleshooting

**"Assistant not found"**
- Make sure `OPENAI_ASSISTANT_ID` is correct
- Run `npm run create-assistant` again

**"Dropbox search failed"**
- Check that `DROPBOX_ACCESS_TOKEN` is valid
- Regenerate token in Dropbox app settings

**"WordPress API error"**
- Verify `WORDPRESS_API_URL` is correct
- Check that `WORDPRESS_API_SECRET` is valid
- Make sure WordPress REST API is enabled

**Widget not appearing**
- Check browser console for errors
- Verify `CHATBOT_MIDDLEWARE_URL` is set correctly
- Make sure the script is loaded (check Network tab)

## 📚 Next Steps

- Deploy to production (see main README.md)
- Customize the assistant's instructions
- Add more functions
- Style the widget

For more details, see the main [README.md](README.md).

