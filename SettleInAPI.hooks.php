<?php

class SettleInAPIHooks {
    
    public static function onUnitTestsList( &$paths )
    {
        $paths[] = __DIR__ . '/tests/phpunit/';
        return true;
    }
    
}