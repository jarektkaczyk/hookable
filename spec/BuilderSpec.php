<?php

use kahlan\plugin\Stub;
use Sofa\Hookable\Builder;

describe('Sofa\Hookable\Builder', function () {

    beforeEach(function () {
        $query = Stub::create(['class' => 'Illuminate\Database\Query\Builder']);
        $this->eloquent = Stub::classname(['class' => 'Illuminate\Database\Eloquent\Builder']);
        $this->builder = new Builder(new $query);
    });

    it('fallbacks to base builder for prefixed columns', function () {
        Stub::on($this->eloquent)->method('where', function () {return 'query';});
        expect($this->builder->where('prefixed.column', 'value'))->toBe('query');
    });

    it('calls hook defined on the model', function () {
        $model = Stub::create();
        expect($model)->toReceive('queryHook');
        Stub::on($this->builder)->method('getModel', function () use ($model) {return $model;});
        $this->builder->select('column', 'value');
    });
});
