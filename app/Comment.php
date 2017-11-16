<?php namespace App;

use Auth;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model {

    protected $fillable = ['content'];

    public static function boot() {
        parent::boot();

        static::creating(function($table) {
            $table->created_by = Auth::id();
            $table->updated_by = Auth::id();
        });

        static::updating(function($table) {
            $table->updated_by = Auth::id();
        });
    }

    public function user() {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function getDiffForHumansAttribute() {
        return $this->created_at->diffForHumans();
    }
}
