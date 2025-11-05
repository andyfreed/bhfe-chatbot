# Fix WordPress Authentication

## Current Status

I can see the application password `bhfe-chatbot-staging` exists in WordPress, but it shows "Last Used: —" which means it's never been used successfully.

## The Issue

The application password **name** (`bhfe-chatbot-staging`) is just a label. The actual **WordPress username** might be different.

## What We Need to Do

### Step 1: Find the Actual WordPress Username

1. **In the WordPress Admin**, look at the top right corner of the screen
2. **Check what username is displayed** (this is the actual WordPress username)
3. OR go to **Users → All Users** and find the user you're logged in as
4. **Note the exact username** (it might be different from `bhfe-chatbot-staging`)

Common possibilities:
- Your email address (if you log in with email)
- A different username format
- An admin account name

### Step 2: Update Render with Correct Username

1. Go to: https://dashboard.render.com/web/srv-d45nkfuuk2gs73cmc80g
2. Click **"Environment"** tab
3. Find `WORDPRESS_API_SECRET`
4. Update it with: `actual-username:3O4P vtTl CPOC rNqB zmFf 9dP7`
   - Replace `actual-username` with the real WordPress username
   - Keep the password as-is (with spaces)
5. **Save** (this will trigger a redeploy)

### Step 3: Alternative - Regenerate the Password

If you're unsure of the username, you can:

1. **Revoke** the existing `bhfe-chatbot-staging` password
2. **Create a new one** with a clear name like `Chatbot API`
3. When it's created, WordPress will show you the password **once**
4. Copy it in this format: `username:xxxx xxxx xxxx xxxx`
5. Use that exact format in Render

### Step 4: Test

After updating Render, wait 2-3 minutes for deployment, then test:
- "Show me courses about ethics"
- "What courses do you have?"

## Quick Check

What username do you see in the WordPress admin (top right corner)? That's likely the username we need to use.

