<?php

namespace Panoscape\Subscription\Tests;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Panoscape\Subscription\HasSubscriptions;

class User extends Authenticatable
{
    use HasSubscriptions;

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = [];
}