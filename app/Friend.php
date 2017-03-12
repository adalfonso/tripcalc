<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    protected $fillable = ['requester_id', 'recipient_id', 'confirmed'];
}
