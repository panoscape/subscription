<?php

namespace Panoscape\Subscription;

trait HasSubscriptions
{
    public static $userModel;

    public static function bootHasSubscriptions()
    {
        static::$userModel = static::class;
    }
}