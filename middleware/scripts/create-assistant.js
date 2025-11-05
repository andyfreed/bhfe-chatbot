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
  },
  {
    name: 'searchCourses',
    description: 'Searches for active CPE/CE courses on the BHFE website. Use this when users ask about courses, want to find courses by topic, or are looking for specific course types. Only returns active courses (approximately 400 active courses available).',
    parameters: {
      type: 'object',
      properties: {
        query: {
          type: 'string',
          description: 'The search query to find courses (e.g., "ethics", "tax planning", "CFP", "CPA", "divorce")'
        },
        per_page: {
          type: 'number',
          description: 'Number of courses to return (default: 20, max recommended: 50)'
        }
      },
      required: ['query']
    }
  }
];

async function createAssistant() {
  try {
    console.log('Creating OpenAI Assistant...');

    const assistant = await openai.beta.assistants.create({
      name: 'WordPress Chatbot',
      instructions: `You are an AI assistant for Beacon Hill Financial Educators (BHFE), a company that sells CPE (Continuing Professional Education) and CE (Continuing Education) courses to financial professionals.

**About BHFE:**
- BHFE sells online and print CPE/CE courses to financial professionals including:
  - CFPs (Certified Financial Planners)
  - CPAs (Certified Public Accountants)
  - CDFAs (Certified Divorce Financial Analysts)
  - IRS Tax Preparers (Enrolled Agents, OTRPs)
  - ERPAs (Enrolled Retirement Plan Agents)
  - IARs (Investment Adviser Representatives)
  - And other certifications

**How the Website Works:**
- Products are linked to courses - when people search for courses to buy, they are searching courses
- Every course has PDF files associated with it:
  - An "About" PDF that shows the table of contents
  - A full PDF of the course material (available after purchase)
- After purchasing, customers take an exam on the website
- When they pass, they get a certificate
- Certificate information is kept in their account for later access
- There are many courses on the site, but only ~400 are active versions
- The chatbot should ONLY focus on active courses (ignore inactive/archived courses)

**What You Can Do:**
- Search for active courses using the searchCourses function (use this when users ask about courses, want to find courses by topic, or are looking for specific course types)
- Search for files in Dropbox using the searchDropbox function (for course materials, PDFs, etc.)
- Retrieve WordPress data using the getWordPressData function (for general WordPress content)

**Important Guidelines:**
- Always use searchCourses when users ask about courses, want to find courses, or are looking for course information
- Only show active courses - never mention inactive or archived courses
- Be helpful and friendly when helping users find courses
- Explain course features like PDFs, exams, and certificates when relevant
- If you don't know something, say so`,
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

