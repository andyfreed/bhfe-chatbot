import axios from 'axios';

const WORDPRESS_API_URL = process.env.WORDPRESS_API_URL;
const WORDPRESS_API_SECRET = process.env.WORDPRESS_API_SECRET;

/**
 * Makes a request to the WordPress REST API
 * 
 * @param {Object} args - Function arguments
 * @param {string} args.endpoint - The API endpoint path (e.g., "/wp/v2/posts")
 * @param {Object} args.params - Optional query parameters
 * @returns {Promise<Object>} API response data
 */
export async function getWordPressData(args) {
  try {
    const { endpoint, params = {} } = args;

    if (!endpoint || typeof endpoint !== 'string') {
      return {
        error: 'Endpoint is required and must be a string'
      };
    }

    // Ensure endpoint starts with /
    const cleanEndpoint = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;

    // Build the full URL
    const url = `${WORDPRESS_API_URL}${cleanEndpoint}`;

    console.log(`Fetching WordPress data from: ${url}`);

    // Parse WordPress API secret (format: username:password or just token)
    let authHeader;
    if (WORDPRESS_API_SECRET.includes(':')) {
      // Application password format: username:password
      const credentials = Buffer.from(WORDPRESS_API_SECRET).toString('base64');
      authHeader = `Basic ${credentials}`;
    } else {
      // Bearer token format
      authHeader = `Bearer ${WORDPRESS_API_SECRET}`;
    }

    // Make the request with authentication
    const response = await axios.get(url, {
      params: params,
      headers: {
        'Authorization': authHeader,
        'Content-Type': 'application/json'
      },
      timeout: 10000 // 10 second timeout
    });

    return {
      endpoint: cleanEndpoint,
      data: response.data,
      count: Array.isArray(response.data) ? response.data.length : 1
    };
  } catch (error) {
    console.error('WordPress API error:', error);
    
    // Return a more user-friendly error message
    if (error.response) {
      return {
        error: `WordPress API error: ${error.response.status} ${error.response.statusText}`,
        details: error.response.data
      };
    } else if (error.request) {
      return {
        error: 'Failed to connect to WordPress API. Please check the API URL.'
      };
    } else {
      return {
        error: error.message || 'Failed to fetch WordPress data'
      };
    }
  }
}

/**
 * Common WordPress endpoints you might want to use:
 * 
 * - /wp/v2/posts - Get posts
 * - /wp/v2/pages - Get pages
 * - /wp/v2/users - Get users
 * - /wp/v2/categories - Get categories
 * - /wp/v2/tags - Get tags
 * - /wp/v2/media - Get media files
 * 
 * Example params:
 * - per_page: number of items to return
 * - search: search term
 * - status: filter by status (published, draft, etc.)
 */

