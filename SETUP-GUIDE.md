# Step-by-Step Setup Guide

This guide walks you through what I can do vs what you need to do.

## ✅ What I've Already Done For You

1. ✅ Created all the code files
2. ✅ Installed dependencies (`npm install`)
3. ✅ Created the `.env` file template

## 📋 What You Need to Do (5 Steps)

I'll walk you through each step below. These are things I **can't** do because they require your accounts.

---

### Step 1: Get Your OpenAI API Key

**What to do:**
1. Go to https://platform.openai.com/api-keys
2. Click "Create new secret key"
3. Give it a name (like "WordPress Chatbot")
4. Copy the key (it starts with `sk-`)
5. **Tell me the key** and I'll add it to your `.env` file

**OR** you can add it yourself:
- Open `middleware/.env` in a text editor
- Find the line: `OPENAI_API_KEY=your_openai_api_key_here`
- Replace `your_openai_api_key_here` with your actual key

---

### Step 2: Get Your Dropbox Access Token

**What to do:**
1. Go to https://www.dropbox.com/developers/apps
2. Click "Create app"
3. Choose:
   - **API**: Scoped access
   - **Type**: Full Dropbox
   - **Name**: WordPress Chatbot (or any name)
4. Click "Create app"
5. Scroll down to "OAuth 2" section
6. Under "Generated access token", click "Generate"
7. Copy the token
8. **Tell me the token** and I'll add it to your `.env` file

**OR** add it yourself to `.env`:
- Find: `DROPBOX_ACCESS_TOKEN=your_dropbox_access_token_here`
- Replace with your actual token

---

### Step 3: Get Your WordPress Site Info

**What you need:**
1. Your WordPress site URL (e.g., `https://yoursite.com`)
2. An application password from WordPress

**To get the application password:**
1. Log into your WordPress admin
2. Go to **Users** → **Profile** (or **Your Profile**)
3. Scroll down to **Application Passwords**
4. Enter a name (like "Chatbot API")
5. Click "Add New Application Password"
6. Copy the password (it will look like: `xxxx xxxx xxxx xxxx`)
7. **Tell me:**
   - Your WordPress site URL
   - Your WordPress username
   - The application password

I'll add them to your `.env` file.

**OR** add them yourself:
- `WORDPRESS_API_URL=https://yoursite.com/wp-json` (replace with your site)
- `WORDPRESS_API_SECRET=username:xxxx xxxx xxxx xxxx` (your username + password)

---

### Step 4: Create the OpenAI Assistant

**I can do this for you!** Once you have your OpenAI API key in the `.env` file, I can run:

```bash
npm run create-assistant
```

This will create your assistant and add the ID to the `.env` file automatically.

**OR** you can do it manually:
1. Go to https://platform.openai.com/assistants
2. Click "Create" → "Assistant"
3. Name it "WordPress Chatbot"
4. Choose model: `gpt-4-turbo-preview` or `gpt-3.5-turbo`
5. Add instructions (optional):
   ```
   You are a helpful AI assistant for a WordPress website. 
   You can search Dropbox files and retrieve WordPress data.
   ```
6. Click "Save"
7. Copy the Assistant ID (starts with `asst_`)
8. Add it to `.env`: `OPENAI_ASSISTANT_ID=asst_xxxxx`

---

### Step 5: Start the Server

**I can do this for you!** Once everything is set up, I can start the server.

**OR** you can run:
```bash
cd middleware
npm start
```

You should see:
```
🚀 Middleware server running on port 3000
```

---

## 🎯 Quick Start (Tell Me What You Have)

If you already have your credentials, just tell me:

1. **OpenAI API Key**: `sk-...`
2. **Dropbox Access Token**: `...`
3. **WordPress URL**: `https://...`
4. **WordPress Username**: `...`
5. **WordPress Application Password**: `...`

I'll add them all to your `.env` file and run the setup!

---

## 📝 Next Steps After Setup

Once the server is running:

1. **Add the widget to WordPress:**
   - Upload `frontend/chat-widget.js` to your theme folder
   - Add this to your theme's `footer.php` (before `</body>`):
   
   ```php
   <script>
     const CHATBOT_MIDDLEWARE_URL = 'http://localhost:3000';
   </script>
   <script src="<?php echo get_template_directory_uri(); ?>/chat-widget.js"></script>
   ```

2. **Test it:**
   - Visit your WordPress site
   - Click the chat bubble in the bottom-right
   - Type a message!

---

## ❓ Need Help?

Just tell me what step you're on and what you need help with. I can:
- Run commands for you
- Add credentials to your `.env` file (if you give them to me)
- Help troubleshoot issues
- Explain what each step does

