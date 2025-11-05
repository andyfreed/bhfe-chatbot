/**
 * Setup script to configure .env file
 * This will help you set up your environment variables
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const envPath = path.join(__dirname, '..', '.env');
const envExamplePath = path.join(__dirname, '..', 'env.example');

// Read existing .env or create from example
let envContent = '';
if (fs.existsSync(envPath)) {
  envContent = fs.readFileSync(envPath, 'utf-8');
} else if (fs.existsSync(envExamplePath)) {
  envContent = fs.readFileSync(envExamplePath, 'utf-8');
}

// Update values from environment variables
// Note: This script should be run with environment variables set, not hardcoded values
const updates = {
  'OPENAI_API_KEY': process.env.OPENAI_API_KEY || '',
  'DROPBOX_ACCESS_TOKEN': process.env.DROPBOX_ACCESS_TOKEN || '',
  'WORDPRESS_API_SECRET': process.env.WORDPRESS_API_SECRET || '',
  'WORDPRESS_API_URL': process.env.WORDPRESS_API_URL || 'https://www.bhfe.com/wp-json',
  'OPENAI_ASSISTANT_ID': process.env.OPENAI_ASSISTANT_ID || '',
};

// Update each line
let lines = envContent.split('\n');
lines = lines.map(line => {
  for (const [key, value] of Object.entries(updates)) {
    if (line.startsWith(`${key}=`)) {
      return `${key}=${value}`;
    }
  }
  return line;
});

// Write back
fs.writeFileSync(envPath, lines.join('\n'), 'utf-8');
console.log('✅ .env file updated successfully!');
console.log('\n⚠️  IMPORTANT: You still need to:');
console.log('1. Update WORDPRESS_API_URL with your WordPress site URL');
console.log('2. Create the OpenAI Assistant (run: npm run create-assistant)');

