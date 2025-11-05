# Deployment Guide: Middleware Backend

Your WordPress plugin runs on WP Engine, but the **middleware backend** needs to be hosted separately. This guide explains your options.

## Quick Answer

**You need to host the middleware somewhere.** Options:
- ✅ **Render.com** (Recommended - easiest, free tier)
- ✅ **Fly.io** (Good for streaming, free tier)
- ⚠️ **Vercel** (Works, but streaming can be tricky)
- ✅ **Your own server** (Any VPS like DigitalOcean, AWS, etc.)

## Option 1: Render.com (Recommended for Beginners)

### Why Render?
- ✅ Free tier available
- ✅ Easy setup (connects to GitHub)
- ✅ Automatic deployments
- ✅ Good for streaming
- ✅ No credit card required for free tier

### Setup Steps

1. **Create Render account:**
   - Go to https://render.com
   - Sign up with GitHub

2. **Create new Web Service:**
   - Click "New" → "Web Service"
   - Connect your GitHub repo: `andyfreed/bhfe-chatbot`
   - Configure:
     - **Name**: `bhfe-chatbot-middleware`
     - **Root Directory**: `middleware` (important!)
     - **Environment**: `Node`
     - **Build Command**: `npm install`
     - **Start Command**: `npm start`
     - **Plan**: Free (or choose paid for better performance)

3. **Add Environment Variables:**
   - In Render dashboard, go to "Environment"
   - Add all variables from your `.env` file:
     ```
     OPENAI_API_KEY=sk-...
     OPENAI_ASSISTANT_ID=asst_...
     DROPBOX_ACCESS_TOKEN=sl.u...
     WORDPRESS_API_URL=https://www.bhfe.com/wp-json
     WORDPRESS_API_SECRET=bhfe-chatbot:...
     PORT=10000
     NODE_ENV=production
     ```
   - **Note**: Render automatically sets `PORT`, so you might not need it

4. **Deploy:**
   - Click "Create Web Service"
   - Render will build and deploy automatically
   - You'll get a URL like: `https://bhfe-chatbot-middleware.onrender.com`

5. **Update WordPress Plugin:**
   - Go to WordPress Admin → Settings → BHFE Chatbot
   - Set Middleware URL: `https://bhfe-chatbot-middleware.onrender.com`
   - Save

6. **Test:**
   - Visit your WordPress site
   - Click the chat bubble
   - Test a message!

## Option 2: Fly.io (Good for Streaming)

### Why Fly.io?
- ✅ Free tier available
- ✅ Great for streaming/SSE
- ✅ Fast global deployment
- ⚠️ Requires CLI setup

### Setup Steps

1. **Install Fly CLI:**
   ```bash
   # Windows (PowerShell)
   powershell -Command "iwr https://fly.io/install.ps1 -useb | iex"
   
   # Mac/Linux
   curl -L https://fly.io/install.sh | sh
   ```

2. **Login:**
   ```bash
   fly auth login
   ```

3. **Initialize (in middleware folder):**
   ```bash
   cd middleware
   fly launch
   ```
   - Follow prompts
   - Choose app name (or use default)
   - Choose region
   - Don't deploy yet (say "no")

4. **Set secrets:**
   ```bash
   fly secrets set OPENAI_API_KEY="sk-..."
   fly secrets set OPENAI_ASSISTANT_ID="asst_..."
   fly secrets set DROPBOX_ACCESS_TOKEN="sl.u..."
   fly secrets set WORDPRESS_API_URL="https://www.bhfe.com/wp-json"
   fly secrets set WORDPRESS_API_SECRET="bhfe-chatbot:..."
   fly secrets set NODE_ENV="production"
   ```

5. **Deploy:**
   ```bash
   fly deploy
   ```

6. **Get URL:**
   - After deploy, you'll get a URL like: `https://your-app.fly.dev`
   - Use this in WordPress plugin settings

## Option 3: Vercel (Possible but Limited)

### Why Vercel?
- ✅ Free tier available
- ✅ Easy GitHub integration
- ⚠️ Serverless (can be slower for first request)
- ⚠️ Streaming support is limited
- ⚠️ Function timeout limits (10s on free tier)

### Setup Steps

1. **Create Vercel account:**
   - Go to https://vercel.com
   - Sign up with GitHub

