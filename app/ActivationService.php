<?php namespace App;

use App\Mail\AccountActivation;
use Carbon\Carbon;
use Mail;

class ActivationService {

    protected $activationRepo;

    // Number of hours to wait before resending the email
    protected $resendAfter = 2;

    public function __construct(ActivationRepository $activationRepo) {
        $this->activationRepo = $activationRepo;
    }

    public function sendActivationMail($user) {
        if ($user->activated) {
            return [
                'success' => false,
                'response' => 'Your account is already activated'
            ];
        }

        if (!$this->shouldSend($user)) {
             return [
                'success' => false,
                'response' => 'We recently sent you an activation email. Please wait a few hours before requesting another.'
            ];
        }

        $token = $this->activationRepo->createActivation($user);
        $link = route('user.activate', $token);

        Mail::to($user)->send(new AccountActivation($link));

        return [
            'success' => true,
            'response' => 'Your activation email has been resent'
        ];
    }

    public function activateUser($token) {
        $activation = $this->activationRepo->getActivationByToken($token);

        if ($activation === null) {
            return null;
        }

        $user = User::find($activation->user_id);
        $user->activated = true;
        $user->save();

        $this->activationRepo->deleteActivation($token);

        return $user;
    }

    private function shouldSend($user) {
        $activation = $this->activationRepo->getActivation($user);

        if ($activation === null) {
            return true;
        }

        $lastSent = Carbon::parse($activation->updated_at, 'America/New_York')
            ->addHours($this->resendAfter)
            ->timestamp;

        $now = Carbon::now('America/New_York')
            ->timestamp;

        return $lastSent < $now;
    }
}
