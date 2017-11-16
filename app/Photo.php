<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Photo extends Model {

    protected $fillable = ['path', 'related_id', 'related_type'];

    public static function boot() {
        parent::boot();

        static::creating(function($table) {
            $table->created_by = Auth::id();
        });
    }

    public function relations() {
        return $this->morphTo();
    }

    public function getThumbnailPathAttribute() {
        preg_match('/^([\w:\-\/]*\/[\w-]*)\.(\w{1,5})$/', $this->path, $matches);

        return asset('storage/' . $matches[1] . '-thumb.' . $matches[2]);
    }
}
