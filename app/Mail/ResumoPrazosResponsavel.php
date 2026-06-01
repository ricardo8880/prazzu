<?php

namespace App\Mail;

use App\Support\WhiteLabelSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResumoPrazosResponsavel extends Mailable
{
    use Queueable, SerializesModels;

    public $responsavel;
    public $itens;

    public array $whiteLabelEmail;

    public function __construct($responsavel, $itens)
    {
        $this->responsavel = $responsavel;
        $this->itens = $itens;
        $this->whiteLabelEmail = WhiteLabelSettings::emailBrandDataForEmpresaId(
            $responsavel->empresa_id ?? auth()->user()?->empresa_id
        );
    }

    public function build()
    {
        return $this
            ->from(
                $this->whiteLabelEmail['remetente_email'],
                $this->whiteLabelEmail['remetente_nome']
            )
            ->subject('⚠️ Alerta de Prazos: Itens Vencidos ou Próximos')
            ->markdown('emails.prazos.resumo', [
                'whiteLabelEmail' => $this->whiteLabelEmail,
            ]);
    }
}
