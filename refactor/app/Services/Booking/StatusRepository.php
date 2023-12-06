<?php

namespace DTApi\Services\Booking;

use DTApi\Events\SessionEnded;
use DTApi\Helpers\SendSMSHelper;
use Event;
use Carbon\Carbon;
use Monolog\Logger;
use DTApi\Models\Job;
use DTApi\Models\User;
use DTApi\Models\Language;
use DTApi\Models\UserMeta;
use DTApi\Helpers\TeHelper;
use Illuminate\Http\Request;
use DTApi\Models\Translator;
use DTApi\Mailers\AppMailer;
use DTApi\Models\UserLanguages;
use DTApi\Events\JobWasCreated;
use DTApi\Events\JobWasCanceled;
use DTApi\Models\UsersBlacklist;
use DTApi\Helpers\DateTimeHelper;
use DTApi\Mailers\MailerInterface;
use Illuminate\Support\Facades\DB;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\FirePHPHandler;
use Illuminate\Support\Facades\Auth;

/**
 * Class StatusRepository
 * @package DTApi\Services\Booking
 */
class StatusRepository extends BaseRepository implements StatusRepositoryInterface 
{

    protected $model;
    protected $mailer;
    protected $logger;

    /**
     * @param Job $model
     */
    function __construct(Job $model, MailerInterface $mailer)
    {
        parent::__construct($model);
        $this->mailer = $mailer;
        $this->logger = new Logger('admin_logger');

        $this->logger->pushHandler(new StreamHandler(storage_path('logs/admin/laravel-' . date('Y-m-d') . '.log'), Logger::DEBUG));
        $this->logger->pushHandler(new FirePHPHandler());
    }

        /**
     * @param $job
     * @param $data
     * @return bool
     */
    private function changeWithdrawafter24Status($job, $data)
    {
        if (in_array($data['status'], ['timedout'])) {
            $job->status = $data['status'];
            if ($data['admin_comments'] == '') return false;
            $job->admin_comments = $data['admin_comments'];
            $job->save();
            return true;
        }
        return false;
    }

    /**
     * @param $job
     * @param $data
     * @return bool
     */
    private function changeAssignedStatus($job, $data)
    {
        if (in_array($data['status'], ['withdrawbefore24', 'withdrawafter24', 'timedout'])) {
            $job->status = $data['status'];
            if ($data['admin_comments'] == '' && $data['status'] == 'timedout') return false;
            $job->admin_comments = $data['admin_comments'];
            if (in_array($data['status'], ['withdrawbefore24', 'withdrawafter24'])) {
                $user = $job->user()->first();

                if (!empty($job->user_email)) {
                    $email = $job->user_email;
                } else {
                    $email = $user->email;
                }
                $name = $user->name;
                $dataEmail = [
                    'user' => $user,
                    'job'  => $job
                ];

                $subject = 'Information om avslutad tolkning för bokningsnummer #' . $job->id;
                $this->mailer->send($email, $name, $subject, 'emails.status-changed-from-pending-or-assigned-customer', $dataEmail);

                $user = $job->translatorJobRel->where('completed_at', Null)->where('cancel_at', Null)->first();

                $email = $user->user->email;
                $name = $user->user->name;
                $subject = 'Information om avslutad tolkning för bokningsnummer # ' . $job->id;
                $dataEmail = [
                    'user' => $user,
                    'job'  => $job
                ];
                $this->mailer->send($email, $name, $subject, 'emails.job-cancel-translator', $dataEmail);
            }
            $job->save();
            return true;
        }
        return false;
    }

        /**
     * @param $job
     * @param $data
     * @param $changedTranslator
     * @return bool
     */
    private function changePendingStatus($job, $data, $changedTranslator)
    {
//        if (in_array($data['status'], ['withdrawnbefore24', 'withdrawafter24', 'timedout', 'assigned'])) {
        $job->status = $data['status'];
        if ($data['admin_comments'] == '' && $data['status'] == 'timedout') return false;
        $job->admin_comments = $data['admin_comments'];
        $user = $job->user()->first();
        if (!empty($job->user_email)) {
            $email = $job->user_email;
        } else {
            $email = $user->email;
        }
        $name = $user->name;
        $dataEmail = [
            'user' => $user,
            'job'  => $job
        ];

        if ($data['status'] == 'assigned' && $changedTranslator) {

            $job->save();
            $job_data = $this->jobToData($job);

            $subject = 'Bekräftelse - tolk har accepterat er bokning (bokning # ' . $job->id . ')';
            $this->mailer->send($email, $name, $subject, 'emails.job-accepted', $dataEmail);

            $translator = Job::getJobsAssignedTranslatorDetail($job);
            $this->mailer->send($translator->email, $translator->name, $subject, 'emails.job-changed-translator-new-translator', $dataEmail);

            $language = TeHelper::fetchLanguageFromJobId($job->from_language_id);

            $this->sendSessionStartRemindNotification($user, $job, $language, $job->due, $job->duration);
            $this->sendSessionStartRemindNotification($translator, $job, $language, $job->due, $job->duration);
            return true;
        } else {
            $subject = 'Avbokning av bokningsnr: #' . $job->id;
            $this->mailer->send($email, $name, $subject, 'emails.status-changed-from-pending-or-assigned-customer', $dataEmail);
            $job->save();
            return true;
        }


//        }
        return false;
    }


