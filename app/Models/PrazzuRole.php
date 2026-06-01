<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PrazzuRole extends Model { protected $table='prazzu_roles'; protected $casts=['active'=>'boolean']; protected $fillable=['name','description','active']; }
