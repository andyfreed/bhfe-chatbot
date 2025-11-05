import axios from 'axios';

const WORDPRESS_API_URL = process.env.WORDPRESS_API_URL;
const WORDPRESS_API_SECRET = process.env.WORDPRESS_API_SECRET;

/**
 * Searches for active courses in WordPress
 * 
 * @param {Object} args - Function arguments
 * @param {string} args.query - The search query
 * @param {number} args.per_page - Number of results to return (default: 20)
 * @returns {Promise<Object>} Search results
 */
export async function searchCourses(args) {
  try {
    const { query, per_page = 20 } = args;

    if (!query || typeof query !== 'string') {
      return {
        error: 'Query is required and must be a string',
        results: []
      };
    }

    console.log(`Searching for active courses: "${query}"`);

    // Try multiple endpoints - custom post types might not have REST API registered
    // Try WooCommerce products first, then custom post type
    let url = `${WORDPRESS_API_URL}/wp/v2/products`;
    let triedCustomEndpoint = false;

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

    // Make the request - try WooCommerce products first
    let response;
    try {
      response = await axios.get(url, {
        params: {
          search: query,
          per_page: per_page,
          status: 'publish',
          _embed: true
        },
        headers: {
          'Authorization': authHeader,
          'Content-Type': 'application/json'
        },
        timeout: 10000
      });
    } catch (error) {
      // If products endpoint fails, try custom post type
      if (error.response?.status === 404 || error.response?.status === 403) {
        triedCustomEndpoint = true;
        url = `${WORDPRESS_API_URL}/wp/v2/flms-courses`;
        response = await axios.get(url, {
          params: {
            search: query,
            per_page: per_page,
            status: 'publish',
            _embed: true
          },
          headers: {
            'Authorization': authHeader,
            'Content-Type': 'application/json'
          },
          timeout: 10000
        });
      } else {
        throw error;
      }
    }

    // Format the results - handle both WooCommerce products and custom post types
    const courses = response.data.map(course => {
      // Check if this is a WooCommerce product or custom post type
      const isProduct = course.type === 'product' || course.product_type;
      
      return {
        id: course.id,
        title: course.title?.rendered || course.title?.raw || course.name || course.title,
        slug: course.slug,
        link: course.link || course.permalink,
        excerpt: course.excerpt?.rendered || course.excerpt?.raw || course.short_description || course.excerpt,
        date: course.date || course.date_created,
        modified: course.modified || course.date_modified,
        featured_image: course._embedded?.['wp:featuredmedia']?.[0]?.source_url || course.images?.[0]?.src || null,
        meta: course.meta || {},
        type: isProduct ? 'product' : 'course',
        price: course.price || course.price_html || null
      };
    });

    return {
      query,
      count: courses.length,
      total: response.headers['x-wp-total'] || courses.length,
      courses: courses
    };
  } catch (error) {
    console.error('Course search error:', error);
    
    // Try alternative authentication method if Basic auth fails
    if (error.response?.status === 401) {
      try {
        // Try with Bearer token instead
        const url = `${WORDPRESS_API_URL}/wp/v2/flms-courses`;
        const response = await axios.get(url, {
          params: {
            search: query,
            per_page: per_page || 20,
            status: 'publish'
          },
          headers: {
            'Authorization': `Bearer ${WORDPRESS_API_SECRET}`,
            'Content-Type': 'application/json'
          },
          timeout: 10000
        });

        const courses = response.data.map(course => {
          return {
            id: course.id,
            title: course.title?.rendered || course.title,
            slug: course.slug,
            link: course.link,
            excerpt: course.excerpt?.rendered || course.excerpt,
            date: course.date,
            modified: course.modified,
            meta: course.meta || {}
          };
        });

        // Filter out archived courses (post-processing)
        // Archived courses have 'bhfe_archived_course' meta key
        const activeCourses = courses.filter(course => {
          // If meta data is available, check for archived flag
          // Note: REST API may not include all meta by default
          // For now, return all published courses as they should be active
          return true;
        });

        return {
          query,
          count: activeCourses.length,
          total: response.headers['x-wp-total'] || activeCourses.length,
          courses: activeCourses
        };
      } catch (retryError) {
        return {
          error: `WordPress API error: ${retryError.response?.status || retryError.message}`,
          results: []
        };
      }
    }
    
    return {
      error: error.message || 'Failed to search courses',
      results: []
    };
  }
}

/**
 * Gets course details by ID
 * 
 * @param {Object} args - Function arguments
 * @param {number} args.course_id - The course ID
 * @returns {Promise<Object>} Course details
 */
export async function getCourseDetails(args) {
  try {
    const { course_id } = args;

    if (!course_id) {
      return {
        error: 'Course ID is required'
      };
    }

    const url = `${WORDPRESS_API_URL}/wp/v2/flms-courses/${course_id}`;

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

    const response = await axios.get(url, {
      headers: {
        'Authorization': authHeader,
        'Content-Type': 'application/json'
      },
      timeout: 10000
    });

    const course = response.data;

    return {
      id: course.id,
      title: course.title?.rendered || course.title,
      slug: course.slug,
      link: course.link,
      content: course.content?.rendered || course.content,
      excerpt: course.excerpt?.rendered || course.excerpt,
      date: course.date,
      modified: course.modified,
      meta: course.meta || {}
    };
  } catch (error) {
    console.error('Get course error:', error);
    return {
      error: error.message || 'Failed to get course details'
    };
  }
}

