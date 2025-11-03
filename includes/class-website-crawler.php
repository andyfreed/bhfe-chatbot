<?php
/**
 * Website Crawler Class
 * Handles crawling website pages to find courses and create links
 */

if (!defined('ABSPATH')) {
    exit;
}

class BHFE_Website_Crawler {
    
    private $site_url;
    private $excluded_urls;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->site_url = get_site_url();
        $this->excluded_urls = array(
            '/wp-admin',
            '/wp-content',
            '/wp-includes',
            '/feed',
            '/robots.txt',
            '.xml',
            '.zip',
        );
    }
    
    /**
     * Search for pages/posts matching keywords
     */
    public function search_pages($keywords, $limit = 10) {
        // Clean keywords
        $search_query = sanitize_text_field($keywords);
        
        if (empty($search_query)) {
            return array();
        }
        
        // Search WordPress database for courses
        $results = $this->search_wordpress($search_query, $limit);
        
        return $results;
    }
    
    /**
     * Search WordPress database
     */
    private function search_wordpress($query, $limit = 10) {
        global $wpdb;
        
        $results = array();
        
        // Build search terms
        $search_terms = explode(' ', $query);
        $like_clauses = array();
        $prepared_values = array();
        
        foreach ($search_terms as $term) {
            $like_clauses[] = " (post_title LIKE %s OR post_content LIKE %s) ";
            $prepared_values[] = '%' . $wpdb->esc_like($term) . '%';
            $prepared_values[] = '%' . $wpdb->esc_like($term) . '%';
        }
        
        $where_clause = ' AND (' . implode(' OR ', $like_clauses) . ') ';
        
        // Query to find relevant posts/pages
        $query_sql = $wpdb->prepare("
            SELECT ID, post_title, post_content, post_name, post_type, post_date
            FROM {$wpdb->posts}
            WHERE post_status = 'publish'
            AND post_type IN ('page', 'post', 'product', 'course')
            AND post_password = ''
            {$where_clause}
            ORDER BY post_date DESC
            LIMIT %d
        ", array_merge($prepared_values, array($limit)));
        
        $posts = $wpdb->get_results($query_sql);
        
        if (!$posts) {
            return array();
        }
        
        // Format results
        foreach ($posts as $post) {
            $permalink = get_permalink($post->ID);
            
            // Extract excerpt from content
            $excerpt = $this->extract_excerpt($post->post_content, $query);
            
            $results[] = array(
                'title' => $post->post_title,
                'url' => $permalink,
                'excerpt' => $excerpt,
                'post_type' => $post->post_type,
                'date' => $post->post_date,
                'score' => $this->calculate_relevance_score($post->post_title, $post->post_content, $query),
            );
        }
        
        // Sort by relevance
        usort($results, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return $results;
    }
    
    /**
     * Extract relevant excerpt from content
     */
    private function extract_excerpt($content, $query, $length = 200) {
        // Strip HTML tags
        $content = wp_strip_all_tags($content);
        
        // Find position of query terms
        $content_lower = strtolower($content);
        $query_terms = explode(' ', strtolower($query));
        $best_position = 0;
        $max_matches = 0;
        
        // Search for best position
        for ($i = 0; $i < strlen($content_lower); $i += 50) {
            $chunk = substr($content_lower, $i, 200);
            $matches = 0;
            
            foreach ($query_terms as $term) {
                if (strpos($chunk, $term) !== false) {
                    $matches++;
                }
            }
            
            if ($matches > $max_matches) {
                $max_matches = $matches;
                $best_position = $i;
            }
        }
        
        // Extract excerpt from best position
        $excerpt = substr($content, $best_position, $length);
        
        // Add ellipsis if truncated
        if (strlen($content) > $best_position + $length) {
            $excerpt .= '...';
        }
        
        // Bold matching terms (for display)
        foreach ($query_terms as $term) {
            $excerpt = preg_replace('/\b' . preg_quote($term, '/') . '\b/i', '**' . $term . '**', $excerpt);
        }
        
        return $excerpt;
    }
    
    /**
     * Calculate relevance score for a post
     */
    private function calculate_relevance_score($title, $content, $query) {
        $score = 0;
        $title_lower = strtolower($title);
        $content_lower = strtolower($content);
        $query_lower = strtolower($query);
        $query_terms = explode(' ', $query_lower);
        
        // Exact match in title gets highest score
        if (strpos($title_lower, $query_lower) !== false) {
            $score += 100;
        }
        
        // Each matching term in title
        foreach ($query_terms as $term) {
            if (strpos($title_lower, $term) !== false) {
                $score += 20;
            }
        }
        
        // Each matching term in content
        foreach ($query_terms as $term) {
            if (strpos($content_lower, $term) !== false) {
                $score += 5;
            }
        }
        
        return $score;
    }
    
    /**
     * Get course categories
     */
    public function get_course_categories() {
        // Try to get categories from WordPress
        $categories = get_terms(array(
            'taxonomy' => 'category',
            'hide_empty' => false,
        ));
        
        if (!empty($categories) && !is_wp_error($categories)) {
            return wp_list_pluck($categories, 'name');
        }
        
        // Try WooCommerce categories if available
        if (taxonomy_exists('product_cat')) {
            $wc_categories = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
            ));
            
            if (!empty($wc_categories) && !is_wp_error($wc_categories)) {
                return wp_list_pluck($wc_categories, 'name');
            }
        }
        
        // Try custom course categories
        if (taxonomy_exists('course_category')) {
            $course_cats = get_terms(array(
                'taxonomy' => 'course_category',
                'hide_empty' => false,
            ));
            
            if (!empty($course_cats) && !is_wp_error($course_cats)) {
                return wp_list_pluck($course_cats, 'name');
            }
        }
        
        return array();
    }
    
    /**
     * Get popular courses
     */
    public function get_popular_courses($limit = 5) {
        $args = array(
            'post_type' => array('page', 'post', 'product', 'course'),
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        $query = new WP_Query($args);
        
        $courses = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                $courses[] = array(
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'excerpt' => wp_trim_words(get_the_content(), 30),
                );
            }
            wp_reset_postdata();
        }
        
        return $courses;
    }
    
    /**
     * Format search results for AI context
     */
    public function format_results_for_ai($results) {
        if (empty($results)) {
            return '';
        }
        
        $formatted = "\n\nWebsite courses found:\n";
        
        foreach ($results as $i => $result) {
            $formatted .= sprintf(
                "%d. [%s](%s)\n   %s\n",
                $i + 1,
                $result['title'],
                $result['url'],
                $result['excerpt']
            );
        }
        
        return $formatted;
    }
}

