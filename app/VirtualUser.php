<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class VirtualUser extends Model {

    protected $fillable = ['name'];

    public static function boot() {
        parent::boot();

        static::creating(function($table) {
            $table->created_by = Auth::id();
            $table->updated_by = Auth::id();
        });
    }

    public function transactions() {
        return $this->belongsToMany('App\Transaction')
            ->withPivot('split_ratio', 'virtual_user_id');
    }

    public function getTypeAttribute() {
        return 'virtual';
    }
}
