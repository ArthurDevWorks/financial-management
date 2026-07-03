---
name: issue-reader
description: Reads and parses GitHub issues, extracting requirements, acceptance criteria, and metadata for the planner.
---

## Issue Reader Skill

Parse GitHub issues into structured summaries for the planning workflow.

### When to Use
- At the start of any new issue from GitHub
- When the planner needs to normalize a new demand
- To produce a concise issue summary before creating implementation issues

### Step 1: Fetch Issue
Run the gh command directly:
```bash
gh issue view <issue-number> --json title,body,labels,assignees,milestone,comments
```

### Step 2: Parse Issue Structure
Extract and categorize:

**Metadata:**
- Issue number and title
- Labels (bug, feature, enhancement, etc.)
- Assignees and milestone
- Priority indicators

**Content:**
- Problem statement / user story
- Acceptance criteria (look for checkboxes, numbered lists)
- Technical requirements (if specified)
- Design references / mockups (links)
- Related issues or dependencies

### Step 3: Classify Issue Type
Based on labels and content:
- `feature` - New functionality
- `bug` - Fix existing behavior
- `refactor` - Code improvement without behavior change
- `docs` - Documentation only
- `test` - Test additions or improvements
- `chore` - Maintenance tasks

### Step 4: Determine Scope
Analyze if the issue requires:
- **Frontend only**: UI changes, components, styling
- **Backend only**: API, database, business logic
- **Full-stack**: Both frontend and backend changes
- **Infrastructure**: DevOps, CI/CD, deployment

### Step 5: Produce a Planning Summary
Return a structured summary with:
- Title and labels
- Problem statement
- Acceptance criteria
- Technical requirements
- Design references
- Dependencies
- Risks and open questions

Do not create local files.

### Parsing Rules
- If acceptance criteria are missing, create them from the description
- If labels are missing, infer type from title/body keywords
- If priority is unclear, default to `medium`
- Always preserve the original issue body verbatim in a collapsible section

### Output
Return a concise summary like this:
```
Parsed issue #<num>: "<title>"
Type: <type> | Scope: <scope>
Ready for: @planner
```
