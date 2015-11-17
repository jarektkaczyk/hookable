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
});
