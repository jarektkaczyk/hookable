<?php

use Sofa\Hookable\ArgumentBag;

describe('Sofa\Hookable\ArgumentBag', function () {

    beforeEach(function () {
        $this->bag = new ArgumentBag(['foo' => 'bar', 'faz' => 'baz', 'fux' => 'blox']);
    });

    it('shows whether is empty', function () {
        expect($this->bag->isEmpty())->toBe(false);
        $empty = new ArgumentBag([]);
        expect($empty->isEmpty())->toBe(true);
    });

    it('gets, sets and updates elements values', function () {
        expect($this->bag->get('foo'))->toBe('bar');
        $this->bag->set('foo', 'baz');
        expect($this->bag->get('foo'))->toBe('baz');
    });

    it('provides api for first and last elements', function () {
        expect($this->bag->last())->toBe('blox');
        expect($this->bag->first())->toBe('bar');
    });

    it('gets all elements as array', function () {
        expect($this->bag->all())->toBe(['foo' => 'bar', 'faz' => 'baz', 'fux' => 'blox']);
    });
});
