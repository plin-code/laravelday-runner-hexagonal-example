<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `vendor/bin/sail composer show --direct` to list direct dependencies with versions, or `vendor/bin/sail composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `vendor/bin/sail npm run build` or ask the user to run `vendor/bin/sail npm run dev` or `vendor/bin/sail composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `vendor/bin/sail artisan route:list`). Use `vendor/bin/sail artisan list` to discover available commands and `vendor/bin/sail artisan [command] --help` to check parameters.
- Inspect routes with `vendor/bin/sail artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `vendor/bin/sail artisan config:show app.name`, `vendor/bin/sail artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `vendor/bin/sail artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `vendor/bin/sail artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== sail rules ===

# Laravel Sail

- This project runs inside Laravel Sail's Docker containers. You MUST execute all commands through Sail.
- Start services using `vendor/bin/sail up -d` and stop them with `vendor/bin/sail stop`.
- Open the application in the browser by running `vendor/bin/sail open`.
- Always prefix PHP, Artisan, Composer, and Node commands with `vendor/bin/sail`. Examples:
    - Run Artisan Commands: `vendor/bin/sail artisan migrate`
    - Install Composer packages: `vendor/bin/sail composer install`
    - Execute Node commands: `vendor/bin/sail npm run dev`
    - Execute PHP scripts: `vendor/bin/sail php [script]`
- View all available Sail commands by running `vendor/bin/sail` without arguments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `vendor/bin/sail artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `vendor/bin/sail artisan list` and check their parameters with `vendor/bin/sail artisan [command] --help`.
- If you're creating a generic PHP class, use `vendor/bin/sail artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `vendor/bin/sail artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `vendor/bin/sail artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/sail bin pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/sail bin pint --test --format agent`, simply run `vendor/bin/sail bin pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `vendor/bin/sail artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `vendor/bin/sail artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/sail bin pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

=== plin-code/laravel-custom-fields/core rules ===

## Laravel Custom Fields

`plin-code/laravel-custom-fields` lets a product define fields on Eloquent models at
runtime. A definition is a `PlinCode\CustomFields\Models\CustomField` row, and every value
is a `PlinCode\CustomFields\Models\CustomFieldValue` row holding the value in one typed
column (`value_string`, `value_text`, `value_integer`, `value_decimal`, `value_boolean`,
`value_date`, `value_datetime`, `value_json`). The package is headless: no routes, no
controllers, no UI. Use the facade `PlinCode\CustomFields\Facades\CustomFields`.

### Making a model own custom fields

There is no interface to implement. Add the `PlinCode\CustomFields\Concerns\HasCustomFields`
trait and register the model under a short entity key in the `boot()` method of a service
provider, on every request, since the key also goes into the morph map.

<code-snippet name="Register an entity" lang="php">
use App\Models\Patient;
use PlinCode\CustomFields\Facades\CustomFields;

CustomFields::registerEntity(Patient::class, 'patient', 'Patient');
</code-snippet>

### Defining fields

Fill `entity_type` with `CustomFields::entityKey()`, never a typed string: nothing checks it,
and a typo creates a field every read ignores. `type` is a registered type key: `text`,
`textarea`, `email`, `url`, `phone`, `number`, `decimal`, `boolean`, `date`, `datetime`,
`select`, `multiselect` (not `multi_select`). The slug is generated from the name on
creation and never changes, it is the key used everywhere else.

<code-snippet name="Create a select definition" lang="php">
use PlinCode\CustomFields\Models\CustomField;

$field = CustomField::create([
    'entity_type' => CustomFields::entityKey(Patient::class),
    'name' => 'Risk level',
    'type' => 'select',
    'is_required' => true,
    'options' => [
        ['key' => 'low', 'label' => 'Low', 'is_active' => true],
        ['key' => 'high', 'label' => 'High', 'is_active' => true],
    ],
]);

$field->slug; // 'risk-level'
</code-snippet>

Option keys are permanent. Change options with `$field->updateOptions()`, deactivate an
option with `is_active => false`, never remove one (it throws).

### Reading and writing values

<code-snippet name="Values on a persisted model" lang="php">
$patient->setCustomFields(['risk-level' => 'high', 'notes' => 'Follow up.']);
$patient->setCustomFields($payload, complete: true);
$patient->setCustomField('notes', null); // null or '' deletes the stored row
$patient->getCustomField('risk-level');   // 'high'
$patient->getCustomFields();              // every active field, keyed by slug
$patient->clearCustomField('notes');
</code-snippet>

- Writes validate the whole batch, then write it in one transaction. Partial validation
  (the default) checks only the submitted keys; `complete: true` also enforces required
  fields against the stored values.
- An invalid value, an unknown slug or an inactive field throws
  `Illuminate\Validation\ValidationException` keyed by slug.
- Writing to an unsaved model throws `PlinCode\CustomFields\Exceptions\ModelNotPersistedException`.
- `getCustomField()`, `clearCustomField()` and the `whereCustomField()` scope throw
  `PlinCode\CustomFields\Exceptions\UnknownCustomFieldException` for an unknown slug.
- Validate without writing with `CustomFields::validate($patient, $values, complete: true)`.

### Querying

`Patient::whereCustomField('risk-level', 'high')` serialises the value like a write would,
except on a `multiselect`, where it uses JSON containment on the raw value. It does not validate.
`null` matches the hosts without a stored value, and an empty string throws `InvalidArgumentException`.
Request filters and sorts are built for `spatie/laravel-query-builder`, one per operation the
field type declares. They read the definitions from the database, so call them while serving
a request, never in a service provider.

<code-snippet name="Expose custom fields to a request" lang="php">
use Spatie\QueryBuilder\QueryBuilder;

