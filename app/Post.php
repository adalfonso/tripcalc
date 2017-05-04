<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {

    protected $fillable = ['content'];

    public function user() {
        return $this->belongsTo('App\User', 'created_by')
            ->select('id', 'first_name', 'last_name');
    }

    public function getDateFormatAttribute() {
        return \Carbon\Carbon::parse($this->date)->format('M d, Y');
    }

}
