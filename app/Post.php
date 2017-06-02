<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Auth;

class Post extends Model {

    protected $fillable = ['content'];

    public static function boot() {
        parent::boot();

        static::saving(function($table) {
            $table->created_by = Auth::id();
        });
    }

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
