<?php namespace App\Library\Trip;

use App\Trip;
use App\TripUserSetting;
use Auth;
use Carbon\Carbon;

class ActivityFeed {

    // Period after
    protected $afterDate;

    protected $dateComparison = '<=';

    protected $activities;

    protected $amount = 15;

    protected $trip;

    public function __construct(Trip $trip) {
        $this->trip = $trip;
        $this->afterDate = Carbon::now();
    }

    public function after(Carbon $datetime) {
        $this->afterDate = $datetime;
        $this->dateComparison = "<";

        return $this;
    }

    public function take($amount) {
        $this->amount = $amount;
        $transactions = $this->transactions();
        $posts = $this->posts();

        return $this->merge($transactions, $posts);
    }

    protected function transactions() {
        $private = TripUserSetting::where([
            'trip_id' => $this->trip->id,
            'private_transactions' => true,
            ['user_id', '!=', Auth::id()]
        ])->pluck('user_id');

        return $this->trip->transactions
        ->where('created_at', $this->dateComparison, $this->afterDate)
        ->sortByDesc('created_at')
        ->reject(function($transaction) use ($private) {
            return $transaction->users->count() === 1
                && $transaction->created_by === $transaction->users->first()->id
                && $private->contains($transaction->users->first()->id);
        })
        ->map(function($transaction) {
            return $this->mapTransactions($transaction);
        });
    }

    protected function posts() {
        return $this->trip->posts
        ->where('created_at', $this->dateComparison, $this->afterDate)
        ->sortByDesc('created_at')
        ->take($this->amount)
        ->map(function($post) {
            return $this->mapPosts($post);
        });
    }

    protected function mapPosts($post) {
        return (object) [
            'type' => 'post',
            'id' => $post->id,
            'poster' => $post->user->fullname,
            'created_at' => $post->created_at,
            'editable' => $post->created_by === Auth::id(),
            'content' => $post->content,
            'dateForHumans' => $post->created_at->diffForHumans()
        ];
    }

    protected function mapTransactions($transaction) {
        return (object) [
            'type' => 'transaction',
            'id' => $transaction->id,
            'creator' => $transaction->creator->fullname,
            'updater' => $transaction->updater->fullname,
            'created_at' => $transaction->created_at,
            'date' => $transaction->dateFormat,
            'dateForHumans' => $transaction->created_at->diffForHumans(),
            'updatedDateForHumans' => $transaction->updated_at->diffForHumans(),
            'description' => $transaction->description,
            'amount' => $transaction->amount,
            'hashtags' => $transaction->hashtags->pluck('tag')->toArray()
        ];
    }

    protected function merge($transactions, $posts) {
        // Cannot merge into empty collection
        $activities = $transactions->isEmpty() ?
            $posts : $transactions->merge($posts);

        return $activities
            ->sortByDesc('created_at')
            ->take(15)
            ->values();
    }
}
