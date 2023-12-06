<?php

namespace DTApi\Services\Booking;

interface UserRepositoryInterface
{   
    public function getUsersJobs($user_id);

    public function getUsersJobsHistory($user_id, Request $request);

    // private function getUserTagsStringFromArray($users);

    public function userLoginFailed();

    public function getPotentialJobIdsWithUserId($user_id);

    public function getPotentialTranslators(Job $job);

    public function bookingExpireNoAccepted();
}

