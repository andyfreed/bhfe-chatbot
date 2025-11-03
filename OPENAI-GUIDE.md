# Complete OpenAI Configuration Guide

This guide shows you how to maximize your chatbot using OpenAI's powerful features.

## Quick Setup

1. **Get Your API Key**: https://platform.openai.com/api-keys
2. **Add it**: WordPress Admin → Chatbot → OpenAI API Key
3. **Choose Model**: Recommended models below
4. **Test**: Ask your chatbot a question!

## Available OpenAI Models

### 🚀 Recommended Models

| Model | Best For | Speed | Cost | Intelligence |
|-------|----------|-------|------|--------------|
| **GPT-4 Turbo** ⭐ | Most use cases | Fast | Medium | High |
| **GPT-4o** 🆕 | Latest features | Very Fast | Medium | High |
| **GPT-4** | Complex tasks | Slow | High | Highest |

### 💰 Budget-Friendly Models

| Model | Best For | Speed | Cost | Intelligence |
|-------|----------|-------|------|--------------|
| **GPT-3.5 Turbo** | Simple Q&A | Very Fast | Low | Medium |

### 🧠 Advanced Reasoning Models

| Model | Best For | Speed | Cost | Intelligence |
|-------|----------|-------|------|--------------|
| **O1 Preview** | Complex problem-solving | Slow | High | Advanced |
| **O1 Mini** | Quick reasoning | Medium | Medium | Advanced |

> **Note**: O1 models work differently - they don't use temperature or other standard parameters.

## Advanced Parameters Explained

### Temperature (0.0 - 2.0)

Controls randomness and creativity of responses.

**Examples:**
```
Temperature = 0.2
- Very focused and consistent
- Best for: Factual information, course details
- Response style: Technical, precise

Temperature = 0.7 ⭐ Recommended
- Balanced creativity and consistency
- Best for: Most chatbot conversations
- Response style: Natural, helpful

Temperature = 1.2
- More creative and varied
- Best for: Creative tasks
- Response style: Varied, less predictable
```

**For CPE/CE Chatbots**: Use **0.5 - 0.8** for professional, consistent responses.

### Max Tokens (100 - 4000)

Maximum length of each response.

**Guide:**
- **500 tokens**: Short, quick answers (~75 words)
- **1000 tokens** ⭐ Default: Medium responses (~150 words)
- **2000 tokens**: Longer, detailed responses (~300 words)
- **4000 tokens**: Very long explanations (~600 words)

**Tip**: Start with 1000, increase if users need more detail.

### Frequency Penalty (-2.0 to 2.0)

Reduces repetition of words/phrases within responses.

**Examples:**
```
Frequency Penalty = 0
- Normal repetition allowed
- Best for: Most use cases

Frequency Penalty = 0.5 ⭐ Recommended for Chatbots
- Reduces word repetition
- Best for: Professional conversations

Frequency Penalty = 1.0
- Highly varied word choice
- Best for: Creative writing
```

**For CPE/CE Chatbots**: Use **0.3 - 0.7** to avoid repetitive phrasing.

### Presence Penalty (-2.0 to 2.0)

Encourages talking about new topics vs. staying on current topic.

**Examples:**
```
Presence Penalty = 0
- Natural topic flow
- Best for: Most conversations

Presence Penalty = 0.5
- Encourages diverse topics
- Best for: Course discovery

Presence Penalty = -0.5
- Stays focused on current topic
- Best for: Deep dives into single course
```

**For CPE/CE Chatbots**: Use **0 - 0.3** to balance focus and exploration.

## Recommended Settings for CPE/CE Courses

### Conservative / Factual Style
```
Model: GPT-4 Turbo
Temperature: 0.5
Max Tokens: 1000
Frequency Penalty: 0.3
Presence Penalty: 0
```

**Use when**: Providing technical information, course details, credits.

### Professional / Friendly Style ⭐ Recommended
```
Model: GPT-4 Turbo
Temperature: 0.7
Max Tokens: 1000
Frequency Penalty: 0.5
Presence Penalty: 0.2
```

**Use when**: General customer service, course recommendations.

### Creative / Engaging Style
```
Model: GPT-4 Turbo
Temperature: 0.8
Max Tokens: 1500
Frequency Penalty: 0.6
Presence Penalty: 0.4
```