2. **Import project:**
   - Click "Add New" → "Project"
   - Import `andyfreed/bhfe-chatbot`
   - Configure:
     - **Root Directory**: `middleware`
     - **Framework Preset**: Other
     - **Build Command**: `npm install`
     - **Output Directory**: (leave empty)

3. **Add Environment Variables:**
   - Add all variables from `.env` file

4. **Create `vercel.json`** in `middleware/` folder:
   ```json
   {
     "version": 2,
     "builds": [
       {
         "src": "index.js",
         "use": "@vercel/node"
       }
     ],
     "routes": [
       {
         "src": "/(.*)",
         "dest": "/index.js"
       }
     ]
   }
   ```

5. **Deploy:**
   - Click "Deploy"
   - You'll get a URL like: `https://your-app.vercel.app`

6. **Update WordPress Plugin:**
   - Use the Vercel URL in plugin settings

### Vercel Limitations

- **Streaming**: May not work perfectly (serverless functions have limits)
- **Timeout**: Free tier has 10-second timeout
- **Cold starts**: First request can be slow

**Recommendation**: Use Render or Fly.io instead for better streaming support.

## Option 4: Your Own Server (Advanced)

If you have a VPS (DigitalOcean, AWS, etc.):

1. **SSH into server:**
   ```bash
   ssh user@your-server.com
   ```

2. **Install Node.js:**
   ```bash
   curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
   sudo apt-get install -y nodejs
   ```

3. **Clone repository:**
   ```bash
   git clone https://github.com/andyfreed/bhfe-chatbot.git
   cd bhfe-chatbot/middleware
   ```

4. **Install dependencies:**
   ```bash
   npm install
   ```

5. **Create .env file:**
   ```bash
   cp env.example .env
   nano .env  # Add your credentials
   ```

6. **Use PM2 to keep it running:**
   ```bash
   npm install -g pm2
   pm2 start index.js --name bhfe-chatbot
   pm2 save
   pm2 startup  # Auto-start on reboot
   ```

7. **Set up nginx (optional):**
   - Configure reverse proxy to port 3000
   - Set up SSL with Let's Encrypt

## Comparison Table

| Service | Free Tier | Ease of Setup | Streaming | Best For |
|---------|-----------|---------------|-----------|----------|
| **Render** | ✅ Yes | ⭐⭐⭐⭐⭐ | ✅ Good | Beginners |
| **Fly.io** | ✅ Yes | ⭐⭐⭐⭐ | ✅✅ Excellent | Streaming |
| **Vercel** | ✅ Yes | ⭐⭐⭐⭐⭐ | ⚠️ Limited | Serverless apps |
| **Own Server** | ❌ No | ⭐⭐ | ✅✅ Excellent | Full control |

## My Recommendation

**For your use case (staging/testing):**

1. **Start with Render.com** - Easiest setup, free, works great
2. **If you need better streaming**: Try Fly.io
3. **For production**: Either Render (paid) or your own server

## Quick Start with Render (5 minutes)

1. Sign up at render.com
2. New → Web Service
3. Connect GitHub repo: `andyfreed/bhfe-chatbot`
4. Root Directory: `middleware`
5. Build: `npm install`, Start: `npm start`
6. Add environment variables
7. Deploy!
8. Copy URL to WordPress plugin settings

## Testing After Deployment

1. **Test health endpoint:**
   ```
   https://your-middleware-url.com/health
   ```
   Should return: `{"status":"ok","timestamp":"..."}`

2. **Test chat endpoint (optional):**
   ```bash
   curl -X POST https://your-middleware-url.com/chat \
     -H "Content-Type: application/json" \
     -d '{"message":"Hello"}'
   ```

3. **Test in WordPress:**
   - Visit your WordPress site
   - Click chat bubble
   - Send a message

## Troubleshooting

### "Connection refused" or timeout
- Check that middleware is deployed and running
- Verify the URL in WordPress plugin settings
- Check Render/Fly.io logs for errors

### CORS errors
- Make sure middleware CORS allows your WordPress domain
- Check `middleware/index.js` CORS settings

### Streaming not working
- Render and Fly.io: Should work fine
- Vercel: May have issues (use Render instead)

## Next Steps

1. ✅ Choose a hosting service (I recommend Render)
2. ✅ Deploy middleware
3. ✅ Get the URL
4. ✅ Update WordPress plugin settings
5. ✅ Test on staging site
6. ✅ Deploy to production when ready

---

**Bottom line**: You don't need Vercel specifically. Render.com is easier and better for this use case!

