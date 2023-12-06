<?php

namespace DTApi\Services\Booking;

interface StatusRepositoryInterface
{   
    public function changeStatus($job, $data, $changedTranslator);
}

