# Update Assistant Instructions

## What I've Done

I've added a new `searchCourses` function that will allow the chatbot to search for active courses on your BHFE website. I've also updated the assistant's instructions to understand your business model.

## What You Need to Do

### Option 1: Update Existing Assistant (Recommended)

1. **Go to OpenAI Dashboard:**
   - Visit: https://platform.openai.com/assistants
   - Find your assistant (ID: `asst_VUtYqESCitIRHPz0gaPGm8d4`)
   - Click on it to edit

2. **Update Instructions:**
   - Replace the instructions with this new text:

```
You are an AI assistant for Beacon Hill Financial Educators (BHFE), a company that sells CPE (Continuing Professional Education) and CE (Continuing Education) courses to financial professionals.

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
- If you don't know something, say so
```

3. **Add the searchCourses Function:**
   - The function should already be available (it's defined in the code)
   - But make sure the assistant has "Function calling" enabled in the Tools section
   - Click "Save"

### Option 2: Create New Assistant

If you prefer to create a new assistant:

1. **Run the updated script:**
   ```bash
   cd middleware
   npm run create-assistant
   ```

2. **Update .env file:**
   - Copy the new Assistant ID to your `.env` file
   - Update it in Render environment variables

## Deploy the Code Changes

After updating the assistant, you need to deploy the code changes:

1. **Push to GitHub:**
   ```bash
   git add .
   git commit -m "Add course search functionality and update assistant instructions"
   git push
   ```

2. **Render will auto-deploy:**
   - Since auto-deploy is enabled, Render will automatically deploy the changes
   - Wait 2-3 minutes for deployment

3. **Test the chatbot:**
   - Visit your staging site
   - Try asking: "Show me courses about ethics" or "Find CPA courses"
   - The chatbot should now use the searchCourses function

## What's New

### New Function: `searchCourses`

- **Purpose**: Searches for active courses on your BHFE website
- **Parameters**:
  - `query`: Search term (e.g., "ethics", "tax planning", "CFP")
  - `per_page`: Number of results (default: 20)
- **Returns**: List of active courses matching the query

### Updated Assistant Instructions

The assistant now understands:
- Your business model (CPE/CE courses for financial professionals)
- How your website works (products linked to courses, PDFs, exams, certificates)
- To only show active courses (~400 active versions)
- To use searchCourses when users ask about courses

## Testing

After deployment, test these queries:
- "Show me courses about ethics"
- "Find CPA courses"
- "What courses do you have for CFP?"
- "Search for divorce courses"
- "Show me tax planning courses"

The chatbot should now understand your business and search courses appropriately!

