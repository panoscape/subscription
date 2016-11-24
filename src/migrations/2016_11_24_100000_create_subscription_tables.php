<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 32)->unique();
            $table->string('fullname')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 7, 2)->default('0.00');
            $table->string('interval')->default('month');
            $table->smallInteger('interval_count')->default(1);
            $table->smallInteger('sort_order')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_features', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 32)->unique();
            $table->string('fullname')->nullable();
            $table->text('description')->nullable();
            $table->text('interval')->nullable();
            $table->text('interval_count')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_feature_plan', function (Blueprint $table) {
            $table->integer('plan_id')->unsigned();
            $table->integer('feature_id')->unsigned();
            $table->string('value');
            $table->smallInteger('sort_order')->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'feature_id']);
            $table->foreign('plan_id')->references('id')->on('subscription_plans')->onDelete('cascade');
            $table->foreign('feature_id')->references('id')->on('subscription_features')->onDelete('cascade');
        });

        Schema::create('subscription_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('plan_id')->unsigned();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'plan_id']);
            //TODO: users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('subscription_plans')->onDelete('cascade');
        });

        Schema::create('subscription_usages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('subscription_id')->unsigned();
            $table->integer('feature_id')->unsigned();
            $table->smallInteger('used')->unsigned();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();

            $table->unique(['subscription_id', 'feature_id']);
            $table->foreign('subscription_id')->references('id')->on('subscription_subscriptions')->onDelete('cascade');
            $table->foreign('feature_id')->references('id')->on('subscription_features')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('subscription_features');
        Schema::dropIfExists('subscription_subscriptions');
        Schema::dropIfExists('subscription_usages');
    }
}
