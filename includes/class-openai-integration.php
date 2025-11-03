<?php
/**
 * OpenAI Integration Class
 * Handles communication with OpenAI API
 */

if (!defined('ABSPATH')) {
    exit;
}

class BHFE_OpenAI_Integration {
    
    private $api_key;
    private $model;
    private $temperature;
    private $max_tokens;
    private $frequency_penalty;
    private $presence_penalty;
    
    /**
     * Constructor
     */
    public function __construct() {
        $options = get_option('bhfe_chatbot_settings');
        $this->api_key = $options['openai_api_key'] ?? '';
        $this->model = $options['openai_model'] ?? 'gpt-4-turbo-preview';
        $this->temperature = isset($options['temperature']) ? floatval($options['temperature']) : 0.7;
        $this->max_tokens = isset($options['max_tokens']) ? intval($options['max_tokens']) : 1000;
        $this->frequency_penalty = isset($options['frequency_penalty']) ? floatval($options['frequency_penalty']) : 0;
        $this->presence_penalty = isset($options['presence_penalty']) ? floatval($options['presence_penalty']) : 0;
    }
    
    /**
     * Generate chat completion
     */
    public function get_chat_completion($messages, $context_files = array(), $website_results = array()) {
        if (empty($this->api_key)) {
            return 'I apologize, but the chatbot is not properly configured. Please contact support.';
        }
        
        // Build system message with business context
        $business_info = $this->get_business_context();
        
        // Add file context to system message if available
        $system_message = $business_info;
        if (!empty($context_files)) {
            $system_message .= "\n\nRelevant course files:\n";
            foreach ($context_files as $file) {
                $file_name = $file['name'] ?? '';
                $file_content = $file['content'] ?? '';
                $system_message .= "\n--- {$file_name} ---\n" . $file_content . "\n";
            }
        }
        
        // Add website results if available
        if (!empty($website_results)) {
            $system_message .= "\n\nAvailable courses on the website:\n";
            foreach ($website_results as $i => $page) {
                $system_message .= sprintf(
                    "%d. [%s](%s)\n   %s\n",
                    $i + 1,
                    $page['title'],
                    $page['url'],
                    $page['excerpt'] ?? ''
                );
            }
        }
        
        $system_message .= "\n\nInstructions: Provide helpful, accurate information about the courses and business. When mentioning courses, ALWAYS include clickable links to website pages using markdown format like [Course Name](url). If you reference a specific course file, be sure to mention it. Be professional but friendly.";
        
        // Prepend system message to messages array
        array_unshift($messages, array(
            'role' => 'system',
            'content' => $system_message,
        ));
        
        // Build request parameters
        $params = array(
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $this->temperature,
            'max_tokens' => $this->max_tokens,
        );
        
        // Add optional parameters if not default
        if ($this->frequency_penalty != 0) {
            $params['frequency_penalty'] = $this->frequency_penalty;
        }
        
        if ($this->presence_penalty != 0) {
            $params['presence_penalty'] = $this->presence_penalty;
        }
        
        $response = $this->api_request('/v1/chat/completions', $params);
        
        if (is_wp_error($response)) {
            error_log('OpenAI API error: ' . $response->get_error_message());
            return 'I apologize, but I encountered an error. Please try again.';
        }
        
        $response_data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($response_data['choices'][0]['message']['content'])) {
            return 'I apologize, but I couldn\'t generate a response. Please try again.';
        }
        
        return trim($response_data['choices'][0]['message']['content']);
    }
    
    /**
     * Get business context for the AI
     */
    private function get_business_context() {
        $options = get_option('bhfe_chatbot_settings');
        $business_description = $options['business_description'] ?? '';
        
        if (!empty($business_description)) {
            return $business_description;
        }
        
        // Default business description
        return "You are a helpful assistant for BHFE, a provider of online CPE (Continuing Professional Education) and CE (Continuing Education) courses. We serve professionals including CFPs (Certified Financial Planners), CPAs (Certified Public Accountants), IRS enrolled agents, CDFAs (Certified Divorce Financial Analysts), IARs (Investment Advisor Representatives), and other financial professionals. We offer comprehensive course materials covering various topics in finance, tax, and professional development.";
    }
    
    /**
     * Make API request to OpenAI
     */
    private function api_request($endpoint, $data = array()) {
        $url = 'https://api.openai.com' . $endpoint;
        
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($data),
            'timeout' => 60,
        );
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200) {
            $error_body = wp_remote_retrieve_body($response);
            error_log('OpenAI API error (HTTP ' . $response_code . '): ' . $error_body);
            return new WP_Error('openai_error', 'OpenAI API returned an error');
        }
        
        return $response;
    }
    
    /**
     * Test the OpenAI connection
     */
    public function test_connection() {
        if (empty($this->api_key)) {
            return false;
        }
        
        try {
            $response = $this->api_request('/v1/models', array());
            return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
        } catch (Exception $e) {
            return false;
        }
    }
}

