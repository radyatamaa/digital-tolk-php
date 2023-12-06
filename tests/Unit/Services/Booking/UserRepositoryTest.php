<?php

namespace Tests\Unit\Services\Booking;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DTApi\Services\Booking\UserRepository;
use DTApi\Models\Job;
use DTApi\Models\User;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;


    public function testGetUsersJobs()
    {
        // Mock the User model's find method to return a user
        $user = factory(User::class)->create(['id' => 1]);
        User::shouldReceive('find')->with($user->id)->andReturn($user);

        // Mock relationships and methods for the User model
        $userMock = $this->mock(User::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('is')->with('customer')->andReturn(true);
            $mock->shouldReceive('is')->with('translator')->andReturn(false);

            // Mock the jobs relationship
            $mock->shouldReceive('jobs')->andReturn($this->mockJobRelation($user));
        });

        // Mock the Job model and its methods
        Job::shouldReceive('getTranslatorJobs')->andReturn(collect([]));
        Job::shouldReceive('checkParticularJob')->andReturn(true);

        // Call the function
        $result = (new YourController())->getUsersJobs($user->id);

        // Assertions
        $this->assertArrayHasKey('emergencyJobs', $result);
        $this->assertArrayHasKey('noramlJobs', $result);
        $this->assertArrayHasKey('cuser', $result);
        $this->assertArrayHasKey('usertype', $result);
        $this->assertEquals('customer', $result['usertype']);
    }

    protected function mockJobRelation($user)
    {
        $job = factory(Job::class)->make([
            'immediate' => 'yes', 
            'due' => now()->addDays(1), 
        ]);

        $relation = $this->mock(stdClass::class, function (MockInterface $mock) use ($job, $user) {
            $mock->shouldReceive('with')->andReturnSelf();
            $mock->shouldReceive('whereIn')->with('status', ['pending', 'assigned', 'started'])->andReturnSelf();
            $mock->shouldReceive('orderBy')->with('due', 'asc')->andReturnSelf();
            $mock->shouldReceive('get')->andReturn(collect([$job]));
        });

        $userMock = $this->mock(User::class, function (MockInterface $mock) use ($user, $relation) {
            $mock->shouldReceive('jobs')->andReturn($relation);
        });

        return $relation;
    }


}