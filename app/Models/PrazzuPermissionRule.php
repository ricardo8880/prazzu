<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PrazzuPermissionRule extends Model{protected $table='prazzu_permission_rules';protected $fillable=['role','module','can_view','can_create','can_update','can_delete','scope'];protected $casts=['can_view'=>'boolean','can_create'=>'boolean','can_update'=>'boolean','can_delete'=>'boolean'];}
