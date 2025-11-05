# Dropbox Permission Fix

## The Problem

The error shows: `missing_scope` - required scope: `files.metadata.read`

This means your Dropbox app doesn't have the right permissions to read file metadata.

## The Solution

You need to update your Dropbox app permissions and regenerate the token.

### Step 1: Update Dropbox App Permissions

1. Go to https://www.dropbox.com/developers/apps
2. Find your app (or create a new one)
3. Click on your app to open settings
4. Go to the "Permissions" tab
5. Make sure these scopes are enabled:
   - ✅ `files.metadata.read` (required for searching files)
   - ✅ `files.content.read` (optional, for reading file contents)
   - ✅ `files.search` (required for searching)

### Step 2: Regenerate Access Token

1. Still in your app settings
2. Go to the "OAuth 2" section
3. Scroll down to "Generated access token"
4. Click "Generate" or "Regenerate"
5. Copy the new token (it will be long)

### Step 3: Update Render Environment Variable

1. Go to Render dashboard: https://dashboard.render.com/web/srv-d45nkfuuk2gs73cmc80g
2. Click "Environment" tab
3. Find `DROPBOX_ACCESS_TOKEN`
4. Click edit/pencil icon
5. Paste the new token
6. Click "Save Changes"
7. This will trigger a redeploy

### Step 4: Wait for Redeploy

Wait 2-3 minutes for the service to redeploy with the new token.

### Step 5: Test Again

Try the Dropbox search again in the chatbot.

## Alternative: Create New App

If you can't update the permissions on your existing app:

1. Create a new Dropbox app:
   - Go to https://www.dropbox.com/developers/apps
   - Click "Create app"
   - Choose "Scoped access"
   - Choose "Full Dropbox"
   - Name it "BHFE Chatbot" (or any name)
   - Click "Create app"

2. Set permissions:
   - Go to "Permissions" tab
   - Enable: `files.metadata.read`, `files.content.read`, `files.search`

3. Generate token:
   - Go to "OAuth 2" section
   - Click "Generate" under "Generated access token"
   - Copy the token

4. Update Render:
   - Update `DROPBOX_ACCESS_TOKEN` in Render dashboard
   - Save and wait for redeploy

## Required Permissions

Your Dropbox app needs these scopes:
- `files.metadata.read` - Read file/folder metadata
- `files.content.read` - Read file contents (optional)
- `files.search` - Search files

Make sure your app has "Full Dropbox" access, not just specific folders.

