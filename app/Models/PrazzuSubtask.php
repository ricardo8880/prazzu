<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PrazzuSubtask extends Model { protected $table='prazzu_subtasks'; protected $fillable=['item_controle_id','parent_id','title','description','status','priority','assigned_to','due_date','completed_at']; }
