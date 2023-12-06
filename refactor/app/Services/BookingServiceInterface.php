<?php

namespace DTApi\Services;

interface BookingServiceInterface
{   
    public function getList(Request $request);

    public function getDetail($id);

    public function create(Request $request);

    public function update($id, Request $request);

    public function createJobEmail(Request $request);

    public function getHistory(Request $request);

    public function acceptJob(Request $request);

    public function acceptJobWithId(Request $request);

    public function cancelJob(Request $request);

    public function endJob(Request $request);

    public function customerNotCall(Request $request);

    public function getPotentialJobs(Request $request);

    public function distanceFeed(Request $request);

    public function reopen(Request $request);

    public function resendNotifications(Request $request);

    public function resendSMSNotifications(Request $request);
}

