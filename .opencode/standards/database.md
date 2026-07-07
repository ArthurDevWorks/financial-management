# Database Standards

1. **Migrations:**
   * All database schema changes MUST be done via Migrations. Do not modify the database manually.
   * When creating a table, make sure to add foreign keys and correct indexes.

2. **N+1 Problem Prevention:**
   * If you are going to iterate over a list of database models and access their relationships, use `with()` in Laravel Eloquent to eager load.
   * In Joomla, use robust queries with `JOIN` instead of fetching related data individually in a loop.

3. **Soft Deletes:**
   * If data is important or requires history, implement Soft Deletes on the table instead of physically deleting it.

4. **Transactions:**
   * Whenever you perform multiple write operations that depend on each other (e.g., creating a User and then their Account), wrap the process in a transaction (`DB::transaction` or `$db->transaction()`).
