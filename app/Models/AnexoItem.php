<?php

namespace App\Models;

/**
 * Alias legado para manter compatibilidade com código antigo que possa chamar AnexoItem.
 * A tabela real usada pelo sistema para esses registros é "anexos".
 */
class AnexoItem extends Anexo
{
    protected $table = 'anexos';
}
