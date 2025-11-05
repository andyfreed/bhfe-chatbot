<?php
/**
 * Plugin Name: BHFE AI Chatbot
 * Plugin URI: https://www.bhfe.com
 * Description: AI-powered chatbot using OpenAI Assistants API with Dropbox and WordPress integrations
 * Version: 1.0.0
 * Author: Beacon Hill Financial Educators
 * Author URI: https://www.bhfe.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bhfe-chatbot
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BHFE_CHATBOT_VERSION', '1.0.0');
define('BHFE_CHATBOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BHFE_CHATBOT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class BHFE_Chatbot {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Admin settings
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // Enqueue frontend script
        add_action('wp_footer', array($this, 'enqueue_chat_widget'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            'BHFE Chatbot Settings',
            'BHFE Chatbot',
            'manage_options',
            'bhfe-chatbot',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('bhfe_chatbot_settings', 'bhfe_chatbot_middleware_url');
        register_setting('bhfe_chatbot_settings', 'bhfe_chatbot_enabled');
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Save settings
        if (isset($_POST['bhfe_chatbot_save_settings'])) {
            check_admin_referer('bhfe_chatbot_settings');
            
            $middleware_url = sanitize_text_field($_POST['bhfe_chatbot_middleware_url']);
            $enabled = isset($_POST['bhfe_chatbot_enabled']) ? 1 : 0;
            
            update_option('bhfe_chatbot_middleware_url', $middleware_url);
            update_option('bhfe_chatbot_enabled', $enabled);
            
            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }
        
        $middleware_url = get_option('bhfe_chatbot_middleware_url', 'http://localhost:3000');
        $enabled = get_option('bhfe_chatbot_enabled', 1);
        
        ?>
        <div class="wrap">
            <h1>BHFE Chatbot Settings</h1>
            <form method="post" action="">
                <?php wp_nonce_field('bhfe_chatbot_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="bhfe_chatbot_enabled">Enable Chatbot</label>
                        </th>
                        <td>
                            <input type="checkbox" 
                                   id="bhfe_chatbot_enabled" 
                                   name="bhfe_chatbot_enabled" 
                                   value="1" 
                                   <?php checked($enabled, 1); ?>>
                            <p class="description">Enable or disable the chatbot widget on your site.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="bhfe_chatbot_middleware_url">Middleware Server URL</label>
                        </th>
                        <td>
                            <input type="url" 
                                   id="bhfe_chatbot_middleware_url" 
                                   name="bhfe_chatbot_middleware_url" 
                                   value="<?php echo esc_attr($middleware_url); ?>" 
                                   class="regular-text"
                                   placeholder="http://localhost:3000 or https://your-server.com">
                            <p class="description">
                                The URL of your middleware server. 
                                For local testing use: <code>http://localhost:3000</code><br>
                                For production, use your deployed server URL (e.g., <code>https://your-middleware.herokuapp.com</code>)
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Settings', 'primary', 'bhfe_chatbot_save_settings'); ?>
            </form>
            
            <hr>
            
            <h2>Testing Instructions</h2>
            <ol>
                <li>Make sure your middleware server is running</li>
                <li>Enter the middleware URL above (e.g., <code>http://localhost:3000</code> for local testing)</li>
                <li>Save settings</li>
                <li>Visit your site and look for the chat bubble in the bottom-right corner</li>
            </ol>
            
            <h2>Deployment</h2>
            <p>For production use, you'll need to:</p>
            <ol>
                <li>Deploy your middleware server to a hosting service (Render, Fly.io, Heroku, etc.)</li>
                <li>Update the Middleware Server URL above with your production URL</li>
                <li>Make sure CORS is configured to allow requests from your WordPress domain</li>
            </ol>
        </div>
        <?php
    }
    
    /**
     * Enqueue chat widget script
     */
    public function enqueue_chat_widget() {
        // Only load if enabled
        if (!get_option('bhfe_chatbot_enabled', 1)) {
            return;
        }
        
        $middleware_url = get_option('bhfe_chatbot_middleware_url', 'http://localhost:3000');
        
        // Remove trailing slash
        $middleware_url = rtrim($middleware_url, '/');
        
        ?>
        <script>
            // Set middleware URL for the widget
            window.CHATBOT_MIDDLEWARE_URL = '<?php echo esc_js($middleware_url); ?>';
        </script>
        <script src="<?php echo esc_url(BHFE_CHATBOT_PLUGIN_URL . 'assets/chat-widget.js'); ?>"></script>
        <?php
    }
}

// Initialize plugin
BHFE_Chatbot::get_instance();

