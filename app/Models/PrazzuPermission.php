<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PrazzuPermission extends Model { protected $table='prazzu_permissions'; protected $fillable=['role_id','name','module','action','scope']; }
