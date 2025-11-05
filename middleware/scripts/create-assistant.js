/**
 * Helper script to create an OpenAI Assistant programmatically
 * 
 * Run this once to create your assistant:
 * node scripts/create-assistant.js
 * 
 * Then copy the Assistant ID to your .env file
 */

import OpenAI from 'openai';
import dotenv from 'dotenv';

dotenv.config();

const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY
});

// Function definitions for the assistant
const functionDefinitions = [
  {
    name: 'searchDropbox',
    description: 'Searches for files and folders in Dropbox that match the given query. Use this when the user asks about files, documents, or content stored in Dropbox.',
    parameters: {
      type: 'object',
      properties: {
        query: {
          type: 'string',
          description: 'The search query to find files or folders in Dropbox'
        }
      },
      required: ['query']
    }
  },
  {
    name: 'getWordPressData',
    description: 'Retrieves data from the WordPress REST API. Use this when the user asks about WordPress content like posts, pages, users, or other WordPress data.',
    parameters: {
      type: 'object',
      properties: {
        endpoint: {
          type: 'string',
          description: 'The WordPress REST API endpoint path (e.g., "/wp/v2/posts", "/wp/v2/pages")'
        },
        params: {
          type: 'object',
          description: 'Optional query parameters for the API request'
        }
      },
      required: ['endpoint']
    }
  }
];

async function createAssistant() {
  try {
    console.log('Creating OpenAI Assistant...');

    const assistant = await openai.beta.assistants.create({
      name: 'WordPress Chatbot',
      instructions: `You are a helpful AI assistant for a WordPress website. 
You can help users by:
- Searching for files in Dropbox using the searchDropbox function
- Retrieving data from the WordPress REST API using the getWordPressData function

Always be friendly and helpful. If you don't know something, say so. 
When users ask about files or documents, use the searchDropbox function.
When users ask about WordPress content (posts, pages, users, etc.), use the getWordPressData function.`,
      model: 'gpt-4-turbo-preview', // Change to 'gpt-3.5-turbo' if you prefer
      tools: functionDefinitions.map(fn => ({
        type: 'function',
        function: fn
      }))
    });

    console.log('\n✅ Assistant created successfully!');
    console.log(`\nAssistant ID: ${assistant.id}`);
    console.log(`\n📝 Copy this to your .env file:`);
    console.log(`OPENAI_ASSISTANT_ID=${assistant.id}\n`);
  } catch (error) {
    console.error('❌ Error creating assistant:', error);
    process.exit(1);
  }
}

createAssistant();

