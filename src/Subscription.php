<?php

namespace Panoscape\Subscription;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscription_subscriptions';
     
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

    /**
    * Get the user that owns the subscription.
    */
    public function user()
    {
        return $this->belongsTo(HasSubscription::$userModel, 'user_id');
    }

    /**
    * Get the plan that owns the subscription.
    */
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * Get the usages for the subscription.
     */
    public function usages()
    {
        return $this->hasMany(Usage::class, 'subscription_id');
    }
}