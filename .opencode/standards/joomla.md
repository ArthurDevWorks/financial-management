# Joomla Standards

Strictly follow these guidelines when writing extensions (Components, Modules, Plugins) for Joomla 4/5:

1. **Architecture (MVC):**
   * Strictly follow the Joomla MVC pattern when creating components.
   * Use namespaces in Joomla classes (Joomla 4+).
   * Models handle state and data; Views/HTML formats deal strictly with rendering.

2. **Database Access:**
   * Use the Database Factory (`Factory::getContainer()->get('DatabaseDriver')` or similar via DI) and Joomla's QueryBuilder.
   * NEVER inject raw variables into SQL. Always use prepared statements or safe quoting methods.

3. **Dependency Injection:**
   * Use the native dependency container introduced in Joomla 4 to load services and factories for your extension.

4. **Plugins and Events:**
   * Do not hack the core. Use events (Dispatcher) and Plugins to alter behaviors.
   * Name plugin methods with the prefix `on...` to transparently capture corresponding events.

5. **Security:**
   * Make extensive use of `JSession::checkToken()` (or modern aliases) in form requests and non-public APIs.
   * Sanitize request inputs according to Input filters (INT, WORD, HTML, etc).
