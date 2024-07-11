<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Reservation;
use App\Models\Department;
use App\Models\User;
use App\Models\Venue;
use App\Models\Schedule;
use App\Policies\ReservationPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\UserPolicy;
use App\Policies\VenuePolicy;
use App\Policies\SchedulePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Reservation::class => ReservationPolicy::class,
        Department::class => DepartmentPolicy::class,
        User::class => UserPolicy::class,
        Venue::class => VenuePolicy::class,
        Schedule::class => SchedulePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
