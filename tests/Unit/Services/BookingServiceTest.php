<?php

namespace Tests\Unit\Services;

use DTApi\Services\Booking\JobRepositoryInterface;
use DTApi\Services\Booking\NotificationRepositoryInterface;
use DTApi\Services\Booking\StatusRepositoryInterface;
use DTApi\Services\Booking\TranslatorRepositoryInterface;
use DTApi\Services\Booking\UserRepositoryInterface;
use DTApi\Services\Booking\DistanceRepositoryInterface;
use DTApi\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\Request;

class BookingServiceTest extends TestCase
{
    protected $jobRepository;
    protected $notificationRepository;
    protected $statusRepository;
    protected $translatorRepository;
    protected $userRepository;
    protected $distanceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock repositories
        $this->jobRepository = $this->createMock(JobRepositoryInterface::class);
        $this->notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
        $this->translatorRepository = $this->createMock(TranslatorRepositoryInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->distanceRepository = $this->createMock(DistanceRepositoryInterface::class);
    }

    public function testGetListWithUserId()
    {
        // Mock the Request
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('get')
            ->with('user_id')
            ->willReturn(123);

        // Mock the UserRepository method
        $this->userRepository->expects($this->once())
            ->method('getUsersJobs')
            ->with(123)
            ->willReturn(['user_job_data']);

        // Create an instance of BookingService with mocked dependencies
        $bookingService = new BookingService(
            $this->jobRepository,
            $this->notificationRepository,
            $this->statusRepository,
            $this->translatorRepository,
            $this->userRepository,
            $this->distanceRepository
        );

        // Call the getList method
        $result = $bookingService->getList($request);

        // Assert the result
        $this->assertEquals(['user_job_data'], $result);
    }

    public function testGetListWithAdminOrSuperAdmin()
    {
        // Mock the Request
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('__get')
            ->with('authenticatedUser')
            ->willReturn((object)['user_type' => config('app.admin_role_id')]);

        // Mock the JobRepository method
        $this->jobRepository->expects($this->once())
            ->method('getAll')
            ->with($request)
            ->willReturn(['all_jobs_data']);

        // Create an instance of BookingService with mocked dependencies
        $bookingService = new BookingService(
            $this->jobRepository,
            $this->notificationRepository,
            $this->statusRepository,
            $this->translatorRepository,
            $this->userRepository,
            $this->distanceRepository
        );

        // Call the getList method
        $result = $bookingService->getList($request);

        // Assert the result
        $this->assertEquals(['all_jobs_data'], $result);
    }

    public function testGetDetail()
    {
        $jobId = 123;

        // Mock the JobRepository method
        $this->jobRepository->expects($this->once())
            ->method('with')
            ->with('translatorJobRel.user')
            ->willReturnSelf(); // Since 'with' returns the repository instance

        $this->jobRepository->expects($this->once())
            ->method('find')
            ->with($jobId)
            ->willReturn(['job_detail_data']);

        // Create an instance of BookingService with mocked dependencies
        $bookingService = new BookingService(
            $this->jobRepository,
            $this->notificationRepository,
            $this->statusRepository,
            $this->translatorRepository,
            $this->userRepository,
            $this->distanceRepository
        );

        // Call the getDetail method
        $result = $bookingService->getDetail($jobId);

        // Assert the result
        $this->assertEquals(['job_detail_data'], $result);
    }


    public function testCreate()
    {
        // Mock the Request
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('all')
            ->willReturn(['job_data']);

        // Mock the authenticatedUser property
        $authenticatedUser = (object)['user_id' => 123];
        $request->__authenticatedUser = $authenticatedUser;

        // Mock the JobRepository method
        $this->jobRepository->expects($this->once())
            ->method('store')
            ->with($authenticatedUser, ['job_data'])
            ->willReturn(['stored_job_data']);

        // Create an instance of BookingService with mocked dependencies
        $bookingService = new BookingService(
            $this->jobRepository,
            $this->notificationRepository,
            $this->statusRepository,
            $this->translatorRepository,
            $this->userRepository,
            $this->distanceRepository
        );

        // Call the create method
        $result = $bookingService->create($request);

        // Assert the result
        $this->assertEquals(['stored_job_data'], $result);
    }
}