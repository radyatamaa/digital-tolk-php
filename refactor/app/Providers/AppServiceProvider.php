<?php

namespace DTApi\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // register Repository Jobs
        $this->app->bind('DTApi\Services\Booking\DistanceRepositoryInterface', 'DTApi\Services\Booking\DistanceRepository');
        $this->app->bind('DTApi\Services\Booking\JobRepositoryInterface', 'DTApi\Services\Booking\JobRepository');
        $this->app->bind('DTApi\Services\Booking\NotificationRepositoryInterface', 'DTApi\Services\Booking\NotificationRepository');
        $this->app->bind('DTApi\Services\Booking\StatusRepositoryInterface', 'DTApi\Services\Booking\StatusRepository');
        $this->app->bind('DTApi\Services\Booking\TranslatorRepositoryInterface', 'DTApi\Services\Booking\TranslatorRepository');
        $this->app->bind('DTApi\Services\Booking\UserRepositoryInterface', 'DTApi\Services\Booking\UserRepository');


        // register Services
        $this->app->bind('DTApi\Services\BookingServiceInterface', 'DTApi\Services\BookingService');
    }
}
