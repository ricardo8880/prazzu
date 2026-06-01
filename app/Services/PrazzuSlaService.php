<?php
namespace App\Services;
use Carbon\Carbon;
class PrazzuSlaService{public function status($record):string{if(empty($record->sla_limite_em)&&empty($record->data_vencimento))return 'sem_sla';$limit=Carbon::parse($record->sla_limite_em??$record->data_vencimento);if(!empty($record->sla_concluido_em))return $limit->gte(Carbon::parse($record->sla_concluido_em))?'concluido_no_prazo':'concluido_atrasado';if($limit->isPast())return 'vencido';return $limit->diffInHours(now())<=8?'em_risco':'em_andamento';}}
