<?php namespace App\Library\Notification;

use Carbon\Carbon;
use Auth;

trait Notifier {

    /**
     * Notify this user directly
     * @param string
     */
    public function notifyDirectly($subtype = null) {
        $this->notificationsAsModel()->create([
            'subtype' => $subtype,
            'user_id' => $this->id,
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
        $this->others()->each(function($user) use ($subtype) {
            $this->notifications()->create([
                'subtype' => $subtype,
                'user_id' => $user->id,
            ]);
        });

        return $this;
    }

    /**
     * Notiify anyone related to this model a maximum of one time
     * @param string
     */
    public function notifyOthersOnce($subtype = null) {
        $this->others()->each(function($user) use ($subtype) {
            $this->notifications()->updateOrCreate([
                'subtype' => $subtype,
                'user_id' => $user->id,
                'seen' => $user->id === Auth::id(),
                'created_at' => Carbon::now(),
                'created_by' => Auth::id()
            ]);
        });

        return $this;
    }

    /**
     * Get everyone realted to this model except the current user
     * @return Illuminate\Database\Eloquent\Collection
     */
    protected function others() {
        return $this
            ->users()
            ->where('id', '!=', Auth::id())
            ->get();
    }
}
