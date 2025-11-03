<?php
/**
 * Chatbot Handler Class
 * Orchestrates the conversation flow
 */

if (!defined('ABSPATH')) {
    exit;
}

class BHFE_Chatbot_Handler {
    
    private $openai;
    private $dropbox;
    private $crawler;
    private $conversation_history;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->openai = new BHFE_OpenAI_Integration();
        $this->dropbox = new BHFE_Dropbox_Integration();
        $this->crawler = new BHFE_Website_Crawler();
        $this->conversation_history = array();
    }
    
    /**
     * Process incoming message
     */
    public function process_message($user_message) {
        // Determine if message is asking about courses or general
        $is_course_query = $this->is_course_related_query($user_message);
        
        $context_files = array();
        $website_results = array();
        
        // If it's a course query, search both Dropbox and website
        if ($is_course_query) {
            $context_files = $this->dropbox->search_files($user_message, 5);
            $website_results = $this->crawler->search_pages($user_message, 5);
        }
        
        // Build conversation history
        $conversation = $this->get_conversation_history();
        $conversation[] = array(
            'role' => 'user',
            'content' => $user_message,
        );
        
        // Get AI response with both Dropbox and website context
        $ai_response = $this->openai->get_chat_completion($conversation, $context_files, $website_results);
        
        // Update conversation history
        $this->add_to_conversation_history($user_message, $ai_response);
        
        return array(
            'message' => $ai_response,
            'has_results' => !empty($context_files) || !empty($website_results),
        );
    }
    
    /**
     * Determine if query is course-related
     */
    private function is_course_related_query($message) {
        $message_lower = strtolower($message);
        
        // Keywords that suggest course-related queries
        $course_keywords = array(
            'course', 'courses', 'cpe', 'ce', 'training', 'education',
            'learn', 'teaching', 'material', 'content', 'curriculum',
            'certification', 'certificate', 'credit', 'credit hours',
            'topic', 'topics', 'subject', 'subjects', 'class', 'classes',
            'workshop', 'webinar', 'seminar', 'program', 'programs',
        );
        
        // Check if message contains course-related keywords
        foreach ($course_keywords as $keyword) {
            if (strpos($message_lower, $keyword) !== false) {
                return true;
            }
        }
        
        // Check for professional titles/acronyms
        $professional_titles = array(
            'cfp', 'cpa', 'irs', 'enrolled agent', 'cdfa', 'iar',
            'financial planner', 'accountant', 'advisor',
        );
        
        foreach ($professional_titles as $title) {
            if (strpos($message_lower, $title) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get conversation history from session/cookie
     */
    private function get_conversation_history() {
        // Get from transient (expires after 1 hour)
        $session_id = $this->get_session_id();
        $history = get_transient('bhfe_chatbot_history_' . $session_id);
        
        if (!$history) {
            return array();
        }
        
        return $history;
    }
    
    /**
     * Add to conversation history
     */
    private function add_to_conversation_history($user_message, $ai_response) {
        $history = $this->get_conversation_history();
        
        // Add user message
        $history[] = array(
            'role' => 'user',
            'content' => $user_message,
        );
        
        // Add AI response
        $history[] = array(
            'role' => 'assistant',
            'content' => $ai_response,
        );
        
        // Keep only last 10 messages (5 exchanges)
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }
        
        // Save to transient (1 hour expiration)
        $session_id = $this->get_session_id();
        set_transient('bhfe_chatbot_history_' . $session_id, $history, HOUR_IN_SECONDS);
    }
    
    /**
     * Get or create session ID
     */
    private function get_session_id() {
        if (!session_id()) {
            session_start();
        }
        
        if (!isset($_SESSION['bhfe_chatbot_session_id'])) {
            $_SESSION['bhfe_chatbot_session_id'] = wp_generate_uuid4();
        }
        
        return $_SESSION['bhfe_chatbot_session_id'];
    }
    
    /**
     * Reset conversation history
     */
    public function reset_history() {
        $session_id = $this->get_session_id();
        delete_transient('bhfe_chatbot_history_' . $session_id);
        
        if (isset($_SESSION['bhfe_chatbot_session_id'])) {
            unset($_SESSION['bhfe_chatbot_session_id']);
        }
    }
}

