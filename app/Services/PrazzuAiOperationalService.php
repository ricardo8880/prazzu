<?php
namespace App\Services;

use App\Support\CachedSchema;
use Illuminate\Support\Facades\DB;use Illuminate\Support\Facades\Schema;
class PrazzuAiOperationalService{public function questions():array{return ['Quais contratos vencem este mês?','Quais clientes estão em atraso?','Quais tarefas estão atrasadas?','Quais documentos aguardam aprovação?'];}public function resumo():array{return ['tarefas_atrasadas'=>CachedSchema::hasTable('item_controles')?DB::table('item_controles')->whereDate('data_vencimento','<',now()->toDateString())->count():0,'clientes_inadimplentes'=>CachedSchema::hasTable('pagamentos')?DB::table('pagamentos')->whereIn('status',['OVERDUE','PAYMENT_OVERDUE'])->count():0];}}
