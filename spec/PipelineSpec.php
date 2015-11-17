<?php

use kahlan\plugin\Stub;
use Sofa\Hookable\Pipeline;

describe('\Sofa\Hookable\Pipeline', function () {

    it('delivers payload to destination', function () {
        $pipeline    = new Pipeline;
        $payload     = 'start';
        $destination = function ($payload) {
            return 'end';
        };

        expect($pipeline->send($payload)->to($destination))->toBe('end');
    });

    it('calls pipes in the same order they were provided', function () {
        $pipeline    = new Pipeline;
        $payload     = 'start';
        $destination = function ($payload) {
            return $payload . ',end';
        };
        $pipes = [
            function ($next, $payload) { $payload .= ',first'; return $next($payload); },
            function ($next, $payload) { $payload .= ',second'; return $next($payload); },
            function ($next, $payload) { $payload .= ',third'; return $next($payload); },
        ];

        $result = $pipeline->send($payload)->through($pipes)->to($destination);

        expect($result)->toBe('start,first,second,third,end');
    });

    it('passes additional arguments along with the parcel', function () {
        $payload  = 'start';
        $pipes = [function ($next, $payload, $args) {
            $payload .= ',pipe-'.$args->get('foo');
            return $next($payload, $args);
        }];
        $destination = function ($payload, $args) {
            return $payload.',end-'.$args->get('foo');
        };
        $pipeline = new Pipeline($pipes);

        $args = Stub::create(['implements' => ['Sofa\Hookable\Contracts\ArgumentBag']]);
        Stub::on($args)->method('get')->andReturn('bar', 'bar');

        expect($args)->toReceive('get');
        expect($args)->toReceiveNext('get');
        $result = $pipeline->send($payload)->with($args)->to($destination);
        expect($result)->toBe('start,pipe-bar,end-bar');
    });
});