**Use when**: Explaining benefits, sparking interest in courses.

## Fine-Tuning Without Training

You don't need to fine-tune a model! Use these techniques:

### 1. System Prompt (Business Description)

In **WordPress Admin → Chatbot → Business Description**, add:

```
You are a professional course advisor for BHFE specializing in CPE/CE education.

AUDIENCE: CFPs, CPAs, IRS Enrolled Agents, CDFAs, IARs

COURSES:
- Tax Planning & Preparation
- Retirement Planning  
- Estate Planning
- Ethics & Professional Conduct
- Regulatory Compliance

TONE:
- Professional but approachable
- Clear and concise
- Use industry terminology correctly
- Mention specific courses when relevant

RESPONSE GUIDELINES:
- Always be helpful and accurate
- Emphasize continuing education importance
- Explain CPE/CE credits clearly
- Never make sales pitches unless asked
```

### 2. Context from Dropbox

Organize your course files for better results:
- Use descriptive filenames: "Ethics_CPA_2024_8_credits.txt"
- Add course descriptions at the top of files
- Use consistent formatting
- Include key details: credits, duration, topics

### 3. Temperature for Consistency

Lower temperature = more consistent responses
- For factual info: 0.3 - 0.5
- For friendly chat: 0.6 - 0.8
- For creative: 0.9 - 1.2

## Cost Management

### Understanding Costs

```
GPT-4 Turbo: ~$0.01 per 1000 tokens (~750 words input/output)
GPT-3.5 Turbo: ~$0.002 per 1000 tokens (~750 words)

Average conversation:
- User asks question: ~100 tokens
- AI responds: ~500 tokens
- Total: ~$0.06 per conversation with GPT-4
```

### Reducing Costs

1. **Lower Max Tokens**: Use 500-800 instead of 1000+
2. **Use GPT-3.5**: For simple questions, 5x cheaper
3. **Optimize Prompts**: Clearer prompts = fewer tokens
4. **Cache Responses**: Consider caching common questions

## Testing Your Settings

### Test Questions

Try these to see how settings affect responses:

**Simple**: "What courses do you have for CPAs?"
**Complex**: "I need 40 CPE credits by December for my CPA license renewal, but I'm traveling for 6 months. What courses would you recommend?"
**Specific**: "Tell me about your estate planning ethics course."

### Evaluating Results

✅ **Good Response**:
- Direct and helpful
- Mentions specific courses
- Professional tone
- Right length

❌ **Needs Adjustment**:
- Too vague → Add business description context
- Too repetitive → Increase frequency penalty
- Too short → Increase max tokens
- Too creative → Decrease temperature

## Troubleshooting

### "I encountered an error"

1. Check API key is correct
2. Verify account has credits: https://platform.openai.com/account/usage
3. Check rate limits in PHP error logs
4. Try GPT-3.5 to test connection

### Responses too generic

1. Enhance business description with more context
2. Organize Dropbox files with better metadata
3. Lower temperature (0.5 - 0.7)
4. Add more specific prompts to system message

### Responses too verbose

1. Lower max tokens (500-800)
2. Increase frequency penalty (0.5-0.7)
3. Simplify business description

### Cost concerns

1. Switch to GPT-3.5 Turbo
2. Reduce max tokens
3. Monitor usage: https://platform.openai.com/account/usage
4. Set monthly spending limits in OpenAI dashboard

## Best Practices

1. **Start with defaults**, then adjust based on real conversations
2. **Monitor actual conversations** to see what works
3. **A/B test** different settings
4. **Document** what settings work for your use case
5. **Regular reviews** - check monthly

## Additional Resources

- [OpenAI Platform](https://platform.openai.com/)
- [OpenAI Documentation](https://platform.openai.com/docs)
- [Model Comparison](https://platform.openai.com/docs/models)
- [Pricing Information](https://openai.com/api/pricing/)
- [Best Practices](https://platform.openai.com/docs/guides/prompt-engineering)

## Need Help?

- Review your **Business Description** in settings
- Check your **Dropbox file organization**
- Try adjusting **Temperature first** (biggest impact)
- Test with **GPT-3.5** to verify everything works
- Check **PHP error logs** for API issues

Remember: You can adjust settings anytime! Experiment to find what works best for your audience.

