<?php

namespace Panoscape\Subscription;

trait HasSubscriptions
{
    /**
     * Get the subscription associated with the user.
     */
    public function subscription()
    {
        return $this->morphOne(Subscription::class, 'user');
    }
    
    /**
     * Check if the user is subscribed
     *
     * @return bool
     */
    public function subscribed()
    {
        return $this->subscription()->exists();
    }

    /**
     * Subscribe this user to the given plan
     *
     * @param mixed $plan
     * @param mixed $starts_at
     * @param mixed $ends_at
     *
     * @return \Panoscape\Subscription\Subscription
     */
    public function subscribe($plan, $starts_at = null, $ends_at = null)
    {
        if($plan instanceof Plan) {
        }
        elseif(is_integer($plan)) {
            $plan = Plan::findOrFail($plan);
        }
        elseif(is_string($plan)) {
            $plan = Plan::where('name', $plan)->firstOrFail();
        }
        else {
            throw new \Exception("Invalid argument type");            
        }

        $subscription = new Subscription([
            'plan_id' => $plan->id,
        ]);

        $subscription->renew($starts_at, $ends_at);

        $this->subscription()->save($subscription);

        return $subscription;
    }

    /**
     * Check if the user is subscribed
     *
     * @return bool
     */
    public function getSubscribedAttribute()
    {
        return $this->subscribed();
    }
}