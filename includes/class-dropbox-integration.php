<?php
/**
 * Dropbox Integration Class
 * Handles communication with Dropbox API
 */

if (!defined('ABSPATH')) {
    exit;
}

class BHFE_Dropbox_Integration {
    
    private $access_token;
    private $folder_path;
    
    /**
     * Constructor
     */
    public function __construct() {
        $options = get_option('bhfe_chatbot_settings');
        $this->access_token = $options['dropbox_access_token'] ?? '';
        $this->folder_path = $options['dropbox_folder'] ?? '/Course Files';
    }
    
    /**
     * Search for files matching a query
     */
    public function search_files($query, $limit = 10) {
        if (empty($this->access_token)) {
            return array();
        }
        
        // Search for files matching the query
        $files = $this->list_files_recursive($this->folder_path, $query, $limit);
        
        return $files;
    }
    
    /**
     * Recursively list files in a folder
     */
    private function list_files_recursive($folder_path, $search_query = '', $limit = 10) {
        $results = array();
        
        try {
            // List files in the folder
            $response = $this->api_request('/2/files/list_folder', array(
                'path' => $folder_path,
                'recursive' => true,
            ));
            
            if (!isset($response['entries'])) {
                return $results;
            }
            
            // Filter and score files based on search query
            foreach ($response['entries'] as $entry) {
                if ($entry['.tag'] !== 'file') {
                    continue;
                }
                
                $file_name = $entry['name'];
                $file_path = $entry['path_display'];
                
                // If we have a search query, check if file matches
                if (!empty($search_query)) {
                    $score = $this->calculate_relevance_score($file_name, $search_query);
                    if ($score > 0) {
                        $results[] = array(
                            'name' => $file_name,
                            'path' => $file_path,
                            'size' => $entry['size'] ?? 0,
                            'score' => $score,
                        );
                    }
                } else {
                    $results[] = array(
                        'name' => $file_name,
                        'path' => $file_path,
                        'size' => $entry['size'] ?? 0,
                        'score' => 1,
                    );
                }
            }
            
            // Sort by relevance and limit results
            usort($results, function($a, $b) {
                return $b['score'] - $a['score'];
            });
            
            $results = array_slice($results, 0, $limit);
            
            // Get content for the most relevant files
            foreach ($results as &$result) {
                $result['content'] = $this->get_file_content($result['path']);
            }
            
        } catch (Exception $e) {
            error_log('Dropbox error: ' . $e->getMessage());
        }
        
        return $results;
    }
    
    /**
     * Calculate relevance score for a file based on search query
     */
    private function calculate_relevance_score($file_name, $query) {
        $score = 0;
        $file_name_lower = strtolower($file_name);
        $query_lower = strtolower($query);
        $query_terms = explode(' ', $query_lower);
        
        // Exact match gets highest score
        if (strpos($file_name_lower, $query_lower) !== false) {
            $score += 100;
        }
        
        // Partial matches get lower scores
        foreach ($query_terms as $term) {
            if (strpos($file_name_lower, $term) !== false) {
                $score += 10;
            }
        }
        
        return $score;
    }
    
    /**
     * Get file content
     */
    public function get_file_content($file_path) {
        if (empty($this->access_token)) {
            return '';
        }
        
        try {
            // Get file metadata
            $download_response = $this->api_request('/2/files/download', array(
                'path' => $file_path,
            ), 'POST');
            
            if (!$download_response) {
                return '';
            }
            
            $content = wp_remote_retrieve_body($download_response);
            
            // Check if it's a text-based file
            $is_text = $this->is_text_file($file_path);
            if (!$is_text) {
                return sprintf('File: %s (Binary file, cannot read content)', basename($file_path));
            }
            
            // Truncate very long content to keep token usage reasonable
            if (strlen($content) > 5000) {
                $content = substr($content, 0, 5000) . '...';
            }
            
            return $content;
            
        } catch (Exception $e) {
            error_log('Dropbox download error: ' . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Check if file is text-based
     */
    private function is_text_file($file_path) {
        $text_extensions = array('txt', 'pdf', 'md', 'doc', 'docx', 'csv', 'html', 'htm', 'xml', 'json', 'rtf');
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        return in_array($extension, $text_extensions);
    }
    
    /**
     * Make API request to Dropbox
     */
    private function api_request($endpoint, $data = array(), $method = 'POST') {
        $url = 'https://api.dropboxapi.com' . $endpoint;
        
        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Content-Type' => 'application/json',
            ),
            'timeout' => 30,
        );
        
        if ($endpoint === '/2/files/download') {
            // Download endpoint needs special handling
            $args['headers']['Dropbox-API-Arg'] = json_encode($data);
            unset($args['headers']['Content-Type']);
            $args['body'] = '';
        } elseif ($method === 'GET') {
            // For GET requests, append query string if needed
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        } else {
            // For POST requests, include data in body
            $args['body'] = json_encode($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200) {
            $error_body = wp_remote_retrieve_body($response);
            throw new Exception('Dropbox API error: ' . $error_body);
        }
        
        // For download, return the response directly
        if ($endpoint === '/2/files/download') {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
    
    /**
     * Test the Dropbox connection
     */
    public function test_connection() {
        try {
            $response = $this->api_request('/2/users/get_current_account');
            return !empty($response);
        } catch (Exception $e) {
            return false;
        }
    }
}

