# Architecture Overview

This document explains how the WordPress AI Chatbot system works.

## 🏗️ System Architecture

```
┌─────────────────────┐
│  WordPress Site     │
│  (Frontend)         │
│                     │
│  ┌───────────────┐  │
│  │ Chat Widget   │  │
│  │ (JavaScript)  │  │
│  └───────┬───────┘  │
└──────────┼──────────┘
           │
           │ POST /chat
           │ (HTTP/SSE)
           ▼
┌─────────────────────┐
│  Middleware Server  │
│  (Node.js/Express)  │
│                     │
│  ┌───────────────┐  │
│  │ Chat Route    │  │
│  └───────┬───────┘  │
│          │           │
│  ┌───────▼───────┐  │
│  │ OpenAI Service│  │
│  └───────┬───────┘  │
└──────────┼──────────┘
           │
           │ API Calls
           ▼
┌─────────────────────┐
│  OpenAI Assistants  │
│  API                │
│                     │
│  ┌───────────────┐  │
│  │ Assistant     │  │
│  │ (GPT-4/GPT-3.5)│  │
│  └───────┬───────┘  │
│          │           │
│          │ Function  │
│          │ Calls     │
│          ▼           │
│  ┌───────────────┐  │
│  │ Function       │  │
│  │ Definitions    │  │
│  └───────────────┘  │
└─────────────────────┘
           │
           │ Function Calls
           ▼
┌─────────────────────┐
│  Middleware Server  │
│  (Function Handlers)│
│                     │
│  ┌───────────────┐  │
│  │ Dropbox       │  │
│  │ Service       │  │
│  └───────┬───────┘  │
│          │           │
│  ┌───────▼───────┐  │
│  │ WordPress     │  │
│  │ Service       │  │
│  └───────┬───────┘  │
└──────────┼──────────┘
           │
           │ API Calls
           ▼
┌─────────────────────┐  ┌─────────────────────┐
│  Dropbox API        │  │  WordPress REST API  │
│  (File Search)      │  │  (Data Retrieval)    │
└─────────────────────┘  └─────────────────────┘
```

## 📁 Project Structure

```
bhfe-chatbot/
├── middleware/                    # Node.js backend server
│   ├── index.js                  # Express app entry point
│   ├── package.json              # Dependencies
│   ├── .env                      # Environment variables (not in git)
│   ├── env.example               # Example env file
│   ├── routes/
│   │   └── chat.js              # Chat endpoint handler
│   ├── services/
│   │   ├── openai.js            # OpenAI Assistants API integration
│   │   ├── dropbox.js           # Dropbox API integration
│   │   └── wordpress.js         # WordPress REST API integration
│   └── scripts/
│       └── create-assistant.js  # Helper to create OpenAI assistant
│
├── frontend/
│   └── chat-widget.js           # WordPress chat widget (JavaScript)
│
├── README.md                    # Full documentation
├── QUICKSTART.md                # Quick setup guide
└── ARCHITECTURE.md              # This file
```

## 🔄 Request Flow

### 1. User Sends Message

```
User types message in widget
    ↓
JavaScript sends POST to /chat
    ↓
Request includes: { message: "Hello", threadId: "..." }
```

### 2. Middleware Processes

```
Express receives POST /chat
    ↓
Chat route handler validates input
    ↓
Calls OpenAI service with message
```

### 3. OpenAI Assistant Responds

```
OpenAI service creates/retrieves thread
    ↓
Adds user message to thread
    ↓
Runs assistant
    ↓
Assistant may call functions (searchDropbox, getWordPressData)
    ↓
Middleware handles function calls
    ↓
Returns results to assistant
    ↓
Assistant generates final response
```

### 4. Response Streamed to Frontend

```
Middleware streams response chunks
    ↓
Frontend receives chunks via SSE
    ↓
Widget displays chunks as they arrive
    ↓
User sees streaming response
```

## 🔐 Security Architecture

### API Keys & Credentials

- **Never exposed to frontend**: All API keys stay on the middleware server
- **Environment variables**: All secrets stored in `.env` file
- **Git ignored**: `.env` is in `.gitignore` to prevent accidental commits

### Authentication Flow

```
WordPress Frontend
    ↓
    No authentication needed (public endpoint)
    ↓
Middleware Server
    ↓
    Validates request (optional: add rate limiting)
    ↓
    Uses API keys internally
    ↓
OpenAI API
    ↓
    Authenticated with OPENAI_API_KEY
    ↓
Dropbox API
    ↓
    Authenticated with DROPBOX_ACCESS_TOKEN
    ↓
WordPress REST API
    ↓
    Authenticated with WORDPRESS_API_SECRET
```

## 🛠️ Function Calling Flow

When the assistant needs data, it calls functions:

### Example: User asks "Search Dropbox for my budget"

```
1. User: "Search Dropbox for my budget"
   ↓
2. OpenAI Assistant decides to call searchDropbox("budget")
   ↓
3. Middleware receives function call
   ↓
4. Calls searchDropbox() function
   ↓
5. Dropbox API returns results
   ↓
6. Results sent back to assistant
   ↓
7. Assistant generates response: "I found 3 files: ..."
   ↓
8. Response streamed to frontend
```

### Example: User asks "Show me my latest posts"

