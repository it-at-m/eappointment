Mellon PHP Validator
====================

Link to main repository: https://gitlab.com/eappointment/mellon/

Mellon is a validator library using the filter-functions offered by PHP. It is written for PHP micro frameworks which do not have a validation library by themself.

      'Speak, friend, and enter'

-----
Usage
-----

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
    ->isSmallerThan(32)
    ->setDefault('John Doe')
    ->getValue();
```

To ensure, that nobody uses the superglobals in your project, take a look at the controversial rule in the [PHP MD Config File](phpmd.rules.xml). 

--------------
Error Messages
--------------

Mellon allow to add error messages for custom validations:

```php
$size = Validator::param('size')
    ->isMatchOf('/(px|em|%)/', 'Do include some valid units like px, em or % for a size')
    ->isFreeOf('/important/', 'The css statement "!important" is not allowed')
    ->isBiggerThan(2, 'The size should contain some value')
    ->isSmallerThan(10, 'You should enter a size, no short story expected')
    ->setDefault('100%');

if ($size->hasFailed()) {
    throw new Exception("Error in size param: " . implode(';', $size->getMessages()));
}
else {
    echo $size->getValue();
}
```

For the usage of validation in template engines, mellon support an output as array:
```php
$sizeParam = $size->getStatus();
   /**
     * Contains the following keys:
     *     failed - True if validation has failed
     *     messages - A list of error messages in case the validation has failed
     *     value - Value, might be the default value if validation has failed
     */
```


