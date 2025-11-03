<?php
/**
 * Plugin Name: BHFE Course Chatbot
 * Plugin URI: https://bhfe.com
 * Description: AI-powered chatbot that searches course files in Dropbox and answers questions about CPE/CE courses for CFPs, CPAs, IRS enrolled agents, CDFAs, and IARs
 * Version: 1.0.0
 * Author: BHFE
 * Author URI: https://bhfe.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bhfe-chatbot
 * Requires at least: 5.0
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
define('BHFE_CHATBOT_PLUGIN_FILE', __FILE__);

/**
 * Main plugin class
 */
class BHFE_Course_Chatbot {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('wp_footer', array($this, 'render_chatbot'));
        
        // AJAX hooks
        add_action('wp_ajax_bhfe_chatbot_send_message', array($this, 'handle_chat_message'));
        add_action('wp_ajax_nopriv_bhfe_chatbot_send_message', array($this, 'handle_chat_message'));
        
        // Load dependencies
        require_once BHFE_CHATBOT_PLUGIN_DIR . 'includes/class-dropbox-integration.php';
        require_once BHFE_CHATBOT_PLUGIN_DIR . 'includes/class-openai-integration.php';
        require_once BHFE_CHATBOT_PLUGIN_DIR . 'includes/class-chatbot-handler.php';
    }
    
    /**
     * Activation hook
     */
    public function activate() {
        // Set default options
        if (!get_option('bhfe_chatbot_settings')) {
            add_option('bhfe_chatbot_settings', array(
                'enabled' => '1',
                'chatbot_title' => 'Course Question? Ask Me!',
                'position' => 'bottom-right',
                'theme_color' => '#2563eb',
            ));
        }
    }
    
    /**
     * Deactivation hook
     */
    public function deactivate() {
        // Clean up if needed
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('BHFE Chatbot Settings', 'bhfe-chatbot'),
            __('Chatbot', 'bhfe-chatbot'),
            'manage_options',
            'bhfe-chatbot',
            array($this, 'render_admin_page'),
            'dashicons-format-chat',
            30
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('bhfe_chatbot_settings', 'bhfe_chatbot_settings', array($this, 'sanitize_settings'));
        
        // General settings section
        add_settings_section(
            'bhfe_chatbot_general_section',
            __('General Settings', 'bhfe-chatbot'),
            array($this, 'render_general_section_callback'),
            'bhfe-chatbot'
        );
        
        add_settings_field(
            'enabled',
            __('Enable Chatbot', 'bhfe-chatbot'),
            array($this, 'render_checkbox_field'),
            'bhfe-chatbot',
            'bhfe_chatbot_general_section',
            array(
                'label_for' => 'enabled',
                'description' => __('Enable or disable the chatbot on your website', 'bhfe-chatbot')
            )
        );
        
        add_settings_field(
            'chatbot_title',
            __('Chatbot Title', 'bhfe-chatbot'),
            array($this, 'render_text_field'),
            'bhfe-chatbot',
            'bhfe_chatbot_general_section',
            array(
                'label_for' => 'chatbot_title',
                'description' => __('Title displayed in the chatbot header', 'bhfe-chatbot')
            )
        );
        
        add_settings_field(
            'position',
            __('Chatbot Position', 'bhfe-chatbot'),
            array($this, 'render_select_field'),
            'bhfe-chatbot',
            'bhfe_chatbot_general_section',
            array(
                'label_for' => 'position',
                'options' => array(
                    'bottom-right' => 'Bottom Right',
                    'bottom-left' => 'Bottom Left',
                    'top-right' => 'Top Right',
                    'top-left' => 'Top Left'
                ),
                'description' => __('Position of the chatbot on the page', 'bhfe-chatbot')
            )
        );
        
        add_settings_field(
            'theme_color',
            __('Theme Color', 'bhfe-chatbot'),
            array($this, 'render_color_field'),
            'bhfe-chatbot',
            'bhfe_chatbot_general_section',
            array(
                'label_for' => 'theme_color',
                'description' => __('Primary color for the chatbot', 'bhfe-chatbot')
            )
        );
        
        // API Settings section
        add_settings_section(
            'bhfe_chatbot_api_section',
            __('API Settings', 'bhfe-chatbot'),
            array($this, 'render_api_section_callback'),
            'bhfe-chatbot'
        );
        
        add_settings_field(
            'openai_api_key',
            __('OpenAI API Key', 'bhfe-chatbot'),
            array($this, 'render_password_field'),
            'bhfe-chatbot',
            'bhfe_chatbot_api_section',
            array(
                'label_for' => 'openai_api_key',
                'description' => __('Your OpenAI API key', 'bhfe-chatbot')
            )
        );
        
        add_settings_field(
            'openai_model',
            __('OpenAI Model', 'bhfe-chatbot'),
            array($this, 'render_select_field'),
            'bhfe-chatbot',
            'bhfe_chatbot_api_section',
            array(
                'label_for' => 'openai_model',
                'options' => array(
                    'gpt-4' => 'GPT-4 (Most Capable)',
                    'gpt-4-turbo-preview' => 'GPT-4 Turbo (Recommended)',
                    'gpt-4o' => 'GPT-4o (Latest & Fastest)',
                    'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Budget-Friendly)',
                    'o1-preview' => 'O1 Preview (Advanced Reasoning)',
                    'o1-mini' => 'O1 Mini (Fast Reasoning)'
                ),
                'description' => __('OpenAI model to use', 'bhfe-chatbot')
            )
        );
        
        add_settings_field(
            'dropbox_access_token',
            __('Dropbox Access Token', 'bhfe-chatbot'),
            array($this, 'render_password_field'),
            'bhfe-chatbot',
            'bhfe_chatbot_api_section',
            array(
                'label_for' => 'dropbox_access_token',
                'description' => __('Dropbox access token with read access to your course files', 'bhfe-chatbot')
            )
        );
        
        add_settings_field(
            'dropbox_folder',
            __('Dropbox Folder Path', 'bhfe-chatbot'),
            array($this, 'render_text_field'),
            'bhfe-chatbot',
            'bhfe_chatbot_api_section',
            array(
                'label_for' => 'dropbox_folder',
                'description' => __('Path to your course files folder (e.g., /Course Files)', 'bhfe-chatbot')
            )
        );
        
        // Business Info section
        add_settings_section(
            'bhfe_chatbot_business_section',
            __('Business Information', 'bhfe-chatbot'),
            array($this, 'render_business_section_callback'),
            'bhfe-chatbot'
        );
        
        add_settings_field(
            'business_description',
            __('Business Description', 'bhfe-chatbot'),
            array($this, 'render_textarea_field'),
            'bhfe-chatbot',
            'bhfe_chatbot_business_section',
            array(
                'label_for' => 'business_description',
                'description' => __('Describe your business, services, and course offerings for the AI context', 'bhfe-chatbot')
            )
        );
        
        // Advanced AI Settings section
        add_settings_section(
            'bhfe_chatbot_advanced_section',
            __('Advanced AI Parameters', 'bhfe-chatbot'),
            array($this, 'render_advanced_section_callback'),
            'bhfe-chatbot'
        );
        
        add_settings_field(
            'temperature',
            __('Temperature', 'bhfe-chatbot'),
            array($this, 'render_number_field'),
            'bhfe-chatbot',
            'bhfe_chatbot_advanced_section',
            array(
                'label_for' => 'temperature',
                'min' => '0',
                'max' => '2',
                'step' => '0.1',
                'default' => '0.7',
                'description' => __('Controls randomness: Lower = more consistent/focused, Higher = more creative (0.7 recommended)', 'bhfe-chatbot')
            )
        );
        
        add_settings_field(
            'max_tokens',
            __('Max Tokens', 'bhfe-chatbot'),
            array($this, 'render_number_field'),
            'bhfe-chatbot',
            'bhfe_chatbot_advanced_section',
            array(
                'label_for' => 'max_tokens',
                'min' => '100',
                'max' => '4000',
                'step' => '100',
                'default' => '1000',
                'description' => __('Maximum length of AI responses (1000 recommended)', 'bhfe-chatbot')
            )
        );
        
        add_settings_field(
            'frequency_penalty',
            __('Frequency Penalty', 'bhfe-chatbot'),
            array($this, 'render_number_field'),
            'bhfe-chatbot',
            'bhfe_chatbot_advanced_section',
            array(
                'label_for' => 'frequency_penalty',
                'min' => '-2',
                'max' => '2',
                'step' => '0.1',
                'default' => '0',
                'description' => __('Reduce repetition: Positive = less repetitive, Negative = more repetitive', 'bhfe-chatbot')
            )
        );
        
        add_settings_field(
            'presence_penalty',
            __('Presence Penalty', 'bhfe-chatbot'),
            array($this, 'render_number_field'),
            'bhfe-chatbot',
            'bhfe_chatbot_advanced_section',
            array(
                'label_for' => 'presence_penalty',
                'min' => '-2',
                'max' => '2',
                'step' => '0.1',
                'default' => '0',
                'description' => __('Encourage diversity: Positive = more diverse topics, Negative = stick to topics', 'bhfe-chatbot')
            )
        );
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['enabled'])) {
            $sanitized['enabled'] = '1';
        } else {
            $sanitized['enabled'] = '0';
        }
        
        $sanitized['chatbot_title'] = sanitize_text_field($input['chatbot_title'] ?? '');
        $sanitized['position'] = sanitize_text_field($input['position'] ?? 'bottom-right');
        $sanitized['theme_color'] = sanitize_hex_color($input['theme_color'] ?? '#2563eb');
        $sanitized['openai_api_key'] = sanitize_text_field($input['openai_api_key'] ?? '');
        $sanitized['openai_model'] = sanitize_text_field($input['openai_model'] ?? 'gpt-4-turbo-preview');
        $sanitized['dropbox_access_token'] = sanitize_text_field($input['dropbox_access_token'] ?? '');
        $sanitized['dropbox_folder'] = sanitize_text_field($input['dropbox_folder'] ?? '');
        $sanitized['business_description'] = sanitize_textarea_field($input['business_description'] ?? '');
        
        // Advanced AI parameters
        $temperature = floatval($input['temperature'] ?? 0.7);
        $sanitized['temperature'] = max(0, min(2, $temperature)); // Clamp between 0 and 2
        
        $max_tokens = intval($input['max_tokens'] ?? 1000);
        $sanitized['max_tokens'] = max(100, min(4000, $max_tokens)); // Clamp between 100 and 4000
        
        $frequency_penalty = floatval($input['frequency_penalty'] ?? 0);
        $sanitized['frequency_penalty'] = max(-2, min(2, $frequency_penalty)); // Clamp between -2 and 2
        
        $presence_penalty = floatval($input['presence_penalty'] ?? 0);
        $sanitized['presence_penalty'] = max(-2, min(2, $presence_penalty)); // Clamp between -2 and 2
        
        return $sanitized;
    }
    
    /**
     * Render general section callback
     */
    public function render_general_section_callback() {
        echo '<p>' . esc_html__('Configure the general appearance and behavior of the chatbot.', 'bhfe-chatbot') . '</p>';
    }
    
    /**
     * Render API section callback
     */
    public function render_api_section_callback() {
        echo '<p>' . esc_html__('Configure API credentials for OpenAI and Dropbox integration.', 'bhfe-chatbot') . '</p>';
    }
    
    /**
     * Render business section callback
     */
    public function render_business_section_callback() {
        echo '<p>' . esc_html__('Provide information about your business and course offerings to help the chatbot answer questions accurately.', 'bhfe-chatbot') . '</p>';
    }
    
    /**
     * Render advanced section callback
     */
    public function render_advanced_section_callback() {
        echo '<p>' . esc_html__('Fine-tune AI behavior and response characteristics. Leave default values unless you need to customize.', 'bhfe-chatbot') . '</p>';
    }
    
    /**
     * Render checkbox field
     */
    public function render_checkbox_field($args) {
        $options = get_option('bhfe_chatbot_settings');
        $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : '0';
        ?>
        <input type="checkbox" id="<?php echo esc_attr($args['label_for']); ?>" name="bhfe_chatbot_settings[<?php echo esc_attr($args['label_for']); ?>]" value="1" <?php checked($value, '1'); ?>>
        <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php
    }
    
    /**
     * Render text field
     */
    public function render_text_field($args) {
        $options = get_option('bhfe_chatbot_settings');
        $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : '';
        ?>
        <input type="text" id="<?php echo esc_attr($args['label_for']); ?>" name="bhfe_chatbot_settings[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($value); ?>" class="regular-text">
        <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php
    }
    
    /**
     * Render password field
     */
    public function render_password_field($args) {
        $options = get_option('bhfe_chatbot_settings');
        $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : '';
        ?>
        <input type="password" id="<?php echo esc_attr($args['label_for']); ?>" name="bhfe_chatbot_settings[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($value); ?>" class="regular-text">
        <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php
    }
    
    /**
     * Render select field
     */
    public function render_select_field($args) {
        $options = get_option('bhfe_chatbot_settings');
        $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : '';
        ?>
        <select id="<?php echo esc_attr($args['label_for']); ?>" name="bhfe_chatbot_settings[<?php echo esc_attr($args['label_for']); ?>]">
            <?php foreach ($args['options'] as $option_value => $option_label): ?>
                <option value="<?php echo esc_attr($option_value); ?>" <?php selected($value, $option_value); ?>>
                    <?php echo esc_html($option_label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php
    }
    
    /**
     * Render color field
     */
    public function render_color_field($args) {
        $options = get_option('bhfe_chatbot_settings');
        $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : '#2563eb';
        ?>
        <input type="color" id="<?php echo esc_attr($args['label_for']); ?>" name="bhfe_chatbot_settings[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($value); ?>">
        <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php
    }
    
    /**
     * Render textarea field
     */
    public function render_textarea_field($args) {
        $options = get_option('bhfe_chatbot_settings');
        $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : '';
        ?>
        <textarea id="<?php echo esc_attr($args['label_for']); ?>" name="bhfe_chatbot_settings[<?php echo esc_attr($args['label_for']); ?>]" rows="5" class="large-text"><?php echo esc_textarea($value); ?></textarea>
        <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php
    }
    
    /**
     * Render number field
     */
    public function render_number_field($args) {
        $options = get_option('bhfe_chatbot_settings');
        $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : ($args['default'] ?? '');
        $min = $args['min'] ?? '0';
        $max = $args['max'] ?? '100';
        $step = $args['step'] ?? '1';
        ?>
        <input type="number" id="<?php echo esc_attr($args['label_for']); ?>" name="bhfe_chatbot_settings[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($value); ?>" min="<?php echo esc_attr($min); ?>" max="<?php echo esc_attr($max); ?>" step="<?php echo esc_attr($step); ?>" class="small-text">
        <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Show notices
        if (isset($_GET['settings-updated'])) {
            add_settings_error('bhfe_chatbot_messages', 'bhfe_chatbot_message', __('Settings Saved', 'bhfe-chatbot'), 'updated');
        }
        settings_errors('bhfe_chatbot_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('bhfe_chatbot_settings');
                do_settings_sections('bhfe-chatbot');
                submit_button(__('Save Settings', 'bhfe-chatbot'));
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_bhfe-chatbot' !== $hook) {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        $options = get_option('bhfe_chatbot_settings');
        
        if (!isset($options['enabled']) || $options['enabled'] !== '1') {
            return;
        }
        
        wp_enqueue_style('bhfe-chatbot-style', BHFE_CHATBOT_PLUGIN_URL . 'assets/css/chatbot.css', array(), BHFE_CHATBOT_VERSION);
        wp_enqueue_script('bhfe-chatbot-script', BHFE_CHATBOT_PLUGIN_URL . 'assets/js/chatbot.js', array('jquery'), BHFE_CHATBOT_VERSION, true);
        
        wp_localize_script('bhfe-chatbot-script', 'bhfeChatbot', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bhfe_chatbot_nonce'),
            'position' => $options['position'] ?? 'bottom-right',
            'themeColor' => $options['theme_color'] ?? '#2563eb',
        ));
    }
    
    /**
     * Render chatbot
     */
    public function render_chatbot() {
        $options = get_option('bhfe_chatbot_settings');
        
        if (!isset($options['enabled']) || $options['enabled'] !== '1') {
            return;
        }
        
        $title = $options['chatbot_title'] ?? 'Course Question? Ask Me!';
        ?>
        <div id="bhfe-chatbot-container" class="bhfe-chatbot-container">
            <div id="bhfe-chatbot-toggle" class="bhfe-chatbot-toggle">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
            </div>
            <div id="bhfe-chatbot-window" class="bhfe-chatbot-window">
                <div class="bhfe-chatbot-header">
                    <h3><?php echo esc_html($title); ?></h3>
                    <button id="bhfe-chatbot-close" class="bhfe-chatbot-close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div id="bhfe-chatbot-messages" class="bhfe-chatbot-messages"></div>
                <div class="bhfe-chatbot-input-container">
                    <textarea id="bhfe-chatbot-input" class="bhfe-chatbot-input" placeholder="Ask me about our courses..."></textarea>
                    <button id="bhfe-chatbot-send" class="bhfe-chatbot-send">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handle chat message AJAX
     */
    public function handle_chat_message() {
        check_ajax_referer('bhfe_chatbot_nonce', 'nonce');
        
        $message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
        
        if (empty($message)) {
            wp_send_json_error(array('message' => 'Please enter a message'));
        }
        
        $handler = new BHFE_Chatbot_Handler();
        $response = $handler->process_message($message);
        
        wp_send_json_success($response);
    }
}

// Initialize plugin
BHFE_Course_Chatbot::get_instance();

