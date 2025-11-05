import OpenAI from 'openai';
import { searchDropbox } from './dropbox.js';
import { getWordPressData } from './wordpress.js';
import { searchCourses } from './courses.js';
import { getCoursePDFs, searchCoursePDFs } from './course-pdfs.js';

// Initialize OpenAI client
const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY
});

const ASSISTANT_ID = process.env.OPENAI_ASSISTANT_ID;

/**
 * Define the functions that the assistant can call
 * These functions will be available to the assistant when it needs to retrieve data
 */
const availableFunctions = {
  searchDropbox: searchDropbox,
  getWordPressData: getWordPressData,
  searchCourses: searchCourses,
  getCoursePDFs: getCoursePDFs,
  searchCoursePDFs: searchCoursePDFs
};

/**
 * Function definitions for OpenAI
 * These tell the assistant what functions are available and how to use them
 */
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
  },
  {
    name: 'getCoursePDFs',
    description: 'Gets PDF files associated with a course. Each course typically has an "About" PDF (table of contents) and a full course PDF (available after purchase). Use this when users ask about course materials, PDFs, or what a course covers.',
    parameters: {
      type: 'object',
      properties: {
        course_id: {
          type: 'number',
          description: 'The course ID (get this from searchCourses results)'
        }
      },
      required: ['course_id']
    }
  },
  {
    name: 'searchCoursePDFs',
    description: 'Searches PDF files in Dropbox to find courses that cover a specific topic. Use this when users want to find courses by content (e.g., "find courses about divorce taxation" or "what courses cover retirement planning"). This searches the actual PDF content to help users find courses.',
    parameters: {
      type: 'object',
      properties: {
        query: {
          type: 'string',
          description: 'What to search for in PDFs (e.g., "divorce taxation", "retirement planning", "estate tax")'
        },
        limit: {
          type: 'number',
          description: 'Maximum number of courses to check (default: 10)'
        }
      },
      required: ['query']
    }
  }
];

/**
 * Handles a chat message and processes it through the OpenAI Assistants API
 * 
 * @param {string} message - The user's message
 * @param {string|null} threadId - Optional existing thread ID for conversation continuity
 * @param {Function} onChunk - Callback function to handle streaming chunks
 */
export async function handleChatMessage(message, threadId, onChunk) {
  try {
    // Create a new thread if one doesn't exist
    let thread;
    if (threadId) {
      try {
        thread = await openai.beta.threads.retrieve(threadId);
      } catch (error) {
        console.log('Thread not found, creating new one');
        thread = await openai.beta.threads.create();
      }
    } else {
      thread = await openai.beta.threads.create();
    }

    // Send chunk with thread ID
    onChunk({ type: 'threadId', threadId: thread.id });

    // Add the user's message to the thread
    await openai.beta.threads.messages.create(thread.id, {
      role: 'user',
      content: message
    });

    // Run the assistant
    // Note: Tools can be defined on the assistant in OpenAI dashboard
    // or passed here. If already defined on assistant, you can omit this.
    let run = await openai.beta.threads.runs.create(thread.id, {
      assistant_id: ASSISTANT_ID
    });

    // Poll for completion and handle function calls
    while (run.status === 'queued' || run.status === 'in_progress' || run.status === 'requires_action') {
      // Wait a bit before checking again
      await new Promise(resolve => setTimeout(resolve, 1000));

      // Check the run status
      run = await openai.beta.threads.runs.retrieve(thread.id, run.id);

      // If the run requires action (function calls), handle them
      if (run.status === 'requires_action') {
        const toolCalls = run.required_action?.submit_tool_outputs?.tool_calls || [];

        if (toolCalls.length > 0) {
          const toolOutputs = [];

          // Process each function call
          for (const toolCall of toolCalls) {
            const functionName = toolCall.function.name;
            const functionArgs = JSON.parse(toolCall.function.arguments);

            console.log(`Calling function: ${functionName} with args:`, functionArgs);

            try {
              // Call the appropriate function
              const functionToCall = availableFunctions[functionName];
              if (!functionToCall) {
                throw new Error(`Function ${functionName} not found`);
              }

              const result = await functionToCall(functionArgs);

              // Submit the tool output
              toolOutputs.push({
                tool_call_id: toolCall.id,
                output: JSON.stringify(result)
              });
            } catch (error) {
              console.error(`Error calling ${functionName}:`, error);
              toolOutputs.push({
                tool_call_id: toolCall.id,
                output: JSON.stringify({ error: error.message })
              });
            }
          }

          // Submit all tool outputs
          run = await openai.beta.threads.runs.submitToolOutputs(thread.id, run.id, {
            tool_outputs: toolOutputs
          });
        }
      }
    }

    // Check if the run completed successfully
    if (run.status === 'completed') {
      // Retrieve the messages from the thread
      const messages = await openai.beta.threads.messages.list(thread.id, {
        limit: 1,
        order: 'desc'
      });

      const assistantMessage = messages.data[0];
      const content = assistantMessage.content[0];

      if (content.type === 'text') {
        // Stream the response
        const text = content.text.value;
        // Split into chunks for streaming effect
        const words = text.split(' ');
        for (let i = 0; i < words.length; i++) {
          onChunk({
            type: 'content',
            content: words[i] + (i < words.length - 1 ? ' ' : '')
          });
          // Small delay for streaming effect
          await new Promise(resolve => setTimeout(resolve, 20));
        }

        onChunk({ type: 'done' });
      } else {
        onChunk({ type: 'content', content: 'I received a response, but it was not in text format.' });
        onChunk({ type: 'done' });
      }
    } else {
      throw new Error(`Run failed with status: ${run.status}`);
    }
  } catch (error) {
    console.error('OpenAI error:', error);
    throw error;
  }
}

