---
name: lessons-writer
description: Updates PROJECT_CONTEXT.md with lessons learned, patterns discovered, architecture decisions, and convention changes for the planner, designer, executor, and QA roles.
---

## Context Updater Skill

Keep `PROJECT_CONTEXT.md` synchronized with durable project learnings.

### When to Use
- After any correction from the user or QA
- When discovering a non-obvious solution, gotcha, or edge case
- After resolving a difficult bug
- When a pattern emerges that should be documented
- When architecture, workflow, or coding conventions change
- At the end of each issue or feature completion

### What Gets Updated
- Project overview for meaningful scope changes
- Stack details for new libraries or framework upgrades
- Architecture and patterns for durable implementation decisions
- Coding standards and workflow rules for convention changes
- Common patterns and lessons learned for future work

### How to Update
1. Read the relevant section of `PROJECT_CONTEXT.md` first.
2. Identify whether the change belongs in overview, stack, architecture, standards, workflow, or lessons.
3. Append concise dated notes for new learnings.
4. Do not overwrite existing knowledge without a clear reason.

### Output
After updating the file, report:
```markdown
## PROJECT_CONTEXT Updated

**Section:** <section number and name>
**Change:** <brief description>
**Reason:** <why this change was needed>
```
