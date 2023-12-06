<?php

namespace DTApi\Services\Booking;

interface JobRepositoryInterface
{
    public function store($user, $data);

    public function updateJob($id, $data, $cuser);

    public function storeJobEmail($data);

    public function jobToData($job);

    public function jobEnd($post_data = array());

    public function isNeedToDelayPush($user_id);

    public function isNeedToSendPush($user_id);

    public function acceptJob($data, $user);

    public function acceptJobWithId($job_id, $cuser);

    public function cancelJobAjax($data, $user);

    public function getPotentialJobs($cuser);

    public function endJob($post_data);

    public function getAll(Request $request, $limit = null);

    public function customerNotCall($post_data);

    public function alerts();

    public function ignoreExpiring($id);

    public function ignoreExpired($id);

    public function ignoreThrottle($id);
}

