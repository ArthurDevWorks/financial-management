# Frontend Standards (React)

When developing in React:

1. **Paradigms:**
   * Only functional components and Hooks. No Class Components.
   * Components should have single purposes.

2. **State Management:**
   * Try to keep state as local as possible.
   * If data needs to be consumed by many components globally, use Zustand, Context API, or React Query (for remote server state).

3. **Styles and Design System:**
   * CSS-in-JS or CSS Modules with consistent variables/tokens.
   * TailwindCSS is preferred, if configured, as long as it is used semantically (avoiding giant classes in complex files by extracting base components).

4. **Accessibility (a11y):**
   * Use semantic HTML5 tags (nav, section, article, aside).
   * Include correct `aria-*` properties when implementing custom UI components (such as modals or dropdowns).

5. **Performance:**
   * Use `useMemo` and `useCallback` judiciously to avoid unnecessary re-renders in lists or props passed as callbacks.
   * Use Lazy Loading for entire routes/pages with `React.lazy` and `Suspense`.
