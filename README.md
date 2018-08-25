pop-audit
=========

[![Build Status](https://travis-ci.org/popphp/pop-audit.svg?branch=master)](https://travis-ci.org/popphp/pop-audit)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-audit)](http://cc.popphp.org/pop-audit/)

OVERVIEW
--------
Pop Audit is an auditing component of the Pop PHP Framework.

`pop-audit` is a component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Install `pop-audit` using Composer.

    composer require popphp/pop-audit

BASIC USAGE
-----------

With the `pop-audit` component, you can monitor and record changes in a model's
state and send that information to either a file, a database or an HTTP service.

### Using files

With the file adapter, you set the folder you want to save the audit record to,
and save the model state changes like this:

```php
use Pop\Audit;

$old = [
    'username' => 'admin',
    'email'    => 'test@test.com'
];

$new = [
    'username' => 'admin2',
    'email'    => 'test@test.com'
];

$auditor = new Audit\Auditor(new Audit\Adapter\File('tmp'));  // Folder passed to the File adapter
$auditor->setModel('MyApp\Model\User', 1001);                 // Model name and model ID
$auditor->setUser('testuser', 101);                           // Username and user ID (optional)
$auditor->setDomain('users.localhost');                       // Domain (optional)
$logFile = $auditor->send($old, $new);
```

In this case, the variable `$logFile` would contain the name of the audit log file,
for example `pop-audit-1535060625.log` in case it needs to be referenced again.
That file will contain the JSON-encoded data that tracks the difference between the
model states:

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
    "timestamp": "2018-08-23 16:56:36"
}
```

Notice that only the difference is stored. In this case, only the `username` value changed.

### Using a database

Using a database connection requires the use of the `pop-db` component and a database table class
that extends the `Pop\Db\Record` class. Consider a database and table class set up in your
application like this:

```php
class AuditLog extends \Pop\Db\Record {}

AuditLog::setDb(\Pop\Db\Db::mysqlConnect([
    'database' => 'audit_db',
    'username' => 'audituser',
    'password' => '12audit34'
]));
```

Then you can use the table adapter like this:

```php
use Pop\Audit;

$old = [
    'username'   => 'admin',
    'email'      => 'test@test.com'
];

$new = [
    'username'   => 'admin2',
    'email'      => 'test@test.com'
];


$auditor = new Audit\Auditor(new Audit\Adapter\Table('AuditLog'));
$auditor->setModel('MyApp\Model\User', 1001);
$auditor->setUser('testuser', 101);
$auditor->setDomain('users.localhost');
$row = $auditor->send($old, $new);
```

If needed, the variable `$row` contains the newly created record in the audit table.

### Using a HTTP service

You can also send your audit data to an HTTP service like this:

```php
use Pop\Audit;

$old = [
    'username'   => 'admin',
    'email'      => 'test@test.com'
];

$new = [
    'username'   => 'admin2',
    'email'      => 'test@test.com'
];

$stream = new \Pop\Http\Client\Stream('http://audit.localhost');
$stream->setContextOptions(['http' => [
    'protocol_version' => '1.1',
    'method'           => 'POST',
    'header'           => 'Authorization: Bearer my-auth-token'
]]);

$auditor = new Audit\Auditor(new Audit\Adapter\Http($stream));
$auditor->setModel('MyApp\Model\User', 1001);
$auditor->setUser('testuser', 101);
$auditor->setDomain('users.localhost');
$auditor->send($old, $new);
```

### Setting the Diff

The `pop-audit` component can either do the "diffing" for you, or if you have another
resource that evaluates the differences in values, you can pass those into the auditor as well.
In the examples above, the auditor object handled the "diffing." If you want to pass
values that have already been evaluated, you can do that like this:

```php
use Pop\Audit;

$old = [
    'username' => 'admin'
];

$new = [
    'username' => 'admin2'
];

$auditor = new Audit\Auditor(new Audit\Adapter\File('tmp'));
$auditor->setModel('MyApp\Model\User', 1001);
$auditor->setUser('testuser', 101);
$auditor->setDomain('users.localhost');
$auditor->setDiff($old, $new);
$auditor->send();
