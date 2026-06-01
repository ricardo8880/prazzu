<?php

namespace App\Support;

class ClienteProblemaService
{
    public static function detectar(array $cliente): array
    {
        if (!empty($cliente['documento_pendente'])) {
            return ['tipo' => 'documento', 'label' => 'Documento pendente'];
        }

        if (!empty($cliente['aprovacao_pendente'])) {
            return ['tipo' => 'aprovacao', 'label' => 'Aguardando aprovação'];
        }

        if (!empty($cliente['financeiro_atrasado'])) {
            return ['tipo' => 'financeiro', 'label' => 'Pendência financeira'];
        }

        if (!empty($cliente['dias_sem_contato']) && $cliente['dias_sem_contato'] > 7) {
            return ['tipo' => 'contato', 'label' => 'Sem contato recente'];
        }

        return ['tipo' => 'ok', 'label' => 'Sem pendências críticas'];
    }
}
