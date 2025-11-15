<?php

namespace Test;

use Exception;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @param callable $callback
     * @return Exception|null
     */
    protected function catchException(callable $callback): ?Exception
    {
        try {
            $callback();
            return null;
        } catch (Exception $e) {
            return $e;
        }
    }
}
