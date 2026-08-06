# Security query audit

All request-derived values must remain query bindings. Do not interpolate a request value into a raw SQL expression, column name, order clause, or aggregate.

## Verified query points

- `app/Http/Controllers/Cms/CmsResourceController.php` — CMS search escapes `!`, `%`, and `_`; its `LIKE` pattern is bound. Search columns come only from the internal resource configuration.
- `app/Http/Controllers/Cms/UtilityPageController.php` — not-found report search escapes wildcard characters and binds the `LIKE` pattern.
- `app/Http/Controllers/Cms/WriterController.php` — the role-priority `orderByRaw` uses a fixed SQL expression and a bound role constant.
- `app/Http/Controllers/Cms/DashboardController.php` — aggregate expressions are static; dynamic values use bindings.

When a new search is added, escape `!`, `%`, and `_`, then use a bound `LIKE ? ESCAPE '!'` expression. Keep dynamic sorting on an allowlist of internal column names.
