<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PrazzuSlaRule extends Model{protected $table='prazzu_sla_rules';protected $fillable=['name','module','priority','hours_limit','warning_hours','active'];protected $casts=['active'=>'boolean'];}
