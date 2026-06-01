<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PrazzuTaskDependency extends Model{protected $table='prazzu_task_dependencies';protected $fillable=['item_controle_id','depends_on_item_controle_id','dependency_type','notes'];}
