<?php namespace App\Library\Notification;

use Carbon\Carbon;
use Auth;
use App\User;

trait Notifier {

    /**
     * Notify oen user
     * @param string
     */
    public function notifyOne(User $user, $subtype = null) {
        $this->notifications()->create([
            'subtype' => $subtype,
            'user_id' => $user->id,
        ]);

        return $this;
    }

    /**
     * Notiify everyone related to this model
     * @param string
     */
    public function notifyAll($subtype = null) {
        $this->users()->each(function($user) use ($subtype) {
            $this->notifications()->create([
                'subtype' => $subtype,
                'user_id' => $user->id,
            ]);
        });

        return $this;
    }

    /**
     * Notiify anyone related to this model
     * @param string
     */
    public function notifyOthers($subtype = null) {
        $this->others->each(function($user) use ($subtype) {
            $this->notifications()->create([
                'subtype' => $subtype,
                'user_id' => $user->id,
            ]);
        });
        dd($this->others);

        return $this;
    }

    /**
     * Notiify anyone related to this model a maximum of one time
     * @param string
     */
    public function notifyOthersOnce($subtype = null) {
        $this->others->each(function($user) use ($subtype) {
            $this->notifications()->updateOrCreate([
                'subtype' => $subtype,
                'user_id' => $user->id,
                'seen' => $user->id === Auth::id(),
                'created_by' => Auth::id()
            ]);
        });

        return $this;
    }
}
