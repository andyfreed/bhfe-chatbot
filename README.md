# WordPress AI Chatbot with OpenAI Assistants API

A complete AI chatbot solution for WordPress that uses OpenAI's Assistants API, with integrations for Dropbox file search and WordPress REST API data retrieval.

## 📋 Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Setup Instructions](#setup-instructions)
- [OpenAI Assistant Configuration](#openai-assistant-configuration)
- [Deployment](#deployment)
- [Usage](#usage)
- [Troubleshooting](#troubleshooting)

## 🎯 Overview

This project consists of:

1. **Middleware Backend** (Node.js/Express) - A secure server that:
   - Holds your API keys and credentials
   - Connects to OpenAI Assistants API
   - Handles function calls to Dropbox and WordPress
   - Provides a `/chat` endpoint for the frontend

2. **Frontend Widget** - A JavaScript chat widget that:
   - Embeds in your WordPress site
   - Sends messages to the middleware
   - Displays streaming responses from the assistant

## 🏗️ Architecture

```
WordPress Site (Frontend)
    ↓ POST /chat
Middleware Server (Node.js/Express)
    ↓
OpenAI Assistants API
    ↓ (function calls)
Dropbox API / WordPress REST API
```

**Why this architecture?**
- **Security**: API keys stay on the server, never exposed to the browser
- **Flexibility**: Easy to add more integrations (databases, APIs, etc.)
- **Scalability**: Can handle multiple WordPress sites
- **Control**: Full control over function calling logic

## 🚀 Setup Instructions

### Prerequisites

- Node.js 18+ installed
- OpenAI API key
- Dropbox access token
- WordPress site with REST API enabled
- A place to host the middleware (Render, Fly.io, Vercel, etc.)

### Step 1: Clone/Download the Project

```bash
cd bhfe-chatbot
```

### Step 2: Install Dependencies

```bash
cd middleware
npm install
```

### Step 3: Configure Environment Variables

Copy the example environment file:

```bash
cp .env.example .env
```

Edit `.env` and fill in your credentials:

```env
# OpenAI Configuration
OPENAI_API_KEY=sk-your-openai-api-key-here
OPENAI_ASSISTANT_ID=asst_xxxxxxxxxxxxx

# Dropbox Configuration
DROPBOX_ACCESS_TOKEN=your-dropbox-access-token-here

# WordPress Configuration
WORDPRESS_API_URL=https://your-site.com/wp-json
WORDPRESS_API_SECRET=your-wordpress-api-secret-here

# Server Configuration
PORT=3000
NODE_ENV=development
```

### Step 4: Get Your Credentials

#### OpenAI API Key & Assistant ID

1. Go to https://platform.openai.com/api-keys
2. Create a new API key
3. Copy it to `OPENAI_API_KEY` in `.env`
4. Create an Assistant (see [OpenAI Assistant Configuration](#openai-assistant-configuration) below)
   - **Option A**: Use the helper script (recommended): `npm run create-assistant`
   - **Option B**: Create manually in OpenAI dashboard
5. Copy the Assistant ID to `OPENAI_ASSISTANT_ID` in `.env`

#### Dropbox Access Token

1. Go to https://www.dropbox.com/developers/apps
2. Create a new app (choose "Scoped access" → "Full Dropbox")
3. Generate an access token
4. Copy it to `DROPBOX_ACCESS_TOKEN` in `.env`

#### WordPress API Secret

You'll need to set up authentication for your WordPress REST API. Options:

**Option A: Application Password (Recommended)**
1. Go to WordPress Admin → Users → Your Profile
2. Scroll to "Application Passwords"
3. Create a new application password
4. Use it as `WORDPRESS_API_SECRET` in `.env`
5. Format: `username:password` (e.g., `admin:xxxx xxxx xxxx xxxx`)

**Option B: Custom Authentication Plugin**
- Install a plugin like "Application Passwords" or "JWT Authentication"
- Configure it to use a bearer token
- Use that token in `WORDPRESS_API_SECRET`

**Option C: Basic Auth (Development Only)**
- For local testing only, you can use basic auth
- Format: `username:password` (base64 encoded)
- **Never use this in production!**

### Step 5: Test Locally

```bash
npm start
```

You should see:
```
🚀 Middleware server running on port 3000
📡 Health check: http://localhost:3000/health
💬 Chat endpoint: http://localhost:3000/chat
```

Test the health endpoint:
```bash
curl http://localhost:3000/health
```

### Step 6: Add Frontend Widget to WordPress

1. Upload `frontend/chat-widget.js` to your WordPress theme directory (e.g., `/wp-content/themes/your-theme/`)
2. Add this to your theme's `footer.php` (before `</body>`) or use a plugin like "Insert Headers and Footers":

```php
<script>
  const CHATBOT_MIDDLEWARE_URL = 'https://your-middleware-url.com';
</script>
<script src="<?php echo get_template_directory_uri(); ?>/chat-widget.js"></script>
```

**Important**: Replace `https://your-middleware-url.com` with your actual middleware server URL.

## 🤖 OpenAI Assistant Configuration

### Creating the Assistant

**Option A: Using the Helper Script (Recommended)**

1. Make sure your `.env` file has `OPENAI_API_KEY` set
2. Run: `npm run create-assistant`
3. Copy the Assistant ID that's printed to your `.env` file

**Option B: Manual Creation in Dashboard**

1. Go to https://platform.openai.com/assistants
2. Click "Create" → "Assistant"
3. Configure:

   **Name**: "WordPress Chatbot" (or your choice)

   **Model**: `gpt-4-turbo-preview` or `gpt-3.5-turbo` (gpt-4 is better but costs more)

   **Instructions**: 
   ```
   You are a helpful AI assistant for a WordPress website. 
   You can help users by:
   - Searching for files in Dropbox
   - Retrieving data from the WordPress REST API
   
   Always be friendly and helpful. If you don't know something, say so.
   ```

   **Tools**: 
   - ✅ Functions (this will be added automatically by the code)
   - ✅ Code Interpreter (optional, for file analysis)
   - ✅ File Search (optional, for uploaded files)

4. Click "Save"
5. Copy the Assistant ID (starts with `asst_`) to your `.env` file

### Updating System Instructions

You can change the assistant's behavior by:
1. Editing the instructions in the OpenAI dashboard, OR
2. Modifying the instructions when creating the assistant in code (see `services/openai.js`)

### Function Calling

The assistant automatically has access to:
- `searchDropbox(query)` - Searches Dropbox for files
- `getWordPressData(endpoint, params)` - Fetches WordPress data

These are defined in `services/openai.js` in the `functionDefinitions` array.

## 🚢 Deployment

### Option 1: Render (Recommended for Beginners)

1. Create account at https://render.com
2. Click "New" → "Web Service"
3. Connect your GitHub repo (or push code to GitHub first)
4. Configure:
   - **Name**: `wordpress-chatbot-middleware`
   - **Environment**: `Node`
   - **Build Command**: `cd middleware && npm install`
   - **Start Command**: `cd middleware && npm start`
5. Add environment variables in the dashboard
6. Deploy!

**Note**: On Render, set `PORT` to use the environment variable they provide (usually automatically handled).

### Option 2: Fly.io

1. Install Fly CLI: `curl -L https://fly.io/install.sh | sh`
2. In the `middleware` directory, run: `fly launch`
3. Follow prompts
4. Set secrets: `fly secrets set OPENAI_API_KEY=xxx OPENAI_ASSISTANT_ID=xxx ...`
5. Deploy: `fly deploy`

### Option 3: Vercel

1. Install Vercel CLI: `npm i -g vercel`
2. In `middleware` directory, run: `vercel`
3. Add environment variables in Vercel dashboard
4. Deploy: `vercel --prod`

**Note**: Vercel is serverless, so you may need to adjust for streaming. Consider using Render or Fly.io for better streaming support.

### Option 4: Your Own Server

1. Set up a Node.js server (Ubuntu, DigitalOcean, AWS, etc.)
2. Install Node.js 18+
3. Clone the repo
4. Run `npm install` in the `middleware` directory
5. Set up environment variables
6. Use PM2 to keep it running: `pm2 start index.js`
7. Set up nginx reverse proxy (optional)

### CORS Configuration

If your WordPress site is on a different domain than the middleware, you may need to update CORS in `middleware/index.js`:

```javascript
app.use(cors({
  origin: 'https://your-wordpress-site.com',
  credentials: true
}));
```

## 💬 Usage

### Testing the Chat Endpoint

```bash
curl -X POST http://localhost:3000/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "Hello, can you search Dropbox for my presentation?"}'
```

### Using the Widget

1. Visit your WordPress site
2. Click the chat bubble in the bottom-right corner
3. Type a message and press Enter
4. The assistant will respond, potentially using function calls to fetch data

### Example Conversations

**User**: "Search Dropbox for my budget files"
- Assistant calls `searchDropbox("budget")`
- Returns matching files

**User**: "What are my latest blog posts?"
- Assistant calls `getWordPressData("/wp/v2/posts", {per_page: 5})`
- Returns recent posts

## 🔧 Troubleshooting

### "Assistant not found" error

- Check that `OPENAI_ASSISTANT_ID` is correct in `.env`
- Make sure the assistant exists in your OpenAI account

### "Dropbox search failed"

- Verify `DROPBOX_ACCESS_TOKEN` is valid
- Check that the token has the right permissions
- Token might have expired (regenerate in Dropbox app settings)

### "WordPress API error"

- Verify `WORDPRESS_API_URL` is correct
- Check that `WORDPRESS_API_SECRET` is valid
- Ensure WordPress REST API is enabled
- Check if your WordPress site requires authentication for REST API

### Widget not appearing

- Check browser console for errors
- Verify `CHATBOT_MIDDLEWARE_URL` is set correctly
- Make sure the script is loaded (check Network tab)
- Check CORS settings if middleware is on different domain

### Streaming not working

- Some hosting providers don't support streaming well
- Try Render or Fly.io instead of Vercel
- Check that `Accept: text/event-stream` header is sent

### Function calls not working

- Check server logs for errors
- Verify function names match in `availableFunctions` and `functionDefinitions`
- Check OpenAI dashboard to see if assistant has function calling enabled

## 📝 Customization

### Adding More Functions

1. Create a new function in `services/` (e.g., `services/database.js`)
2. Add it to `availableFunctions` in `services/openai.js`
3. Add function definition to `functionDefinitions` array
4. The assistant will automatically be able to call it!

### Changing the Assistant's Behavior

Edit the system instructions in the OpenAI dashboard, or modify them when creating the assistant in code.

### Styling the Widget

Edit the CSS in `frontend/chat-widget.js` (look for the `style.textContent` section).

## 🔒 Security Best Practices

1. **Never commit `.env` to git** - It's already in `.gitignore`
2. **Use HTTPS in production** - Always use SSL/TLS
3. **Rotate API keys regularly**
4. **Limit API key permissions** - Use least privilege
5. **Monitor usage** - Check OpenAI dashboard for unexpected costs
6. **Rate limiting** - Consider adding rate limiting to prevent abuse (not included by default)

## 📚 Additional Resources

- [OpenAI Assistants API Docs](https://platform.openai.com/docs/assistants)
- [Dropbox API Docs](https://www.dropbox.com/developers/documentation)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)

## 🆘 Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review server logs for errors
3. Check OpenAI dashboard for API errors
4. Verify all environment variables are set correctly

---

**Built with ❤️ for WordPress users**