```
1. User: "Show me my latest posts"
   ↓
2. OpenAI Assistant decides to call getWordPressData("/wp/v2/posts", {per_page: 5})
   ↓
3. Middleware receives function call
   ↓
4. Calls getWordPressData() function
   ↓
5. WordPress REST API returns posts
   ↓
6. Results sent back to assistant
   ↓
7. Assistant generates response: "Here are your latest posts: ..."
   ↓
8. Response streamed to frontend
```

## 📡 Communication Protocols

### Frontend → Middleware

- **Protocol**: HTTP POST
- **Format**: JSON
- **Response**: Server-Sent Events (SSE) for streaming
- **Example**:
  ```javascript
  POST /chat
  Content-Type: application/json
  Accept: text/event-stream
  
  {
    "message": "Hello",
    "threadId": "thread_abc123"
  }
  ```

### Middleware → OpenAI

- **Protocol**: HTTPS (OpenAI SDK)
- **Format**: JSON
- **API**: OpenAI Assistants API
- **Authentication**: API Key in header

### Middleware → Dropbox

- **Protocol**: HTTPS (Dropbox SDK)
- **Format**: JSON
- **API**: Dropbox API v2
- **Authentication**: Access Token in header

### Middleware → WordPress

- **Protocol**: HTTPS (Axios)
- **Format**: JSON
- **API**: WordPress REST API
- **Authentication**: Bearer Token or Basic Auth

## 🎯 Key Design Decisions

### Why a Middleware Server?

1. **Security**: API keys never exposed to browser
2. **Flexibility**: Easy to add new integrations
3. **Control**: Full control over function calling logic
4. **Scalability**: Can handle multiple WordPress sites

### Why OpenAI Assistants API?

1. **Thread Management**: Automatic conversation context
2. **Function Calling**: Built-in support for custom functions
3. **Streaming**: Native support for streaming responses
4. **Persistent**: Conversations persist across sessions

### Why Server-Sent Events (SSE)?

1. **Streaming**: Real-time response streaming
2. **Simple**: Easier than WebSockets for one-way streaming
3. **Compatible**: Works with standard HTTP infrastructure
4. **Automatic Reconnection**: Built-in retry logic

## 🔧 Extensibility

### Adding New Functions

1. Create function in `services/` (e.g., `services/database.js`)
2. Add to `availableFunctions` in `services/openai.js`
3. Add function definition to `functionDefinitions` array
4. Assistant automatically can call it!

### Adding New Integrations

1. Create service in `services/` (e.g., `services/notion.js`)
2. Add function to `availableFunctions`
3. Add function definition
4. Done!

### Customizing the Assistant

- **Instructions**: Edit in OpenAI dashboard or in `create-assistant.js`
- **Model**: Change in `create-assistant.js` (gpt-4-turbo-preview, gpt-3.5-turbo, etc.)
- **Tools**: Add more tools in OpenAI dashboard (Code Interpreter, File Search, etc.)

## 📊 Data Flow Diagram

```
User Input
    │
    ▼
Chat Widget (JavaScript)
    │
    │ POST /chat
    ▼
Express Router
    │
    ▼
Chat Route Handler
    │
    │ handleChatMessage()
    ▼
OpenAI Service
    │
    │ Create/Retrieve Thread
    │ Add User Message
    │ Run Assistant
    ▼
Assistant Processing
    │
    │ (May call functions)
    ▼
Function Handlers
    │
    ├─→ Dropbox Service ──→ Dropbox API
    │
    └─→ WordPress Service ──→ WordPress REST API
    │
    ▼
Function Results
    │
    ▼
Assistant Response
    │
    │ Stream chunks
    ▼
Chat Route Handler
    │
    │ SSE chunks
    ▼
Chat Widget
    │
    ▼
Display to User
```

## 🚀 Deployment Architecture

### Production Setup

```
┌─────────────────┐
│  WordPress Site  │
│  (WP Engine)     │
│                  │
│  - Chat Widget   │
│  - Loaded via    │
│    footer.php    │
└─────────────────┘
         │
         │ HTTPS
         │
         ▼
┌─────────────────┐
│  Middleware     │
│  (Render/Fly.io)│
│                 │
│  - Express App  │
│  - Environment  │
│    Variables    │
│  - API Keys     │
└─────────────────┘
         │
         │ HTTPS
         │
         ▼
┌─────────────────┐
│  External APIs  │
│                 │
│  - OpenAI       │
│  - Dropbox      │
│  - WordPress    │
│    (same site)  │
└─────────────────┘
```

## 🔍 Monitoring & Debugging

### Logs to Check

1. **Middleware Server Logs**: Console output from Express app
2. **OpenAI Dashboard**: API usage and errors
3. **Browser Console**: Frontend errors
4. **Network Tab**: HTTP requests/responses

### Common Issues

- **CORS errors**: Check middleware CORS settings
- **Function call failures**: Check service logs
- **Streaming issues**: Check SSE headers
- **Authentication errors**: Verify API keys

## 📝 Next Steps

1. **Add rate limiting**: Prevent abuse
2. **Add logging**: Track usage and errors
3. **Add caching**: Cache frequent requests
4. **Add analytics**: Track usage patterns
5. **Add error handling**: Better error messages
6. **Add tests**: Unit and integration tests

---

For questions or issues, see the main [README.md](README.md).

