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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'starts_at',
        'ends_at',
        'canceled_at'
    ];

    /**
    * Get the user that owns the subscription.
    */
    public function user()
    {
        return $this->morphTo();
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

    /**
     * Renew the subscription.
     *
     * @return void
     */
    public function renew($starts_at = null, $ends_at = null)
    {
        if(empty($starts_at)) {
            $starts_at = new \Carbon\Carbon;            
        }
        if(empty($ends_at)) {
            $start = clone $starts_at;
            switch($this->plan->interval) {
                case 'day':
                    $ends_at = $start->addDays($this->plan->interval_count);
                    break;
                case 'week':
                    $ends_at = $start->addWeeks($this->plan->interval_count);
                    break;
                case 'month':
                    $ends_at = $start->addMonths($this->plan->interval_count);
                    break;
                case 'yeay':
                    $ends_at = $start->addYears($this->plan->interval_count);
                    break;
                default:
                    throw new \Exception("Invalid interval type");  
            }
        }

        $this->canceled_at = null;
        $this->starts_at = $starts_at;
        $this->ends_at = $ends_at;
    }

    /**
     * Cancel the subscription.
     *
     * @return void
     */
    public function cancel()
    {
        $this->canceled_at = \Carbon\Carbon::now();
        $this->save();
    }

    /**
     * Check if the subscription is canceled
     *
     * @return bool
     */
    public function canceled()
    {
        return !is_null($this->canceled_at);
    }

    /**
     * Check if the subscription is ended
     *
     * @return bool
     */
    public function ended()
    {
        $ends = \Carbon\Carbon::instance($this->ends_at);

        return !\Carbon\Carbon::now()->lt($ends);
    }

    /**
     * Check if the subscription is canceled
     *
     * @return bool
     */
    public function getCanceledAttribute()
    {
        return $this->canceled();
    }

    /**
     * Check if the subscription is ended
     *
     * @return bool
     */
    public function getEndedAttribute()
    {
        return $this->ended();
    }

    /**
     * Check if the given feature can be used
     *
     * @param mixed $feature
     * @return bool
     */
    public function canUse($feature)
    {
        if($this->ended() || $this->canceled()) {
            return false;
        }

        if($feature instanceof Feature) {
            return $this->plan->features()->where('id', $feature->id)->exists();
        }
        elseif(is_integer($feature)) {
            return $this->plan->features()->where('id', $feature)->exists();
        }
        elseif(is_string($feature)) {
            return $this->plan->features()->where('name', $feature)->exists();
        }
        else {
            throw new \Exception("Invalid argument type");            
        }
    }
}