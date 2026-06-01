<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PrazzuClientPortalMessage extends Model{protected $table='prazzu_client_portal_messages';protected $fillable=['empresa_id','item_controle_id','user_id','client_name','client_email','message','attachment','read_at'];protected $casts=['read_at'=>'datetime'];}
