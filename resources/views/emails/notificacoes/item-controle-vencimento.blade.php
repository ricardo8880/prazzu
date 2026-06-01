@php
    $brand = $whiteLabelEmail ?? \App\Support\WhiteLabelSettings::emailBrandDataForEmpresaId($item->empresa_id ?? auth()->user()?->empresa_id);
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
            <p style="margin: 0 0 6px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; opacity: .9;">Alerta operacional</p>
            <h1 style="margin: 0; font-size: 24px; line-height: 1.25; font-weight: 800; color: {{ $brand['cor_texto_botao'] }};">{{ $assunto }}</h1>
            <p style="margin: 10px 0 0; font-size: 15px; line-height: 1.6; color: {{ $brand['cor_texto_botao'] }}; opacity: .95;">{{ $mensagem }}</p>
        </div>

        <div style="padding: 24px;">
            @if(! empty($destinatarioLabel))
                <p style="margin: 0 0 14px; padding: 10px 12px; border-radius: 12px; background: {{ $brand['cor_suave'] }}; color: #111827; font-size: 14px;"><strong>Perfil avisado:</strong> {{ $destinatarioLabel }}</p>
            @endif

            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin: 0 0 22px; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden;">
                <tbody>
                    <tr>
                        <td style="padding: 12px; width: 34%; background: #f9fafb; color: #6b7280; font-size: 13px; font-weight: 700; border-bottom: 1px solid #eef2f7;">Título</td>
                        <td style="padding: 12px; color: #111827; font-size: 14px; border-bottom: 1px solid #eef2f7;">{{ $item->titulo }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; background: #f9fafb; color: #6b7280; font-size: 13px; font-weight: 700; border-bottom: 1px solid #eef2f7;">Status</td>
                        <td style="padding: 12px; color: #111827; font-size: 14px; border-bottom: 1px solid #eef2f7;">{{ $statusFormatado }}</td>
                    </tr>
                    @if(! empty($dataVencimentoFormatada))
                        <tr>
                            <td style="padding: 12px; background: #f9fafb; color: #6b7280; font-size: 13px; font-weight: 700; border-bottom: 1px solid #eef2f7;">Vencimento</td>
                            <td style="padding: 12px; color: #111827; font-size: 14px; border-bottom: 1px solid #eef2f7;">{{ $dataVencimentoFormatada }}</td>
                        </tr>
                    @endif
                    @if(! empty($item->tipo))
                        <tr>
                            <td style="padding: 12px; background: #f9fafb; color: #6b7280; font-size: 13px; font-weight: 700;">Tipo</td>
                            <td style="padding: 12px; color: #111827; font-size: 14px;">{{ ucfirst((string) $item->tipo) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div style="text-align: center; margin: 24px 0 6px;">
                <a href="{{ $url }}" style="display: inline-block; background: linear-gradient(135deg, {{ $brand['cor'] }}, {{ $brand['cor_escura'] }}); color: {{ $brand['cor_texto_botao'] }}; text-decoration: none; font-weight: 800; padding: 13px 22px; border-radius: 999px; box-shadow: 0 12px 24px rgba(15, 23, 42, .16);">Abrir item no painel</a>
            </div>
        </div>
    </div>

    <p style="margin: 24px 0 4px; color: #6b7280; font-size: 13px; line-height: 1.6;">Este é um aviso automático do sistema.</p>
    <p style="margin: 0; color: #374151; font-size: 14px; line-height: 1.6;">Atenciosamente,<br><strong>{{ $brand['assinatura'] }}</strong></p>
</div>
@endcomponent
