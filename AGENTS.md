# Project Guidelines and Agent Instructions

## Stack

- **PHP** 8.5, **Symfony** 8.1, **Doctrine ORM** 3 (MariaDB 10.9, `server_version: mariadb-10.9.7`), Twig, Vite (`pentatrion/vite-bundle`).
- Main bundles: EasyAdmin, KnpPaginator, API Platform, NelmioApiDoc, Overblog GraphQL, FOSElastica (Elasticsearch), Gedmo Doctrine Extensions, Messenger (AMQP), Mercure, `doctrine/orm-plugin-second-level-cache`, plus our own `danilovl/*` bundles (`hashids`, `doctrine-entity-dto`, `object-dto-mapper`, `render-service-twig-extension`, `select-autocompleter`, `async`, `cache-response`, `parameter`, `permission-middleware`, `translator`, `open-telemetry`).
- Environment — `docker-compose.yml`: `php` (php-fpm, user `www-data` uid 1000), `nginx` (`localhost:7100`), `mysql` (MariaDB), `redis`, `rabbitmq`, `elasticsearch`, `kibana`, `minio` (S3), `mercure`, `node` (build and Playwright), `supervisor` (Messenger workers), `otel-collector`, `apm-server`.
- PSR-4 autoloading: `App\` → `src/`, `App\Tests\` → `tests/`.

## Project Architecture

The project follows DDD; code in `src/` is split into three layers:

- **Domain/**: business logic by domain (`Work`, `Task`, `Conversation`, `ConversationMessage`, `Event`, `Media`, `User`, `SystemEvent`, etc.).
  - `Controller/`: `readonly` controllers only accept `Request` and entities and delegate the work to `Http/XxxHandle` via `->__invoke(...)`. API — `Controller/Api/`, AJAX — `Controller/Ajax/`.
  - `Http/`: invokable request handling classes (`readonly class XxxHandle`), API — `Http/Api/`, AJAX — `Http/Ajax/`.
  - `Bus/Command/<Name>/` and `Bus/Query/<Name>/`: commands and queries with handlers (`XxxCommand` + `XxxHandler`, `GetXxxQuery` + `GetXxxQueryHandler` + `GetXxxQueryResult`); buses `command.bus` and `query.bus`.
  - `Entity/`: Doctrine entities mapped with attributes; shared fields come from traits in `Application/Traits/Entity` (`IdTrait`, `CreateUpdateAbleTrait`, etc.).
  - `Facade/`: high-level access to domain data; handlers and services work with facades, not with repositories directly.
  - `Repository/`: `XxxRepository` (extends `ServiceEntityRepository`) builds queries from `XxxQueryBuilder` methods (extends `Infrastructure/Persistence/Doctrine/Repository/BaseQueryBuilder`); the root entity alias is the entity name in snake_case. Search — `Repository/Elastica/`.
  - `Security/Voter/`: permission checks (`VoterSupportConstant`, `AuthorizationCheckerService::denyAccessUnlessGranted()`).
  - `DTO/`, `DTO/Api/`, `Model/` (form models), `Form/`, `Factory/`, `Service/`, `Helper/`, `Constant/`, `EventDispatcher/`, `EventSubscriber/`, `EventListener/`, `Twig/Runtime/` (`#[AsTwigFunction]`, `#[AsTwigFilter]`), `Admin/` (EasyAdmin), `Command/`, `Autocompleter/`, `GraphQL/`.