        /**
     * @param $job
     * @param $data
     * @return bool
     */
    private function changeStartedStatus($job, $data)
    {
        $job->status = $data['status'];
        
        if ($data['admin_comments'] === '') {
            return false;
        }
        
        $job->admin_comments = $data['admin_comments'];
    
        if ($data['status'] == 'completed') {
            if ($data['sesion_time'] === '') {
                return false;
            }
    
            $user = $job->user()->first();
            $interval = $data['sesion_time'];
            $diff = explode(':', $interval);
            $job->end_at = now();
            $job->session_time = $interval;
            $session_time = $diff[0] . ' tim ' . $diff[1] . ' min';
    
            $email = !empty($job->user_email) ? $job->user_email : $user->email;
            $name = $user->name;
            $dataEmail = [
                'user'         => $user,
                'job'          => $job,
                'session_time' => $session_time,
                'for_text'     => 'faktura'
            ];
    
            $subject = 'Information om avslutad tolkning för bokningsnummer #' . $job->id;
            $this->mailer->send($email, $name, $subject, 'emails.session-ended', $dataEmail);
    
            $translatorJobRel = $job->translatorJobRel->where('completed_at', null)->where('cancel_at', null)->first();
    
            if ($translatorJobRel) {
                $email = $translatorJobRel->user->email;
                $name = $translatorJobRel->user->name;
                $subject = 'Information om avslutad tolkning för bokningsnummer # ' . $job->id;
                $dataEmail = [
                    'user'         => $translatorJobRel->user,
                    'job'          => $job,
                    'session_time' => $session_time,
                    'for_text'     => 'lön'
                ];
                $this->mailer->send($email, $name, $subject, 'emails.session-ended', $dataEmail);
            }
        }
    
        $job->save();
        return true;
    }

        /**
     * @param $job
     * @param $data
     * @param $changedTranslator
     * @return bool
     */
    private function changeTimedoutStatus($job, $data, $changedTranslator)
    {
        $old_status = $job->status;
        $job->status = $data['status'];
        $user = $job->user()->first();
        $email = !empty($job->user_email) ? $job->user_email : $user->email;
        $name = $user->name;
        $dataEmail = ['user' => $user, 'job' => $job];
    
        if ($data['status'] == 'pending') {
            $this->handlePendingStatus($job, $email, $name, $dataEmail);
            return true;
        } elseif ($changedTranslator) {
            $this->handleChangedTranslator($job, $email, $name, $dataEmail);
            return true;
        }
    
        return false;
    }
    
    private function handlePendingStatus($job, $email, $name, $dataEmail)
    {
        $job->created_at = now();
        $job->emailsent = 0;
        $job->emailsenttovirpal = 0;
        $job->save();
    
        $job_data = $this->jobToData($job);
        $subject = 'Vi har nu återöppnat er bokning av ' . TeHelper::fetchLanguageFromJobId($job->from_language_id) . 'tolk för bokning #' . $job->id;
        $this->mailer->send($email, $name, $subject, 'emails.job-change-status-to-customer', $dataEmail);
    
        $this->sendNotificationTranslator($job, $job_data, '*'); // send Push all suitable translators
    }
    
    private function handleChangedTranslator($job, $email, $name, $dataEmail)
    {
        $job->save();
        $subject = 'Bekräftelse - tolk har accepterat er bokning (bokning # ' . $job->id . ')';
        $this->mailer->send($email, $name, $subject, 'emails.job-accepted', $dataEmail);
    }
    

    /**
     * @param $job
     * @param $data
     * @return bool
     */
    private function changeCompletedStatus($job, $data)
    {
        $job->status = $data['status'];
    
        if ($data['status'] == 'timedout' && empty($data['admin_comments'])) {
            return false;
        }
    
        if ($data['status'] == 'timedout') {
            $job->admin_comments = $data['admin_comments'];
        }
    
        $job->save();
        return true;
    }


    
    /**
     * @param $job
     * @param $data
     * @param $changedTranslator
     * @return array
     */
    public function changeStatus($job, $data, $changedTranslator)
    {
        $old_status = $job->status;
    
        if ($old_status == $data['status']) {
            return ['statusChanged' => false];
        }
    
        switch ($job->status) {
            case 'timedout':
                $statusChanged = $this->changeTimedoutStatus($job, $data, $changedTranslator);
                break;
            case 'completed':
                $statusChanged = $this->changeCompletedStatus($job, $data);
                break;
            case 'started':
                $statusChanged = $this->changeStartedStatus($job, $data);
                break;
            case 'pending':
                $statusChanged = $this->changePendingStatus($job, $data, $changedTranslator);
                break;
            case 'withdrawafter24':
                $statusChanged = $this->changeWithdrawafter24Status($job, $data);
                break;
            case 'assigned':
                $statusChanged = $this->changeAssignedStatus($job, $data);
                break;
            default:
                $statusChanged = false;
                break;
        }
    
        if ($statusChanged) {
            $log_data = [
                'old_status' => $old_status,
                'new_status' => $data['status']
            ];
    
            return ['statusChanged' => true, 'log_data' => $log_data];
        }
    
        return ['statusChanged' => false];
    }
}