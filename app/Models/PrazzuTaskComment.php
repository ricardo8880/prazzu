<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PrazzuTaskComment extends Model{protected $table='prazzu_task_comments';protected $fillable=['item_controle_id','user_id','parent_id','comment','mentions','is_internal'];protected $casts=['mentions'=>'array','is_internal'=>'boolean'];}
