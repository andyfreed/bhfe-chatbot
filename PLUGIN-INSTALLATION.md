# Plugin Installation Guide

## Quick Installation for Staging Site

Your staging site is at: **https://bhfestaging.wpenginepowered.com/**

### Step 1: Create Plugin ZIP

1. Navigate to the `wordpress-plugin` folder
2. Select all files in the folder
3. Create a ZIP file named `bhfe-chatbot.zip`
   - On Windows: Right-click → Send to → Compressed (zipped) folder
   - On Mac: Right-click → Compress Items

### Step 2: Upload to WordPress

1. **Log into your staging WordPress site:**
   - Go to: https://bhfestaging.wpenginepowered.com/wp-admin

2. **Install the plugin:**
   - Go to **Plugins** → **Add New**
   - Click **Upload Plugin**
   - Click **Choose File** and select `bhfe-chatbot.zip`
   - Click **Install Now**
   - Click **Activate Plugin**

### Step 3: Configure the Plugin

1. **Go to Settings:**
   - Navigate to **Settings** → **BHFE Chatbot**
   - Or go to **Plugins** → **BHFE Chatbot** → **Settings**

2. **Set up the middleware URL:**
   
   Since your staging site needs to reach the middleware server, you have two options:

   **Option A: Use ngrok (for local testing)**
   
   1. **Install ngrok** (if you don't have it):
      - Download from: https://ngrok.com/download
      - Or install via chocolatey: `choco install ngrok`
   
   2. **Start your middleware server:**
      ```bash
      cd middleware
      npm start
      ```
   
   3. **Start ngrok in a new terminal:**
      ```bash
      ngrok http 3000
      ```
   
   4. **Copy the HTTPS URL** that ngrok gives you (e.g., `https://abc123.ngrok.io`)
   
   5. **Enter it in plugin settings:**
      - Middleware URL: `https://abc123.ngrok.io`
      - Enable Chatbot: ✓ Checked
      - Click **Save Settings**

   **Option B: Deploy middleware to a hosting service**
   
   1. Deploy to Render.com or Fly.io (see README.md for instructions)
   2. Use the deployed URL in plugin settings

### Step 4: Test the Chatbot

1. **Visit your staging site:**
   - Go to: https://bhfestaging.wpenginepowered.com/

2. **Look for the chat bubble:**
   - You should see a blue chat bubble in the bottom-right corner

3. **Test it:**
   - Click the chat bubble
   - Type a message like: "Hello" or "Search Dropbox for my files"
   - The assistant should respond!

## Troubleshooting

### Plugin won't install

- Make sure the ZIP file includes the `bhfe-chatbot.php` file at the root
- The ZIP should contain: `bhfe-chatbot.php`, `assets/` folder, and `README.md`

### Widget not appearing

- Check plugin is activated: **Plugins** → look for "BHFE AI Chatbot" → should be "Active"
- Check settings: **Settings** → **BHFE Chatbot** → "Enable Chatbot" should be checked
- Verify middleware URL is correct
- Check browser console (F12) for errors

### Connection errors

- Make sure middleware server is running
- If using ngrok, make sure ngrok is still running
- Verify the middleware URL in plugin settings matches your ngrok/ deployed URL
- Check CORS settings on middleware (should allow your WordPress domain)

### Testing locally vs staging

- **Local middleware + ngrok**: Best for quick testing
- **Deployed middleware**: Best for production-like testing

## Next Steps

Once everything is working on staging:

1. Test all features (Dropbox search, WordPress data retrieval)
2. Customize the assistant's behavior if needed
3. Deploy middleware to production
4. Install plugin on production site
5. Update plugin settings with production middleware URL

## Files Included

The plugin includes:
- `bhfe-chatbot.php` - Main plugin file with settings page
- `assets/chat-widget.js` - The chat widget JavaScript
- `README.md` - Plugin documentation

All files are in the `wordpress-plugin/` folder.

