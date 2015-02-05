Mellon PHP Validator
====================

Mellon is a validator library using the filter-functions offered by PHP. It is written for PHP micro frameworks which do not have a validation library by themself.

      'Speak, friend, and enter'


Example
-------

In flat PHP, a simple validation would look like this:

```php
$name = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
```
    
Using Mellon, it looks like this:

```php
$validator = new \BO\Mellon\Validator($_GET);
$name = $validator->getParameter('name')->isString()->getValue();
```

With a shortcut for all $_REQUEST variables, you could use:

```php
use \BO\Mellon\Validator;

$name = Validator::param('name')
    ->isString()
    ->maxLength(32)
    ->default('John Doe')
    ->getValue();
```

To ensure, that nobody uses the superglobals in your project, take a look at the controversial rule in the [PHP MD Config File](phpmd.rules.xml). 