<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PrazzuAutomationRule extends Model { protected $table='prazzu_automation_rules'; protected $casts=['active'=>'boolean']; protected $fillable=['module','name','trigger_type','condition_field','condition_operator','condition_value','action_type','action_value','active']; }
