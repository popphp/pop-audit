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

$auditor = new Audit\Auditor(new Audit\Adapter\File('tmp'));  // Folder
$auditor->setModel('MyApp\Model\User', 1001);                 // Model name and model ID
$auditor->setUser('testuser', 101);                           // Username and user ID (optional)
$auditor->setDomain('users.localhost');                        // Domain (optional)
$logFile = $auditor->send($old, $new);
```

In this case, the variable `$logFile` would contain the name of the audit log file,
for example `pop-audit-1535060625.log` in case it's needed to reference again.
That file will contain the JSON-encoded data that tracks the difference between the
model states:

```json
{
    "model": "MyApp\\Model\\User",
    "model_id": 1001,
    "action": "UPDATED",
    "old": {
        "username": "admin"
    },
    "new": {
        "username": "admin2"
    },
    "domain": "users.localhost",
    "user_id": 101,
    "username": "testuser",
    "timestamp": "2018-08-23 11:20:30"
}
```

Notice that only the difference is stored. In this case, only the username value changed.

