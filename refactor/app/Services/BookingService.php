<?php

namespace DTApi\Services;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Services\Booking\JobRepositoryInterface;
use DTApi\Services\Booking\NotificationRepositoryInterface;
use DTApi\Services\Booking\StatusRepositoryInterface;
use DTApi\Services\Booking\TranslatorRepositoryInterface;
use DTApi\Services\Booking\UserRepositoryInterface;
use DTApi\Services\Booking\DistanceRepositoryInterface;

class BookingService implements BookingServiceInterface 
{
    protected $jobRepository;
    protected $notificationRepository;
    protected $statusRepository;
    protected $translatorRepository;
    protected $userRepository;
    protected $distanceRepository;

    /**
     * BookingService constructor.
     * @param JobRepositoryInterface $jobRepository
     * @param NotificationRepositoryInterface $notificationRepository
     * @param StatusRepositoryInterface $statusRepository
     * @param TranslatorRepositoryInterface $translatorRepository
     * @param UserRepositoryInterface $userRepository
     * @param DistanceRepositoryInterface $distanceRepository
     */
    public function __construct(
        JobRepositoryInterface $jobRepository,
        NotificationRepositoryInterface $notificationRepository,
        StatusRepositoryInterface $statusRepository,
        TranslatorRepositoryInterface $translatorRepository,
        UserRepositoryInterface $userRepository,
        DistanceRepositoryInterface $distanceRepository)
    {
        $this->jobRepository = $jobRepository;
        $this->notificationRepository = $notificationRepository;
        $this->statusRepository = $statusRepository;
        $this->translatorRepository = $translatorRepository;
        $this->userRepository = $userRepository;
        $this->distanceRepository = $distanceRepository;
    }

    public function getList(Request $request)
    {
        $result = [];
        if($user_id = $request->get('user_id')) {

            $result = $this->userRepository->getUsersJobs($user_id);

        }
        elseif($request->__authenticatedUser->user_type == config('app.admin_role_id') || $request->__authenticatedUser->user_type == config('app.super_admin_role_id'))
        {
            $result = $this->jobRepository->getAll($request);
        }

        return $result;
    }

    public function getDetail($id)
    {
        $result = $this->jobRepository->with('translatorJobRel.user')->find($id);
        return $result;
    }

    public function create(Request $request)
    {
        $data = $request->all();

        $result = $this->jobRepository->store($request->__authenticatedUser, $data);

        return $result;
    }

    public function update($id, Request $request)
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $result = $this->jobRepository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);

        return $result;
    }

    public function createJobEmail(Request $request)
    {
        $adminSenderEmail = config('app.adminemail');
        $data = $request->all();

        $result = $this->jobRepository->storeJobEmail($data);

        return $result;
    }

    public function getHistory(Request $request)
    {
        if($user_id = $request->get('user_id')) {

            $result = $this->userRepository->getUsersJobsHistory($user_id, $request);
            return $result;
        }

        return null;
    }

    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $result = $this->jobRepository->acceptJob($data, $user);

        return $result;
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $result = $this->jobRepository->acceptJobWithId($data, $user);

        return $result;
    }

    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $result = $this->jobRepository->cancelJobAjax($data, $user);

        return $result;
    }

    public function endJob(Request $request)
    {
        $data = $request->all();

        $result = $this->jobRepository->endJob($data);

        return $result;

    }

    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $result = $this->jobRepository->customerNotCall($data);

        return $result;
    }

    public function getPotentialJobs(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $result = $this->jobRepository->getPotentialJobs($user);

        return $result;
    }

    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        $result = $this->distanceRepository->distanceFeed($data);

        return $result;
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $result = $this->translatorRepository->reopen($data);

        return $result;
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->jobRepository->find($data['jobid']);
        $job_data = $this->jobRepository->jobToData($job);
        $this->notificationRepository->sendNotificationTranslator($job, $job_data, '*');

        return ['success' => 'Push sent'];
    }

    public function resendSMSNotifications(Request $request)
    {
        
        $data = $request->all();
        $job = $this->jobRepository->find($data['jobid']);
        $job_data = $this->jobRepository->jobToData($job);

        try {
            $this->notificationRepository->sendSMSNotificationToTranslator($job);
            return ['success' => 'SMS sent'];
        } catch (\Exception $e) {
            return ['success' => $e->getMessage()];
        }
    }
}