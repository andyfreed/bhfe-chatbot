import express from 'express';
import { handleChatMessage } from '../services/openai.js';

const router = express.Router();

/**
 * POST /chat
 * Handles chat messages from the WordPress frontend
 * 
 * Request body:
 * {
 *   message: string (required) - The user's message
 *   threadId?: string (optional) - The conversation thread ID
 * }
 * 
 * Response:
 * - If streaming: Server-Sent Events (SSE)
 * - If not streaming: JSON with response and threadId
 */
router.post('/', async (req, res) => {
  try {
    const { message, threadId } = req.body;

    // Validate input
    if (!message || typeof message !== 'string') {
      return res.status(400).json({
        error: 'Message is required and must be a string'
      });
    }

    // Check if client wants streaming
    const acceptsStreaming = req.headers.accept === 'text/event-stream';
    
    if (acceptsStreaming) {
      // Set headers for SSE
      res.setHeader('Content-Type', 'text/event-stream');
      res.setHeader('Cache-Control', 'no-cache');
      res.setHeader('Connection', 'keep-alive');
      
      // Handle streaming response
      await handleChatMessage(message, threadId, (chunk) => {
        res.write(`data: ${JSON.stringify(chunk)}\n\n`);
      });
      
      res.end();
    } else {
      // Handle non-streaming response
      let fullResponse = '';
      let finalThreadId = threadId;
      
      await handleChatMessage(message, threadId, (chunk) => {
        if (chunk.type === 'content') {
          fullResponse += chunk.content;
        } else if (chunk.type === 'threadId') {
          finalThreadId = chunk.threadId;
        }
      });
      
      res.json({
        message: fullResponse,
        threadId: finalThreadId
      });
    }
  } catch (error) {
    console.error('Chat error:', error);
    res.status(500).json({
      error: error.message || 'Failed to process chat message'
    });
  }
});

export default router;

