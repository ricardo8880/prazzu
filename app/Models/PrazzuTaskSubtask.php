<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PrazzuTaskSubtask extends Model{protected $table='prazzu_task_subtasks';protected $fillable=['item_controle_id','title','description','status','priority','assigned_to','due_date','completed_at'];protected $casts=['due_date'=>'date','completed_at'=>'datetime'];}
