# Changelog

All notable changes to the BHFE Course Chatbot plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024

### Added
- Initial release of BHFE Course Chatbot
- OpenAI GPT integration for intelligent responses
- Dropbox file search integration
- Smart course matching based on keywords
- Conversational history management
- Customizable chatbot appearance (colors, position)
- Admin settings page for configuration
- Mobile responsive design
- Session-based conversation tracking
- Secure AJAX handling with nonces
- Support for multiple OpenAI models (GPT-4, GPT-4 Turbo, GPT-3.5)
- File content extraction from Dropbox
- Relevance scoring for search results
- Welcome message on first load
- Typing indicators
- Professional styling and animations
- Comprehensive documentation

### Security
- API keys stored securely in WordPress database
- Nonce protection for all AJAX requests
- Session transient storage with expiration
- No long-term data storage

### Performance
- Efficient file search with relevance scoring
- Content truncation to manage token usage
- Optimized API calls
- Lightweight frontend code

