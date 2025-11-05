# WP Pusher Setup Guide

This guide explains how to deploy and update the BHFE Chatbot plugin using WP Pusher.

## What is WP Pusher?

WP Pusher is a WordPress plugin that allows you to deploy plugins and themes directly from GitHub repositories. This means you can:

- Push code to GitHub → Automatically update on your WordPress site
- Manage versions through Git
- No need to manually upload ZIP files
- Easy rollbacks through Git history

## Setup Steps

### 1. Push Code to GitHub

First, make sure your code is in a GitHub repository:

```bash
# Initialize git (if not already done)
git init

# Add all files
git add .

# Commit
git commit -m "Initial commit: BHFE Chatbot plugin"

# Add your GitHub repository as remote
git remote add origin https://github.com/yourusername/bhfe-chatbot.git

# Push to GitHub
git push -u origin main
```

### 2. Install WP Pusher on WordPress

1. **Install WP Pusher plugin:**
   - Go to WordPress Admin → Plugins → Add New
   - Search for "WP Pusher"
   - Install and activate "WP Pusher" by WP Pusher

2. **Get your GitHub token:**
   - Go to GitHub → Settings → Developer settings → Personal access tokens
   - Click "Generate new token (classic)"
   - Give it a name (e.g., "WP Pusher")
   - Select scopes: `repo` (for private repos) or `public_repo` (for public repos)
   - Generate token and **copy it** (you won't see it again!)

### 3. Configure WP Pusher

1. **Go to WP Pusher settings:**
   - WordPress Admin → Settings → WP Pusher
   - Or go to Plugins → WP Pusher

2. **Connect GitHub:**
   - Click "Connect GitHub" or "Add GitHub Token"
   - Paste your GitHub token
   - Click "Save"

3. **Add the plugin:**
   - Go to **Plugins** → **WP Pusher** → **Install Plugin**
   - Or go to **WP Pusher** → **Install Plugin**
   - Fill in:
     - **Repository**: `yourusername/bhfe-chatbot` (or your GitHub username/repo)
     - **Branch**: `main` (or `master`)
     - **Subdirectory**: `wordpress-plugin` (this is important!)
     - **Host**: GitHub
   - Click **Install Plugin**

4. **Activate the plugin:**
   - Go to **Plugins**
   - Find "BHFE AI Chatbot"
   - Click **Activate**

### 4. Configure the Chatbot

1. **Go to Settings:**
   - WordPress Admin → Settings → BHFE Chatbot

2. **Enter middleware URL:**
   - For staging: Your deployed middleware URL or ngrok URL
   - Enable the chatbot

3. **Save Settings**

## Updating the Plugin

### Automatic Updates (Recommended)

When you push changes to GitHub:

1. **Push your changes:**
   ```bash
   git add .
   git commit -m "Update chatbot widget styling"
   git push
   ```

2. **Update on WordPress:**
   - Go to **Plugins** → **WP Pusher**
   - Find "BHFE AI Chatbot"
   - Click **Pull** or **Update**
   - The plugin will update from GitHub

### Manual Updates

You can also set up automatic pulls:
- Go to **WP Pusher** → **Settings**
- Enable "Automatic updates" (if available)
- Or set up a webhook to trigger updates automatically

## Important Notes

### Plugin Structure

The plugin is in the `wordpress-plugin/` folder. WP Pusher needs to know this:

- **Subdirectory**: `wordpress-plugin`
- **Main File**: `bhfe-chatbot.php`

This is already configured in `.wppusher.json` in the repository root.

### File Structure

Your repository should look like this:

```
bhfe-chatbot/
├── .wppusher.json          # WP Pusher config
├── middleware/             # Node.js backend (not deployed)
├── frontend/                # Original widget (not deployed)
├── wordpress-plugin/        # WordPress plugin (deployed)
│   ├── bhfe-chatbot.php    # Main plugin file
│   ├── assets/
│   │   └── chat-widget.js  # Widget JavaScript
│   ├── readme.txt          # Plugin readme
│   └── README.md           # Plugin docs
└── README.md               # Project docs
```

### What Gets Deployed

Only the `wordpress-plugin/` folder is deployed to WordPress. The `middleware/` and `frontend/` folders are not deployed (they're separate).

### Staging vs Production

You can use WP Pusher on both:

1. **Staging site** (`bhfestaging.wpenginepowered.com`):
   - Install WP Pusher
   - Connect to GitHub
   - Install plugin from `wordpress-plugin/` folder
   - Test changes here first

2. **Production site** (`bhfe.com`):
   - Same setup
   - Install from same GitHub repo
   - Use production middleware URL

## Troubleshooting

### Plugin not installing

- **Check subdirectory**: Make sure it's set to `wordpress-plugin`
- **Check branch**: Make sure it's `main` or `master`
- **Check repository**: Make sure it's `username/repo-name`
- **Check GitHub token**: Make sure it has the right permissions

### Updates not working

- **Check GitHub connection**: Settings → WP Pusher → verify token
- **Check branch**: Make sure you're pushing to the branch WP Pusher is watching
- **Manual pull**: Try clicking "Pull" manually in WP Pusher

### Plugin structure issues

- Make sure `bhfe-chatbot.php` is in `wordpress-plugin/` folder
- Make sure `assets/chat-widget.js` is in `wordpress-plugin/assets/` folder
- The main plugin file should have proper WordPress headers

## Best Practices

1. **Use branches for testing:**
   - Create a `develop` branch for testing
   - Install from `develop` on staging
   - Merge to `main` when ready
   - Install from `main` on production

2. **Version control:**
   - Update version in `bhfe-chatbot.php` header
   - Update changelog in `readme.txt`
   - Tag releases in GitHub

3. **Test before deploying:**
   - Test on staging first
   - Verify all features work
   - Then update production

## Alternative: Manual ZIP Upload

If you prefer not to use WP Pusher:

1. Create ZIP of `wordpress-plugin/` folder
2. Upload via WordPress Admin → Plugins → Add New → Upload
3. Activate plugin
4. Configure settings

But WP Pusher makes updates much easier!

## Next Steps

1. ✅ Push code to GitHub
2. ✅ Install WP Pusher on staging site
3. ✅ Connect GitHub account
4. ✅ Install plugin via WP Pusher
5. ✅ Configure middleware URL
6. ✅ Test chatbot
7. ✅ Deploy to production when ready

---

For more information, see:
- [WP Pusher Documentation](https://wppusher.com/docs)
- Main project [README.md](README.md)

