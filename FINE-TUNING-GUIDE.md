# Fine-Tuning Your Chatbot Guide

## Understanding Fine-Tuning vs Prompt Engineering

Your current chatbot uses **prompt engineering**, which is generally more cost-effective and easier to manage than fine-tuning. However, I'll explain both approaches:

## Current Setup (Prompt Engineering) ✅ Recommended

Your chatbot is already "fine-tuned" through **system prompts** and **business context**. This is the most flexible and cost-effective approach for most use cases.

### How to Optimize Without Fine-Tuning

#### 1. **Enhance Business Description** (Primary Method)

Go to **WordPress Admin → Chatbot → Business Information**

Add detailed context:

```
You are a professional course advisor for BHFE, specializing in CPE/CE education for:

PROFESSIONAL AUDIENCE:
- CFPs (Certified Financial Planners) - Wealth management, retirement planning
- CPAs (Certified Public Accountants) - Tax, accounting standards, ethics
- IRS Enrolled Agents - Tax preparation, tax law changes
- CDFAs (Certified Divorce Financial Analysts) - Divorce financial analysis
- IARs (Investment Advisor Representatives) - Portfolio management, compliance

COURSE CATEGORIES:
- Tax Planning & Preparation
- Retirement Planning
- Estate Planning
- Ethics & Professional Conduct
- Regulatory Compliance
- Financial Planning Fundamentals

TONE & STYLE:
- Professional but approachable
- Use industry terminology correctly
- Be concise but thorough
- Always mention course specifics when available
- Emphasize continuing education importance

IMPORTANT GUIDELINES:
- Always reference specific course names when relevant
- Explain CPE/CE credit hours clearly
- Mention certification requirements
- Be helpful but never make sales pitches unless specifically asked
```

#### 2. **Adjust AI Parameters**

Edit `includes/class-openai-integration.php` around line 58:

```php
'temperature' => 0.7,  // Lower = more consistent (0.3-0.7 good for professional use)
'max_tokens' => 1000,  // Adjust based on desired response length
'top_p' => 1.0,        // Add for more focused responses
'frequency_penalty' => 0.5,  // Reduce repetition
'presence_penalty' => 0.6,   // Encourage new topics
```

#### 3. **Organize Dropbox Files**

Better file organization = better context:
- Use descriptive filenames: "Ethics_for_CPAs_2024.txt"
- Add course descriptions as separate files
- Use folders for categories: /Tax/, /Ethics/, /Retirement/

## True Fine-Tuning (Advanced & Expensive) ⚠️

Fine-tuning trains a **custom model** on your specific data. This is expensive and usually unnecessary for chatbots.

### When to Consider Fine-Tuning:
- You have 500+ example conversations
- You need specialized terminology
- You're creating a product (not internal tool)
- Budget: $1000+ for training data

### How to Fine-Tune (If Needed):

#### Step 1: Prepare Training Data

Create `training_data.jsonl`:

```json
{"messages": [{"role": "system", "content": "You are a helpful course advisor."}, {"role": "user", "content": "What courses do you have for CPAs?"}, {"role": "assistant", "content": "We offer comprehensive CPA courses including tax planning, ethics, and regulatory updates. Our most popular course is..."}]}
{"messages": [{"role": "system", "content": "You are a helpful course advisor."}, {"role": "user", "content": "Do you have CPE credits for enrolled agents?"}, {"role": "assistant", "content": "Yes! We have IRS-approved continuing education courses specifically designed for enrolled agents. Topics include..."}]}
```

#### Step 2: Upload Training File

```bash
curl https://api.openai.com/v1/files \
  -H "Authorization: Bearer $OPENAI_API_KEY" \
  -F "file=@training_data.jsonl" \
  -F "purpose=fine-tune"
```

#### Step 3: Create Fine-Tuning Job

```bash
curl https://api.openai.com/v1/fine_tuning/jobs \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $OPENAI_API_KEY" \
  -d '{
    "training_file": "file-abc123",
    "model": "gpt-4",
    "hyperparameters": {
      "n_epochs": 3
    }
  }'
```

#### Step 4: Use Fine-Tuned Model

Update plugin settings to use your fine-tuned model ID instead of "gpt-4".

## Recommended Approach for Your Use Case

**Use prompt engineering** (current method) and optimize:

1. ✅ Detailed business description in settings
2. ✅ Well-organized Dropbox files
3. ✅ Tuned system prompts
4. ✅ Adjusted temperature/token parameters

This gives you 90% of fine-tuning benefits at 1% of the cost!

## Cost Comparison

| Method | Setup Time | Monthly Cost* | Best For |
|--------|-----------|---------------|----------|
| **Prompt Engineering** | 5 minutes | $10-50 | Most use cases ✅ |
| **Fine-Tuning** | 2-4 days | $500-2000+ | Specialized products |

*Based on typical usage

## Testing Your Improvements

1. Test with real questions from your customers
2. Adjust business description based on responses
3. Monitor response quality and relevance
4. Iterate until satisfied

## Additional Resources

- [OpenAI Best Practices](https://platform.openai.com/docs/guides/prompt-engineering)
- [Fine-Tuning Documentation](https://platform.openai.com/docs/guides/fine-tuning)
- [Context Window Management](https://platform.openai.com/docs/guides/rate-limits)

## Questions?

Review the system prompts in `includes/class-openai-integration.php` and business context in your WordPress settings. Most "fine-tuning" happens through better prompts!

