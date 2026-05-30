# Anti-Patterns (What to Avoid)

This document serves as a living memory of approaches that have proven ineffective, problematic, or that violate the defined architecture for this project.

AI and developers **must not** commit or suggest the following mistakes:

## Backend and Database
1. **Fat Controllers:** Inserting complex business logic or dense SQL/ORM queries directly in the Controller.
2. **N+1 Query Problem:** Executing queries inside loops (`foreach`, `map`) without using eager loading (`with()` in Eloquent, or corresponding joins in Joomla).
3. **Excessive Magic:** Using magic functions (`__call`, `__get`) or obscure traits that hinder readability and traceability of the flow by the editor.
4. **Hardcoding Configurations:** Placing credentials, tokens, or URLs directly in the source code instead of using environment variables (`.env`).

## Frontend and UX
1. **Unnecessary Global State:** Putting everything in Redux/Context API when the state is strictly local to a component.
2. **Prop Drilling:** Passing props through many unnecessary component levels. Use component composition or Context API to resolve.
3. **Lack of Visual Feedback:** Leaving the user unaware of the system state (missing loaders for asynchronous requests or clear error messages).

*With each code review containing recurring failures, this file must be updated by the reviewing agent or the developer themselves.*
