<?php

namespace Panoscape\Subscription;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
     * @param \Carbon\Carbon|null $starts_at
     * @param \Carbon\Carbon|null $ends_at
     *
     * @return void
     */
    public function renew($starts_at = null, $ends_at = null)
    {
        if(empty($starts_at)) {
            $starts_at = new Carbon;            
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
        $this->canceled_at = Carbon::now();
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
        $ends = Carbon::instance($this->ends_at);

        return !Carbon::now()->lt($ends);
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
     * Check if the given feature exists
     *
     * @param mixed $feature
     * @return bool
     */
    public function featureExists($feature)
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

    /**
     * Check if the given feature is active
     *
     * @param mixed $feature
     * @return bool
     */
    public function featureActive($feature)
    {
        if($this->ended() || $this->canceled()) {
            return false;
        }

        $feature = $this->getFeature($feature);

        if(is_null($feature)) {
            return false;
        }

        //TODO

        $usage = $this->usages()->where('feature_id', $feature->id)->first();

        if(is_null($usage)) {
            return false;
        }

        return $feature->pivot->value;
    }

    /**
     * Get the feature's value
     *
     * @param mixed $feature
     * @return mixed
     */
    public function featureValue($feature)
    {
        if($this->ended() || $this->canceled()) {
            return null;
        }

        $feature = $this->getFeature($feature);

        if(is_null($feature)) {
            return null;
        }

        return $feature->pivot->value;
    }

    /**
     * Get how much the given feature's value has been consumed
     *
     * @param mixed $feature
     * @return mixed
     */
    public function featureConsumed($feature)
    {
        if($this->ended() || $this->canceled()) {
            return null;
        }

        $feature = $this->getFeature($feature);

        if(is_null($feature)) {
            return null;
        }

        $usage = $this->usages()->where('feature_id', $feature->id)->first();

        if(is_null($usage)) {
            return null;
        }

        return $usage->used;
    }

    /**
     * Get how much the given feature's value left
     *
     * @param mixed $feature
     * @return mixed
     */
    public function featureRemains($feature)
    {
        if($this->ended() || $this->canceled()) {
            return null;
        }

        $feature = $this->getFeature($feature);

        if(is_null($feature)) {
            return null;
        }

        $usage = $this->usages()->where('feature_id', $feature->id)->first();

        if(is_null($usage)) {
            return null;
        }

        return $feature->pivot->value - $usage->used;
    }

    /**
     * Get feature usage
     *
     * @param mixed $feature
     * @return Usage|null
     */
    protected function featureUsage($feature)
    {
        if($this->ended() || $this->canceled()) {
            return null;
        }

        if($feature instanceof Feature) {
            return $this->usages()->where('feature_id', $feature->id)->first();
        }
        elseif(is_integer($feature)) {
            return $this->usages()->where('feature_id', $feature)->first();
        }
        elseif(is_string($feature)) {
            $feature = $this->plan->features()->where('name', $feature)->first();
            if(is_null($feature)) {
                return null;
            }
            return $this->usages()->where('feature_id', $feature->id)->first();
        }
        else {
            throw new \Exception("Invalid argument type");            
        }
    }

    /**
     * Get feature instance
     *
     * @param mixed $feature
     * @return Feature|null
     */
    protected function getFeature($feature)
    {
        if($this->ended() || $this->canceled()) {
            return null;
        }

        if($feature instanceof Feature) {
            return $this->plan->features()->where('id', $feature->id)->first();
        }
        elseif(is_integer($feature)) {
            return $this->plan->features()->where('id', $feature)->first();
        }
        elseif(is_string($feature)) {
            return $this->plan->features()->where('name', $feature)->first();
        }
        else {
            throw new \Exception("Invalid argument type");            
        }
    }
}