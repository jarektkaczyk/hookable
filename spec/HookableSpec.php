<?php

use kahlan\plugin\Stub;
use Sofa\Hookable\Hookable;

describe('Sofa\Hookable\Hookable', function () {

    it('resolves hooks in instance scope', function () {
        $parent = Stub::classname();
        Stub::on($parent)->method('getAttribute', function () { return 'value'; });

        $hookableClass = Stub::classname(['uses' => Hookable::class, 'extends' => $parent]);
        $hookableClass::hook('getAttribute', function ($next, $value, $args) {
            $this->instanceMethod();
        });

        $hookable = new $hookableClass;
        expect($hookable)->toReceive('instanceMethod');
        $hookable->getAttribute('attribute');
    });

    it('flushes all hooks with the flushHooks method', function () {
        $parent = Stub::classname();

        $hookableClass = Stub::classname(['uses' => Hookable::class, 'extends' => $parent]);
        $hookableClass::hook('method1', function ($next, $value, $args) {});
        $hookableClass::hook('method2', function ($next, $value, $args) {});

        $reflectedClass = new ReflectionClass($hookableClass);
        $reflectedProperty = $reflectedClass->getProperty('hooks');
        $reflectedProperty->setAccessible(true);

        $hooks = $reflectedProperty->getValue();
        expect($hooks)->toHaveLength(2);

        $hookableClass::flushHooks();

        $hooks = $reflectedProperty->getValue();
        expect($hooks)->toHaveLength(0);
    });

});