$options = CustomFields::queryOptionsFor(Patient::class);

QueryBuilder::for(Patient::class)
    ->allowedFilters(...$options['filters'])
    ->allowedSorts(...$options['sorts'])
    ->paginate();
</code-snippet>

Names are the prefix plus the slug, with `:operation` for anything but equality:
`filter[cf_risk-level]=high`, `filter[cf_risk-level:in]=high,low`, `sort=-cf_risk-level`.
Build them with `CustomFields::filterName()` and `CustomFields::sortName()`.

A malformed value on an equality, list, range or comparison filter, or an empty `contains`
item, throws `PlinCode\CustomFields\Exceptions\InvalidFilterValueException`, an HTTP 400
that extends Spatie's `InvalidFilterValue`. Presence filters and `contains_any`/`contains_all`
are not validated.

### Configuration

Read `config/laravel-custom-fields.php` before assuming table names or key types:

- `laravel-custom-fields.tables.fields` and `laravel-custom-fields.tables.values`: table names.
- `laravel-custom-fields.key_type` and `laravel-custom-fields.morph_key_type`: `id`, `uuid` or
  `ulid`. Set them before running the migrations.
- `laravel-custom-fields.models.custom_field` and `laravel-custom-fields.models.custom_field_value`:
  replacement models, which must extend the package ones. Resolve them with
  `CustomFields::fieldModel()` and `CustomFields::valueModel()`.
- `laravel-custom-fields.key_prefix`: the filter and sort prefix, `cf_` by default.

=== plin-code/laravel-sql-dialect/core rules ===

## Laravel SQL Dialect

Static helpers in `PlinCode\SqlDialect` for SQL that must behave the same on PostgreSQL, MySQL and SQLite. There is no service provider, config, facade or macro: import the class and call it. Use them instead of writing the driver specific SQL by hand.

### Partial text matches

Do not write `->where('title', 'like', "%{$term}%")` with a user supplied term: `%`, `_` and `\` in the term act as wildcards, and case sensitivity differs per driver. `LikeOperator::applyContains()` escapes the term, binds it, adds the right `ESCAPE` clause, and uses `ILIKE` on PostgreSQL and `LIKE` elsewhere.

<code-snippet name="Wildcard safe contains filter" lang="php">
use Illuminate\Database\Eloquent\Builder;
use PlinCode\SqlDialect\LikeOperator;

Movie::query()
    ->where(function (Builder $query) use ($term, $excluded) {
        LikeOperator::applyContains($query, 'title', $term);
        LikeOperator::applyNotContains($query, 'title', $excluded);
    })
    ->get();
</code-snippet>

- Variants: `LikeOperator::orApplyContains()`, `LikeOperator::applyNotContains()`, `LikeOperator::orApplyNotContains()`. Wrap OR clauses in a closure so they do not leak into the rest of the query. A `NULL` column matches neither the positive nor the negated form.
- `LikeOperator::applyContainsOnDate()` matches a partial date by casting the column to text first, instead of a hand written `CAST(... AS CHAR)` or `::text`.
- These methods take `Illuminate\Database\Eloquent\Builder` only, not `Illuminate\Database\Query\Builder` (so not inside `whereExists()` or `orWhereIn()` closures).
- `$column` is interpolated into raw SQL: pass a column name from your code, never request input. The term is always bound.
- Building SQL by hand: `LikeOperator::for()` returns the operator, `LikeOperator::containsPattern()` the escaped `%term%` pattern.

### Year of a date column

Do not write `YEAR(...)`, `EXTRACT(YEAR FROM ...)` or `strftime('%Y', ...)` in `whereRaw()` or `orderByRaw()`. Use `YearExpression::numeric()` (integer, for ordering and comparisons) or `YearExpression::text()` (text, for display).

<code-snippet name="Year expressions" lang="php">
use PlinCode\SqlDialect\YearExpression;

$query->whereRaw(YearExpression::numeric($query, 'release_date').' = ?', [$year]);
$query->orderByRaw(YearExpression::numeric($query, 'release_date').' desc');
</code-snippet>

### Multi value filters

Instead of `explode(',', ...)` plus `trim()` and `array_filter()`, call `CsvValues::parse($value)`. It accepts an array, a comma separated string or a scalar and returns a `list<string>`: trimmed, blanks dropped, order kept. Non scalar entries are dropped silently.

### Drivers

Supported: `pgsql`, `mysql`, `mariadb` (handled but not covered by the test suite), `sqlite`. Any other driver, `sqlsrv` included, is unsupported and raises no error up front: `YearExpression::numeric()`, `YearExpression::text()` and `LikeOperator::applyContainsOnDate()` fall back to syntax that fails at the database at query time.

=== spatie/laravel-activitylog/core rules ===

## Activitylog

- `spatie/laravel-activitylog` logs model events and manual activities to a database table, with subjects, causers, tracked attribute changes, and custom properties.
- Always activate the `activitylog-development` skill when working with activity logging or any code that uses the `LogsActivity`, `CausesActivity`, or `HasActivity` traits, `LogOptions`, the `Activity` model, or the `activity()` helper.
- If the skill is unavailable, read `vendor/spatie/laravel-activitylog/resources/boost/skills/activitylog-development/SKILL.md` before making changes.

=== spatie/laravel-medialibrary/core rules ===

## Media Library

- `spatie/laravel-medialibrary` associates files with Eloquent models, with support for collections, conversions, and responsive images.
- Always activate the `medialibrary-development` skill when working with media uploads, conversions, collections, responsive images, or any code that uses the `HasMedia` interface or `InteractsWithMedia` trait.

</laravel-boost-guidelines>
