<?php

namespace Panoscape\Subscription;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscription_plans';
     
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
     * The features that belong to the plan.
     */
    public function features()
    {
        return $this->belongsToMany(Feature::class, 'subscription_feature_plan', 'plan_id', 'feature_id')
                        ->withTimestamps()
                        ->withPivot('value')
                        ->withPivot('sort_order');
    }

    /**
     * Get the subscriptions for plan.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}