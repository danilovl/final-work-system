# Project Guidelines and Agent Instructions

## Стек

- **PHP** 8.5, **Symfony** 8.1, **Doctrine ORM** 3 (MariaDB 10.9, `server_version: mariadb-10.9.7`), Twig, Vite (`pentatrion/vite-bundle`).
- Основные бандлы: EasyAdmin, KnpPaginator, API Platform, NelmioApiDoc, Overblog GraphQL, FOSElastica (Elasticsearch), Gedmo Doctrine Extensions, Messenger (AMQP), Mercure, `doctrine/orm-plugin-second-level-cache`, а также собственные `danilovl/*` бандлы (`hashids`, `doctrine-entity-dto`, `object-dto-mapper`, `render-service-twig-extension`, `select-autocompleter`, `async`, `cache-response`, `parameter`, `permission-middleware`, `translator`, `open-telemetry`).
- Окружение — `docker-compose.yml`: `php` (php-fpm, пользователь `www-data` uid 1000), `nginx` (`localhost:7100`), `mysql` (MariaDB), `redis`, `rabbitmq`, `elasticsearch`, `kibana`, `minio` (S3), `mercure`, `node` (сборка и Playwright), `supervisor` (воркеры Messenger), `otel-collector`, `apm-server`.
- Автозагрузка PSR-4: `App\` → `src/`, `App\Tests\` → `tests/`.

## Архитектура проекта

Проект построен по DDD, код в `src/` разделён на три слоя:

- **Domain/**: бизнес-логика по доменам (`Work`, `Task`, `Conversation`, `ConversationMessage`, `Event`, `Media`, `User`, `SystemEvent` и др.).
  - `Controller/`: `readonly`-контроллеры только принимают `Request` и сущности и делегируют работу в `Http/XxxHandle` через `->__invoke(...)`. API — `Controller/Api/`, AJAX — `Controller/Ajax/`.
  - `Http/`: invokable-классы обработки запроса (`readonly class XxxHandle`), API — `Http/Api/`, AJAX — `Http/Ajax/`.
  - `Bus/Command/<Name>/` и `Bus/Query/<Name>/`: команды и запросы с обработчиками (`XxxCommand` + `XxxHandler`, `GetXxxQuery` + `GetXxxQueryHandler` + `GetXxxQueryResult`); шины `command.bus` и `query.bus`.
  - `Entity/`: Doctrine-сущности на атрибутах; общие поля — трейты из `Application/Traits/Entity` (`IdTrait`, `CreateUpdateAbleTrait` и др.).
  - `Facade/`: высокоуровневый доступ к данным домена; обработчики и сервисы работают с фасадами, а не с репозиториями напрямую.
  - `Repository/`: `XxxRepository` (наследует `ServiceEntityRepository`) собирает запросы из методов `XxxQueryBuilder` (наследует `Infrastructure/Persistence/Doctrine/Repository/BaseQueryBuilder`); алиас корневой сущности — имя сущности в snake_case. Поиск — `Repository/Elastica/`.
  - `Security/Voter/`: проверки прав (`VoterSupportConstant`, `AuthorizationCheckerService::denyAccessUnlessGranted()`).
  - `DTO/`, `DTO/Api/`, `Model/` (модели форм), `Form/`, `Factory/`, `Service/`, `Helper/`, `Constant/`, `EventDispatcher/`, `EventSubscriber/`, `EventListener/`, `Twig/Runtime/` (`#[AsTwigFunction]`, `#[AsTwigFilter]`), `Admin/` (EasyAdmin), `Command/`, `Autocompleter/`, `GraphQL/`.
