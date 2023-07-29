<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPUnit\Framework\MockObject\BadMethodCallException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function __call($method, $args)
    {
        try {
            if (in_array($method, ['get', 'post', 'put', 'patch', 'delete']))
            {
                return $this->call($method, $args[0]);
            }
        } catch (BadMethodCallException $exception) {
            return $exception->getMessage();
        }

    }
}
