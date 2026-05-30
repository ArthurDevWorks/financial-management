# Automated Testing Standards

1. **Backend Tests:**
   * The main framework should be PHPUnit or Pest, configured according to the stack in `PROJECT_CONTEXT.MD`.
   * Focus on creating **Feature Tests** (to test the endpoint / full flow) and **Unit Tests** only for very isolated business logic and complex helpers.
   * Use Factories to generate test data in Laravel.
   * Clean the database and reset state (e.g., `RefreshDatabase` trait in Laravel) in integration tests.

2. **Frontend Tests:**
   * For React component-based UI tests, Vitest or Jest with React Testing Library is preferred.
   * Avoid testing implementation details ("whether the button class is green"), but instead test whether the functionality occurs ("if I click the button, the modal opens").

3. **Mocks and Stubs:**
   * Isolate third-party APIs and computationally expensive services using mocks and stubs. Automated tests should never depend on the uptime and response time of a real external web service.
