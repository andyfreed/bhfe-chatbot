=== BHFE AI Chatbot ===
Contributors: bhfe
Tags: chatbot, ai, openai, assistant, dropbox, wordpress
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered chatbot using OpenAI Assistants API with Dropbox and WordPress integrations.

== Description ==

BHFE AI Chatbot is a WordPress plugin that adds an intelligent chatbot to your site using OpenAI's Assistants API. The chatbot can:

* Answer questions using AI
* Search Dropbox files
* Retrieve WordPress data via REST API
* Maintain conversation context across sessions

The plugin provides a clean, modern chat interface that appears as a floating widget on your site.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/bhfe-chatbot` directory, or install the plugin through the WordPress plugins screen directly using WP Pusher
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->BHFE Chatbot screen to configure the middleware server URL

== Frequently Asked Questions ==

= How do I configure the middleware server? =

Go to Settings -> BHFE Chatbot and enter your middleware server URL. For local development, use `http://localhost:3000`. For production, use your deployed server URL.

= Can I use this with a local development server? =

Yes, but you'll need to use a tunneling service like ngrok to make your local server accessible from your WordPress site.

= Does this work with WP Engine? =

Yes, this plugin works with WP Engine hosting, including staging environments.

== Changelog ==

= 1.0.0 =
* Initial release
* OpenAI Assistants API integration
* Dropbox file search integration
* WordPress REST API integration
* Admin settings page
* Responsive chat widget

== Upgrade Notice ==

= 1.0.0 =
Initial release.

