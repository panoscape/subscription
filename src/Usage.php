<?php

namespace Panoscape\Subscription;

use Illuminate\Database\Eloquent\Model;

class Usage extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscription_usages';
     
    /**
    * The attributes that are not mass assignable.
    *
    * @var array
    */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
}