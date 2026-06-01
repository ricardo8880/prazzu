<?php

namespace App\Notifications;

use App\Models\ItemControle;
use App\Support\WhiteLabelSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ItemControleVencimentoNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ItemControle $item,
        public string $tipo,
        public ?string $destinatarioLabel = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    protected function getAssunto(): string
    {
        return match ($this->tipo) {
            '3_dias' => 'Item de controle vence em 3 dias',
            'hoje' => 'Item de controle vence hoje',
            'vencido' => 'Item de controle vencido',
            'lembrete_recorrente' => 'Lembrete recorrente de item vencido',
            default => 'Alerta de item de controle',
        };
    }

    protected function getMensagem(): string
    {
        return match ($this->tipo) {
            '3_dias' => "O item \"{$this->item->titulo}\" vence em 3 dias.",
            'hoje' => "O item \"{$this->item->titulo}\" vence hoje.",
            'vencido' => "O item \"{$this->item->titulo}\" está vencido.",
            'lembrete_recorrente' => "O item \"{$this->item->titulo}\" continua vencido e ainda precisa de ação.",
            default => "Há uma atualização no item \"{$this->item->titulo}\".",
        };
    }

    protected function getUrl(): string
    {
        return url('/admin/item-controles/' . $this->item->id . '/edit');
    }

    protected function getStatusFormatado(): string
    {
        return match ((string) $this->item->status) {
            'pendente' => 'Pendente',
            'em_andamento' => 'Em andamento',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            'vencido' => 'Vencido',
            default => ucfirst(str_replace('_', ' ', (string) $this->item->status)),
        };
    }

    protected function getDataVencimentoFormatada(): ?string
    {
        return optional($this->item->data_vencimento)?->format('d/m/Y');
    }

    protected function getEmpresaId(object $notifiable): ?int
    {
        return $this->item->empresa_id
            ?? $this->item->responsavel?->empresa_id
            ?? $notifiable->empresa_id
            ?? auth()->user()?->empresa_id;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $whiteLabelEmail = WhiteLabelSettings::emailBrandDataForEmpresaId(
            $this->getEmpresaId($notifiable)
        );

        return (new MailMessage)
            ->from(
                $whiteLabelEmail['remetente_email'],
                $whiteLabelEmail['remetente_nome']
            )
            ->subject($this->getAssunto())
            ->markdown('emails.notificacoes.item-controle-vencimento', [
                'whiteLabelEmail' => $whiteLabelEmail,
                'item' => $this->item,
                'tipo' => $this->tipo,
                'assunto' => $this->getAssunto(),
                'mensagem' => $this->getMensagem(),
                'destinatarioLabel' => $this->destinatarioLabel,
                'statusFormatado' => $this->getStatusFormatado(),
                'dataVencimentoFormatada' => $this->getDataVencimentoFormatada(),
                'url' => $this->getUrl(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'item_id' => $this->item->id,
            'titulo' => $this->item->titulo,
            'status' => $this->item->status,
            'status_formatado' => $this->getStatusFormatado(),
            'tipo' => $this->tipo,
            'destinatario_label' => $this->destinatarioLabel,
            'data_vencimento' => optional($this->item->data_vencimento)?->format('Y-m-d'),
            'data_vencimento_formatada' => $this->getDataVencimentoFormatada(),
            'url' => $this->getUrl(),
            'mensagem' => $this->getMensagem(),
            'assunto' => $this->getAssunto(),
        ];
    }
}
