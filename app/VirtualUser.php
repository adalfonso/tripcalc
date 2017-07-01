<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class VirtualUser extends Model {

    protected $fillable = ['name'];

    public static function boot() {
        parent::boot();

        static::saving(function($table) {
            $table->created_by = Auth::id();
            $table->updated_by = Auth::id();
        });
    }

    public function transactions() {
        return $this->belongsToMany('App\Transaction');
    }

    public function getTypeAttribute() {
        return 'virtual';
    }
}