- **Application/**: shared constants (`VoterSupportConstant`, etc.), bus interfaces, attributes (`EntityRelationValidatorAttribute`), traits, exceptions, helpers, DTOs, migrations (`src/Application/Migrations`).
- **Infrastructure/**: services (`EntityManagerService`, `TwigRenderService`, `RequestService`, `PaginatorService`, `S3ClientService`, `AuthorizationCheckerService`, etc.), persistence (`Persistence/Doctrine`: DBAL types, `BaseQueryBuilder`), web layer (`Web/`: forms, Twig extensions), listeners (`Event/EventListener`), console, Messenger, GraphQL.
- **Dependency direction**: `Domain` → `Application` → `Infrastructure`. `Infrastructure` does not reference `Application` or `Domain`, `Application` does not reference `Domain`; domain logic (listeners, compiler passes, admin) lives in its own domain. Enforced by the PHPStan rule `tests/PHPStan/Rule/LayerDependencyRule.php`; current violations are in `phpstan-layer-baseline.neon`, do not add new ones there.
- **Domain independence**: a domain does not use facades, services, repositories or factories of another domain — additional data is read via `query.bus`, writes go through `command.bus`; data loaded in SQL via JOIN stays in the query. Exceptions — `UserService`, DTO factories and `EventScheduleTemplateFactory` (`ALLOWED_CLASSES`); entities, GenericEvent and static helpers of other domains are allowed. Enforced by the PHPStan rule `tests/PHPStan/Rule/DomainDependencyRule.php`.

## Configuration

- `config/services.yaml`: autowire and autoconfigure for `App\Infrastructure\`, `App\Application\`, `App\Domain\` with exclusions (DTO, Entity, Constant, Model, Helper, Migrations, etc.).
- `config/project/`: parameters (`parameters.yaml`), services (`services/*.yaml`, Doctrine listeners — `services/entity_listener.yaml`), menu, widgets.
- **Routes** are defined in YAML: `config/project/routing.yaml` + `config/project/routing/<domain>.yaml`; API — `routing/api`, `api_key.yaml`, `api_platform`, `api_public`.
- **IDs in URLs** are encoded with Hashids: in templates `{{ entity.id | hashids_encode }}`, in controllers `#[HashidsRequestConverterAttribute]` (`danilovl/hashids-bundle`) + `#[MapEntity]`. Current user — `Domain/User/Service/UserService`. Ownership between entities from the URL is checked with `#[EntityRelationValidatorAttribute]` (specify `property` when several properties match).
- **Doctrine mapping**: every domain with entities is registered explicitly in `config/packages/doctrine.yaml` (`orm.mappings`). New domain — new entry.
- Doctrine second-level cache and sessions — Redis (`doctrine.redis_adapter`, database number — `%env(REDIS_CACHE_DB)%`).
- Elasticsearch indexes — `config/packages/fos_elastica.yaml`, index name prefixed with `%env(ELASTICSEARCH_INDEX_PREFIX)%`; aliases are used (`use_alias: true`), `populate` builds a new index and switches the alias. If an environment still has a plain index named like the alias, delete it once and run `populate`.

## Environments and Databases

- **dev**: database `final_work_system`, indexes without prefix, Redis 13 (`.env` + `.env.local`).
- **PHPUnit** (`APP_ENV=test`): database `final_work_system_test`, indexes `test_*`, Redis 14 — values are set in the local `.env.test.local` (in `.gitignore`).
- **E2E** (Playwright): requests with the cookie `e2e_test=1` (set in `playwright.config.ts`; the header `X-E2E-TEST: 1` is accepted too) from a private network with `E2E_TEST_ENABLE=1` (container `php`) switch the application to `.env.test` (`public/index.php`); cache directory — `var/cache/e2e_*` (`Kernel::getCacheDir()`).
- **CI** (GitHub Actions): a single regular database `final_work_system` with `docker/mysql/data/dump.sql`, there is no `.env.test.local` — that is why `.env.test` specifies the shared database, indexes and Redis. Do not change this without considering CI.
- **Working with data**: any experiments and checks that write data — only on `final_work_system_test` or on a temporary copy. Do not modify the dev database `final_work_system` without explicit user confirmation.
- Scripts outside PHPUnit that call `Dotenv::bootEnv()` themselves must be run with `APP_ENV=test`, otherwise they connect to the dev database.
- **Destructive operations** (`DROP`, `TRUNCATE`, `DELETE`/`UPDATE` without `WHERE`, `doctrine:schema:drop`, reindexing dev indexes) — only after explicit user confirmation.
- **Schema**: when the mapping changes, create a migration in `src/Application/Migrations`; `doctrine:schema:validate` must pass without errors.

## Data and Queries

- **Elasticsearch and DB**: work lists and search come from Elasticsearch, detail pages — from the DB. Deletions performed by DB cascade (`onDelete: 'CASCADE'`) are invisible to FOSElastica — documents of dependent records are removed by `Domain/Work/EventListener/Entity/ElasticaCascadeRemoveListener` (collects ids in `preRemove` via DBAL, deletes in `postFlush`). Add new cascades for indexed entities there as well.
- **Doctrine listeners**: in `preRemove`/`onFlush` do not load related entities (collections, proxies) — only scalar queries via DBAL, otherwise `flush` fails with `ORMInvalidArgumentException`.
- **N+1**: data for lists is loaded in batches (`IN (:ids)`) in the handler or facade, not in a loop over rows; conversation read statuses — `ConversationStatusService` (per-request cache, `ResetInterface`).
- **Fetch-joining collections**: do not combine `addSelect` of a collection with `setMaxResults`/`getOneOrNullResult` and do not filter by the alias of the selected collection — the collection gets hydrated truncated.
- **Sorting**: add `addOrderBy('<alias>.id', $order)` to every sort by a non-unique field, otherwise pagination is unstable.
- **Iterating large tables** while modifying data — by key (`id > :lastId ORDER BY id`), not via `OFFSET`.
- **Custom hydration**: `setHydrationMode(Entity::class)` (`danilovl/doctrine-entity-dto`) creates unmanaged objects with incomplete associations — for logic that needs associations, get the entity via `EntityManagerService::getReference()`.
- **Query optimization**: templates and output do not change — the result before and after is compared 1:1.

## PHP Standards and Code Style

- Every file starts with `<?php declare(strict_types=1);` and the project license header.
- Strict typing of properties, parameters, return values; typed constants (`private const int LIMIT = 500;`).
- `readonly class` for services, facades, controllers and handlers; if a class needs mutable state, `readonly` is put on the constructor properties.
- **Imports**: grouped from the same namespace (`use Symfony\Component\HttpFoundation\{Request, Response};`), global classes — via `use` (`use DateTimeImmutable;`, `use SortDirection;`).
- **Dates**: only `DateTimeImmutable` and `Types::*_IMMUTABLE`; in forms `'input' => 'datetime_immutable'`.
- `new ArrayCollection`, `new DateTimeImmutable` without empty parentheses.
- **Named arguments** — for calls with several parameters; each argument on its own line.
- Instead of search loops — `array_find()`, `array_any()`, `array_filter()`; intermediate results — in separate variables.
- **`sprintf` as an argument** is forbidden: `$message = sprintf(...); throw new LogicException($message);`, `$cacheKey = sprintf(...); $this->cache->getItem($cacheKey);`.
- **Call chains in arguments** are forbidden: put the result in a variable first. A single call is allowed.
- **Calls with arguments in `if`/`foreach`/`match` conditions** are forbidden — put the result in a variable first.
- `match` always has a `default` branch (PHPStan).
- **Entities are not used as view models**: values computed for display (conversation partner, read status) are not stored in entities but computed in PHP (service, helper, Twig runtime).
- **Code comments** are not added.
- **Line endings**: the repository has files with LF, CRLF and a mixed pattern (CRLF except the `namespace` line, the empty line after it and the last `}`). When editing, keep line endings as in `HEAD`; check — `git diff --numstat` matches `git diff --numstat --ignore-cr-at-eol`.
- **Quality**: PHPStan (`composer phpstan`) without new baseline entries, php-cs-fixer (`composer cs-fixer-check`).

## Templates

- Twig: `templates/domain/<domain_snake_case>/`, shared — `templates/application/`, `templates/other/`.
- Filters with spaces around `|`: `{{ value | trans }}`.
- Long attribute hashes (`form_widget`, `form_start`, `path(...)`) — one key per line.
- Template data is prepared in PHP: no `| default(...)`, computations or `null` handling in templates. User HTML is output via `sanitize_html('app.rich_text_sanitizer')` (null-safe, `Infrastructure/Web/Twig/Extension/HtmlSanitizerExtension`).
- Links with parameters are built only via `path(...)`, without concatenating query strings.
- Actions that modify data — only POST (forms, AJAX), not GET links.

## Tests

- `tests/Unit` — without kernel and DB (`TestCase`, `createStub()`); `tests/Integration`, `tests/Functional` — with kernel; `tests/Mock` — helper classes; `tests/EndToEnd/Playwright` — E2E.
- Test structure mirrors `src/`: `tests/Unit/Domain/<Domain>/Repository/XxxQueryBuilderTest.php`.
- Data provider — `Generator` with named keys in `yield`, name — `provide<TestName>Cases`.
- Assertions via `$this->`; the checked value goes into a variable first, no calls inside the assertion.
- PHPUnit creates mocks of `readonly` classes itself; `final` on `src/` classes is stripped on the fly by `dg/bypass-finals` (extension in `phpunit.xml.dist`), files on disk are not changed. Inside tests, reading `src/` files via PHP returns code already without `final`.
- **Playwright**: scenarios are registered in `tests/EndToEnd/Playwright/tests.ts` (order matters: create → edit → data checks → delete), shared pages — `page/`, data — `fixtures/`. Data read checks (`test/data/`) do not depend on specific database content: pagination integrity, list matches detail, "write — read back". Run — `docker compose exec node npx playwright test`.
- A test that catches a found bug is not weakened to match current behavior — the code is fixed.

## Commands

- `composer cs-fixer` / `composer cs-fixer-check`, `composer phpstan`.
- `composer test-unit`, `composer test-integration`, `composer test-all`, `composer test-web`; full suite — `vendor/bin/phpunit --testsuite tests`.
- `npm run playwright:test` (in the `node` container).
- Reindexing: `bin/console fos:elastica:populate` (dev), `APP_ENV=test bin/console fos:elastica:populate -e test` (test indexes).
- Everything runs in containers: `docker compose exec php ...`, `docker compose exec node ...`.
- **Resource control** (WSL: 10 GB memory, 5 cores): run PHPUnit, PHPStan, Playwright and builds in the background, and during the run check disk (`/proc/diskstats`), memory (`MemAvailable` in `/proc/meminfo`) and container CPU (`docker stats --no-stream`) every few seconds. Stop the run immediately if disk I/O stays above 100 MB/s for three consecutive samples (a normal PHPUnit run is up to ~20 MB/s), free memory drops below 1.5 GB, or the `supervisor` container goes above 150% CPU; then look for the cause instead of restarting. High CPU on `supervisor` usually means Messenger workers crash on startup (broken Symfony container) and restart in a loop — check `docker compose exec supervisor tail /var/log/supervisor/supervisord.log` and `bin/console lint:container`. Do not enable coverage (`bin/phpunit.sh` with pcov) unless needed — `vendor/bin/phpunit` is enough for verification.

## Commits

- Message — one short line `<Area> - <what>` (up to ~70 characters), without listing changes and without `Co-Authored-By` lines.
- A commit includes all changed files, including lock files.
- Commits are made only when the user asks.

## Change Principles

- **Minimalism**: targeted changes without extra abstractions; new code follows the structure of neighboring domains (Controller → Handle → Facade → Repository/QueryBuilder).
- Every change is verified: PHPUnit, PHPStan, php-cs-fixer, Playwright when pages change; for data read changes — compare responses before and after.
- Tests are created only at the user's request.
- New exceptions in `phpstan-baseline.neon` — only with explicit user confirmation.
- **Language**: documentation and instructions for agents (`AGENTS.md`, etc.) are written in English.
