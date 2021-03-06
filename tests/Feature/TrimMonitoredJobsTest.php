<?php

namespace Laravel\Horizon\Tests\Feature;

use Cake\Chronos\Chronos;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Events\MasterSupervisorLooped;
use Laravel\Horizon\Listeners\TrimMonitoredJobs;
use Laravel\Horizon\MasterSupervisor;
use Laravel\Horizon\Tests\IntegrationTest;
use Mockery;

class TrimMonitoredJobsTest extends IntegrationTest
{
    public function test_trimmer_has_a_cooldown_period()
    {
        $trim = new TrimMonitoredJobs;

        $repository = Mockery::mock(JobRepository::class);
        $repository->shouldReceive('trimMonitoredJobs')->twice();
        $this->app->instance(JobRepository::class, $repository);

        // Should not be called first time since date is initialized...
        $trim->handle(new MasterSupervisorLooped(Mockery::mock(MasterSupervisor::class)));

        Chronos::setTestNow(Chronos::now()->addMinutes(1600));

        // Should only be called twice...
        $trim->handle(new MasterSupervisorLooped(Mockery::mock(MasterSupervisor::class)));
        $trim->handle(new MasterSupervisorLooped(Mockery::mock(MasterSupervisor::class)));
        $trim->handle(new MasterSupervisorLooped(Mockery::mock(MasterSupervisor::class)));

        Chronos::setTestNow();
    }
}
