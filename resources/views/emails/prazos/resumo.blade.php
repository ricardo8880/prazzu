@php
    $brand = $whiteLabelEmail ?? \App\Support\WhiteLabelSettings::emailBrandDataForEmpresaId($responsavel->empresa_id ?? auth()->user()?->empresa_id);
    $panelUrl = url('/admin/item-controles');
@endphp

@component('mail::message')
<div style="font-family: Arial, Helvetica, sans-serif; color: #111827;">
    @if(! empty($brand['logo']))
        <div style="text-align: center; margin: 0 0 24px;">
            <img src="{{ $brand['logo'] }}" alt="{{ $brand['nome_sistema'] }}" style="max-width: 180px; max-height: 76px; object-fit: contain;">
        </div>
    @endif

    <div style="border: 1px solid #e5e7eb; border-radius: 18px; overflow: hidden; background: #ffffff;">
        <div style="background: linear-gradient(135deg, {{ $brand['cor'] }}, {{ $brand['cor_escura'] }}); padding: 22px 24px; color: {{ $brand['cor_texto_botao'] }};">
            <p style="margin: 0 0 6px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; opacity: .9;">Resumo de prazos</p>
            <h1 style="margin: 0; font-size: 24px; line-height: 1.25; font-weight: 800; color: {{ $brand['cor_texto_botao'] }};">Olá, {{ $responsavel->nome }}!</h1>
            <p style="margin: 10px 0 0; font-size: 15px; line-height: 1.6; color: {{ $brand['cor_texto_botao'] }}; opacity: .95;">Existem pendências que precisam da sua atenção em {{ $brand['nome_sistema'] }}.</p>
        </div>

        <div style="padding: 24px;">
            <p style="margin: 0 0 18px; font-size: 15px; line-height: 1.7; color: #374151;">Abaixo estão os itens vencidos ou próximos do vencimento. Acesse o painel para revisar responsáveis, datas e próximos passos.</p>

            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin: 0 0 22px; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden;">
                <thead>
                    <tr style="background: {{ $brand['cor_suave'] }}; color: #111827;">
                        <th align="left" style="padding: 12px; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #e5e7eb;">Item</th>
                        <th align="left" style="padding: 12px; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #e5e7eb;">Tipo</th>
                        <th align="left" style="padding: 12px; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #e5e7eb;">Vencimento</th>
                        <th align="left" style="padding: 12px; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #e5e7eb;">Situação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($itens as $item)
                        <tr>
                            <td style="padding: 12px; font-size: 14px; color: #111827; border-bottom: 1px solid #f3f4f6;">{{ $item->descricao ?? $item->titulo ?? 'Item sem descrição' }}</td>
                            <td style="padding: 12px; font-size: 14px; color: #374151; border-bottom: 1px solid #f3f4f6;">{{ ucfirst((string) ($item->tipo ?? '-')) }}</td>
                            <td style="padding: 12px; font-size: 14px; color: #374151; border-bottom: 1px solid #f3f4f6;">{{ optional($item->data_vencimento)->format('d/m/Y') ?? '-' }}</td>
                            <td style="padding: 12px; font-size: 14px; color: #111827; border-bottom: 1px solid #f3f4f6;"><strong>{{ $item->situacao_vencimento ?? ucfirst(str_replace('_', ' ', (string) ($item->status ?? '-'))) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="text-align: center; margin: 24px 0 6px;">
                <a href="{{ $panelUrl }}" style="display: inline-block; background: linear-gradient(135deg, {{ $brand['cor'] }}, {{ $brand['cor_escura'] }}); color: {{ $brand['cor_texto_botao'] }}; text-decoration: none; font-weight: 800; padding: 13px 22px; border-radius: 999px; box-shadow: 0 12px 24px rgba(15, 23, 42, .16);">Acessar painel de controle</a>
            </div>
        </div>
    </div>

    <p style="margin: 24px 0 4px; color: #6b7280; font-size: 13px; line-height: 1.6;">Este é um e-mail automático. Por favor, não responda.</p>
    <p style="margin: 0; color: #374151; font-size: 14px; line-height: 1.6;">Atenciosamente,<br><strong>{{ $brand['assinatura'] }}</strong></p>
</div>
@endcomponent
