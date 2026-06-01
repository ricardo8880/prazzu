<?php

namespace App\Console\Commands;


use App\Support\CachedSchema;
use App\Models\ItemControle;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProcessarCentroOperacional extends Command
{
    protected $signature = 'centro-operacional:processar {--silent-notifications : Evita excesso de saída no scheduler}';

    protected $description = 'Processa automações do Centro Operacional: atrasos, prioridade e comentários operacionais.';

    public function handle(): int
    {
        $atrasados = $this->processarAtrasados();
        $priorizados = $this->processarCorrecoesParadas();

        if (! $this->option('silent-notifications')) {
            $this->info("Centro Operacional processado. Atrasados: {$atrasados}. Priorizados: {$priorizados}.");
        }

        return self::SUCCESS;
    }

    protected function processarAtrasados(): int
    {
        $query = ItemControle::query()
            ->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->with('responsavel:id,nome,user_id');

        $count = 0;

        $query->chunkById(100, function ($items) use (&$count): void {
            foreach ($items as $item) {
                $this->registrarComentarioUnico(
                    $item,
                    '@' . ($item->responsavel?->nome ?: 'responsável') . ', esta tarefa entrou no radar de atrasos do Centro Operacional.',
                    'alerta_atraso_centro_operacional'
                );

                if (CachedSchema::hasColumn('item_controles', 'urgencia') && ! in_array((string) $item->urgencia, ['alta', 'critica'], true)) {
                    $item->forceFill(['urgencia' => 'alta'])->saveQuietly();
                }

                $count++;
            }
        });

        return $count;
    }

    protected function processarCorrecoesParadas(): int
    {
        $marco = now()->subDay();
        $statusAtColumn = CachedSchema::hasColumn('item_controles', 'status_operacional_at');

        $query = ItemControle::query()
            ->whereIn('status', ['correcao_necessaria', 'reprovado'])
            ->where(function (Builder $query) use ($statusAtColumn, $marco): void {
                if ($statusAtColumn) {
                    $query->whereNotNull('status_operacional_at')
                        ->where('status_operacional_at', '<=', $marco);
                    return;
                }

                $query->where('updated_at', '<=', $marco);
            });

        $payload = [];
        if (CachedSchema::hasColumn('item_controles', 'urgencia')) {
            $payload['urgencia'] = 'critica';
        }
        if (CachedSchema::hasColumn('item_controles', 'prioridade')) {
            $payload['prioridade'] = 'urgente';
        }

        if (empty($payload)) {
            return 0;
        }

        $items = $query->select(['id'])->pluck('id');

        foreach ($items->chunk(100) as $ids) {
            ItemControle::query()->whereIn('id', $ids)->update($payload);
        }

        foreach ($items as $id) {
            $item = ItemControle::query()->find($id);
            if ($item) {
                $this->registrarComentarioUnico(
                    $item,
                    'Correção parada há mais de 24h. Prioridade alterada automaticamente para Crítica/Urgente pelo Centro Operacional.',
                    'prioridade_correcao_24h'
                );
            }
        }

        return $items->count();
    }

    protected function registrarComentarioUnico(ItemControle $item, string $comentario, string $chave): void
    {
        if (CachedSchema::hasTable('item_controle_comentarios')) {
            $existe = DB::table('item_controle_comentarios')
                ->where('item_controle_id', $item->id)
                ->where('tipo', $chave)
                ->exists();

            if (! $existe) {
                DB::table('item_controle_comentarios')->insert([
                    'item_controle_id' => $item->id,
                    'user_id' => null,
                    'tipo' => $chave,
                    'comentario' => $comentario,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } elseif (CachedSchema::hasTable('comentarios')) {
            $existe = DB::table('comentarios')
                ->where('item_controle_id', $item->id)
                ->where('comentario', $comentario)
                ->exists();

            if (! $existe) {
                DB::table('comentarios')->insert([
                    'item_controle_id' => $item->id,
                    'user_id' => null,
                    'comentario' => $comentario,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $item->registrarTimeline('automacao', 'Centro Operacional atualizou o item', $comentario);
    }
}
