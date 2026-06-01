<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PrazzuBillingRule extends Model { protected $table='prazzu_billing_rules'; protected $casts=['active'=>'boolean']; protected $fillable=['name','days_after_due','action_type','message','active']; }
