# Quick Start Guide

Get your chatbot up and running in 5 minutes!

## Step 1: Install the Plugin

1. Upload the `bhfe-chatbot` folder to `/wp-content/plugins/`
2. Go to WordPress Admin → Plugins
3. Find "BHFE Course Chatbot" and click "Activate"

## Step 2: Get Your API Keys

### OpenAI API Key (Required)
1. Go to https://platform.openai.com/api-keys
2. Click "Create new secret key"
3. Copy the key

### Dropbox Access Token (Required)
1. Go to https://www.dropbox.com/developers/apps
2. Create app → "Full Dropbox" access
3. Generate access token
4. Copy the token

## Step 3: Configure Settings

1. Go to WordPress Admin → Chatbot
2. Fill in:
   - ✅ **Enable Chatbot** - Check the box
   - **OpenAI API Key** - Paste from Step 2
   - **Dropbox Access Token** - Paste from Step 2
   - **Dropbox Folder Path** - e.g., `/Course Files`
3. **Business Description** - Write a few sentences about your courses
4. Click "Save Settings"

## Step 4: Test It!

1. Visit your website
2. Look for the chat icon in the bottom-right corner
3. Click it and ask: "What courses do you have for CPAs?"
4. Watch the magic happen! 🎉

## Example Business Description

```
BHFE provides CPE and CE courses for CFPs, CPAs, IRS enrolled agents, CDFAs, and IARs. 
We cover topics like tax planning, retirement planning, estate planning, ethics, and compliance. 
All courses are approved and feature expert instructors.
```

## Common Issues

**Chatbot not showing?**
- Make sure "Enable Chatbot" is checked
- Clear your browser cache

**Getting errors?**
- Verify your API keys are correct
- Check if OpenAI account has credits
- Ensure Dropbox token is valid

**Need more help?**
- Read the full README.md
- Check INSTALLATION.txt for detailed steps

That's it! Your chatbot is ready to help your customers find the perfect course.

