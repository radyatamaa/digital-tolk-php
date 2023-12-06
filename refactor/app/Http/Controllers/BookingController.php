<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Services\BookingServiceInterface;
use App\Exceptions\HttpInternalServerException;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingServiceInterface
     */
    protected $service;

    /**
     * BookingController constructor.
     * @param BookingServiceInterface $bookingService
     */
    public function __construct(BookingServiceInterface $bookingService)
    {
        $this->service = $bookingService;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $response = $this->service->getList($request);

        return response($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $response = $this->service->getDetail($id);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $response = $this->service->create($request);

        return response($response);

    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $response = $this->service->update($id, $request);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $response = $this->service->createJobEmail($request);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        $response = $this->service->getUsersJobsHistory($request);
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $response = $this->service->acceptJob($request);

        return response($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $response = $this->service->acceptJobWithId($request);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $response = $this->service->cancelJob($request);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $response = $this->service->endJob($request);

        return response($response);

    }

    public function customerNotCall(Request $request)
    {
        $response = $this->service->customerNotCall($request);

        return response($response);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $response = $this->service->getPotentialJobs($request);

        return response($response);
    }

    public function distanceFeed(Request $request)
    {
        $response = $this->service->distanceFeed($request);

        return response($response);
    }

    public function reopen(Request $request)
    {
        $response = $this->service->reopen($request);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $response = $this->service->sendNotificationTranslator($request);

        return response($response);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $response = $this->service->resendSMSNotifications($request);
        return response($response);
    }

}
