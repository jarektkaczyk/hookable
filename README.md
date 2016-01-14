# Sofa/Hookable

[![Build Status](https://travis-ci.org/jarektkaczyk/hookable.svg)](https://travis-ci.org/jarektkaczyk/hookable) [![stable](https://poser.pugx.org/sofa/hookable/v/stable.svg)](https://packagist.org/packages/sofa/hookable) [![Downloads](https://poser.pugx.org/sofa/hookable/downloads)](https://packagist.org/packages/sofa/hookable)

Hooks system for the [Eloquent ORM (Laravel 5.2)](https://laravel.com/docs/5.2/eloquent).

Hooks are available for the following methods:

* `Model::getAttribute`
* `Model::setAttribute`
* `Model::save`
* `Model::toArray`
* `Model::replicate`
* `Model::isDirty`
* `Model::__isset`
* `Model::__unset`

and all methods available on the `Illuminate\Database\Eloquent\Builder` class.

## Installation

Clone the repo or pull as composer dependency:

```
composer require sofa/hookable:~5.2
```

## Usage

In order to register a hook you use static method `hook` on the model: [example](https://github.com/jarektkaczyk/eloquence/blob/5.1/src/Mappable.php#L42-L56).

**Important** Due to the fact that PHP will not let you bind a `Closure` to your model's instance if it is created **in a static context** (for example model's `boot` method), you need to hack it a little bit, in that the closure is created in an object context. 

For example see the above example along with the [class that encloses our closures in an instance scope](https://github.com/jarektkaczyk/eloquence/blob/5.1/src/Mappable/Hooks.php) that is being used there.

Signature for the hook closure is following:

```php
function (Closure $next, mixed $payload, Sofa\Hookable\Contracts\ArgumentBag $args)
```

Hooks are resolved via `Sofa\Hookable\Pipeline` in the same order they were registered (except for `setAttribute` where the order is reversed), and each is called unless you return early:

```php
// example hook on getAttribute method:
function ($next, $value, $args)
{
    if (/* your condition */) {
        // return early
        return 'some value'; // or the $value
    }

    else if (/* other condition */) {
        // you may want to mutate the value
        $value = strtolower($value);
    }

    // finally continue calling other hooks
    return $next($value, $args);
}
```

## Contribution

All contributions are welcome, PRs must be **tested** and **PSR-2 compliant**.
