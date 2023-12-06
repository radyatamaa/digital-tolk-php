<?php

namespace Tests\Unit\Services;

use DTApi\Services\BookingServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\Request;
use DTApi\Http\Controllers\BookingController;

class BookingServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock repositories
        $this->service = $this->createMock(BookingServiceInterface::class);
    }

    public function testIndex()
    {
        // Mock the Request
        $request = new Request;
        $request->setMethod('GET');

        // Mock the BookingServiceInterface method
        $this->bookingService->expects($this->once())
            ->method('getList')
            ->with($request)
            ->willReturn(['list_data']);

        // Create an instance of BookingController with mocked dependencies
        $bookingController = new BookingController($this->bookingService);

        // Call the index method
        $response = $bookingController->index($request);

        // Assert the response
        $this->assertEquals(response(['list_data']), $response);
    }

    public function testShow()
    {
        $jobId = 123;

        // Mock the BookingServiceInterface method
        $this->bookingService->expects($this->once())
            ->method('getDetail')
            ->with($jobId)
            ->willReturn(['detail_data']);

        // Create an instance of BookingController with mocked dependencies
        $bookingController = new BookingController($this->bookingService);

        // Call the show method
        $response = $bookingController->show($jobId);

        // Assert the response
        $this->assertEquals(response(['detail_data']), $response);
    }

}