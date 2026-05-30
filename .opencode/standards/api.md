# API Standards

When building API endpoints:

1. **Response Pattern and HTTP Status:**
   * Return predictable formats (e.g., JSON).
   * Use the correct HTTP codes (200 OK, 201 Created, 204 No Content, 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found, 422 Unprocessable Entity, 500 Internal Server Error).
   * In case of an error, the structure should provide the message and error metadata in a standardized way.

2. **Resources and Transformations (Backend):**
   * In Laravel, use Eloquent API Resources (`JsonResource`, `ResourceCollection`).
   * Do not accidentally send sensitive data (`password`, hidden tokens, internal primary keys).

3. **Versioning:**
   * Consider versioning important endpoints if building for real-world usage (e.g., `/api/v1/users`).

4. **Pagination:**
   * APIs that return lists should always be paginated to avoid memory bottlenecks.

5. **Idempotency and Security:**
   * POST, PUT, DELETE, PATCH routes require rigorous payload validation.
   * Use JWT tokens or Sanctum/Passport in Laravel for API authentication.
