<x-filament-panels::page>
    @php
        $percent = (int) ($progress['percent'] ?? 0);
        $empresaNome = $empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? 'Empresa não selecionada';
        $empresasLista = collect($empresas ?? []);
        $mensagensChat = collect($chat ?? []);
        $solicitacoesAbertas = collect($supportQueue ?? []);
        $pendenciasCliente = collect($pendingActions ?? []);
        $documentosPublicados = collect($documents ?? []);
        $entregasCalendario = collect($calendar ?? []);
        $timelineAtendimento = collect($timeline ?? []);
        $ultimaMensagem = $mensagensChat->last();
        $ultimaAtualizacao = $ultimaMensagem['created_at_label'] ?? ($solicitacoesAbertas->first()['created_at_label'] ?? 'Sem atualização recente');
        $mensagensCliente = $mensagensChat->where('css_class', 'cliente')->count();
        $mensagensEquipe = $mensagensChat->where('css_class', 'equipe')->count();
        $atendimentoAberto = $mensagensChat->where('conversa_status', 'aberta')->count() > 0 || $solicitacoesAbertas->count() > 0;
        $statusAtendimento = $atendimentoAberto ? ($mensagensCliente > $mensagensEquipe ? 'Aguardando suporte' : 'Em andamento') : 'Sem atendimento aberto';
        $statusClass = $atendimentoAberto ? ($mensagensCliente > $mensagensEquipe ? 'warn' : 'ok') : 'muted';
        $protocolo = 'PC-' . str_pad((string) ($empresaId ?? 0), 5, '0', STR_PAD_LEFT) . '-' . now()->format('Y');
        $responsavel = auth()->user()?->name ?? 'Equipe de suporte';
        $suporteOnline = now()->isWeekday() && now()->hour >= 8 && now()->hour <= 18;
        $canalAtivo = $solicitacoesAbertas->first();
        $prioridadeAtual = $canalAtivo['prioridade'] ?? ($pendenciasCliente->where('is_late', true)->count() ? 'alta' : 'media');
        $prioridadeLabel = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'urgente' => 'Urgente'][$prioridadeAtual] ?? ucfirst((string) $prioridadeAtual);
        $temPendencias = $pendenciasCliente->count() > 0;
        $temSolicitacoes = $solicitacoesAbertas->count() > 0;
        $portalLink = $portalLink ?? null;
        $supportForm = $supportForm ?? ['prioridades' => ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'urgente' => 'Urgente']];
        $clienteVisualizouAteId = $clienteVisualizouAteId ?? ($this->clienteVisualizouAteId ?? null);
        $suporteVisualizouAteId = $suporteVisualizouAteId ?? ($this->suporteVisualizouAteId ?? null);
        $clienteDigitando = $clienteDigitando ?? ($this->clienteDigitando ?? false);
        $clienteDigitandoNome = $clienteDigitandoNome ?? ($this->clienteDigitandoNome ?? null);
    @endphp

    <style>
        .pc-service-shell,
        .pc-service-shell * { box-sizing: border-box; }
        .pc-service-shell {
            --pc-primary: #2563eb;
            --pc-primary-dark: #1d4ed8;
            --pc-surface: #ffffff;
            --pc-soft: #f8fafc;
            --pc-soft-2: #eff6ff;
            --pc-border: #e2e8f0;
            --pc-border-strong: #cbd5e1;
            --pc-text: #0f172a;
            --pc-muted: #64748b;
            --pc-success: #16a34a;
            --pc-warning: #d97706;
            --pc-danger: #dc2626;
            width: 100%;
            max-width: 1720px;
            min-height: calc(100vh - 166px);
            margin: 0 auto;
            color: var(--pc-text);
        }
        .dark .pc-service-shell {
            --pc-surface: #0f172a;
            --pc-soft: #111827;
            --pc-soft-2: rgba(37, 99, 235, .16);
            --pc-border: rgba(148, 163, 184, .22);
            --pc-border-strong: rgba(148, 163, 184, .35);
            --pc-text: #f8fafc;
            --pc-muted: #cbd5e1;
        }
        .pc-workspace {
            display: grid;
            grid-template-columns: minmax(250px, 310px) minmax(560px, 1fr) minmax(300px, 370px);
            gap: 1rem;
            min-height: calc(100vh - 166px);
            align-items: stretch;
        }
        .pc-panel {
            min-width: 0;
            border: 1px solid var(--pc-border);
            border-radius: 1.2rem;
            background: var(--pc-surface);
            box-shadow: 0 18px 44px rgba(15, 23, 42, .055);
            overflow: hidden;
        }
        .dark .pc-panel { box-shadow: 0 18px 45px rgba(0, 0, 0, .25); }
        .pc-main {
            display: grid;
            grid-template-rows: auto auto minmax(0, 1fr) auto auto;
            min-height: calc(100vh - 166px);
            position: relative;
            background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,250,252,.92));
        }
        .pc-inbox,
        .pc-context {
            display: flex;
            flex-direction: column;
            gap: .9rem;
            min-height: 0;
            padding: .9rem;
            background: rgba(255,255,255,.86);
        }
        .dark .pc-inbox,
        .dark .pc-context { background: rgba(15, 23, 42, .86); }
        .pc-inbox { position: sticky; top: 1rem; max-height: calc(100dvh - 92px); overflow-y: auto; }
        .pc-context { position: sticky; top: 1rem; max-height: calc(100dvh - 92px); overflow-y: auto; }
        .pc-section-head { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; }
        .pc-section-head strong { display:block; color:var(--pc-text); font-size:.95rem; font-weight:950; line-height:1.2; }
        .pc-section-head span,
        .pc-muted { color:var(--pc-muted); font-size:.75rem; line-height:1.45; font-weight:750; }
        .pc-company-select { width: 100%; border: 1px solid var(--pc-border); border-radius: .95rem; background: var(--pc-soft); color: var(--pc-text); padding: .72rem .82rem; font-weight: 850; outline: none; }
        .pc-company-select:focus { border-color: var(--pc-primary); box-shadow: 0 0 0 4px rgba(37,99,235,.14); }
        .pc-ticket-list { display:grid; gap:.72rem; }
        .pc-ticket-card {
            width: 100%;
            text-align: left;
            border: 1px solid var(--pc-border);
            border-radius: 1rem;
            background: var(--pc-surface);
            color: var(--pc-text);
            padding: .85rem;
            transition: border-color .18s ease, background .18s ease, transform .18s ease, box-shadow .18s ease;
        }
        .pc-ticket-card.is-active,
        .pc-ticket-card:hover { border-color: rgba(37,99,235,.38); background: linear-gradient(135deg, rgba(37,99,235,.13), rgba(37,99,235,.035)); box-shadow: 0 16px 30px rgba(37,99,235,.10); transform: translateY(-1px); }
        .pc-ticket-top { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom:.45rem; }
        .pc-ticket-top strong { color:var(--pc-text); font-size:.82rem; font-weight:950; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .pc-ticket-card p { margin:0; color:var(--pc-muted); font-size:.78rem; line-height:1.35; font-weight:750; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .pc-ticket-meta { display:flex; align-items:center; justify-content:space-between; gap:.7rem; margin-top:.56rem; color:var(--pc-muted); font-size:.72rem; font-weight:850; }
        .pc-dot { display:inline-flex; align-items:center; gap:.35rem; }
        .pc-dot::before { content:''; width:.48rem; height:.48rem; border-radius:999px; background:var(--pc-success); }
        .pc-dot.warn::before { background:var(--pc-warning); }
        .pc-dot.muted::before { background:#94a3b8; }
        .pc-header {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:1rem;
            padding: 1rem 1.1rem .9rem;
            border-bottom:1px solid var(--pc-border);
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(12px);
        }
        .dark .pc-header { background: rgba(15, 23, 42, .80); }
        .pc-title-wrap { min-width:0; }
        .pc-title-line { display:flex; align-items:center; gap:.65rem; min-width:0; flex-wrap:wrap; }
        .pc-title-line h2 { margin:0; color:var(--pc-text); font-size:1.1rem; line-height:1.2; font-weight:950; letter-spacing:-.025em; }
        .pc-subtitle { margin-top:.25rem; color:var(--pc-muted); font-size:.76rem; font-weight:800; line-height:1.35; }
        .pc-actions { display:flex; align-items:center; justify-content:flex-end; gap:.5rem; flex-wrap:wrap; }
        .pc-btn {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:.45rem;
            min-height:38px;
            border:1px solid transparent;
            border-radius:.82rem;
            background:var(--pc-primary);
            color:#fff;
            padding:.58rem .88rem;
            font-weight:950;
            font-size:.8rem;
            text-decoration:none;
            cursor:pointer;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease;
        }
        .pc-btn:hover, .pc-btn:focus-visible { background:var(--pc-primary-dark); color:#fff; transform:translateY(-1px); box-shadow:0 12px 24px rgba(37,99,235,.18); outline:none; }
        .pc-btn.secondary { background:var(--pc-surface); color:var(--pc-text); border-color:var(--pc-border); }
        .pc-btn.secondary:hover { background:var(--pc-soft); border-color:var(--pc-border-strong); color:var(--pc-text); box-shadow:0 12px 24px rgba(15,23,42,.08); }
        .pc-btn.danger { background:#fff; color:var(--pc-danger); border-color:#fecaca; }
        .pc-btn.danger:hover { background:#fef2f2; color:#b91c1c; box-shadow:0 12px 24px rgba(220,38,38,.10); }
        .pc-btn:disabled { opacity:.55; cursor:not-allowed; transform:none; box-shadow:none; }
        .pc-tabs { display:flex; align-items:center; gap:1.4rem; padding:0 1.1rem; min-height:50px; border-bottom:1px solid var(--pc-border); background:rgba(255,255,255,.92); }
        .dark .pc-tabs { background: rgba(15, 23, 42, .80); }
        .pc-tab { display:inline-flex; align-items:center; height:50px; border-bottom:2px solid transparent; color:var(--pc-muted); font-size:.8rem; font-weight:950; text-decoration:none; }
        .pc-tab.is-active { color:var(--pc-primary); border-bottom-color:var(--pc-primary); }
        .pc-messages {
            display:flex;
            flex-direction:column;
            gap:1rem;
            min-height:0;
            height:100%;
            padding:1.25rem 1.25rem 1rem;
            overflow-y:auto;
            overflow-x:hidden;
            overscroll-behavior:contain;
            background: linear-gradient(180deg, #fbfdff 0%, #f8fbff 52%, #ffffff 100%);
            scroll-behavior:smooth;
        }
        .dark .pc-messages { background: linear-gradient(180deg, rgba(17,24,39,.65), rgba(17,24,39,.92)); }
        .pc-message { display:grid; grid-template-columns:2.35rem minmax(0, min(80%, 780px)); gap:.72rem; align-items:start; justify-content:start; animation:pcMessageIn .22s ease both; }
        .pc-message.cliente { grid-template-columns:2.35rem minmax(0, min(82%, 800px)); justify-content:start; }
        .pc-message-avatar { display:inline-flex; align-items:center; justify-content:center; width:2.35rem; height:2.35rem; border-radius:999px; background:#dbeafe; color:#1d4ed8; font-size:.76rem; font-weight:950; box-shadow:0 6px 14px rgba(37,99,235,.08); }
        .pc-message.equipe .pc-message-avatar { background:#e2e8f0; color:#334155; box-shadow:none; }
        .pc-bubble { min-width:0; max-width:100%; border:1px solid rgba(226,232,240,.95); border-radius:1rem; background:var(--pc-surface); color:var(--pc-text); padding:.9rem 1rem; box-shadow:0 10px 26px rgba(15,23,42,.045); }
        .pc-message.cliente .pc-bubble { background:linear-gradient(135deg, rgba(37,99,235,.12), rgba(219,234,254,.55)); border-color:rgba(37,99,235,.18); box-shadow:0 12px 30px rgba(37,99,235,.08); }
        .pc-bubble-head { display:flex; align-items:center; justify-content:space-between; gap:.8rem; margin-bottom:.42rem; font-size:.72rem; font-weight:950; color:var(--pc-muted); }
        .pc-bubble-head span:first-child { color:var(--pc-text); }
        .pc-bubble-text { white-space:pre-wrap; overflow-wrap:anywhere; font-size:.9rem; line-height:1.54; font-weight:650; }
        .pc-seen-status { display:inline-flex; justify-content:flex-end; width:100%; margin-top:.4rem; color:var(--pc-primary); font-size:.68rem; font-weight:900; opacity:.86; }
        .pc-date-divider { display:flex; align-items:center; gap:.75rem; color:var(--pc-muted); font-size:.68rem; font-weight:950; text-transform:uppercase; letter-spacing:.045em; margin:.15rem 0; }
        .pc-date-divider::before, .pc-date-divider::after { content:''; height:1px; flex:1; background:var(--pc-border); }
        .pc-message-status-line { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-top:.5rem; color:var(--pc-muted); font-size:.68rem; font-weight:850; }
        .pc-message-status-line .pc-seen-status { width:auto; margin:0; }
        .pc-message.is-sending .pc-bubble { opacity:.78; }
        .pc-message.is-failed .pc-bubble { border-color:#fecaca; background:#fff7f7; }
        .pc-attachments { display:grid; gap:.45rem; margin-top:.65rem; }
        .pc-attachment { display:grid; grid-template-columns:1.8rem minmax(0,1fr) auto; gap:.5rem; align-items:center; border-radius:.8rem; padding:.5rem; background:rgba(15,23,42,.045); color:inherit; text-decoration:none; }
        .pc-attachment strong { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:.76rem; color:inherit; }
        .pc-attachment span { display:block; margin-top:.12rem; font-size:.66rem; opacity:.82; }
        .pc-chat-composer { border-top:1px solid var(--pc-border); padding:.75rem 1rem .9rem; background:rgba(255,255,255,.97); position:sticky; bottom:0; z-index:2; box-shadow:0 -12px 32px rgba(15,23,42,.04); }
        .dark .pc-chat-composer { background:rgba(15,23,42,.96); }
        .pc-composer-tabs { display:flex; gap:1.25rem; margin-bottom:.55rem; }
        .pc-composer-tab { appearance:none; border:0; background:transparent; color:var(--pc-muted); font-size:.78rem; font-weight:950; padding:0 0 .45rem; border-bottom:2px solid transparent; cursor:pointer; }
        .pc-composer-tab.is-active { color:var(--pc-primary); border-color:var(--pc-primary); }
        .pc-composer-tab[disabled] { cursor:not-allowed; opacity:.58; }
        .pc-composer-box { display:grid; grid-template-columns:minmax(0,1fr); gap:.45rem; border:1px solid var(--pc-border); border-radius:.95rem; background:var(--pc-surface); padding:.62rem; max-width:100%; box-shadow:0 10px 28px rgba(15,23,42,.045); }
        .pc-composer-box:focus-within { border-color:rgba(37,99,235,.42); box-shadow:0 0 0 4px rgba(37,99,235,.10), 0 12px 28px rgba(37,99,235,.08); }
        .pc-composer-row { display:flex; align-items:center; justify-content:space-between; gap:.65rem; }
        .pc-composer-tools { display:flex; align-items:center; gap:.45rem; min-width:0; flex-wrap:wrap; }
        .pc-file-trigger { position:relative; overflow:hidden; }
        .pc-file-trigger input { position:absolute; inset:0; opacity:0; cursor:pointer; }
        .pc-icon-btn { display:inline-flex; align-items:center; justify-content:center; gap:.35rem; min-height:2.25rem; border-radius:.75rem; border:1px solid transparent; background:transparent; color:var(--pc-muted); padding:.45rem .6rem; font-size:.78rem; font-weight:900; cursor:pointer; }
        .pc-icon-btn:hover { background:var(--pc-soft); color:var(--pc-text); }
        .pc-composer-textarea { width:100%; min-height:3.2rem; max-height:8rem; resize:none; border:0; outline:none; background:transparent; color:var(--pc-text); padding:.25rem .3rem; font-weight:700; line-height:1.45; }
        .pc-upload-list { display:flex; flex-wrap:wrap; gap:.45rem; margin:0 0 .6rem; }
        .pc-upload-pill { display:inline-flex; align-items:center; gap:.35rem; max-width:100%; border-radius:999px; background:#eef2ff; color:#3730a3; padding:.35rem .65rem; font-size:.72rem; font-weight:900; }
        .pc-upload-progress { display:flex; align-items:center; gap:.5rem; margin:0 0 .6rem; color:var(--pc-primary); font-size:.75rem; font-weight:950; }
        .pc-upload-progress i { width:.72rem; height:.72rem; border:2px solid rgba(37,99,235,.25); border-top-color:var(--pc-primary); border-radius:999px; animation:pcSpin .8s linear infinite; }
        .dark .pc-upload-pill { background:rgba(79,70,229,.22); color:#c7d2fe; }
        .pc-composer-helper { display:flex; justify-content:space-between; gap:.75rem; margin-top:.45rem; color:var(--pc-muted); font-size:.7rem; font-weight:800; }
        .pc-send-loading { display:none; align-items:center; gap:.4rem; }
        .pc-send-loading i { width:.72rem; height:.72rem; border:2px solid rgba(255,255,255,.5); border-top-color:#fff; border-radius:999px; animation:pcSpin .8s linear infinite; }
        .pc-error { margin-top:.45rem; color:var(--pc-danger); font-size:.78rem; font-weight:850; }
        .pc-card { border:1px solid var(--pc-border); border-radius:1rem; background:rgba(255,255,255,.96); padding:1rem; }
        .dark .pc-card { background:rgba(15,23,42,.96); }
        .pc-card.soft { background:var(--pc-soft); }
        .pc-card-title { display:block; color:var(--pc-text); font-size:.92rem; font-weight:950; margin-bottom:.7rem; }
        .pc-client-card-head { display:grid; grid-template-columns:3rem minmax(0,1fr) auto; gap:.75rem; align-items:center; }
        .pc-avatar { display:inline-flex; align-items:center; justify-content:center; width:3rem; height:3rem; border-radius:999px; background:#f8c8d6; color:#9f1239; font-weight:950; flex:none; }
        .pc-client-name { min-width:0; }
        .pc-client-name strong { display:block; color:var(--pc-text); font-size:.92rem; font-weight:950; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .pc-client-name span { display:block; margin-top:.16rem; color:var(--pc-muted); font-size:.74rem; font-weight:750; overflow-wrap:anywhere; }
        .pc-stats-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.75rem; margin-top:1rem; padding-top:1rem; border-top:1px solid var(--pc-border); }
        .pc-stat span { display:block; color:var(--pc-muted); font-size:.68rem; font-weight:850; line-height:1.25; }
        .pc-stat strong { display:block; margin-top:.24rem; color:var(--pc-text); font-size:.82rem; font-weight:950; overflow-wrap:anywhere; }
        .pc-info-list { display:grid; gap:.62rem; }
        .pc-info-row { display:flex; align-items:flex-start; justify-content:space-between; gap:.8rem; padding-bottom:.62rem; border-bottom:1px dashed var(--pc-border); }
        .pc-info-row:last-child { border-bottom:0; padding-bottom:0; }
        .pc-info-row span { color:var(--pc-muted); font-size:.74rem; font-weight:850; }
        .pc-info-row strong { color:var(--pc-text); font-size:.78rem; text-align:right; overflow-wrap:anywhere; }
        .pc-quick-actions { display:grid; gap:.55rem; }
        .pc-action-link { display:flex; align-items:center; justify-content:center; gap:.55rem; width:100%; border:1px solid var(--pc-border); border-radius:.82rem; background:var(--pc-surface); color:var(--pc-primary); padding:.66rem .75rem; font-size:.78rem; font-weight:950; text-decoration:none; cursor:pointer; transition:transform .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease; }
        .pc-action-link:hover { background:var(--pc-soft-2); border-color:rgba(37,99,235,.25); transform:translateY(-1px); box-shadow:0 10px 20px rgba(37,99,235,.08); }
        .pc-action-link.danger { color:var(--pc-danger); border-color:#fecaca; }
        .pc-context .pc-card { box-shadow:0 12px 26px rgba(15,23,42,.045); }
        .pc-client-status-row { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-top:.9rem; padding-top:.9rem; border-top:1px solid var(--pc-border); }
        .pc-health-pill { display:inline-flex; align-items:center; gap:.38rem; border-radius:999px; background:#dcfce7; color:#15803d; padding:.38rem .68rem; font-size:.72rem; font-weight:950; }
        .pc-health-pill.warn { background:#fef3c7; color:#92400e; }
        .pc-health-pill.muted { background:#e2e8f0; color:#475569; }
        .pc-next-step { border:1px solid rgba(37,99,235,.20); border-radius:1rem; background:linear-gradient(135deg, rgba(37,99,235,.12), rgba(219,234,254,.34)); padding:1rem; box-shadow:0 16px 36px rgba(37,99,235,.07); }
        .pc-next-step span { display:block; color:var(--pc-primary); font-size:.7rem; font-weight:950; text-transform:uppercase; letter-spacing:.04em; margin-bottom:.34rem; }
        .pc-next-step strong { display:block; color:var(--pc-text); font-size:.9rem; line-height:1.35; font-weight:950; }
        .pc-next-step p { margin:.35rem 0 0; color:var(--pc-muted); font-size:.76rem; line-height:1.4; font-weight:750; }
        .pc-action-group { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.55rem; }
        .pc-action-link.primary { background:var(--pc-primary); border-color:var(--pc-primary); color:#fff; box-shadow:0 12px 24px rgba(37,99,235,.18); }
        .pc-action-link.primary:hover { background:var(--pc-primary-dark); color:#fff; border-color:var(--pc-primary-dark); }
        .pc-action-link.full { grid-column:1 / -1; }
        .pc-mini-timeline { display:grid; gap:.72rem; }
        .pc-mini-event { display:grid; grid-template-columns:.7rem minmax(0,1fr); gap:.6rem; align-items:start; }
        .pc-mini-event::before { content:''; width:.55rem; height:.55rem; border-radius:999px; background:var(--pc-primary); margin-top:.25rem; box-shadow:0 0 0 4px rgba(37,99,235,.12); }
        .pc-mini-event strong { display:block; color:var(--pc-text); font-size:.78rem; font-weight:950; line-height:1.3; overflow-wrap:anywhere; }
        .pc-mini-event span { display:block; margin-top:.12rem; color:var(--pc-muted); font-size:.7rem; font-weight:800; line-height:1.35; }
        .pc-file-list { display:grid; gap:.5rem; }
        .pc-file-row { display:grid; grid-template-columns:2rem minmax(0,1fr) auto; gap:.55rem; align-items:center; border:1px solid var(--pc-border); border-radius:.82rem; padding:.55rem; color:var(--pc-text); text-decoration:none; background:var(--pc-surface); }
        .pc-file-row:hover { border-color:rgba(37,99,235,.28); background:var(--pc-soft-2); }
        .pc-file-row strong { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--pc-text); font-size:.76rem; font-weight:950; }
        .pc-file-row span { display:block; margin-top:.1rem; color:var(--pc-muted); font-size:.67rem; font-weight:800; }
        .pc-progress { height:.65rem; border-radius:999px; background:#e2e8f0; overflow:hidden; margin-top:.65rem; }
        .pc-progress i { display:block; height:100%; width:var(--progress); background:linear-gradient(90deg,#2563eb,#22c55e); border-radius:inherit; }
        .pc-empty { border:1px dashed #94a3b8; border-radius:.9rem; padding:.8rem; background:var(--pc-soft); color:var(--pc-muted); font-size:.82rem; line-height:1.45; font-weight:750; }
        .pc-ticket-form { display:grid; gap:.52rem; }
        .pc-input, .pc-textarea, .pc-select { width:100%; min-width:0; border-radius:.82rem; border:1px solid var(--pc-border); padding:.68rem .74rem; background:var(--pc-surface); color:var(--pc-text); font-weight:700; outline:none; }
        .pc-textarea { min-height:84px; resize:vertical; }
        .pc-input:focus, .pc-textarea:focus, .pc-select:focus { border-color:var(--pc-primary); box-shadow:0 0 0 4px rgba(37,99,235,.13); }
        .pc-badge { display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:.34rem .66rem; font-size:.72rem; line-height:1; font-weight:950; white-space:nowrap; background:#dbeafe; color:#1d4ed8; }
        .pc-badge.ok { background:#dcfce7; color:#15803d; }
        .pc-badge.warn { background:#fef3c7; color:#92400e; }
        .pc-badge.danger { background:#fee2e2; color:#b91c1c; }
        .pc-badge.muted { background:#e2e8f0; color:#475569; }
        .pc-realtime-pill { display:inline-flex; align-items:center; gap:.38rem; color:var(--pc-muted); font-size:.72rem; font-weight:900; }
        .pc-realtime-pill::before { content:''; width:.48rem; height:.48rem; border-radius:999px; background:var(--pc-success); box-shadow:0 0 0 4px rgba(22,163,74,.13); }
        .pc-typing-row { display:flex; align-items:center; gap:.55rem; padding:.35rem 1.15rem .75rem; color:var(--pc-muted); font-size:.78rem; font-weight:850; background:rgba(255,255,255,.97); }
        .dark .pc-typing-row { background:rgba(15,23,42,.96); }
        .pc-typing-dots { display:inline-flex; align-items:center; gap:.22rem; border:1px solid var(--pc-border); border-radius:999px; background:var(--pc-soft); padding:.42rem .62rem; }
        .pc-typing-dots i { display:block; width:.32rem; height:.32rem; border-radius:999px; background:currentColor; opacity:.45; animation:pcTypingPulse 1s infinite ease-in-out; }
        .pc-typing-dots i:nth-child(2) { animation-delay:.14s; }
        .pc-typing-dots i:nth-child(3) { animation-delay:.28s; }
        .pc-loading-overlay { display:none; position:absolute; inset:0; align-items:center; justify-content:center; background:rgba(248,250,252,.68); backdrop-filter:blur(2px); z-index:3; font-weight:950; color:var(--pc-primary); }
        [wire\:loading].pc-loading-overlay { display:flex; }
        .dark .pc-loading-overlay { background:rgba(15,23,42,.64); }
        .pc-messages::-webkit-scrollbar, .pc-inbox::-webkit-scrollbar, .pc-context::-webkit-scrollbar { width:8px; }
        .pc-messages::-webkit-scrollbar-thumb, .pc-inbox::-webkit-scrollbar-thumb, .pc-context::-webkit-scrollbar-thumb { background:rgba(100,116,139,.35); border-radius:999px; }
        [x-cloak] { display:none !important; }
        @keyframes pcTypingPulse { 0%, 80%, 100% { transform:translateY(0); opacity:.35; } 40% { transform:translateY(-3px); opacity:.9; } }
        @keyframes pcMessageIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
        @keyframes pcSpin { to { transform:rotate(360deg); } }


        /* Ajuste pontual: bloco "Meus atendimentos" fiel ao modelo visual gerado */
        .pc-inbox {
            padding: 1rem;
            gap: .9rem;
            background: #ffffff;
            border-radius: 1.15rem;
            overflow-x: hidden;
        }
        .dark .pc-inbox { background: rgba(15, 23, 42, .96); }
        .pc-inbox-model-head {
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:.75rem;
            padding:.1rem .05rem 0;
            min-width:0;
        }
        .pc-inbox-model-title {
            min-width:0;
        }
        .pc-inbox-model-title strong {
            display:block;
            color:var(--pc-text);
            font-size:1rem;
            line-height:1.05;
            font-weight:950;
            letter-spacing:-.025em;
        }
        .pc-inbox-model-title span {
            display:block;
            margin-top:.28rem;
            color:#64748b;
            font-size:.76rem;
            line-height:1.15;
            font-weight:850;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
            max-width:13.25rem;
        }
        .pc-inbox-filter {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            flex:0 0 auto;
            width:2rem;
            height:2rem;
            border:0;
            border-radius:.78rem;
            background:transparent;
            color:#64748b;
            cursor:pointer;
            transition:background .18s ease, color .18s ease, transform .18s ease;
        }
        .pc-inbox-filter:hover {
            background:#f1f5f9;
            color:#0f172a;
            transform:translateY(-1px);
        }
        .pc-inbox-filter svg {
            width:1rem;
            height:1rem;
        }
        .pc-inbox-model-select-wrap {
            display:grid;
            gap:.35rem;
            min-width:0;
        }
        .pc-company-select.pc-company-select-model {
            border-radius:.9rem;
            background:#f8fafc;
            border-color:#dbe3ef;
            min-height:2.7rem;
            padding:.64rem 1rem;
            font-size:.84rem;
            font-weight:950;
            color:#111827;
            box-shadow:none;
        }
        .pc-company-select.pc-company-select-model:focus {
            border-color:#93c5fd;
            box-shadow:0 0 0 4px rgba(37,99,235,.10);
        }
        .pc-ticket-list.pc-ticket-list-model {
            display:grid;
            gap:.8rem;
            min-width:0;
        }
        .pc-ticket-card.pc-ticket-model {
            display:grid;
            gap:.54rem;
            width:100%;
            min-width:0;
            border-radius:.98rem;
            padding:1rem .95rem;
            min-height:6.95rem;
            background:#ffffff;
            border:1px solid #e2e8f0;
            box-shadow:none;
            overflow:hidden;
        }
        .pc-ticket-card.pc-ticket-model.is-active,
        .pc-ticket-card.pc-ticket-model:hover {
            border-color:#93c5fd;
            background:linear-gradient(135deg, #eff6ff 0%, #f8fbff 58%, #ffffff 100%);
            box-shadow:0 14px 28px rgba(37,99,235,.10);
            transform:none;
        }
        .pc-ticket-model .pc-ticket-top {
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:.65rem;
            margin:0;
            min-width:0;
        }
        .pc-ticket-model .pc-ticket-top strong {
            display:block;
            min-width:0;
            max-width:100%;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
            color:#0f172a;
            font-size:.92rem;
            line-height:1.15;
            font-weight:950;
            letter-spacing:-.015em;
        }
        .pc-ticket-model p {
            display:-webkit-box;
            -webkit-line-clamp:1;
            -webkit-box-orient:vertical;
            overflow:hidden;
            min-width:0;
            max-width:100%;
            margin:0;
            color:#475569;
            font-size:.84rem;
            font-weight:750;
            line-height:1.38;
        }
        .pc-ticket-model .pc-ticket-meta {
            display:flex;
            align-items:center;
            justify-content:flex-start;
            gap:.7rem;
            margin-top:.1rem;
            min-width:0;
            color:#64748b;
            font-size:.73rem;
            font-weight:950;
        }
        .pc-ticket-model .pc-ticket-date {
            color:#64748b;
            font-weight:900;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }
        .pc-dot {
            display:inline-flex;
            align-items:center;
            gap:.38rem;
            min-width:0;
            color:#64748b;
            white-space:nowrap;
        }
        .pc-dot.ok { color:#16a34a; }
        .pc-dot.warn { color:#d97706; }
        .pc-dot.muted { color:#64748b; }
        .pc-dot::before {
            content:'';
            width:.45rem;
            height:.45rem;
            border-radius:999px;
            background:#16a34a;
            flex:0 0 auto;
        }
        .pc-dot.warn::before { background:#d97706; }
        .pc-dot.muted::before { background:#94a3b8; }
        .pc-ticket-unread {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            flex:0 0 auto;
            min-width:1.55rem;
            height:1.55rem;
            border-radius:999px;
            background:#dbeafe;
            color:#2563eb;
            font-size:.72rem;
            font-weight:950;
            margin-left:auto;
        }
        .pc-inbox-footer-action {
            margin-top:auto;
            padding-top:.25rem;
        }
        .pc-view-all-btn {
            display:flex;
            align-items:center;
            justify-content:center;
            width:100%;
            min-height:2.45rem;
            border:1px solid #dbe3ef;
            border-radius:.78rem;
            background:#ffffff;
            color:#0f172a;
            font-size:.78rem;
            font-weight:950;
            text-decoration:none;
            cursor:pointer;
            transition:background .18s ease, border-color .18s ease, transform .18s ease, box-shadow .18s ease;
        }
        .pc-view-all-btn:hover {
            background:#f8fafc;
            border-color:#cbd5e1;
            box-shadow:0 10px 22px rgba(15,23,42,.06);
            transform:translateY(-1px);
        }
        .pc-ticket-create-compact {
            display:none !important;
        }



        /* Ajuste cirúrgico do chat: abas funcionais, mensagens fiéis ao modelo e respostas rápidas */
        .pc-main {
            background:#ffffff;
        }
        .pc-tabs {
            gap:1.7rem;
            padding:0 1.15rem;
            min-height:3.7rem;
            background:#ffffff;
        }
        .pc-tab {
            appearance:none;
            border:0;
            border-bottom:2px solid transparent;
            background:transparent;
            height:3.7rem;
            padding:0 .1rem;
            cursor:pointer;
            color:#64748b;
            font-size:.82rem;
            font-weight:950;
        }
        .pc-tab:hover,
        .pc-tab:focus-visible {
            color:var(--pc-primary);
            outline:none;
        }
        .pc-tab.is-active {
            color:var(--pc-primary);
            border-bottom-color:var(--pc-primary);
        }
        .pc-messages {
            gap:1.45rem;
            padding:1.45rem 1.65rem 1.4rem;
            background:#ffffff;
        }
        .pc-message {
            display:grid;
            grid-template-columns:2.55rem minmax(0, min(80%, 760px));
            gap:.85rem;
            align-items:flex-start;
            justify-content:start;
        }
        .pc-message.cliente {
            grid-template-columns:minmax(0, min(82%, 790px));
            padding-left:0;
        }
        .pc-message-avatar {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            width:2.55rem;
            height:2.55rem;
            border-radius:999px;
            margin-top:.14rem;
            background:#dbeafe;
            color:#2563eb;
            font-size:.78rem;
            font-weight:950;
            flex:0 0 auto;
            box-shadow:0 10px 22px rgba(37,99,235,.10);
        }
        .pc-message.equipe .pc-message-avatar {
            background:#e2e8f0;
            color:#334155;
            box-shadow:0 10px 22px rgba(15,23,42,.08);
        }
        .pc-bubble {
            position:relative;
            min-width:0;
            border:1px solid #e5e7eb;
            border-radius:1rem;
            background:#ffffff;
            padding:1.02rem 1.08rem .82rem;
            box-shadow:0 14px 32px rgba(15,23,42,.045);
        }
        .pc-message.cliente .pc-bubble {
            display:grid;
            grid-template-columns:2.35rem minmax(0,1fr);
            column-gap:.72rem;
            align-items:start;
            background:linear-gradient(135deg, #edf5ff 0%, #f4f8ff 64%, #f8fbff 100%);
            border-color:transparent;
            box-shadow:0 16px 34px rgba(37,99,235,.075);
            padding:1rem 1.05rem .82rem;
        }
        .pc-message.cliente .pc-message-avatar {
            width:2.28rem;
            height:2.28rem;
            margin-top:0;
            background:#dbeafe;
            color:#2563eb;
            box-shadow:0 8px 18px rgba(37,99,235,.08);
        }
        .pc-bubble-content {
            min-width:0;
        }
        .pc-bubble-head {
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:1rem;
            margin-bottom:.62rem;
            color:#64748b;
            font-size:.75rem;
            line-height:1.25;
        }
        .pc-bubble-head span:first-child {
            color:#0f172a;
            font-size:.88rem;
            font-weight:950;
        }
        .pc-bubble-head span:last-child {
            white-space:nowrap;
            font-size:.72rem;
            font-weight:850;
        }
        .pc-bubble-text {
            color:#111827;
            white-space:pre-wrap;
            overflow-wrap:anywhere;
            font-size:.94rem;
            line-height:1.56;
            font-weight:650;
        }
        .pc-message-status-line {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:.75rem;
            margin-top:.72rem;
            color:#64748b;
            font-size:.7rem;
            font-weight:900;
        }
        .pc-seen-status {
            color:#2563eb;
            font-size:.7rem;
            font-weight:950;
            white-space:nowrap;
        }
        .pc-date-divider {
            margin:.15rem 0 .25rem;
            color:#64748b;
            font-size:.72rem;
            letter-spacing:.035em;
        }
        .pc-section-panel {
            height:100%;
            min-height:0;
            overflow-y:auto;
            padding:1.4rem 1.65rem;
            background:#ffffff;
        }
        .pc-history-list,
        .pc-notes-list {
            display:grid;
            gap:1rem;
            max-width:920px;
        }
        .pc-history-summary {
            display:grid;
            grid-template-columns:repeat(3,minmax(0,1fr));
            gap:.8rem;
            margin-bottom:.15rem;
        }
        .pc-history-summary-card {
            position:relative;
            overflow:hidden;
            border:1px solid #e5edf8;
            border-radius:1.1rem;
            background:linear-gradient(135deg,#ffffff 0%,#f8fbff 100%);
            padding:.95rem 1rem;
            box-shadow:0 14px 34px rgba(15,23,42,.045);
        }
        .pc-history-summary-card::before {
            content:'';
            position:absolute;
            inset:0 auto 0 0;
            width:3px;
            background:linear-gradient(180deg,#2563eb,#93c5fd);
            opacity:.78;
        }
        .pc-history-summary-card span {
            display:block;
            color:#64748b;
            font-size:.66rem;
            font-weight:950;
            text-transform:uppercase;
            letter-spacing:.055em;
        }
        .pc-history-summary-card strong {
            display:block;
            margin-top:.32rem;
            color:#0f172a;
            font-size:.9rem;
            font-weight:950;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
        }
        .pc-history-timeline {
            position:relative;
            display:grid;
            gap:.72rem;
            padding:.25rem 0 .25rem .1rem;
        }
        .pc-history-timeline::before {
            content:'';
            position:absolute;
            left:1.22rem;
            top:.6rem;
            bottom:.7rem;
            width:2px;
            border-radius:999px;
            background:linear-gradient(180deg,#dbeafe 0%,#e2e8f0 42%,#f1f5f9 100%);
        }
        .pc-history-item,
        .pc-note-card {
            border:1px solid #e4ebf5;
            border-radius:1.15rem;
            background:#ffffff;
            padding:1rem 1.05rem;
            box-shadow:0 14px 34px rgba(15,23,42,.045);
        }
        .pc-history-item {
            position:relative;
            display:grid;
            grid-template-columns:2.45rem minmax(0,1fr) auto;
            gap:.85rem;
            align-items:flex-start;
            overflow:hidden;
            transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
        }
        .pc-history-item:hover {
            transform:translateY(-1px);
            border-color:#cbdaf0;
            background:linear-gradient(180deg,#ffffff 0%,#fbfdff 100%);
            box-shadow:0 18px 44px rgba(15,23,42,.065);
        }
        .pc-history-item::before {
            content:'';
            position:absolute;
            inset:0 auto 0 0;
            width:3px;
            background:#dbeafe;
        }
        .pc-history-item.is-ok::before { background:#86efac; }
        .pc-history-item.is-warn::before { background:#facc15; }
        .pc-history-item.is-danger::before { background:#fca5a5; }
        .pc-history-item.is-muted::before { background:#cbd5e1; }
        .pc-history-icon {
            position:relative;
            z-index:1;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            width:2.45rem;
            height:2.45rem;
            border-radius:999px;
            background:#eff6ff;
            color:#2563eb;
            font-size:.9rem;
            font-weight:950;
            box-shadow:0 0 0 7px #ffffff, 0 10px 22px rgba(37,99,235,.10);
        }
        .pc-history-icon.ok { background:#ecfdf5; color:#16a34a; box-shadow:0 0 0 7px #ffffff, 0 10px 22px rgba(22,163,74,.10); }
        .pc-history-icon.warn { background:#fffbeb; color:#d97706; box-shadow:0 0 0 7px #ffffff, 0 10px 22px rgba(217,119,6,.10); }
        .pc-history-icon.danger { background:#fff1f2; color:#ef4444; box-shadow:0 0 0 7px #ffffff, 0 10px 22px rgba(239,68,68,.12); }
        .pc-history-icon.muted { background:#f1f5f9; color:#64748b; box-shadow:0 0 0 7px #ffffff, 0 10px 22px rgba(100,116,139,.10); }
        .pc-history-content { min-width:0; }
        .pc-history-title-row {
            display:flex;
            align-items:center;
            gap:.55rem;
            min-width:0;
        }
        .pc-history-item strong,
        .pc-note-card strong {
            display:block;
            color:#0f172a;
            font-size:.92rem;
            font-weight:950;
            line-height:1.28;
            min-width:0;
        }
        .pc-history-badge {
            display:inline-flex;
            align-items:center;
            flex-shrink:0;
            border-radius:999px;
            background:#f1f5f9;
            color:#64748b;
            padding:.2rem .5rem;
            font-size:.62rem;
            font-weight:950;
            letter-spacing:.035em;
            text-transform:uppercase;
        }
        .pc-history-item span.pc-history-description,
        .pc-note-card span,
        .pc-note-card p {
            display:block;
            margin-top:.32rem;
            color:#64748b;
            font-size:.78rem;
            line-height:1.48;
            font-weight:800;
        }
        .pc-history-meta {
            display:flex;
            justify-content:flex-end;
            align-items:center;
            min-width:7.4rem;
            color:#64748b;
            font-size:.72rem;
            font-weight:950;
            white-space:nowrap;
            padding-top:.05rem;
        }
        .pc-history-empty {
            border-style:dashed;
            background:linear-gradient(135deg,#ffffff,#f8fafc);
        }
        @media (max-width: 900px) {
            .pc-history-summary { grid-template-columns:1fr; }
            .pc-history-item { grid-template-columns:2.45rem minmax(0,1fr); }
            .pc-history-meta { grid-column:2; justify-content:flex-start; padding-top:0; min-width:0; }
            .pc-history-title-row { align-items:flex-start; flex-direction:column; gap:.35rem; }
        }
        .pc-note-textarea {
            width:100%;
            min-height:8rem;
            margin-top:.75rem;
            border:1px solid #dbe3ef;
            border-radius:.95rem;
            background:#f8fafc;
            color:#0f172a;
            padding:.8rem .9rem;
            outline:none;
            resize:vertical;
            font-weight:750;
            line-height:1.5;
        }
        .pc-note-textarea:focus {
            border-color:#93c5fd;
            box-shadow:0 0 0 4px rgba(37,99,235,.10);
            background:#ffffff;
        }
        .pc-chat-composer {
            background:#ffffff;
            padding:.8rem 1rem .95rem;
        }
        .pc-composer-tabs {
            gap:1.55rem;
            margin-bottom:.55rem;
        }
        .pc-composer-box {
            border-color:#dbe3ef;
            border-radius:1rem;
            padding:.75rem;
            box-shadow:none;
        }
        .pc-composer-textarea {
            min-height:3.65rem;
            color:#111827;
            font-size:.9rem;
        }
        .pc-composer-tools {
            position:relative;
        }
        .pc-quick-replies-panel {
            position:absolute;
            left:0;
            bottom:calc(100% + .55rem);
            z-index:10;
            width:min(24rem, calc(100vw - 2rem));
            border:1px solid #dbe3ef;
            border-radius:1rem;
            background:#ffffff;
            padding:.55rem;
            box-shadow:0 22px 50px rgba(15,23,42,.16);
        }
        .pc-quick-reply-option {
            display:block;
            width:100%;
            border:0;
            border-radius:.78rem;
            background:transparent;
            color:#0f172a;
            padding:.62rem .7rem;
            text-align:left;
            cursor:pointer;
            font-size:.78rem;
            font-weight:850;
            line-height:1.35;
        }
        .pc-quick-reply-option:hover,
        .pc-quick-reply-option:focus-visible {
            background:#eff6ff;
            color:#1d4ed8;
            outline:none;
        }
        .pc-icon-btn.is-active {
            background:#eff6ff;
            color:#2563eb;
        }
        .pc-btn-send-split {
            min-width:5.9rem;
            border-radius:.78rem;
        }

        @media (max-width: 1280px) {
            .pc-workspace { grid-template-columns:minmax(220px,280px) minmax(0,1fr); }
            .pc-context { grid-column:1 / -1; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); position:static; max-height:none; overflow:visible; }
        }
        @media (max-width: 900px) {
            .pc-workspace { grid-template-columns:1fr; min-height:auto; }
            .pc-inbox, .pc-context { position:static; max-height:none; overflow:visible; }
            .pc-inbox { order:2; }
            .pc-main { order:1; min-height:min(780px, calc(100dvh - 120px)); }
            .pc-context { order:3; grid-template-columns:1fr; }
        }
        @media (max-width: 700px) {
            .pc-service-shell { min-height:auto; width:100%; max-width:100%; overflow-x:hidden; }
            .pc-workspace { gap:.75rem; }
            .pc-header { align-items:flex-start; flex-direction:column; padding:.9rem; }
            .pc-actions { width:100%; justify-content:stretch; }
            .pc-actions .pc-btn, .pc-actions a, .pc-actions button { flex:1 1 100%; }
            .pc-tabs { overflow-x:auto; padding:0 .9rem; }
            .pc-messages { padding:1rem .9rem; }
            .pc-message, .pc-message.cliente { grid-template-columns:2.1rem minmax(0,1fr); }
            .pc-message-avatar { width:2.1rem; height:2.1rem; }
            .pc-bubble-head { align-items:flex-start; flex-direction:column; gap:.18rem; }
            .pc-chat-composer { padding:.75rem .85rem .9rem; }
            .pc-composer-row { align-items:stretch; flex-direction:column; }
            .pc-composer-row .pc-btn { width:100%; }
            .pc-client-card-head { grid-template-columns:2.6rem minmax(0,1fr); }
            .pc-client-card-head .pc-btn { grid-column:1 / -1; width:100%; }
            .pc-stats-grid { gap:.55rem; }
            .pc-action-group { grid-template-columns:1fr; }
        }
    </style>

    <div class="pc-service-shell" x-data="{
        shouldStick: true,
        activeSection: 'conversa',
        quickReplyOpen: false,
        internalNote: localStorage.getItem('portal_cliente_internal_note_{{ $empresaId ?? 0 }}') || '',
        quickReplies: [
            'Olá! Tudo bem? Vou te ajudar com isso. Pode me enviar mais detalhes ou um print da mensagem que aparece?',
            'Entendi. Já estou verificando por aqui e retorno assim que concluir a análise.',
            'Pode testar novamente, por favor? Fiz o ajuste necessário e vou acompanhar por aqui.',
            'Obrigado pelo retorno. Vou registrar essa informação no atendimento e seguir com a tratativa.',
            'Conseguimos resolver sua solicitação. Posso marcar o atendimento como resolvido?'
        ],
        setSection(section) {
            this.activeSection = section;
            this.quickReplyOpen = false;
            if (section === 'conversa') this.scrollChat(true);
        },
        applyQuickReply(text) {
            this.quickReplyOpen = false;
            this.$wire.set('respostaChat', text);
            this.$nextTick(() => {
                const textarea = this.$refs.replyTextarea;
                if (!textarea) return;
                textarea.value = text;
                textarea.focus();
                this.grow(textarea);
            });
        },
        saveInternalNote() {
            localStorage.setItem('portal_cliente_internal_note_{{ $empresaId ?? 0 }}', this.internalNote || '');
        },
        scrollChat(force = false) {
            this.$nextTick(() => {
                const el = this.$refs.chatBody;
                if (!el) return;
                const nearBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 140;
                if (force || this.shouldStick || nearBottom) {
                    el.scrollTop = el.scrollHeight;
                }
            });
        },
        watchScroll() {
            const el = this.$refs.chatBody;
            if (!el) return;
            this.shouldStick = el.scrollHeight - el.scrollTop - el.clientHeight < 170;
        },
        grow(el) {
            if (!el) return;
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 128) + 'px';
        }
    }" x-init="scrollChat(true); setTimeout(() => scrollChat(true), 120); setTimeout(() => scrollChat(true), 450)" x-on:livewire:navigated.window="scrollChat(true)" x-on:livewire:updated.window="scrollChat()" x-on:livewire:update.window="scrollChat()" x-on:message.processed.window="scrollChat()">
        <div class="pc-workspace">
            <aside class="pc-panel pc-inbox" aria-label="Atendimentos do portal">
                <div class="pc-inbox-model-head">
                    <div class="pc-inbox-model-title">
                        <strong>Meus atendimentos</strong>
                        <span>{{ $empresaNome }}</span>
                    </div>
                    <button type="button" class="pc-inbox-filter" title="Filtrar atendimentos" aria-label="Filtrar atendimentos">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 7h16M7 12h10M10 17h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                @if ($empresasLista->count() > 1)
                    <div class="pc-inbox-model-select-wrap">
                        <select class="pc-company-select pc-company-select-model" wire:model.live="empresaSelecionadaId" aria-label="Selecionar empresa">
                            @foreach ($empresasLista as $empresaOpcao)
                                <option value="{{ $empresaOpcao['id'] }}">{{ $empresaOpcao['nome_fantasia'] ?? $empresaOpcao['razao_social'] ?? 'Empresa #' . $empresaOpcao['id'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="pc-ticket-list pc-ticket-list-model">
                    <div class="pc-ticket-card pc-ticket-model is-active">
                        <div class="pc-ticket-top">
                            <strong>{{ $protocolo }}</strong>
                        </div>
                        <div class="pc-ticket-meta">
                            <span class="pc-dot {{ $statusClass === 'warn' ? 'warn' : ($atendimentoAberto ? 'ok' : 'muted') }}">{{ $statusAtendimento }}</span>
                        </div>
                        <p>{{ $ultimaMensagem['mensagem_texto'] ?? $ultimaMensagem['mensagem'] ?? 'Conversa principal do portal do cliente' }}</p>
                        <div class="pc-ticket-meta">
                            <span class="pc-ticket-date">{{ $ultimaAtualizacao }}</span>
                            @if ($mensagensCliente > 0)
                                <span class="pc-ticket-unread" title="Mensagens do cliente">{{ $mensagensCliente }}</span>
                            @endif
                        </div>
                    </div>

                    @foreach ($solicitacoesAbertas->take(4) as $solicitacao)
                        @php
                            $solicitacaoPrioridade = (string) ($solicitacao['prioridade'] ?? 'media');
                            $solicitacaoPrioridadeLabel = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'urgente' => 'Urgente'][$solicitacaoPrioridade] ?? ucfirst($solicitacaoPrioridade);
                            $solicitacaoStatusLabel = ($solicitacao['status_label'] ?? null) ?: (($solicitacao['status'] ?? null) ? ucfirst((string) $solicitacao['status']) : 'Em andamento');
                            $solicitacaoStatusClass = in_array(strtolower((string) $solicitacaoStatusLabel), ['concluído', 'concluido', 'resolvido', 'finalizado'], true) ? 'muted' : (($solicitacaoPrioridade === 'alta' || $solicitacaoPrioridade === 'urgente') ? 'warn' : 'ok');
                        @endphp
                        <div class="pc-ticket-card pc-ticket-model">
                            <div class="pc-ticket-top">
                                <strong>{{ $solicitacao['protocolo'] ?? $solicitacao['codigo'] ?? $solicitacao['titulo'] ?? 'Solicitação' }}</strong>
                            </div>
                            <div class="pc-ticket-meta">
                                <span class="pc-dot {{ $solicitacaoStatusClass }}">{{ $solicitacaoStatusLabel }}</span>
                            </div>
                            <p>{{ $solicitacao['descricao'] ?? $solicitacao['titulo'] ?? 'Solicitação em acompanhamento' }}</p>
                            <div class="pc-ticket-meta">
                                <span class="pc-ticket-date">{{ $solicitacao['created_at_label'] ?? $solicitacao['updated_at_label'] ?? $solicitacaoPrioridadeLabel }}</span>
                                @if (! empty($solicitacao['mensagens_count']) || ! empty($solicitacao['unread_count']))
                                    <span class="pc-ticket-unread">{{ $solicitacao['unread_count'] ?? $solicitacao['mensagens_count'] }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <details class="pc-ticket-create-compact">
                    <summary class="pc-ticket-create-summary">Novo atendimento</summary>
                    <div class="pc-ticket-create-body">
                        <form class="pc-ticket-form" wire:submit.prevent="criarSolicitacao">
                            <input class="pc-input" type="text" wire:model.defer="solicitacaoTitulo" placeholder="Título da solicitação">
                            <textarea class="pc-textarea" wire:model.defer="solicitacaoDescricao" placeholder="Descreva a necessidade"></textarea>
                            <select class="pc-select" wire:model.defer="solicitacaoPrioridade">
                                @foreach (($supportForm['prioridades'] ?? []) as $valor => $label)
                                    <option value="{{ $valor }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="pc-btn" wire:loading.attr="disabled" wire:target="criarSolicitacao">Criar atendimento</button>
                        </form>
                    </div>
                </details>

                <div class="pc-inbox-footer-action">
                    <button type="button" class="pc-view-all-btn">Ver todos os atendimentos</button>
                </div>
            </aside>

            <section class="pc-panel pc-main" aria-label="Conversa com o cliente">
                <div class="pc-loading-overlay" wire:loading.flex wire:target="empresaSelecionadaId,finalizarConversa">Atualizando atendimento...</div>

                <header class="pc-header">
                    <div class="pc-title-wrap">
                        <div class="pc-title-line">
                            <h2>Atendimento {{ $protocolo }}</h2>
                            <span class="pc-badge {{ $statusClass }}">{{ $statusAtendimento }}</span>
                        </div>
                        <div class="pc-subtitle">{{ $suporteOnline ? 'Suporte online' : 'Suporte offline' }} • Atualizado {{ $ultimaAtualizacao }}</div>
                    </div>
                    <div class="pc-actions">
                        <span class="pc-realtime-pill">tempo real</span>
                        <button type="button" class="pc-btn secondary" wire:click="finalizarConversa" wire:loading.attr="disabled" wire:target="finalizarConversa">✓ Marcar como resolvido</button>
                        @if ($portalLink)
                            <a class="pc-btn secondary" href="{{ $portalLink }}" target="_blank" rel="noopener">Abrir portal público</a>
                        @endif
                    </div>
                </header>

                <nav class="pc-tabs" aria-label="Seções do atendimento">
                    <button type="button" class="pc-tab" :class="activeSection === 'conversa' ? 'is-active' : ''" :aria-selected="activeSection === 'conversa'" role="tab" x-on:click="setSection('conversa')">Conversa</button>
                    <button type="button" class="pc-tab" :class="activeSection === 'historico' ? 'is-active' : ''" :aria-selected="activeSection === 'historico'" role="tab" x-on:click="setSection('historico')">Histórico</button>
                    <button type="button" class="pc-tab" :class="activeSection === 'anotacoes' ? 'is-active' : ''" :aria-selected="activeSection === 'anotacoes'" role="tab" x-on:click="setSection('anotacoes')">Anotações</button>
                </nav>

                <main class="pc-messages" id="portalClienteChatBody" data-chat-state-url="{{ route('admin.portal-cliente.chat.estado', ['empresa' => $empresaId]) }}" x-show="activeSection === 'conversa'" x-cloak x-ref="chatBody" x-init="scrollChat(true)" x-on:scroll.passive="watchScroll()">
                    @php $dataAnteriorMensagem = null; @endphp
                    @forelse ($mensagensChat as $mensagem)
                        @php
                            $isCliente = ($mensagem['css_class'] ?? '') === 'cliente';
                            $autor = $mensagem['nome'] ?? ($isCliente ? 'Cliente' : 'Equipe');
                            $iniciais = strtoupper(substr((string) $autor, 0, 2));
                            $textoMensagem = trim((string) ($mensagem['mensagem_texto'] ?? $mensagem['mensagem'] ?? ''));
                            $dataMensagem = $mensagem['date_label'] ?? $mensagem['data_label'] ?? null;

                            if (! $dataMensagem && ! empty($mensagem['created_at'])) {
                                try {
                                    $dataMensagem = \Illuminate\Support\Carbon::parse($mensagem['created_at'])->format('d/m/Y');
                                } catch (\Throwable $e) {
                                    $dataMensagem = null;
                                }
                            }

                            $mostrarDivisorData = $dataMensagem && $dataMensagem !== $dataAnteriorMensagem;
                            if ($dataMensagem) {
                                $dataAnteriorMensagem = $dataMensagem;
                            }
                        @endphp

                        @if ($mostrarDivisorData)
                            <div class="pc-date-divider" wire:key="portal-chat-date-{{ $dataMensagem }}-{{ $loop->index }}">{{ $dataMensagem }}</div>
                        @endif

                        <article class="pc-message {{ $isCliente ? 'cliente' : 'equipe' }}" wire:key="portal-chat-message-{{ $mensagem['id'] ?? $loop->index }}" data-message-id="{{ $mensagem['id'] ?? 0 }}">
                            @unless ($isCliente)
                                <div class="pc-message-avatar" title="{{ $autor }}">{{ $iniciais ?: 'EQ' }}</div>
                            @endunless

                            <div class="pc-bubble">
                                @if ($isCliente)
                                    <div class="pc-message-avatar" title="{{ $autor }}">{{ $iniciais ?: 'CL' }}</div>
                                @endif

                                <div class="pc-bubble-content">
                                    <div class="pc-bubble-head">
                                        <span>{{ $autor }} {{ $isCliente ? '(Cliente)' : '(Suporte)' }}</span>
                                        <span>{{ $mensagem['created_at_label'] ?? '' }}</span>
                                    </div>
                                    @if ($textoMensagem !== '')
                                        <div class="pc-bubble-text">{{ $textoMensagem }}</div>
                                    @endif

                                    @if (! empty($mensagem['attachments']))
                                        <div class="pc-attachments">
                                            @foreach ($mensagem['attachments'] as $anexo)
                                                <a class="pc-attachment" href="{{ $anexo['url'] }}" target="_blank" rel="noopener" download>
                                                    <span>{{ ($anexo['is_image'] ?? false) ? '🖼️' : '📄' }}</span>
                                                    <span>
                                                        <strong>{{ $anexo['nome'] ?? 'Anexo' }}</strong>
                                                        <span>{{ $anexo['size_label'] ?? ($anexo['mime_type'] ?? 'arquivo') }}</span>
                                                    </span>
                                                    <span>↗</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="pc-message-status-line">
                                        <span>{{ $isCliente ? 'Mensagem do cliente' : 'Resposta do suporte' }}</span>
                                        @if (! $isCliente && ! empty($mensagem['id']) && $clienteVisualizouAteId && (int) $mensagem['id'] <= (int) $clienteVisualizouAteId)
                                            <span class="pc-seen-status">✓✓ Visualizado pelo cliente</span>
                                        @elseif ($isCliente && ! empty($mensagem['id']) && $suporteVisualizouAteId && (int) $mensagem['id'] <= (int) $suporteVisualizouAteId)
                                            <span class="pc-seen-status">✓✓ Visualizado pelo suporte</span>
                                        @else
                                            <span>{{ $isCliente ? 'Aguardando leitura' : 'Enviada' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="pc-empty">
                            Ainda não há mensagens neste atendimento. Envie a primeira resposta para iniciar a conversa com histórico organizado.
                        </div>
                    @endforelse
                </main>

                <section class="pc-section-panel" x-show="activeSection === 'historico'" x-cloak aria-label="Histórico do atendimento">
                    <div class="pc-history-list">
                        <div class="pc-history-summary">
                            <div class="pc-history-summary-card"><span>Protocolo</span><strong>{{ $protocolo }}</strong></div>
                            <div class="pc-history-summary-card"><span>Status atual</span><strong>{{ $statusAtendimento }}</strong></div>
                            <div class="pc-history-summary-card"><span>Atualizado em</span><strong>{{ $ultimaAtualizacao }}</strong></div>
                        </div>

                        <div class="pc-history-timeline">
                            @forelse ($timelineAtendimento as $evento)
                                @php
                                    $tipoEvento = $evento['tipo'] ?? $evento['type'] ?? 'info';
                                    $iconeEvento = $evento['icone'] ?? $evento['icon'] ?? match ($tipoEvento) {
                                        'status' => '✓',
                                        'prazo', 'sla' => '⏱',
                                        'pendencia' => '!',
                                        'documento' => '📎',
                                        'atendimento' => '💬',
                                        default => '•',
                                    };
                                    $corEvento = $evento['cor'] ?? $evento['color'] ?? match ($tipoEvento) {
                                        'status', 'documento' => 'ok',
                                        'prazo', 'sla' => 'warn',
                                        'pendencia' => 'danger',
                                        default => 'muted',
                                    };
                                @endphp
                                <div class="pc-history-item is-{{ $corEvento }}" wire:key="portal-history-{{ $loop->index }}">
                                    <span class="pc-history-icon {{ $corEvento }}">{{ $iconeEvento }}</span>
                                    <div class="pc-history-content">
                                        <div class="pc-history-title-row">
                                            <strong>{{ $evento['titulo'] ?? $evento['title'] ?? $evento['acao'] ?? 'Movimentação registrada' }}</strong>
                                            <span class="pc-history-badge">{{ ucfirst((string) $tipoEvento) }}</span>
                                        </div>
                                        <span class="pc-history-description">{{ $evento['descricao'] ?? $evento['description'] ?? 'Evento operacional registrado no atendimento.' }}</span>
                                    </div>
                                    <span class="pc-history-meta">{{ $evento['created_at_label'] ?? $evento['data_label'] ?? $evento['tempo'] ?? 'Agora' }}</span>
                                </div>
                            @empty
                                <div class="pc-history-item pc-history-empty is-muted">
                                    <span class="pc-history-icon muted">•</span>
                                    <div class="pc-history-content">
                                        <div class="pc-history-title-row">
                                            <strong>Histórico operacional ainda vazio</strong>
                                            <span class="pc-history-badge">Sistema</span>
                                        </div>
                                        <span class="pc-history-description">Assim que houver mudança de status, prioridade, prazo, documento ou ação relevante, ela aparecerá aqui sem misturar com o chat.</span>
                                    </div>
                                    <span class="pc-history-meta">—</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="pc-section-panel" x-show="activeSection === 'anotacoes'" x-cloak aria-label="Anotações internas do atendimento">
                    <div class="pc-notes-list">
                        <div class="pc-note-card">
                            <strong>Anotações internas</strong>
                            <span>Use este campo para observações rápidas do suporte. A anotação fica salva neste navegador e não aparece para o cliente.</span>
                            <textarea class="pc-note-textarea" x-model="internalNote" x-on:input.debounce.350ms="saveInternalNote()" placeholder="Ex.: cliente informou erro ao acessar, validar cache e retorno por e-mail..."></textarea>
                        </div>
                        <div class="pc-note-card">
                            <strong>Resumo operacional</strong>
                            <p>Cliente: {{ $empresaNome }}</p>
                            <p>Prioridade: {{ $prioridadeLabel }}</p>
                            <p>Mensagens no atendimento: {{ $mensagensChat->count() }}</p>
                        </div>
                    </div>
                </section>

                <div class="pc-typing-row" data-cliente-typing style="display: none;">
                    <span class="pc-typing-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                    <span data-cliente-typing-text>Cliente está digitando...</span>
                </div>

                <footer class="pc-chat-composer" x-show="activeSection === 'conversa'" x-cloak>
                    <div class="pc-upload-progress" wire:loading.flex wire:target="portalAnexos"><i></i> Preparando anexos...</div>

                    @if ($portalAnexos)
                        <div class="pc-upload-list">
                            @foreach ($portalAnexos as $anexoTemporario)
                                <span class="pc-upload-pill">📎 {{ method_exists($anexoTemporario, 'getClientOriginalName') ? $anexoTemporario->getClientOriginalName() : 'Arquivo anexado' }}</span>
                            @endforeach
                        </div>
                    @endif

                    <form wire:submit.prevent="responderChat" data-admin-chat-form data-send-url="{{ route('admin.portal-cliente.chat.mensagem', ['empresa' => $empresaId]) }}">
                        <div class="pc-composer-tabs" role="tablist" aria-label="Modo de mensagem">
                            <button type="button" class="pc-composer-tab is-active" role="tab" aria-selected="true">Responder</button>
                            <button type="button" class="pc-composer-tab" role="tab" aria-selected="false" disabled title="Mensagem interna ficará para o próximo lote funcional">Mensagem interna</button>
                        </div>
                        <div class="pc-composer-box">
                            <textarea class="pc-composer-textarea" x-ref="replyTextarea" wire:model.defer="respostaChat" data-admin-chat-textarea placeholder="Digite sua mensagem..." aria-label="Mensagem de resposta para o cliente" x-on:input="grow($event.target); window.portalClienteAvisarSuporteDigitando && window.portalClienteAvisarSuporteDigitando($event.target.value)" x-on:keydown.enter="if (!$event.shiftKey && !$event.isComposing) { $event.preventDefault(); const form = $event.target.closest('form'); if (form && $event.target.value.trim().length > 0) form.requestSubmit(); }"></textarea>
                            <div class="pc-composer-row">
                                <div class="pc-composer-tools">
                                    <label class="pc-file-trigger pc-icon-btn" title="Anexar arquivo">
                                        📎 Anexar
                                        <input type="file" wire:model="portalAnexos" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv" aria-label="Anexar arquivos na conversa">
                                    </label>
                                    <button type="button" class="pc-icon-btn" :class="quickReplyOpen ? 'is-active' : ''" title="Inserir resposta rápida" x-on:click="quickReplyOpen = !quickReplyOpen">▱ Resposta rápida</button>
                                    <div class="pc-quick-replies-panel" x-show="quickReplyOpen" x-cloak x-on:click.outside="quickReplyOpen = false">
                                        <template x-for="reply in quickReplies" :key="reply">
                                            <button type="button" class="pc-quick-reply-option" x-text="reply" x-on:click="applyQuickReply(reply)"></button>
                                        </template>
                                    </div>
                                </div>
                                <button type="submit" class="pc-btn pc-btn-send-split" wire:loading.attr="disabled" wire:target="responderChat,portalAnexos" data-admin-chat-submit>
                                    <span wire:loading.remove wire:target="responderChat" data-send-label>Enviar</span>
                                    <span class="pc-send-loading" wire:loading.inline-flex wire:target="responderChat" data-send-loading><i></i> Enviando</span>
                                </button>
                            </div>
                        </div>
                        <div class="pc-composer-helper">
                            <span>Enter envia • Shift + Enter quebra linha</span>
                            <span wire:dirty wire:target="respostaChat">digitando...</span>
                        </div>
                        @error('respostaChat') <div class="pc-error">{{ $message }}</div> @enderror
                        @error('portalAnexos') <div class="pc-error">{{ $message }}</div> @enderror
                        @error('portalAnexos.*') <div class="pc-error">{{ $message }}</div> @enderror
                    </form>
                </footer>
            </section>

            <aside class="pc-context" aria-label="Contexto do cliente e atendimento">
                <div class="pc-card">
                    <div class="pc-client-card-head">
                        <div class="pc-avatar">{{ strtoupper(substr($empresaNome, 0, 2)) }}</div>
                        <div class="pc-client-name">
                            <strong>{{ $empresaNome }}</strong>
                            <span>{{ $portalLink ? 'Portal público ativo' : 'Portal interno' }}</span>
                        </div>
                        @if ($portalLink)
                            <a class="pc-btn secondary" href="{{ $portalLink }}" target="_blank" rel="noopener">Ver perfil</a>
                        @endif
                    </div>

                    <div class="pc-client-status-row">
                        <span class="pc-health-pill {{ $statusClass }}">{{ $statusAtendimento }}</span>
                        <span class="pc-muted">{{ $suporteOnline ? 'Suporte disponível' : 'Fora do horário' }}</span>
                    </div>

                    <div class="pc-stats-grid">
                        <div class="pc-stat"><span>Mensagens</span><strong>{{ $mensagensChat->count() }}</strong></div>
                        <div class="pc-stat"><span>Pendências</span><strong>{{ $pendenciasCliente->count() }}</strong></div>
                        <div class="pc-stat"><span>Progresso</span><strong>{{ $percent }}%</strong></div>
                    </div>
                </div>

                <div class="pc-next-step">
                    <span>Próximo passo</span>
                    @if ($temPendencias)
                        <strong>Resolver as pendências abertas do cliente.</strong>
                        <p>Priorize os itens pendentes antes de encerrar o atendimento.</p>
                    @elseif ($mensagensCliente > $mensagensEquipe)
                        <strong>Responder a última mensagem do cliente.</strong>
                        <p>O cliente está aguardando uma orientação do suporte.</p>
                    @elseif ($atendimentoAberto)
                        <strong>Acompanhar a conversa até a confirmação do cliente.</strong>
                        <p>Se a situação já foi resolvida, marque o atendimento como resolvido.</p>
                    @else
                        <strong>Nenhuma ação obrigatória no momento.</strong>
                        <p>Use esta área para iniciar um novo atendimento quando necessário.</p>
                    @endif
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Ações rápidas</strong>
                    <div class="pc-action-group">
                        <button type="button" class="pc-action-link primary full" wire:click="$refresh">↻ Atualizar conversa</button>
                        <button type="button" class="pc-action-link" onclick="document.querySelector('.pc-composer-textarea')?.focus()">💬 Responder</button>
                        @if ($portalLink)
                            <a class="pc-action-link" href="{{ $portalLink }}" target="_blank" rel="noopener">🔗 Portal</a>
                        @else
                            <button type="button" class="pc-action-link" disabled style="opacity:.55;cursor:not-allowed;">🔗 Portal</button>
                        @endif
                        <button type="button" class="pc-action-link danger full" wire:click="finalizarConversa" wire:loading.attr="disabled" wire:target="finalizarConversa">✓ Marcar atendimento como resolvido</button>
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Sobre o atendimento</strong>
                    <div class="pc-info-list">
                        <div class="pc-info-row"><span>Protocolo</span><strong>{{ $protocolo }}</strong></div>
                        <div class="pc-info-row"><span>Prioridade</span><strong>{{ $prioridadeLabel }}</strong></div>
                        <div class="pc-info-row"><span>Canal</span><strong>Portal do Cliente</strong></div>
                        <div class="pc-info-row"><span>Responsável</span><strong>{{ $responsavel }}</strong></div>
                        <div class="pc-info-row"><span>Atualização</span><strong>{{ $ultimaAtualizacao }}</strong></div>
                        <div class="pc-info-row"><span>SLA</span><strong>{{ $temPendencias ? 'Acompanhar pendências' : 'Dentro do prazo' }}</strong></div>
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Pendências do cliente</strong>
                    <div class="pc-info-list">
                        @forelse ($pendenciasCliente->take(5) as $pendencia)
                            <div class="pc-info-row">
                                <span>{{ $pendencia['action_label'] ?? 'Acompanhar' }}</span>
                                <strong>{{ $pendencia['titulo'] ?? 'Item sem título' }}</strong>
                            </div>
                        @empty
                            <div class="pc-empty">Nenhuma pendência do cliente no momento.</div>
                        @endforelse
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Arquivos recentes</strong>
                    <div class="pc-file-list">
                        @forelse ($documentosPublicados->take(4) as $documento)
                            @php
                                $documentoUrl = $documento['url'] ?? $documento['download_url'] ?? null;
                                $documentoNome = $documento['nome'] ?? $documento['titulo'] ?? $documento['name'] ?? 'Documento';
                                $documentoMeta = $documento['size_label'] ?? $documento['created_at_label'] ?? $documento['tipo'] ?? 'Arquivo do portal';
                            @endphp
                            @if ($documentoUrl)
                                <a class="pc-file-row" href="{{ $documentoUrl }}" target="_blank" rel="noopener">
                                    <span>📄</span>
                                    <span><strong>{{ $documentoNome }}</strong><span>{{ $documentoMeta }}</span></span>
                                    <span>↗</span>
                                </a>
                            @else
                                <div class="pc-file-row">
                                    <span>📄</span>
                                    <span><strong>{{ $documentoNome }}</strong><span>{{ $documentoMeta }}</span></span>
                                    <span>—</span>
                                </div>
                            @endif
                        @empty
                            <div class="pc-empty">Nenhum arquivo publicado para este cliente.</div>
                        @endforelse
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Linha do tempo</strong>
                    <div class="pc-mini-timeline">
                        @forelse ($timelineAtendimento->take(4) as $evento)
                            <div class="pc-mini-event">
                                <div>
                                    <strong>{{ $evento['titulo'] ?? $evento['title'] ?? $evento['label'] ?? 'Evento do atendimento' }}</strong>
                                    <span>{{ $evento['created_at_label'] ?? $evento['data_label'] ?? $evento['description'] ?? 'Registrado no portal' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="pc-mini-event">
                                <div>
                                    <strong>Atendimento iniciado</strong>
                                    <span>{{ $ultimaAtualizacao }}</span>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Progresso do fluxo</strong>
                    <div class="pc-info-list">
                        <div class="pc-info-row"><span>Documentos</span><strong>{{ $documentosPublicados->count() }}</strong></div>
                        <div class="pc-info-row"><span>Próximas datas</span><strong>{{ $entregasCalendario->count() }}</strong></div>
                        <div class="pc-info-row"><span>Concluído</span><strong>{{ $progress['done'] ?? 0 }} de {{ $progress['total'] ?? 0 }}</strong></div>
                    </div>
                    <div class="pc-progress" style="--progress: {{ $percent }}%;"><i></i></div>
                    <p class="pc-muted" style="margin-top:.55rem;">{{ $percent }}% do fluxo concluído.</p>
                </div>
            </aside>
        </div>
    </div>

    <script>
        (function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const chat = document.getElementById('portalClienteChatBody');
            const stateUrl = chat?.dataset.chatStateUrl;
            const typingUrl = '{{ route('admin.portal-cliente.digitando', ['empresa' => $empresaId]) }}';
            const typingState = { lastSent: 0, timer: null };
            const knownMessageIds = new Set();
            let lastSignature = '';
            let pollTimer = null;
            let pollingBusy = false;
            let sendingBusy = false;


            function setSending(form, sending) {
                sendingBusy = Boolean(sending);
                if (!form) return;
                const button = form.querySelector('[data-admin-chat-submit]');
                const label = form.querySelector('[data-send-label]');
                const loading = form.querySelector('[data-send-loading]');
                if (button) button.disabled = Boolean(sending);
                if (label) label.style.display = sending ? 'none' : '';
                if (loading) loading.style.display = sending ? 'inline-flex' : 'none';
            }

            function appendMessageHtml(html) {
                if (!chat || !html) return;
                const empty = chat.querySelector('.pc-empty');
                if (empty) empty.remove();
                chat.insertAdjacentHTML('beforeend', html);
                chat.scrollTop = chat.scrollHeight;
            }

            function appendOptimisticMessage(text) {
                const tempId = 'tmp-' + Date.now();
                const author = @json(auth()->user()?->name ?: 'Suporte');
                const html = '<article class="pc-message equipe is-sending" data-message-id="' + tempId + '">'
                    + '<div class="pc-message-avatar" title="' + escapeHtml(author) + '">' + initials(author, 'EQ') + '</div>'
                    + '<div class="pc-bubble"><div class="pc-bubble-content"><div class="pc-bubble-head"><span>' + escapeHtml(author) + ' (Suporte)</span><span>agora</span></div>'
                    + '<div class="pc-bubble-text">' + escapeHtml(text) + '</div>'
                    + '<div class="pc-message-status-line"><span>Resposta do suporte</span><span data-seen-status>Enviando...</span></div>'
                    + '</div></div></article>';
                appendMessageHtml(html);
                return tempId;
            }

            function markOptimisticMessage(tempId, status, serverId) {
                const row = chat?.querySelector('[data-message-id="' + tempId + '"]');
                if (!row) return;
                const seen = row.querySelector('[data-seen-status]');
                row.classList.remove('is-sending');
                row.classList.toggle('is-failed', status === 'failed');
                if (serverId) row.dataset.messageId = String(serverId);
                if (seen) seen.textContent = status === 'failed' ? 'Falha ao enviar' : 'Enviada';
            }

            function escapeHtml(value) {
                return String(value || '').replace(/[&<>"']/g, function (char) {
                    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
                });
            }

            function initials(author, fallback) {
                const clean = String(author || '').trim();
                return (clean ? clean.slice(0, 2) : fallback).toUpperCase();
            }

            function renderAttachment(anexo) {
                const url = escapeHtml(anexo.url || '#');
                const name = escapeHtml(anexo.name || anexo.nome || 'Anexo');
                const size = escapeHtml(anexo.size || anexo.size_label || anexo.mime_type || 'arquivo');
                return '<a class="pc-attachment" href="' + url + '" target="_blank" rel="noopener" download>'
                    + '<span>' + (anexo.is_image ? '🖼️' : '📄') + '</span>'
                    + '<span><strong>' + name + '</strong><span>' + size + '</span></span><span>↗</span></a>';
            }

            function renderMessage(msg) {
                const isCliente = msg.class === 'cliente';
                const author = msg.author || (isCliente ? 'Cliente' : 'Equipe');
                const id = Number(msg.id || 0);
                const attach = Array.isArray(msg.attachments) && msg.attachments.length
                    ? '<div class="pc-attachments">' + msg.attachments.map(renderAttachment).join('') + '</div>'
                    : '';
                return '<article class="pc-message ' + (isCliente ? 'cliente' : 'equipe') + '" data-message-id="' + id + '">'
                    + (!isCliente ? '<div class="pc-message-avatar" title="' + escapeHtml(author) + '">' + initials(author, 'EQ') + '</div>' : '')
                    + '<div class="pc-bubble">'
                    + (isCliente ? '<div class="pc-message-avatar" title="' + escapeHtml(author) + '">' + initials(author, 'CL') + '</div>' : '')
                    + '<div class="pc-bubble-content"><div class="pc-bubble-head"><span>' + escapeHtml(author) + ' ' + (isCliente ? '(Cliente)' : '(Suporte)') + '</span><span>' + escapeHtml(msg.time || '') + '</span></div>'
                    + (msg.text ? '<div class="pc-bubble-text">' + escapeHtml(msg.text) + '</div>' : '')
                    + attach
                    + '<div class="pc-message-status-line"><span>' + (isCliente ? 'Mensagem do cliente' : 'Resposta do suporte') + '</span><span data-seen-status>Enviada</span></div>'
                    + '</div></div></article>';
            }

            function updateTyping(isTyping, name) {
                const box = document.querySelector('[data-cliente-typing]');
                const text = document.querySelector('[data-cliente-typing-text]');
                if (!box) return;
                box.style.display = isTyping ? 'flex' : 'none';
                if (text) text.textContent = (name || 'Cliente') + ' está digitando...';
            }

            function updateSeen(supportSeenUntil, clientSeenUntil) {
                document.querySelectorAll('#portalClienteChatBody [data-message-id]').forEach(function (row) {
                    const id = Number(row.dataset.messageId || 0);
                    const status = row.querySelector('[data-seen-status]');
                    if (!status || !id) return;
                    const isCliente = row.classList.contains('cliente');
                    if (isCliente && Number(supportSeenUntil || 0) >= id) status.textContent = '✓✓ Visualizado pelo suporte';
                    else if (!isCliente && Number(clientSeenUntil || 0) >= id) status.textContent = '✓✓ Visualizado pelo cliente';
                    else status.textContent = isCliente ? 'Aguardando leitura' : 'Enviada';
                });
            }

            function renderMessages(messages, supportSeenUntil, clientSeenUntil) {
                if (!chat || !Array.isArray(messages)) return;
                const signature = messages.map(function (m) { return [m.id, m.class, m.time, m.text].join(':'); }).join('|');

                if (!lastSignature) {
                    chat.querySelectorAll('[data-message-id]').forEach(function (row) {
                        const id = Number(row.dataset.messageId || 0);
                        if (id) knownMessageIds.add(id);
                    });
                    lastSignature = signature;
                    updateSeen(supportSeenUntil, clientSeenUntil);
                    return;
                }

                if (signature !== lastSignature) {
                    const incoming = messages.filter(function (m) {
                        const id = Number(m.id || 0);
                        return id && !knownMessageIds.has(id);
                    });

                    if (incoming.length) {
                        const nearBottom = chat.scrollHeight - chat.scrollTop - chat.clientHeight < 220;
                        const empty = chat.querySelector('.pc-empty');
                        if (empty) empty.remove();
                        incoming.forEach(function (message) {
                            knownMessageIds.add(Number(message.id || 0));
                            const pendingSameText = Array.from(chat.querySelectorAll('.pc-message.is-sending .pc-bubble-text')).find(function (node) {
                                return node.textContent.trim() === String(message.text || '').trim();
                            });
                            if (pendingSameText && message.class === 'equipe') {
                                const row = pendingSameText.closest('.pc-message');
                                row.classList.remove('is-sending');
                                row.dataset.messageId = String(message.id || 0);
                                const status = row.querySelector('[data-seen-status]');
                                if (status) status.textContent = 'Enviada';
                                return;
                            }
                            chat.insertAdjacentHTML('beforeend', renderMessage(message));
                        });
                        if (nearBottom || !document.activeElement?.classList.contains('pc-composer-textarea')) {
                            chat.scrollTop = chat.scrollHeight;
                        }
                    } else if (chat.querySelectorAll('[data-message-id]').length === 0 && messages.length) {
                        chat.innerHTML = messages.map(renderMessage).join('');
                        messages.forEach(function (message) { if (message.id) knownMessageIds.add(Number(message.id)); });
                        chat.scrollTop = chat.scrollHeight;
                    }
                    lastSignature = signature;
                }
                updateSeen(supportSeenUntil, clientSeenUntil);
            }

            async function pollChatState() {
                if (!stateUrl || !window.fetch || document.hidden || pollingBusy) return;
                pollingBusy = true;
                try {
                    const response = await fetch(stateUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                    if (!response.ok) return;
                    const data = await response.json();
                    if (!data || !data.ok) return;
                    renderMessages(data.messages || [], data.support_seen_until_id || 0, data.client_seen_until_id || 0);
                    updateTyping(Boolean(data.client_typing), data.client_typing_name || 'Cliente');
                } catch (error) {} finally { pollingBusy = false; }
            }

            async function sendAdminMessage(form) {
                if (!form || sendingBusy || !window.fetch) return false;
                const textarea = form.querySelector('[data-admin-chat-textarea]');
                const fileInput = form.querySelector('input[type=\"file\"]');
                const message = (textarea?.value || '').trim();
                if (!message && (!fileInput || !fileInput.files || fileInput.files.length === 0)) return false;
                if (fileInput && fileInput.files && fileInput.files.length > 0) return true;

                setSending(form, true);
                const tempId = appendOptimisticMessage(message);
                if (textarea) { textarea.value = ''; textarea.dispatchEvent(new Event('input', { bubbles: true })); }

                try {
                    const response = await fetch(form.dataset.sendUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ mensagem: message }),
                        credentials: 'same-origin'
                    });
                    const data = await response.json().catch(function () { return null; });
                    if (!response.ok || !data || !data.ok) throw new Error(data?.message || 'Falha ao enviar');
                    markOptimisticMessage(tempId, 'sent', data.message_id || null);
                    if (Array.isArray(data.messages)) renderMessages(data.messages, data.support_seen_until_id || 0, data.client_seen_until_id || 0);
                    else pollChatState();
                    return false;
                } catch (error) {
                    markOptimisticMessage(tempId, 'failed');
                    if (textarea) { textarea.value = message; textarea.focus(); }
                    return false;
                } finally {
                    setSending(form, false);
                }
            }

            document.querySelectorAll('[data-admin-chat-form]').forEach(function (form) {
                form.addEventListener('submit', async function (event) {
                    const fileInput = form.querySelector('input[type=\"file\"]');
                    if (fileInput && fileInput.files && fileInput.files.length > 0) return;

                    event.preventDefault();
                    await sendAdminMessage(form);
                });
            });

            window.portalClienteAvisarSuporteDigitando = function (text) {
                if (!typingUrl || !window.fetch) return;
                window.clearTimeout(typingState.timer);
                typingState.timer = window.setTimeout(function () {
                    const now = Date.now();
                    if (now - typingState.lastSent < 2500) return;
                    typingState.lastSent = now;
                    fetch(typingUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ text: text || '' }),
                        credentials: 'same-origin',
                        keepalive: true
                    }).catch(function () {});
                }, 450);
            };

            pollChatState();
            pollTimer = window.setInterval(pollChatState, 1000);
            document.addEventListener('visibilitychange', function () { if (!document.hidden) pollChatState(); });
            document.addEventListener('livewire:navigated', function () { if (pollTimer) window.clearInterval(pollTimer); });
        })();
    </script>

</x-filament-panels::page>
