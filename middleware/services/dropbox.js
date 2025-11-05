import { Dropbox } from 'dropbox';

// Initialize Dropbox client
const dbx = new Dropbox({
  accessToken: process.env.DROPBOX_ACCESS_TOKEN
});

/**
 * Searches for files and folders in Dropbox
 * 
 * @param {Object} args - Function arguments
 * @param {string} args.query - The search query
 * @returns {Promise<Object>} Search results
 */
export async function searchDropbox(args) {
  try {
    const { query } = args;

    if (!query || typeof query !== 'string') {
      return {
        error: 'Query is required and must be a string',
        results: []
      };
    }

    console.log(`Searching Dropbox for: "${query}"`);

    // Use Dropbox search API
    const response = await dbx.filesSearchV2({
      query: query,
      options: {
        max_results: 20, // Limit results
        file_status: 'active' // Only search active files
      }
    });

    // Format the results
    const results = response.result.matches.map(match => {
      const metadata = match.metadata.metadata;
      return {
        name: metadata.name,
        path: metadata.path_lower || metadata.path_display,
        type: metadata['.tag'], // 'file' or 'folder'
        size: metadata.size || null,
        modified: metadata.client_modified || metadata.server_modified || null
      };
    });

    return {
      query,
      count: results.length,
      results: results
    };
  } catch (error) {
    console.error('Dropbox search error:', error);
    return {
      error: error.message || 'Failed to search Dropbox',
      results: []
    };
  }
}

/**
 * Optional: Get file content from Dropbox
 * This can be used if you want the assistant to read file contents
 */
export async function getDropboxFileContent(path) {
  try {
    const response = await dbx.filesDownload({ path });
    const content = response.result.fileBinary.toString('utf-8');
    return content;
  } catch (error) {
    console.error('Dropbox download error:', error);
    throw error;
  }
}

