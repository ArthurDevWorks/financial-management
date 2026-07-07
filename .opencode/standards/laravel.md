# Laravel Standards

Strictly follow these guidelines when writing Laravel code:

1. **Routing:**
   * Avoid defining Closures in route files (`web.php`, `api.php`). Always map routes to a Controller method.
   * Group routes by middleware, prefix, and namespace.

2. **Controllers:**
   * Keep them Slim. Their sole role is to receive the Request, delegate logic, and return a Response.
   * Do not interact with the database directly in Controllers.

3. **Validation:**
   * Always use `FormRequests` instead of `$request->validate()` in the Controller for complex validations or those requiring prior authorization.

4. **Business Logic:**
   * Create `Service` or `Action` classes for any business logic that is not strictly tied to input/output formatting.

5. **Models:**
   * Fill in `$fillable` or `$guarded` properties.
   * Define all relationships clearly.
   * Avoid heavy queries (Scopes are useful, but do not put complex logic in them).

6. **Queues and Jobs:**
   * Long-running processes (sending emails, external API calls, report generation) should be done in asynchronous Jobs.
