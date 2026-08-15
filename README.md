pop-audit
=========

[![Build Status](https://github.com/popphp/pop-audit/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-audit/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-audit)](http://cc.popphp.org/pop-audit/)

[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
  - [Storing Changes](#storing-changes)
  - [Metadata](#metadata)
  - [Retrieving Changes](#retrieving-changes)
* [Diffing](#diffing)
* [Using Files](#using-files)
* [Using a Database](#using-a-database)
* [Using HTTP](#using-http)
* [Auditable Models](#auditable-models)

Overview
--------
Pop Audit is an auditing component of the Pop PHP Framework. It allows you to track and recall changes
in model states, which is useful for building out a system for rolling back mistakes or recovering lost
data. It provides different adapters to achieve this, all of which are interchangeable using the same
interface:

- File
- Database (Table)
- HTTP

`pop-audit` is a component of the [Pop PHP Framework](http://www.popphp.org/).

[Top](#pop-audit)

Install
-------

Install `pop-audit` using Composer.

    composer require popphp/pop-audit

Or, require it in your composer.json file

    "require": {
        "popphp/pop-audit" : "^2.0.3"
    }

[Top](#pop-audit)

Quickstart
----------

With the audit component, you can store model state data changes and recall them at a later date.

### Storing Changes

To store the model data, there are two required data points - the model name and model ID.
After that, optional data points such as user data or the domain can be stored. First we create
the auditor and set the data points:

```php
use Pop\Audit\Auditor;
use Pop\Audit\Adapter\File;

$auditor = new Auditor(new File(__DIR__ . '/tmp')); // Folder passed to the File adapter
$auditor->setModel('MyApp\Model\User', 1001);       // Model name and model ID (required)
$auditor->setUser('testuser', 101);                 // Username/ID that made the change (optional)
$auditor->setDomain('users.localhost');             // Domain (optional)
```

Then, we look at the changed model data. In this example, the model state contains 4 data points,
2 of which have changed: `username` and `phone`. Once passed to the auditor's `send()` method,
it will "diff" the two states and record the differences, as well as snapshot of the final
changed state:

```php
$old = [
    'id'       => 1,
    'username' => 'admin',
    'email'    => 'test@test.com',
    'phone'    => '504-555-5555'
];

$new = [
    'id'       => 1,
    'username' => 'admin2',
    'email'    => 'test@test.com',
    'phone'    => '504-555-6666'
];

$auditor->send($old, $new);
```

If `$old` and `$new` don't actually differ, `send()` returns `false` and nothing is stored — a no-op diff
results in a no-op send, rather than an empty audit record.

[Top](#pop-audit)

### Metadata

Arbitrary key/value context can be attached to a record beyond the built-in user/domain/route/method fields,
using `setMetadata()` (replaces all metadata) or `addMetadata()` (adds a single key):

```php
$auditor->setMetadata(['request_id' => 'abc123']);
$auditor->addMetadata('ip_address', '127.0.0.1');
```

Metadata is stored alongside the rest of the record and appears under the `metadata` key in the state
structure shown below.

[Top](#pop-audit)

### Retrieving Changes

Interacting with the auditor's adapter, the previously stored model states can be retrieved. Every adapter
implements the same six read methods, but their exact parameters are adapter-specific (e.g. `File`'s
`getStates()` takes a sort direction/limit/offset, while `Table`'s takes `pop-db` `findBy()`-style
columns/options) — see each adapter's own section below for its full signatures.

**List all stored states**

```php
var_dump($auditor->adapter()->getStates());
```

**List stored states for a particular model and model ID**

```php
var_dump($auditor->adapter()->getStateByModel('MyApp\Model\User', 1001));
```

Other methods are available to help refine your search for previous states:

- `getStateById()`
- `getStateByTimestamp()`
- `getStateByDate()`
- `getSnapshot()`

**Get a before/after snapshot for a single record**

This is the method most directly useful for rolling back a change or recovering lost data - it returns just
the `old` (pre-change) or `new` (post-change) half of a single stored record, by ID:

```php
$before = $auditor->adapter()->getSnapshot($id);        // the 'old' values
$after  = $auditor->adapter()->getSnapshot($id, true);   // the 'new' values
```

The state structure will look like:

```text
Array
(
    [user_id] => 101
    [username] => testuser
    [domain] => users.localhost
    [route] => 
    [method] => 
    [model] => MyApp\Model\User
    [model_id] => 1001
    [action] => updated
    [old] => Array
        (
            [username] => admin
            [phone] => 504-555-5555
        )

    [new] => Array
        (
            [username] => admin2
            [phone] => 504-555-6666
        )

    [state] => Array
        (
            [id] => 1
            [username] => admin2
            [email] => test@test.com
            [phone] => 504-555-6666
        )

    [metadata] => Array
        (
        )

    [timestamp] => 2023-10-29 16:05:53
)
```

`action` is set automatically based on which of `old`/`new` were empty when the change was recorded: an empty
`old` means `created`, an empty `new` means `deleted`, and anything else means `updated`.

The storing of the full state is on by default, can be turned off by passing a `false` boolean
to the `send()` method:

```php
$auditor->send($old, $new, false);
```

[Top](#pop-audit)

Diffing
-------

In the above examples, the `pop-audit` component automatically handles "diffing" for you. If you have
another resource that evaluates the differences, you can pass those directly into the auditor as well:

```php
use Pop\Audit\Auditor;
use Pop\Audit\Adapter\File;

$old   = ['username' => 'admin'];
$new   = ['username' => 'admin2'];
$state = [
    'id'       => 1,
    'username' => 'admin2',
    'email'    => 'test@test.com',
    'phone'    => '504-555-5555'
];

$auditor = new Auditor(new File(__DIR__ . '/tmp'));
$auditor->setModel('MyApp\Model\User', 1001);
$auditor->setUser('testuser', 101);
$auditor->setDomain('users.localhost');
$auditor->setDiff($old, $new);
$auditor->setStateData($state); // optional if you want to record the final changed state
$auditor->send();
```

An example of this is the `Pop\Db\Record` class from the `pop-db` component. It automatically tracks the
"dirty" values that have been changed while working with a record object. You can then used the `getDirty()`
method of the `Pop\Db\Record` class to return an array with the keys `old` and `new` and pass them off to
the auditor.

[Top](#pop-audit)

Using Files
-----------

With the file adapter, you set the folder you want to save the audit record to,
and save the model state changes like this:

```php
use Pop\Audit\Auditor;
use Pop\Audit\Adapter\File;

$auditor = new Auditor(new File(__DIR__ . '/tmp')); // Folder passed to the File adapter
$auditor->setModel('MyApp\Model\User', 1001);       // Model name and model ID (required)
$auditor->setUser('testuser', 101);                 // Username/ID that made the change (optional)
$auditor->setDomain('users.localhost');             // Domain (optional)

$old = [
    'id'       => 1,
    'username' => 'admin',
    'email'    => 'test@test.com',
    'phone'    => '504-555-5555'
];

$new = [
    'id'       => 1,
    'username' => 'admin2',
    'email'    => 'test@test.com',
    'phone'    => '504-555-6666'
];

$logFile = $auditor->send($old, $new);
```

In this case, the variable `$logFile` would contain the name of the audit log file, for example
`pop-audit-aed112d5d6de258762c03aa597a47f9b-653ec767ee591-1698613095.log` in case it needs to be
referenced again. That file will contain the JSON-encoded data that tracks the difference between
the model states, as well as a snapshot of the full state (if provided):

```json
{
    "user_id": 101,
    "username": "testuser",
    "domain": "users.localhost",
    "model": "MyApp\\Model\\User",
    "model_id": 1001,
    "action": "updated",
    "old": {
        "username": "admin"
    },
    "new": {
        "username": "admin2"
    },
    "state": {
        "id": 1,
        "username": "admin2",
        "email": "test@test.com",
        "phone": "504-555-6666"
    },
    "timestamp": "2023-08-23 16:56:36"
}
```

**Retrieving from files**

`File`'s read methods scan the configured folder directly - there's no index, so this adapter is best suited
to lower-volume/dev use, not a high-traffic production audit trail:

- `getStates(string $sort = 'DESC', ?int $limit = null, ?int $offset = null)`
- `getStateById(int|string $id)`
- `getStateByModel(string $model, int|string|null $modelId = null)`
- `getStateByTimestamp(int $from, ?int $backTo = null)` - pass unix timestamps (e.g. `time()`); they're
  compared directly against each file's modified time
- `getStateByDate(string $from, ?string $backTo = null)` - `'Y-m-d'` or `'Y-m-d H:i:s'` strings
- `getSnapshot(int|string $id, bool $post = false)`

[Top](#pop-audit)

Using a Database
----------------

Using a database connection requires the use of the `pop-db` component and a database table class
that extends the `Pop\Db\Record` class. Consider a database and table class set up in your
application like this:

```php
class AuditLog extends \Pop\Db\Record {}

AuditLog::setDb(\Pop\Db\Db::mysqlConnect([
    'database' => 'MY_DATABASE',
    'username' => 'DB_USER',
    'password' => 'DB_PASS'
]));
```

Then you can use the table adapter like this:

```php
use Pop\Audit\Auditor;
use Pop\Audit\Adapter\Table;

$old = [
    "id"       => 1,
    'username' => 'admin',
    'email'    => 'test@test.com'
];

$new = [
    "id"       => 1,
    'username' => 'admin2',
    'email'    => 'test@test.com'
];

$auditor = new Auditor(new Table('AuditLog'));
$auditor->setModel('MyApp\Model\User', 1001);
$auditor->setUser('testuser', 101);
$auditor->setDomain('users.localhost');
$row = $auditor->send($old, $new);
```

If needed, the variable `$row` contains the newly created record in the audit table.

If the configured table doesn't exist yet, the `Table` adapter creates it automatically the first time it's
used - which means the database user needs `CREATE TABLE` privileges. If that's not desirable (e.g. in
production), reference schema files for MySQL, PostgreSQL, and SQLite are included at
`vendor/popphp/pop-audit/src/Adapter/Sql/` so you can create the table yourself ahead of time instead (e.g.
via a migration).

**Retrieving from the database**

`Table`'s read methods proxy to `pop-db`'s `findAll()`/`findBy()`/`findById()`, so `$columns`/`$options`
follow standard `pop-db` `findBy()` conventions:

- `getStates(?array $columns = null, ?array $options = null)`
- `getStateById(int|string $id)`
- `getStateByModel(string $model, int|string|null $modelId = null, array $columns = [])`
- `getStateByTimestamp(int $from, ?int $backTo = null, array $columns = [])` - pass unix timestamps
  (e.g. `time()`)
- `getStateByDate(string $from, ?string $backTo = null, array $columns = [])`
- `getSnapshot(int|string $id, bool $post = false)`

[Top](#pop-audit)

Using HTTP
----------

You can also send your audit data to an HTTP service like this:

```php
use Pop\Http\Client;
use Pop\Http\Auth;
use Pop\Audit\Auditor;
use Pop\Audit\Adapter\Http;

$old = [
    "id"       => 1,
    'username' => 'admin',
    'email'    => 'test@test.com'
];

$new = [
    "id"       => 1,
    'username' => 'admin2',
    'email'    => 'test@test.com'
];

$sendClient = new Client(
    'http://audit.localhost',
    Auth::createBearer('AUTH_TOKEN'),
    ['method' => 'POST']
);

$auditor = new Auditor(new Http($sendClient));
$auditor->setModel('MyApp\Model\User', 1001);
$auditor->setUser('testuser', 101);
$auditor->setDomain('users.localhost');
$response = $auditor->send($old, $new);
```

If needed, the variable `$response` contains the HTTP response returned by the HTTP request.

`Http` takes an optional second client, used only for retrieving states, separately from the one used to send
them - a write-only audit sink doesn't need read credentials/URL configured, and vice versa:

```php
$fetchClient = new Client(
    'http://audit.localhost',
    Auth::createBearer('AUTH_TOKEN'),
    ['method' => 'GET']
);

$auditor = new Auditor(new Http($sendClient, $fetchClient));
```

Calling any of the read methods below without a fetch client configured will fail - only pass a single client
if you only intend to send audit data through this adapter, never retrieve it.

**Retrieving over HTTP**

- `getStates(array $fields = [])`
- `getStateById(int|string $id, bool $asQuery = false)`
- `getStateByModel(string $model, int|string|null $modelId = null)`
- `getStateByTimestamp(int $from, ?int $backTo = null)` - pass unix timestamps (e.g. `time()`)
- `getStateByDate(string $from, ?string $backTo = null)`
- `getSnapshot(int|string $id, bool $post = false)`

These build a request in a shape your own audit service needs to understand (e.g. a `filter` array of loose
string expressions like `'model = MyApp\Model\User'`) - `pop-audit` only defines the client-side contract, not
a wire protocol, so the receiving service has to implement matching semantics.

[Top](#pop-audit)

Auditable Models
-----------------

If you'd rather a model hold its own `Auditor` instance directly, rather than calling `Auditor` from elsewhere
in your application, `Pop\Audit\Model\AuditableModel` (an abstract `Pop\Model\AbstractDataModel` subclass) and
its `Pop\Audit\Model\AuditableInterface` provide the minimal wiring for that:

```php
use Pop\Audit\Auditor;
use Pop\Audit\Adapter\File;
use Pop\Audit\Model\AuditableModel;

class User extends AuditableModel
{
    // ...
}

$user = new User();
$user->setAuditor(new Auditor(new File(__DIR__ . '/tmp')));

if ($user->isAuditable()) {
    $user->getAuditor()->setModel('MyApp\Model\User', 1001);
    $user->getAuditor()->send($old, $new); // $old/$new as shown under Storing Changes
}
```

This is intentionally minimal - it only holds the `Auditor` reference (`setAuditor()`, `getAuditor()`,
`hasAuditor()`, `isAuditable()`); it does not automatically call the auditor on any model lifecycle event.
Wiring *when* auditing fires (e.g. from a save/update hook) is left to your application.

[Top](#pop-audit)
