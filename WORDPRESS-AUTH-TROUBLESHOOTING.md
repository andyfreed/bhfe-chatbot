# WordPress Authentication Troubleshooting

## Current Issue

The WordPress REST API is returning `401 Unauthorized` with error `invalid_username: Unknown username`.

## The Problem

The error suggests that WordPress doesn't recognize the username `bhfe-chatbot-staging`. This could mean:

1. **The username doesn't exist** - The WordPress user with this username might not exist
2. **Application password was created for a different user** - The password might be associated with a different username
3. **Username format issue** - WordPress might be case-sensitive or require a different format

## Solution Steps

### Step 1: Verify the WordPress Username

1. Go to WordPress Admin → Users
2. Find the user that has the application password
3. Check the exact username (it might be different from `bhfe-chatbot-staging`)
4. Common variations:
   - `bhfe-chatbot-staging` (what we're using)
   - `bhfe_chatbot_staging` (underscores instead of hyphens)
   - `bhfechatbotstaging` (no separators)
   - Email address format

### Step 2: Create a New Application Password

1. Go to WordPress Admin → Users → Your Profile (or the user that should have access)
2. Scroll to "Application Passwords"
3. **Delete the old one** if it exists
4. Create a new application password:
   - Name: `Chatbot API` or `Middleware`
   - Click "Add New Application Password"
5. **Copy the new password immediately** (it's shown only once)
6. Format: `username:xxxx xxxx xxxx xxxx`

### Step 3: Update Render Environment Variables

1. Go to: https://dashboard.render.com/web/srv-d45nkfuuk2gs73cmc80g
2. Click "Environment" tab
3. Find `WORDPRESS_API_SECRET`
4. Update it with: `username:password` (with spaces in the password)
   - Example: `bhfe-chatbot-staging:3O4P vtTl CPOC rNqB zmFf 9dP7`
   - The code will automatically remove spaces
5. Save (this will trigger a redeploy)

### Step 4: Verify the Username

If you're unsure of the username, you can:

**Option A: Check WordPress Admin**
- Go to Users → All Users
- Find the user → Edit
- Check the "Username" field (not the display name)

**Option B: Use Email Instead**
- WordPress Application Passwords can sometimes use email addresses
- Try: `email@example.com:password` instead of `username:password`

**Option C: Test with curl**
```bash
curl -X GET "https://bhfestaging.wpenginepowered.com/wp-json/wp/v2/products" \
  -u "username:password" \
  -H "Content-Type: application/json"
```

Replace `username:password` with your actual credentials (no spaces in password).

### Step 5: Check Permissions

Make sure the user has:
- ✅ Administrator role (or at least permissions to read products)
- ✅ Application Passwords enabled (usually enabled by default in WordPress 5.6+)

## Alternative: Use a Different User

If the current user doesn't work, try:

1. **Use an existing admin user**
   - Go to WordPress Admin → Users
   - Find an administrator account
   - Create an application password for that user
   - Update `WORDPRESS_API_SECRET` in Render

2. **Create a new user specifically for the API**
   - Go to Users → Add New
   - Username: `api-bot` (simple, no special characters)
   - Email: something you can access
   - Role: Administrator
   - Create application password
   - Use that username:password in Render

## Testing

After updating, test with:
- "Show me courses about ethics"
- "What courses do you have?"

The logs will show: `[DEBUG] Using username for auth: <username>` to help verify what's being sent.

