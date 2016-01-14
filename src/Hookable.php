<?php

namespace Sofa\Hookable;

use Closure;

/**
 * This trait is an entry point for all the hooks that we want to apply
 * on the Eloquent Model and Builder in order to let the magic happen.
 *
 * @version 1.0
 */
trait Hookable
{
    /**
     * @var \Closure[]
     */
    protected static $hooks = [];

    /**
     * Register hook on Eloquent method.
     *
     * @param  string   $method
     * @param  \Closure $hook
     * @return void
     */
    public static function hook($method, Closure $hook)
    {
        static::$hooks[$method][] = $hook;
    }

    /**
     * Remove all of the hooks on the Eloquent model.
     *
     * @return void
     */
    public static function flushHooks()
    {
        static::$hooks = [];
    }

    /**
     * Create new Hookable query builder for the instance.
     *
     * @param  \Illuminate\Database\Query\Builder
     * @return \Sofa\Hookable\Builder
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /*
    |--------------------------------------------------------------------------
    | Register hooks
    |--------------------------------------------------------------------------
    */

    /**
     * Allow custom where method calls on the builder.
     *
     * @param  \Sofa\Hookable\Builder  $query
     * @param  string  $method
     * @param  \Sofa\Hookable\ArgumentBag  $args
     * @return \Sofa\Hookable\Builder
     */
    public function queryHook(Builder $query, $method, ArgumentBag $args)
    {
        $hooks       = $this->boundHooks(__FUNCTION__);
        $params      = compact('method', 'args');
        $payload     = $query;
        $destination = function ($query) use ($method, $args) {
            return call_user_func_array([$query, 'callParent'], [$method, $args->all()]);
        };

        return $this->pipe($hooks, $payload, $params, $destination);
    }

    /**
     * Register hook for getAttribute.
     *
     * @param  string $key
     * @return mixed
     * @return mixed
     */
    public function getAttribute($key)
    {
        $hooks       = $this->boundHooks(__FUNCTION__);
        $params      = compact('key');
        $payload     = parent::getAttribute($key);
        $destination = function ($attribute) {
            return $attribute;
        };

        return $this->pipe($hooks, $payload, $params, $destination);
    }

    /**
     * Register hook for setAttribute.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function setAttribute($key, $value)
    {
        $hooks       = array_reverse($this->boundHooks(__FUNCTION__));
        $params      = compact('key');
        $payload     = $value;
        $destination = function ($value) use ($key) {
            parent::setAttribute($key, $value);
        };

        return $this->pipe($hooks, $payload, $params, $destination);
    }

    /**
     * Register hook for save.
     *
     * @param  array  $options
     * @return boolean
     */
    public function save(array $options = [])
    {
        if (!parent::save($options)) {
            return false;
        }

        $hooks       = $this->boundHooks(__FUNCTION__);
        $params      = compact('options');
        $payload     = true;
        $destination = function () {
            return true;
        };

        return $this->pipe($hooks, $payload, $params, $destination);
    }

    /**
     * Register hook for isDirty.
     *
     * @param null $attributes
     * @return bool
     */
    public function isDirty($attributes = null)
    {
        if (! is_array($attributes) && !is_null($attributes)) {
            $attributes = func_get_args();
        }

        $hooks       = $this->boundHooks(__FUNCTION__);
        $params      = compact('attributes');
        $payload     = $attributes;
        $destination = function ($attributes) {
            return parent::isDirty($attributes);
        };

        return $this->pipe($hooks, $payload, $params, $destination);
    }

    /**
     * Register hook for toArray.
     *
     * @return mixed
     */
    public function toArray()
    {
        $hooks       = $this->boundHooks(__FUNCTION__);
        $params      = [];
        $payload     = parent::toArray();
        $destination = function ($array) {
            return $array;
        };

        return $this->pipe($hooks, $payload, $params, $destination);
    }

    /**
     * Register hook for replicate.
     *
     * @return mixed
     */
    public function replicate(array $except = null)
    {
        $hooks       = $this->boundHooks(__FUNCTION__);
        $params      = ['except' => $except, 'original' => $this];
        $payload     = parent::replicate($except);
        $destination = function ($copy) {
            return $copy;
        };

        return $this->pipe($hooks, $payload, $params, $destination);
    }

    /**
     * Register hook for isset call.
     *
     * @param  string  $key
     * @return boolean
     */
    public function __isset($key)
    {
        $hooks       = $this->boundHooks(__FUNCTION__);
        $params      = compact('key');
        $payload     = parent::__isset($key);
        $destination = function ($isset) {
            return $isset;
        };

        return $this->pipe($hooks, $payload, $params, $destination);
    }

    /**
     * Register hook for isset call.
     *
     * @param  string  $key
     * @return boolean
     */
    public function __unset($key)
    {
        $hooks       = $this->boundHooks(__FUNCTION__);
        $params      = compact('key');
        $payload     = false;
        $destination = function () use ($key) {
            return call_user_func('parent::__unset', $key);
        };

        return $this->pipe($hooks, $payload, $params, $destination);
    }

    /**
     * Send payload through the pipeline.
     *
     * @param  \Closure[] $pipes
     * @param  mixed      $payload
     * @param  array      $params
     * @param  \Closure   $destination
     * @return mixed
     */
    protected function pipe($pipes, $payload, $params, $destination)
    {
        return (new Pipeline($pipes))
                ->send($payload)
                ->with(new ArgumentBag($params))
                ->to($destination);
    }

    /**
     * Get all hooks for given method bound to $this instance.
     *
     * @param  string $method
     * @return \Closure[]
     */
    protected function boundHooks($method)
    {
        $hooks = isset(static::$hooks[$method]) ? static::$hooks[$method] : [];

        return array_map(function ($hook) {
            return $hook->bindTo($this, get_class($this));
        }, $hooks);
    }
}
