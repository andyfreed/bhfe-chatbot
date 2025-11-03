# BHFE Course Chatbot WordPress Plugin

A powerful AI-powered chatbot plugin for WordPress that helps customers search and find information about your CPE/CE courses. The chatbot integrates with OpenAI's API and your Dropbox course files to provide intelligent, context-aware responses.

## Features

- 🤖 **AI-Powered Responses** - Uses OpenAI GPT models for natural conversation
- 📁 **Dropbox Integration** - Searches through your course files to find relevant information
- 🎯 **Smart Course Matching** - Automatically detects course-related queries and searches your library
- 💬 **Conversational History** - Maintains context throughout the conversation
- 🎨 **Customizable Design** - Configurable colors and positioning
- 📱 **Mobile Responsive** - Works beautifully on all devices
- ⚡ **Fast & Lightweight** - Optimized for performance

## Installation

1. Download or clone this repository
2. Upload the entire `bhfe-chatbot` folder to your WordPress site's `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

## Configuration

### 1. General Settings

Navigate to **Chatbot** in your WordPress admin menu and configure:

- **Enable Chatbot** - Turn the chatbot on/off
- **Chatbot Title** - Customize the header text
- **Chatbot Position** - Choose from bottom-right, bottom-left, top-right, or top-left
- **Theme Color** - Set your brand color

### 2. API Settings

#### OpenAI API Key

1. Sign up for an OpenAI account at https://platform.openai.com/
2. Navigate to API Keys section
3. Create a new API key
4. Paste it into the **OpenAI API Key** field in plugin settings

#### OpenAI Model

Choose from available GPT models:
- GPT-4 (Recommended, most capable)
- GPT-4 Turbo (Faster, still highly capable)
- GPT-3.5 Turbo (Budget-friendly)

#### Dropbox Access Token

1. Go to https://www.dropbox.com/developers/apps
2. Create a new app or use an existing one
3. Generate an access token with read permissions
4. Paste it into the **Dropbox Access Token** field

#### Dropbox Folder Path

Enter the path to your course files folder in Dropbox (e.g., `/Course Files` or `/CPE Courses`)

### 3. Business Information

Add a detailed description of your business, services, and course offerings. This helps the AI provide more accurate and contextual responses to general questions.

Example:
```
BHFE provides comprehensive online CPE (Continuing Professional Education) and CE (Continuing Education) courses for financial professionals including CFPs, CPAs, IRS enrolled agents, CDFAs, IARs, and more. Our course library covers topics including tax planning, retirement planning, estate planning, ethics, and regulatory compliance. All courses are approved by relevant certifying bodies and feature expert instructors with decades of experience.
```

## How It Works

### Course Queries

When a user asks a question about courses, the chatbot:

1. Identifies it as a course-related query using smart keyword detection
2. Searches your Dropbox folder for relevant course files
3. Extracts content from the most relevant files
4. Sends the context to OpenAI along with the user's question
5. Returns a helpful response that may reference specific courses

### General Queries

For non-course questions, the chatbot:

1. Uses your business information as context
2. Answers based on general knowledge about your industry
3. Can discuss pricing, support, certifications, etc.

## Supported File Types

The chatbot can search for files in Dropbox and extract text from:
- PDF files (Dropbox API returns raw PDF, may need preprocessing)
- Text files (.txt, .md, .csv)
- Word documents (.doc, .docx)
- HTML/XML files
- Other text-based formats

Note: For best results with PDFs and Word docs, consider pre-converting to plain text or using a text extraction service. The plugin will attempt to read file contents but binary-formatted PDFs may not parse correctly through Dropbox's API.

Binary files will be recognized but their content won't be searchable.

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Active internet connection (for API calls)
- OpenAI API account
- Dropbox account with API access

## Security

- All API keys are stored securely in WordPress database
- AJAX requests are protected with nonces
- User data is only stored in session transients (expires after 1 hour)
- No sensitive data is stored long-term

## Troubleshooting

### Chatbot not appearing

- Ensure "Enable Chatbot" is checked in settings
- Clear your browser cache
- Check for JavaScript errors in browser console

### "I encountered an error" responses

- Verify your OpenAI API key is correct and has credits
- Check your API quota/billing status
- Review PHP error logs

### Dropbox searches not working

- Verify Dropbox access token is valid
- Ensure the folder path is correct (include leading /)
- Check that your Dropbox app has read permissions
- Verify files exist in the specified folder

### Poor response quality

- Add more detailed business description
- Organize your Dropbox files with descriptive names
- Consider switching to GPT-4 for better understanding
- Increase the number of context files retrieved

## Support

For issues, questions, or feature requests, please contact BHFE support.

## License

GPL v2 or later

## Credits

Developed for BHFE - Professional CPE/CE Course Provider

