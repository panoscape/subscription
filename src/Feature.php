<?php

namespace Panoscape\Subscription;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscription_features';
     
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
    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'subscription_feature_plan', 'feature_id', 'plan_id')
                        ->withTimestamps()
                        ->withPivot('value')
                        ->withPivot('sort_order');
    }
}