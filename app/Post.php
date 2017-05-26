<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Post extends Model {

    protected $fillable = ['content'];

    public function postable() {
        return $this->morphTo();
    }

    public function user() {
        return $this->belongsTo('App\User', 'created_by')
            ->select('id', 'first_name', 'last_name');
    }

    public function getDiffForHumansAttribute() {
        return $this->created_at->diffForHumans();
    }
}
