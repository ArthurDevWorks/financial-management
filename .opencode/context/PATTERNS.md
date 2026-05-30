# Patterns Validated

This document catalogs the code and design patterns that have been validated and approved for use in the project. They must be followed consistently.

## Backend (PHP / Laravel / Joomla)

### 1. Thin Controllers
Controllers serve ONLY to:
* Receive the HTTP request.
* Trigger validation (e.g., FormRequests in Laravel).
* Delegate processing to a Service or Action.
* Return a standardized response (JSON, View, Redirect).

### 2. Service Classes
Complex business logic must be encapsulated in Service classes (`AppName\Services\ModuleNameService`).
* Avoid injecting mutable state into the service; it should preferably be stateless.
* A Controller may call multiple Service methods.

### 3. Action Classes (Optional, preferred in modern Laravel)
For very specific use-case operations. Each Action must have only one public method (usually `execute()` or `handle()`).
Example: `CreateUserAction`, `ProcessPaymentAction`.

### 4. Standardized API Responses
Every API must return a consistent format, preferably following a standard like JSend or JSON:API. Never mix error structures within success data.

## Frontend (React)

### 1. Functional Components
Always use functional components and Hooks. Class components are discouraged.

### 2. Container / Presentational Pattern
Separate logic (Container) from visual presentation (Presentational).
* Containers handle state, data fetching, and complex logic.
* Presentationals handle only UI and receive data via `props`.