- **Application/**: общие константы (`VoterSupportConstant` и др.), интерфейсы шин, атрибуты (`EntityRelationValidatorAttribute`), трейты, исключения, хелперы, DTO, миграции (`src/Application/Migrations`).
- **Infrastructure/**: сервисы (`EntityManagerService`, `TwigRenderService`, `RequestService`, `PaginatorService`, `S3ClientService`, `AuthorizationCheckerService` и др.), персистентность (`Persistence/Doctrine`: DBAL-типы, `BaseQueryBuilder`), веб-слой (`Web/`: формы, Twig-расширения), слушатели (`Event/EventListener`), консоль, Messenger, GraphQL, EasyAdmin.

## Конфигурация

- `config/services.yaml`: autowire и autoconfigure для `App\Infrastructure\`, `App\Application\`, `App\Domain\` с исключениями (DTO, Entity, Constant, Model, Helper, Migrations и т. п.).
- `config/project/`: параметры (`parameters.yaml`), сервисы (`services/*.yaml`, Doctrine-слушатели — `services/entity_listener.yaml`), меню, виджеты.
- **Маршруты** описываются в YAML: `config/project/routing.yaml` + `config/project/routing/<domain>.yaml`; API — `routing/api`, `api_key.yaml`, `api_platform`, `api_public`.
- **ID в URL** кодируются Hashids: в шаблонах `{{ entity.id | hashids_encode }}`, в контроллерах `#[HashidsRequestConverterAttribute]` (`danilovl/hashids-bundle`) + `#[MapEntity]`. Текущий пользователь — `Domain/User/Service/UserService`. Принадлежность сущностей из URL друг другу проверяется `#[EntityRelationValidatorAttribute]` (при нескольких подходящих свойствах указывать `property`).
- **Doctrine-маппинг**: каждый домен с сущностями явно зарегистрирован в `config/packages/doctrine.yaml` (`orm.mappings`). Новый домен — новая запись.
- Вторичный кэш Doctrine и сессии — Redis (`doctrine.redis_adapter`, номер базы — `%env(REDIS_CACHE_DB)%`).
- Индексы Elasticsearch — `config/packages/fos_elastica.yaml`, имя индекса с префиксом `%env(ELASTICSEARCH_INDEX_PREFIX)%`.

## Окружения и базы данных

- **dev**: база `final_work_system`, индексы без префикса, Redis 13 (`.env` + `.env.local`).
- **PHPUnit** (`APP_ENV=test`): база `final_work_system_test`, индексы `test_*`, Redis 14 — значения задаются в локальном `.env.test.local` (в `.gitignore`).
- **E2E** (Playwright): запросы с cookie `e2e_test=1` (задаётся в `playwright.config.ts`; заголовок `X-E2E-TEST: 1` тоже принимается) из частной сети при `E2E_TEST_ENABLE=1` (контейнер `php`) переключают приложение на `.env.test` (`public/index.php`); каталог кэша — `var/cache/e2e_*` (`Kernel::getCacheDir()`).
- **CI** (GitHub Actions): одна обычная база `final_work_system` с `docker/mysql/data/dump.sql`, `.env.test.local` нет — поэтому в `.env.test` указаны общие база, индексы и Redis. Не менять это без учёта CI.
- **Работа с данными**: любые эксперименты и проверки с записью — только на `final_work_system_test` или на временной копии. dev-базу `final_work_system` не изменять без явного подтверждения пользователя.
- Скрипты вне PHPUnit, которые сами вызывают `Dotenv::bootEnv()`, запускать с `APP_ENV=test`, иначе подключится dev-база.
- **Деструктивные операции** (`DROP`, `TRUNCATE`, `DELETE`/`UPDATE` без `WHERE`, `doctrine:schema:drop`, переиндексация dev-индексов) — только после явного подтверждения пользователя.
- **Схема**: при изменении маппинга создавать миграцию в `src/Application/Migrations`; `doctrine:schema:validate` должен быть без ошибок.

## Данные и запросы

- **Elasticsearch и БД**: списки работ и поиск идут из Elasticsearch, детальные страницы — из БД. Удаления, выполняемые каскадом в БД (`onDelete: 'CASCADE'`), FOSElastica не видит — документы зависимых записей удаляет `ElasticaCascadeRemoveListener` (собирает id в `preRemove` через DBAL, удаляет в `postFlush`). Новые каскады для индексируемых сущностей добавлять туда же.
- **Doctrine-слушатели**: в `preRemove`/`onFlush` не загружать связанные сущности (коллекции, прокси) — только скалярные запросы через DBAL, иначе `flush` падает с `ORMInvalidArgumentException`.
- **N+1**: данные для списков загружаются пакетно (`IN (:ids)`) в обработчике или фасаде, а не в цикле по строкам; статусы прочтения диалогов — `ConversationStatusService` (кэш на время запроса, `ResetInterface`).
- **Fetch-join коллекций**: не сочетать `addSelect` коллекции с `setMaxResults`/`getOneOrNullResult` и не фильтровать по алиасу выбранной коллекции — коллекция гидрируется урезанной.
- **Сортировка**: к каждой сортировке по неуникальному полю добавлять `addOrderBy('<alias>.id', $order)`, иначе пагинация нестабильна.
- **Обход больших таблиц** с изменением данных — по ключу (`id > :lastId ORDER BY id`), не через `OFFSET`.
- **Кастомная гидрация**: `setHydrationMode(Entity::class)` (`danilovl/doctrine-entity-dto`) создаёт неуправляемые объекты с неполными связями — для логики, которой нужны связи, брать сущность через `EntityManagerService::getReference()`.
- **Оптимизация запросов**: шаблоны и выходные данные не меняются — результат до и после сравнивается 1:1.

## Стандарты PHP и стиль кода

- Каждый файл начинается с `<?php declare(strict_types=1);` и шапки лицензии проекта.
- Строгая типизация свойств, параметров, возвращаемых значений; типизированные константы (`private const int LIMIT = 500;`).
- `readonly class` для сервисов, фасадов, контроллеров и обработчиков; если классу нужно изменяемое состояние, `readonly` ставится на свойства конструктора.
- **Импорты**: групповые из одного пространства имён (`use Symfony\Component\HttpFoundation\{Request, Response};`), глобальные классы — через `use` (`use DateTimeImmutable;`, `use SortDirection;`).
- **Даты**: только `DateTimeImmutable` и `Types::*_IMMUTABLE`; в формах `'input' => 'datetime_immutable'`.
- `new ArrayCollection`, `new DateTimeImmutable` без пустых скобок.
- **Именованные аргументы** — для вызовов с несколькими параметрами; каждый аргумент на своей строке.
- Вместо циклов поиска — `array_find()`, `array_any()`, `array_filter()`; промежуточные результаты — в отдельных переменных.
- **`sprintf` как аргумент** запрещён: `$message = sprintf(...); throw new LogicException($message);`, `$cacheKey = sprintf(...); $this->cache->getItem($cacheKey);`.
- **Цепочки вызовов в аргументах** запрещены: результат сначала в переменную. Один вызов допустим.
- **Вызовы с аргументами в условиях** `if`/`foreach`/`match` запрещены — результат сначала в переменную.
- `match` всегда с веткой `default` (PHPStan).
- **Сущности не используются как view-model**: вычисляемые для отображения значения (собеседник, прочитанность) не хранятся в сущностях, а считаются в PHP (сервис, хелпер, Twig-runtime).
- **Комментарии к коду** не добавляются.
- **Окончания строк**: в репозитории файлы с LF, CRLF и смешанным шаблоном (CRLF, кроме строк `namespace`, пустой строки после него и последней `}`). При правке сохранять окончания строк как в `HEAD`; проверка — `git diff --numstat` совпадает с `git diff --numstat --ignore-cr-at-eol`.
- **Качество**: PHPStan (`composer phpstan`) без новых записей в baseline, php-cs-fixer (`composer cs-fixer-check`).

## Шаблоны

- Twig: `templates/domain/<domain_snake_case>/`, общие — `templates/application/`, `templates/other/`.
- Фильтры с пробелами вокруг `|`: `{{ value | trans }}`.
- Длинные хэши атрибутов (`form_widget`, `form_start`, `path(...)`) — по одному ключу на строку.
- Данные для шаблона готовятся в PHP: никаких `| default(...)`, вычислений и обработки `null` в шаблоне. HTML пользователя выводится через `sanitize_html('app.rich_text_sanitizer')` (null-безопасен, `Infrastructure/Web/Twig/Extension/HtmlSanitizerExtension`).
- Ссылки с параметрами строятся только через `path(...)`, без склеивания строк запроса.
- Действия, меняющие данные, — только POST (формы, AJAX), не ссылки GET.

## Тесты

- `tests/Unit` — без ядра и БД (`TestCase`, `createStub()`); `tests/Integration`, `tests/Functional` — с ядром; `tests/Mock` — вспомогательные классы; `tests/EndToEnd/Playwright` — E2E.
- Структура тестов повторяет `src/`: `tests/Unit/Domain/<Domain>/Repository/XxxQueryBuilderTest.php`.
- Data provider — `Generator` с именованными ключами в `yield`, имя — `provide<TestName>Cases`.
- Ассерты через `$this->`; проверяемое значение сначала в переменную, без вызовов внутри ассерта.
- Моки `readonly`-классов PHPUnit создаёт сам; `final` с классов `src/` снимает на лету `dg/bypass-finals` (расширение в `phpunit.xml.dist`), файлы на диске не меняются. Внутри тестов чтение файлов `src/` через PHP возвращает код уже без `final`.
- **Playwright**: сценарии регистрируются в `tests/EndToEnd/Playwright/tests.ts` (порядок важен: создание → редактирование → проверки данных → удаление), общие страницы — `page/`, данные — `fixtures/`. Проверки чтения данных (`test/data/`) не завязываются на конкретное содержимое базы: целостность пагинации, совпадение списка и детали, «записали — прочитали». Запуск — `docker compose exec node npx playwright test`.
- Тест, который ловит найденный баг, не ослабляется под текущее поведение — исправляется код.

## Команды

- `composer cs-fixer` / `composer cs-fixer-check`, `composer phpstan`.
- `composer test-unit`, `composer test-integration`, `composer test-all`, `composer test-web`; весь набор — `vendor/bin/phpunit --testsuite tests`.
- `npm run playwright:test` (в контейнере `node`).
- Переиндексация: `bin/console fos:elastica:populate` (dev), `APP_ENV=test bin/console fos:elastica:populate -e test` (тестовые индексы).
- Всё запускается в контейнере: `docker compose exec php ...`, `docker compose exec node ...`.

## Коммиты

- Сообщение — одна короткая строка `<Area> - <what>` (до ~70 символов), без перечисления изменений и без строк `Co-Authored-By`.
- В коммит входят все изменённые файлы, включая lock-файлы.
- Коммиты делаются только по просьбе пользователя.

## Принципы внесения изменений

- **Минимализм**: точечные изменения без лишних абстракций; новый код повторяет устройство соседних доменов (Controller → Handle → Facade → Repository/QueryBuilder).
- Каждое изменение проверяется: PHPUnit, PHPStan, php-cs-fixer, при изменении страниц — Playwright; для изменений чтения данных — сравнение ответов до и после.
- Тесты создаются только по запросу пользователя.
- Новые исключения в `phpstan-baseline.neon` — только с явного подтверждения пользователя.
