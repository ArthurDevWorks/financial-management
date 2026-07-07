# Architecture

This document describes the macro and permanent architectural decisions of the project. The decisions herein guide software design and are complemented by Architecture Decision Records (ADRs).

## General Principles

1. **Separation of Concerns (SoC):** Each layer (Controller, Service, Repository, Model, etc.) must have a single, clear responsibility.
2. **Dependency Injection:** Always use constructor-based dependency injection to facilitate testing and reduce coupling.
3. **N-Tier Pattern:**
   * **Presentation (HTTP/Web):** Clean controllers, validators, and response formatting.
   * **Business Logic:** Services or Actions that contain the core of the application.
   * **Data Access:** Repositories (when applicable) or Models (using Active Record consciously without overloading with business logic).
4. **Resilience and Scalability:** Assume external resources (APIs, Database, Cache) can fail. Implement logging and fallback/retry strategies.

## Architectural Evolution

New decisions that impact the global structure of the application must be documented as a new ADR in the `.opencode/context/adrs/` directory.
