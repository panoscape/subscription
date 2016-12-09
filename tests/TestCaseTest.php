<?php

namespace Panoscape\Subscription\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Database\Schema\Blueprint;

class TestCaseTest extends TestCase
{
    protected $user;

    protected $plan;

    protected $feature;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', true);
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            \Panoscape\Subscription\SubscriptionServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'App\Plan' => \Panoscape\Subscription\Plan::class,
            'App\Usage' => \Panoscape\Subscription\Usage::class,
            'App\Feature' => \Panoscape\Subscription\Feature::class,
            'App\Subscription' => \Panoscape\Subscription\Subscription::class,
        ];
    }

    protected function setUpDatabase()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        $this->user = User::create(['name' => 'user']);

        $this->loadMigrationsFrom([
            '--database' => 'sqlite',
            '--realpath' => realpath(__DIR__.'/../src/migrations'),
        ]);

        $this->plan = \App\Plan::create([
            'name' => 'basic',
        ]);

        $this->feature = \App\Feature::create([
            'name' => 'storage',
        ]);

        $this->plan->features()->save($this->feature, ['value' => 512]);
    }

    /**
     * Test databse.
     *
     * @test
     */
    public function testDatabase()
    {
        $this->seeInDatabase('subscription_feature_plan', [
            'plan_id' => $this->plan->id,
            'feature_id' => $this->feature->id,
            'value' => 512,
        ]);
    }

    /**
     * Test subscription.
     *
     * @test
     */
    public function testSubscription()
    {
        $this->assertFalse($this->user->subscribed());

        $subscription = $this->user->subscribe($this->plan);

        $this->assertTrue($this->user->id == $subscription->user->id);
        $this->assertTrue($this->plan->id == $subscription->plan->id);

        $this->assertFalse($subscription->canceled);
        $this->assertFalse($subscription->ended);

        $this->assertTrue($this->user->subscription->featureExists($this->feature->name));
        $this->assertTrue($this->user->subscription->featureExists($this->feature));
        $this->assertTrue($this->user->subscription->featureExists($this->feature->id));

        $this->user->subscription->cancel();

        $this->assertTrue($this->user->subscription->canceled);
        $this->assertFalse($this->user->subscription->ended);

        $this->assertFalse($this->user->subscription->featureExists($this->feature->name));
        $this->assertFalse($this->user->subscription->featureExists($this->feature));
        $this->assertFalse($this->user->subscription->featureExists($this->feature->id));

        $this->user->subscription->renew();

        $this->assertFalse($this->user->subscription->canceled);
        $this->assertFalse($this->user->subscription->ended);

        $this->assertTrue($this->user->subscription->featureExists($this->feature->name));
        $this->assertTrue($this->user->subscription->featureExists($this->feature));
        $this->assertTrue($this->user->subscription->featureExists($this->feature->id));
    }
}