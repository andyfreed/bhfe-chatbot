import axios from 'axios';
import { searchDropbox } from './dropbox.js';

const WORDPRESS_API_URL = process.env.WORDPRESS_API_URL;
const WORDPRESS_API_SECRET = process.env.WORDPRESS_API_SECRET;

/**
 * Gets PDF files associated with a course
 * 
 * @param {Object} args - Function arguments
 * @param {number} args.course_id - The course ID
 * @returns {Promise<Object>} Course PDFs
 */
export async function getCoursePDFs(args) {
  try {
    const { course_id } = args;

    if (!course_id) {
      return {
        error: 'Course ID is required'
      };
    }

    console.log(`Getting PDFs for course ID: ${course_id}`);

    // Parse WordPress API secret for direct API calls
    let authHeader;
    if (WORDPRESS_API_SECRET.includes(':')) {
      const [username, ...passwordParts] = WORDPRESS_API_SECRET.split(':');
      const password = passwordParts.join(':').replace(/\s+/g, '');
      const credentials = Buffer.from(`${username}:${password}`, 'utf8').toString('base64');
      authHeader = `Basic ${credentials}`;
    } else {
      authHeader = `Bearer ${WORDPRESS_API_SECRET}`;
    }

    // Get course details including meta data - try WooCommerce products first
    let courseData;
    let url = `${WORDPRESS_API_URL}/wp/v2/products/${course_id}`;
    
    try {
      const response = await axios.get(url, {
        headers: {
          'Authorization': authHeader,
          'Content-Type': 'application/json'
        },
        timeout: 10000
      });
      courseData = { data: response.data, error: null };
    } catch (error) {
      // Try custom post type
      url = `${WORDPRESS_API_URL}/wp/v2/flms-courses/${course_id}`;
      try {
        const response = await axios.get(url, {
          headers: {
            'Authorization': authHeader,
            'Content-Type': 'application/json'
          },
          timeout: 10000
        });
        courseData = { data: response.data, error: null };
      } catch (err) {
        courseData = { error: err.message || 'Course not found' };
      }
    }

    if (courseData.error) {
      return {
        error: 'Course not found',
        course_id
      };
    }

    // Extract PDFs from course/product meta
    const versionContent = courseData.data.meta?.flms_version_content || {};
    const pdfs = [];

    // Extract PDFs from course materials in version content
    Object.keys(versionContent).forEach(version => {
      const versionData = versionContent[version];
      if (versionData.course_materials) {
        versionData.course_materials.forEach(material => {
          if (material.file && material.file.endsWith('.pdf')) {
            pdfs.push({
              title: material.title || 'Course Material',
              url: material.file,
              type: material.title === 'Course Details, Learning Objectives, Table of Contents' ? 'about' : 'full',
              version: version
            });
          }
        });
      }
    });

    return {
      course_id,
      course_title: courseData.data.title?.rendered || courseData.data.title,
      pdfs: pdfs
    };
  } catch (error) {
    console.error('Get course PDFs error:', error);
    return {
      error: error.message || 'Failed to get course PDFs',
      course_id: args.course_id
    };
  }
}

/**
 * Searches PDF content to find courses covering a topic
 * 
 * @param {Object} args - Function arguments
 * @param {string} args.query - What to search for in PDFs
 * @param {number} args.limit - Max number of courses to check (default: 10)
 * @returns {Promise<Object>} Matching courses
 */
export async function searchCoursePDFs(args) {
  try {
    const { query, limit = 10 } = args;

    if (!query || typeof query !== 'string') {
      return {
        error: 'Query is required and must be a string'
      };
    }

    console.log(`Searching PDFs for: "${query}"`);

    // Strategy: Search for courses first, then check their PDFs
    // First, search Dropbox for PDF files matching the query
    const dropboxResults = await searchDropbox({ query: `${query} pdf` });
    
    // Also search courses by name/title
    const { searchCourses } = await import('./courses.js');
    const courseResults = await searchCourses({ query, per_page: limit });
    
    // Combine results
    const results = {
      query,
      dropbox_pdfs: [],
      courses: [],
      suggestions: []
    };

    // Process Dropbox PDF results
    if (!dropboxResults.error && dropboxResults.results) {
      const pdfFiles = dropboxResults.results.filter(file => 
        file.type === 'file' && file.name.toLowerCase().endsWith('.pdf')
      );
      
      results.dropbox_pdfs = pdfFiles.slice(0, 5).map(file => ({
        file_name: file.name,
        file_path: file.path,
        match_type: 'filename'
      }));
    }

    // Process course search results
    if (!courseResults.error && courseResults.courses) {
      results.courses = courseResults.courses.map(course => ({
        id: course.id,
        title: course.title,
        link: course.link,
        excerpt: course.excerpt
      }));

      // For each course, try to get PDFs
      const pdfPromises = results.courses.slice(0, 5).map(async (course) => {
        try {
          const pdfs = await getCoursePDFs({ course_id: course.id });
          if (!pdfs.error && pdfs.pdfs && pdfs.pdfs.length > 0) {
            course.pdfs = pdfs.pdfs;
          }
        } catch (error) {
          // Ignore errors getting PDFs for individual courses
        }
        return course;
      });

      results.courses = await Promise.all(pdfPromises);
    }

    // Provide suggestions
    if (results.courses.length > 0) {
      results.suggestions = [
        `Found ${results.courses.length} course(s) matching "${query}".`,
        'Use getCoursePDFs with a course ID to see what materials are available for a specific course.'
      ];
    } else if (results.dropbox_pdfs.length > 0) {
      results.suggestions = [
        `Found ${results.dropbox_pdfs.length} PDF file(s) in Dropbox matching "${query}".`,
        'Try searching for courses using searchCourses to find courses related to this topic.'
      ];
    } else {
      results.suggestions = [
        'No courses or PDFs found matching your query.',
        'Try using searchCourses with different keywords or a broader topic.'
      ];
    }

    return {
      query,
      count: results.courses.length + results.dropbox_pdfs.length,
      courses: results.courses,
      pdfs: results.dropbox_pdfs,
      suggestions: results.suggestions
    };
  } catch (error) {
    console.error('Search course PDFs error:', error);
    return {
      error: error.message || 'Failed to search course PDFs',
      suggestion: 'Try using searchCourses function with a different query'
    };
  }
}

/**
 * Extracts PDF URLs from course meta data
 */
function extractPDFsFromMeta(meta) {
  const pdfs = [];
  
  // Check for course materials in various meta fields
  const versionContent = meta.flms_version_content || {};
  
  Object.keys(versionContent).forEach(version => {
    const versionData = versionContent[version];
    if (versionData.course_materials) {
      versionData.course_materials.forEach(material => {
        if (material.file && (material.file.endsWith('.pdf') || material.file.includes('.pdf'))) {
          pdfs.push({
            title: material.title || 'Course Material',
            url: material.file,
            type: material.title?.includes('Table of Contents') || material.title?.includes('About') ? 'about' : 'full',
            version: version
          });
        }
      });
    }
  });

  return pdfs;
}

