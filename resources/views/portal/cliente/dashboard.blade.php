    <!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal do Cliente</title>
    <link rel="stylesheet" href="{{ asset('css/prazzu-global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/prazzu-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/atendimentos.css') }}">
    <style>
        body.portal-cliente-area {
            min-height: 100vh;
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #0f172a;
            background: #f3f6fb;
        }

        .portal-area-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 22px 28px;
            background: linear-gradient(135deg, #020617, #06376d);
            color: #fff;
            box-shadow: 0 18px 45px rgba(6, 55, 109, .22);
        }

        .portal-area-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .portal-area-logo {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            background: linear-gradient(135deg, #6d2df6, #0f4ca3);
            box-shadow: inset -14px -14px 28px rgba(255,255,255,.16), 0 18px 35px rgba(15,23,42,.28);
            position: relative;
        }

        .portal-area-logo::after {
            content: '';
            width: 22px;
            height: 22px;
            border-radius: 999px;
            background: #fff;
            position: absolute;
            right: -5px;
            bottom: -4px;
            box-shadow: 0 10px 25px rgba(255,255,255,.28);
        }

        .portal-area-title h1 {
            margin: 0 0 4px;
            font-size: 1.35rem;
            letter-spacing: -.02em;
            text-transform: uppercase;
        }

        .portal-area-title p {
            margin: 0;
            color: rgba(255, 255, 255, .76);
            font-size: .92rem;
        }

        .portal-area-user {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .portal-area-avatar {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #174ea6;
            color: #fff;
            font-weight: 900;
        }

        .portal-area-logout {
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 14px;
            background: rgba(255, 255, 255, .10);
            color: #fff;
            padding: 10px 14px;
            font-weight: 800;
            cursor: pointer;
        }

        .portal-area-shell {
            max-width: 1500px;
            margin: 0 auto;
            padding: 26px 20px 36px;
        }

        .portal-dashboard-grid {
            display: grid;
            grid-template-columns: 340px minmax(0, 1fr) 340px;
            gap: 18px;
            align-items: stretch;
        }

        .portal-panel {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: rgba(255,255,255,.94);
            box-shadow: 0 22px 55px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .portal-panel-pad { padding: 22px; }
        .portal-panel-title { margin: 0; font-size: 1.12rem; letter-spacing: -.02em; color: #0f172a; }
        .portal-panel-muted { color: #64748b; font-size: .88rem; line-height: 1.6; }

        .portal-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 18px;
        }

        .portal-summary-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px;
            background: #f8fafc;
        }

        .portal-summary-card strong { display: block; font-size: 1.4rem; color: #0f172a; }
        .portal-summary-card span { color: #64748b; font-size: .78rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }

        .portal-ticket-list {
            display: grid;
            gap: 12px;
            margin-top: 18px;
            max-height: 620px;
            overflow: auto;
            padding-right: 4px;
        }

        .portal-ticket-item {
            display: block;
            text-decoration: none;
            color: inherit;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 15px;
            background: #fff;
            transition: .18s ease;
        }

        .portal-ticket-item:hover { transform: translateY(-1px); border-color: #bfdbfe; box-shadow: 0 14px 30px rgba(15,23,42,.08); }
        .portal-ticket-item.is-active { border-color: #174ea6; background: linear-gradient(135deg, #eff6ff, #fff); box-shadow: 0 16px 36px rgba(23,78,166,.16); }

        .portal-ticket-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
        }

        .portal-ticket-title { font-weight: 900; color: #0f172a; line-height: 1.35; }
        .portal-ticket-desc { margin: 8px 0 0; color: #475569; font-size: .84rem; line-height: 1.5; }
        .portal-ticket-meta { display: flex; gap: 7px; flex-wrap: wrap; margin-top: 12px; color: #64748b; font-size: .78rem; }

        .portal-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 7px 10px;
            font-size: .74rem;
            font-weight: 900;
            white-space: nowrap;
            background: #f1f5f9;
            color: #334155;
        }

        .portal-badge::before { content: ''; width: 8px; height: 8px; border-radius: 999px; background: currentColor; opacity: .9; }
        .portal-badge.is-success { background: #dcfce7; color: #16a34a; }
        .portal-badge.is-info { background: #dbeafe; color: #174ea6; }
        .portal-badge.is-warning { background: #fff7ed; color: #ea580c; }
        .portal-badge.is-danger { background: #fee2e2; color: #dc2626; }
        .portal-badge.is-done { background: #f1f5f9; color: #64748b; }
        .portal-badge.is-neutral { background: #f8fafc; color: #64748b; }

        .portal-chat-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding: 22px;
            border-bottom: 1px solid #e2e8f0;
        }

        .portal-chat-title h2 { margin: 0 0 6px; font-size: 1.15rem; color: #0f172a; }
        .portal-chat-title p { margin: 0; color: #64748b; font-size: .86rem; }

        .portal-chat-body {
            height: 600px;
            overflow: auto;
            padding: 24px 22px;
            background: linear-gradient(180deg, #fff, #f8fafc);
        }

        .portal-message {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            margin-bottom: 16px;
        }

        .portal-message.is-client { justify-content: flex-end; }
        .portal-message-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #0f172a;
            color: #fff;
            display: grid;
            place-items: center;
            font-size: .78rem;
            font-weight: 900;
            flex: 0 0 auto;
        }

        .portal-message-bubble {
            max-width: min(620px, 82%);
            border-radius: 18px;
            padding: 14px 16px;
            background: #f1f5f9;
            color: #0f172a;
            line-height: 1.55;
            box-shadow: 0 10px 28px rgba(15,23,42,.06);
        }

        .portal-message.is-client .portal-message-bubble {
            background: linear-gradient(135deg, #174ea6, #06376d);
            color: #fff;
        }

        .portal-message-author { display: block; font-weight: 900; margin-bottom: 3px; font-size: .86rem; }
        .portal-message-time { display: block; margin-top: 8px; opacity: .75; font-size: .78rem; }
        .portal-typing-status { display: none; align-items: center; gap: 8px; padding: 0 22px 14px; color: #64748b; font-size: .8rem; font-weight: 850; background: #f8fafc; }
        .portal-typing-status.is-visible { display: flex; }
        .portal-typing-dots { display: inline-flex; align-items: center; gap: 3px; border: 1px solid #e2e8f0; border-radius: 999px; background: #fff; padding: 7px 10px; }
        .portal-typing-dots i { width: 5px; height: 5px; border-radius: 999px; background: currentColor; opacity: .45; animation: portalTypingPulse 1s infinite ease-in-out; }
        .portal-typing-dots i:nth-child(2) { animation-delay: .14s; }
        .portal-typing-dots i:nth-child(3) { animation-delay: .28s; }
        @keyframes portalTypingPulse { 0%, 80%, 100% { transform: translateY(0); opacity: .35; } 40% { transform: translateY(-3px); opacity: .95; } }

        .portal-empty {
            border: 1px dashed #cbd5e1;
            border-radius: 20px;
            padding: 34px;
            text-align: center;
            background: #fff;
            color: #64748b;
            line-height: 1.7;
        }

        .portal-detail-list {
            display: grid;
            gap: 0;
            margin-top: 18px;
            border-top: 1px solid #e2e8f0;
        }

        .portal-detail-row {
            display: grid;
            grid-template-columns: 130px 1fr;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: .9rem;
        }

        .portal-detail-row span { color: #64748b; font-weight: 800; }
        .portal-detail-row strong { color: #0f172a; }

        .portal-action-disabled {
            width: 100%;
            border: 1px solid #dbeafe;
            border-radius: 16px;
            padding: 13px 16px;
            margin-top: 18px;
            background: #eff6ff;
            color: #174ea6;
            font-weight: 900;
        }


        .portal-primary-action {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            border: 0;
            border-radius: 16px;
            padding: 13px 16px;
            margin-top: 18px;
            background: linear-gradient(135deg, #174ea6, #06376d);
            color: #fff;
            font-weight: 900;
            text-decoration: none;
            box-shadow: 0 14px 30px rgba(23, 78, 166, .20);
            cursor: pointer;
            transition: .18s ease;
        }

        .portal-primary-action:hover { transform: translateY(-1px); box-shadow: 0 18px 36px rgba(23, 78, 166, .26); }

        .portal-alert {
            max-width: 1500px;
            margin: 0 auto 18px;
            border-radius: 16px;
            padding: 14px 16px;
            font-weight: 800;
            line-height: 1.5;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #174ea6;
        }

        .portal-alert.is-success { border-color: #bbf7d0; background: #f0fdf4; color: #15803d; }
        .portal-alert.is-danger { border-color: #fecaca; background: #fef2f2; color: #b91c1c; }

        .portal-create-wrap { padding: 24px 22px; background: linear-gradient(180deg, #fff, #f8fafc); min-height: 600px; }
        .portal-create-card { max-width: 780px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 22px; background: #fff; box-shadow: 0 18px 45px rgba(15,23,42,.07); overflow: hidden; }
        .portal-create-card-header { padding: 24px; border-bottom: 1px solid #e2e8f0; background: linear-gradient(135deg, #eff6ff, #fff); }
        .portal-create-card-header h3 { margin: 0 0 8px; font-size: 1.25rem; color: #0f172a; letter-spacing: -.02em; }
        .portal-create-card-header p { margin: 0; color: #64748b; line-height: 1.6; }
        .portal-create-form { display: grid; gap: 18px; padding: 24px; }
        .portal-field { display: grid; gap: 8px; }
        .portal-field label { color: #0f172a; font-weight: 900; font-size: .9rem; }
        .portal-field small { color: #64748b; line-height: 1.5; }
        .portal-input, .portal-select, .portal-textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 15px;
            padding: 13px 14px;
            background: #fff;
            color: #0f172a;
            font: inherit;
            outline: none;
            transition: .16s ease;
            box-sizing: border-box;
        }
        .portal-textarea { min-height: 170px; resize: vertical; line-height: 1.6; }
        .portal-input:focus, .portal-select:focus, .portal-textarea:focus { border-color: #174ea6; box-shadow: 0 0 0 4px rgba(23,78,166,.12); }
        .portal-form-grid { display: grid; grid-template-columns: 1fr 220px; gap: 14px; }
        .portal-form-actions { display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap; padding-top: 6px; }
        .portal-secondary-action { border: 1px solid #cbd5e1; border-radius: 14px; padding: 12px 16px; background: #fff; color: #334155; font-weight: 900; text-decoration: none; }


        .portal-chat-body.has-composer {
            height: 520px;
            scroll-behavior: smooth;
        }

        .portal-chat-composer {
            border-top: 1px solid #e2e8f0;
            padding: 16px 18px 18px;
            background: #fff;
        }

        .portal-chat-composer form {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .portal-chat-input-wrap {
            display: grid;
            gap: 7px;
        }

        .portal-chat-textarea {
            width: 100%;
            min-height: 48px;
            max-height: 160px;
            resize: none;
            overflow: auto;
            border: 1px solid #cbd5e1;
            border-radius: 18px;
            padding: 13px 15px;
            background: #f8fafc;
            color: #0f172a;
            line-height: 1.5;
            font: inherit;
            box-sizing: border-box;
            outline: none;
            transition: .16s ease;
        }

        .portal-chat-textarea:focus {
            border-color: #174ea6;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(23,78,166,.12);
        }

        .portal-chat-send {
            min-height: 48px;
            border: 0;
            border-radius: 16px;
            padding: 0 20px;
            background: linear-gradient(135deg, #174ea6, #06376d);
            color: #fff;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(23,78,166,.20);
            transition: .18s ease;
        }

        .portal-chat-send:hover { transform: translateY(-1px); box-shadow: 0 18px 34px rgba(23,78,166,.26); }
        .portal-chat-send:disabled { opacity: .55; cursor: not-allowed; transform: none; box-shadow: none; }

        .portal-chat-locked {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 16px;
            background: #f8fafc;
            color: #64748b;
            font-weight: 800;
            line-height: 1.5;
        }

        .portal-attachment-list {
            display: grid;
            gap: 8px;
            margin-top: 10px;
        }

        .portal-attachment-card {
            display: grid;
            grid-template-columns: 38px 1fr auto;
            gap: 10px;
            align-items: center;
            padding: 10px;
            border: 1px solid rgba(148,163,184,.36);
            border-radius: 14px;
            background: rgba(255,255,255,.74);
        }

        .portal-attachment-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: #eff6ff;
            color: #174ea6;
            font-weight: 1000;
            font-size: .78rem;
        }

        .portal-attachment-info strong { display: block; color: #0f172a; font-size: .88rem; word-break: break-word; }
        .portal-attachment-info span { display: block; margin-top: 2px; color: #64748b; font-size: .76rem; font-weight: 800; }
        .portal-attachment-download { border: 1px solid #bfdbfe; border-radius: 12px; padding: 8px 10px; background: #fff; color: #174ea6; font-weight: 900; text-decoration: none; transition: .16s ease; }
        .portal-attachment-download:hover { transform: translateY(-1px); box-shadow: 0 10px 18px rgba(23,78,166,.12); }
        .portal-attachment-preview { max-width: 260px; max-height: 180px; border-radius: 14px; border: 1px solid rgba(148,163,184,.36); object-fit: cover; margin-top: 8px; display: block; }

        .portal-file-row {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            color: #64748b;
            font-size: .82rem;
            font-weight: 800;
        }

        .portal-file-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            padding: 8px 12px;
            background: #fff;
            color: #334155;
            cursor: pointer;
            font-weight: 900;
            transition: .16s ease;
        }

        .portal-file-button:hover { border-color: #174ea6; color: #174ea6; box-shadow: 0 8px 18px rgba(23,78,166,.10); }
        .portal-file-input { position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; }

        .portal-field-error {
            color: #b91c1c;
            font-size: .84rem;
            font-weight: 800;
        }


        .portal-ticket-item:focus-visible,
        .portal-primary-action:focus-visible,
        .portal-secondary-action:focus-visible,
        .portal-area-logout:focus-visible,
        .portal-chat-send:focus-visible {
            outline: 3px solid rgba(37, 99, 235, .35);
            outline-offset: 3px;
        }

        .portal-message-text:empty { display: none; }


        .portal-tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            padding: 14px 18px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .portal-tab-button {
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            padding: 10px 14px;
            background: #fff;
            color: #334155;
            font-weight: 900;
            cursor: pointer;
            transition: .16s ease;
        }

        .portal-tab-button:hover,
        .portal-tab-button.is-active {
            border-color: #174ea6;
            background: #eff6ff;
            color: #174ea6;
            box-shadow: 0 10px 22px rgba(23,78,166,.10);
        }

        .portal-tab-panel { display: none; }
        .portal-tab-panel.is-active { display: block; }

        .portal-info-wrap {
            min-height: 600px;
            padding: 24px 22px;
            background: linear-gradient(180deg, #fff, #f8fafc);
        }

        .portal-info-card {
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(15,23,42,.07);
            padding: 22px;
        }

        .portal-info-card + .portal-info-card { margin-top: 16px; }
        .portal-info-card h3 { margin: 0 0 8px; color: #0f172a; font-size: 1.12rem; letter-spacing: -.02em; }
        .portal-info-card p { margin: 0; color: #64748b; line-height: 1.6; }

        .portal-status-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .portal-status-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px;
            background: #f8fafc;
        }

        .portal-status-card span {
            display: block;
            color: #64748b;
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 6px;
        }

        .portal-status-card strong { color: #0f172a; font-size: .96rem; }

        .portal-history-list {
            display: grid;
            gap: 12px;
            margin-top: 16px;
        }

        .portal-history-item {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #fff;
            padding: 14px;
        }

        .portal-history-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .portal-history-head strong { color: #0f172a; }
        .portal-history-head span { color: #64748b; font-size: .82rem; font-weight: 800; }
        .portal-history-message { color: #334155; line-height: 1.6; }

        .portal-mini-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 9px 12px;
            background: #eff6ff;
            color: #174ea6;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
        }

        .portal-composer-hint {
            color: #64748b;
            font-size: .78rem;
            font-weight: 800;
        }

        @media (max-width: 1180px) {
            .portal-dashboard-grid { grid-template-columns: 320px minmax(0, 1fr); }
            .portal-right-panel { grid-column: 1 / -1; }
            .portal-chat-body { height: 520px; }
        }

        @media (max-width: 820px) {
            .portal-area-topbar, .portal-area-user, .portal-area-brand { align-items: flex-start; flex-direction: column; }
            .portal-dashboard-grid { grid-template-columns: 1fr; }
            .portal-chat-body { height: auto; min-height: 360px; }
            .portal-detail-row { grid-template-columns: 1fr; gap: 4px; }
            .portal-form-grid { grid-template-columns: 1fr; }
            .portal-status-grid { grid-template-columns: 1fr; }
            .portal-chat-composer form { grid-template-columns: 1fr; }
            .portal-chat-send { width: 100%; }
        }

        .portal-message.is-pending .portal-message-bubble { opacity: .78; }
        .portal-message.is-failed .portal-message-bubble { border-color: #fecaca; background: #fff1f2; }
        .portal-message-status { display:block; margin-top:6px; font-size:.76rem; font-weight:900; opacity:.8; }
    </style>

    <script>
        window.portalClienteDebugLog = function () { return; };

        window.portalClienteAtivarAba = function (tabName) {
            window.portalClienteDebugLog('ativar_aba_chamado', { tab: tabName });
            if (! tabName) return false;

            var buttons = document.querySelectorAll('[data-portal-tab], [data-tab]');
            var panels = document.querySelectorAll('.portal-tab-panel, [data-tab-content]');

            buttons.forEach(function (button) {
                var buttonTab = button.getAttribute('data-portal-tab') || button.getAttribute('data-tab');
                if (buttonTab === tabName) {
                    button.classList.add('is-active');
                    button.classList.add('active');
                    button.setAttribute('aria-selected', 'true');
                } else {
                    button.classList.remove('is-active');
                    button.classList.remove('active');
                    button.setAttribute('aria-selected', 'false');
                }
            });

            panels.forEach(function (panel) {
                var panelTab = panel.getAttribute('data-tab-content') || panel.id.replace('portal-tab-', '');
                var active = panelTab === tabName;
                panel.classList.toggle('is-active', active);
                panel.style.display = active ? 'block' : 'none';
                panel.hidden = ! active;
            });

            var chatBody = document.getElementById('portal-chat-body');
            if (tabName === 'atendimento' && chatBody) {
                setTimeout(function () { chatBody.scrollTop = chatBody.scrollHeight; }, 80);
            }

            if (history.replaceState) {
                history.replaceState(null, '', '#tab-' + tabName);
            }

            window.portalClienteDebugLog('ativar_aba_finalizado', {
                tab: tabName,
                botoes: document.querySelectorAll('[data-portal-tab], [data-tab]').length,
                paineis: document.querySelectorAll('.portal-tab-panel, [data-tab-content]').length,
                painel_existe: !! document.getElementById('portal-tab-' + tabName)
            });

            return false;
        };

        window.portalClienteEnviarEnter = function (event, textarea) {
            if (! event || event.key !== 'Enter' || event.shiftKey || event.isComposing) {
                return true;
            }

            event.preventDefault();
            window.portalClienteDebugLog('enter_interceptado_inline', { shift: !! event.shiftKey, textarea_id: textarea ? textarea.id : null });

            var form = textarea ? textarea.closest('form') : document.getElementById('portal-chat-form');
            var fileInput = document.getElementById('portal-chat-file');
            var sendButton = document.getElementById('portal-chat-send');
            var hasText = textarea && textarea.value.trim() !== '';
            var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

            if (! form || (! hasText && ! hasFile)) {
                if (textarea) textarea.focus();
                return false;
            }

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit(sendButton || undefined);
            } else if (sendButton) {
                sendButton.click();
            } else {
                form.submit();
            }

            return false;
        };
    </script>
</head>
<body class="portal-cliente-area">
<header class="portal-area-topbar">
    <div class="portal-area-brand">
        <div class="portal-area-logo" aria-hidden="true"></div>
        <div class="portal-area-title">
            <h1>Portal do Cliente</h1>
            <p>{{ $empresaNome }} · acompanhe seus atendimentos com segurança.</p>
        </div>
    </div>

    <div class="portal-area-user">
        <div class="portal-area-avatar" aria-hidden="true">{{ strtoupper($iniciais) }}</div>
        <div>
            <strong>{{ $clienteNome }}</strong><br>
            <small>{{ $cliente->email ?? '' }}</small>
        </div>
        <form method="POST" action="{{ route('portal.cliente.logout') }}">
            @csrf
            <button class="portal-area-logout" type="submit">Sair</button>
        </form>
    </div>
</header>

<main class="portal-area-shell">
    @if (session('success'))
        <div class="portal-alert is-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="portal-alert is-danger">
            <strong>Não foi possível concluir a operação.</strong><br>
            {{ $errors->first() }}
        </div>
    @endif

    @if (! $estruturaDisponivel)
        <section class="portal-panel portal-panel-pad">
            <h2 class="portal-panel-title">Estrutura de atendimentos não encontrada</h2>
            <p class="portal-panel-muted">A autenticação está funcionando, mas a tabela de atendimentos ainda não está disponível neste ambiente.</p>
        </section>
    @else
        <section class="portal-dashboard-grid">
            <aside class="portal-panel portal-panel-pad">
                <h2 class="portal-panel-title">Meus atendimentos</h2>
                <p class="portal-panel-muted">Veja os chamados vinculados à sua empresa e abra o histórico correto de cada solicitação.</p>

                <div class="portal-summary" aria-label="Resumo dos atendimentos">
                    <div class="portal-summary-card"><strong>{{ $resumo['total'] }}</strong><span>Total</span></div>
                    <div class="portal-summary-card"><strong>{{ $resumo['abertos'] + $resumo['andamento'] }}</strong><span>Ativos</span></div>
                    <div class="portal-summary-card"><strong>{{ $resumo['aguardando'] }}</strong><span>Aguardando</span></div>
                    <div class="portal-summary-card"><strong>{{ $resumo['finalizados'] }}</strong><span>Finalizados</span></div>
                </div>

                <a class="portal-primary-action" href="{{ route('portal.cliente.atendimentos.create') }}">+ Novo atendimento</a>

                <div class="portal-ticket-list">
                    @forelse ($atendimentos as $atendimento)
                        <a class="portal-ticket-item {{ ($atendimentoAtual['id'] ?? null) === $atendimento['id'] ? 'is-active' : '' }}" href="{{ $atendimento['url'] }}">
                            <div class="portal-ticket-top">
                                <div>
                                    <div class="portal-ticket-title">{{ $atendimento['titulo'] }}</div>
                                    <p class="portal-ticket-desc">{{ Str::limit($atendimento['descricao'] ?: 'Sem descrição registrada.', 95) }}</p>
                                </div>
                                <span class="portal-badge {{ $atendimento['status_badge'] }}">{{ $atendimento['status_label'] }}</span>
                            </div>
                            <div class="portal-ticket-meta">
                                <span>{{ $atendimento['protocolo'] }}</span>
                                <span>•</span>
                                <span>{{ $atendimento['interacoes_total'] }} interação(ões)</span>
                                <span>•</span>
                                <span>{{ $atendimento['updated_at_label'] }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="portal-empty">
                            <strong>Nenhum atendimento encontrado.</strong><br>
                            Clique em “Novo atendimento” para falar com o suporte.
                        </div>
                    @endforelse
                </div>
            </aside>

            <section class="portal-panel">
                <div class="portal-chat-head">
                    <div class="portal-chat-title">
                        <h2>{{ $abrirFormulario ? 'Novo atendimento' : ($atendimentoAtual['titulo'] ?? 'Atendimento') }}</h2>
                        <p>{{ $abrirFormulario ? 'Preencha os dados para abrir um chamado com o suporte.' : ($atendimentoAtual['protocolo'] ?? 'Selecione um atendimento') }} @if(! $abrirFormulario && $atendimentoAtual) · {{ $atendimentoAtual['updated_at_label'] }} @endif</p>
                    </div>
                    @if (! $abrirFormulario && $atendimentoAtual)
                        <span class="portal-badge {{ $atendimentoAtual['status_badge'] }}">{{ $atendimentoAtual['status_label'] }}</span>
                    @endif
                </div>

                @if (! $abrirFormulario && $atendimentoAtual)
                    <nav class="portal-tabs" aria-label="Abas do atendimento">
                        <button type="button" class="portal-tab-button is-active" onclick="return window.portalClienteAtivarAba('atendimento')" data-portal-tab="atendimento">Atendimento</button>
                        <button type="button" class="portal-tab-button" onclick="return window.portalClienteAtivarAba('pendencias')" data-portal-tab="pendencias">Pendências</button>
                        <button type="button" class="portal-tab-button" onclick="return window.portalClienteAtivarAba('status')" data-portal-tab="status">Status</button>
                        <button type="button" class="portal-tab-button" onclick="return window.portalClienteAtivarAba('historico')" data-portal-tab="historico">Histórico completo</button>
                    </nav>
                @endif

                @if ($abrirFormulario)
                    <div class="portal-create-wrap">
                        <div class="portal-create-card">
                            <div class="portal-create-card-header">
                                <h3>Abrir novo atendimento</h3>
                                <p>Descreva sua necessidade com clareza. O protocolo será gerado automaticamente e ficará disponível para acompanhamento nesta tela.</p>
                            </div>
                            <form class="portal-create-form" method="POST" action="{{ route('portal.cliente.atendimentos.store') }}">
                                @csrf
                                <input type="text" name="website" value="" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px;height:1px;width:1px;opacity:0;">

                                <div class="portal-form-grid">
                                    <div class="portal-field">
                                        <label for="titulo">Assunto</label>
                                        <input class="portal-input" id="titulo" name="titulo" value="{{ old('titulo') }}" maxlength="180" required placeholder="Ex.: Dúvida sobre contrato, documento ou acesso">
                                    </div>
                                    <div class="portal-field">
                                        <label for="prioridade">Prioridade</label>
                                        <select class="portal-select" id="prioridade" name="prioridade" required>
                                            @foreach ($prioridades as $valor => $label)
                                                <option value="{{ $valor }}" @selected(old('prioridade', 'media') === $valor)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="portal-field">
                                    <label for="categoria">Categoria <small>(opcional)</small></label>
                                    <input class="portal-input" id="categoria" name="categoria" value="{{ old('categoria') }}" maxlength="120" placeholder="Ex.: Financeiro, documentos, contrato, acesso">
                                </div>

                                <div class="portal-field">
                                    <label for="descricao">Descrição</label>
                                    <textarea class="portal-textarea" id="descricao" name="descricao" required maxlength="6000" placeholder="Explique o que aconteceu, o que precisa ser analisado e qualquer detalhe importante.">{{ old('descricao') }}</textarea>
                                    <small>Não inclua senhas ou dados sensíveis.</small>
                                </div>

                                <div class="portal-form-actions">
                                    <a class="portal-secondary-action" href="{{ route('portal.cliente.dashboard') }}">Cancelar</a>
                                    <button class="portal-primary-action" type="submit" style="width:auto;margin-top:0;">Abrir atendimento</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div id="portal-tab-atendimento" class="portal-tab-panel is-active" style="display:block;">
                        <div id="portal-chat-body" class="portal-chat-body {{ $atendimentoAtual ? 'has-composer' : '' }}">
                            @if (! $atendimentoAtual)
                                <div class="portal-empty">
                                    <strong>Selecione ou abra um atendimento.</strong><br>
                                    O histórico aparecerá aqui quando existir um chamado vinculado à sua empresa.
                                </div>
                            @elseif ($interacoes === [])
                                <div class="portal-empty">
                                    <strong>Histórico ainda vazio.</strong><br>
                                    Envie uma mensagem abaixo para iniciar a conversa com o suporte.
                                </div>
                            @else
                                @foreach ($interacoes as $interacao)
                                    <article class="portal-message {{ $interacao['is_cliente'] ? 'is-client' : 'is-support' }}">
                                        @unless ($interacao['is_cliente'])
                                            <div class="portal-message-avatar" aria-hidden="true">S</div>
                                        @endunless

                                        <div class="portal-message-bubble">
                                        <span class="portal-message-author">
                                            {{ $interacao['is_cliente'] ? $clienteNome : ($interacao['usuario_nome'] ?? 'Equipe de suporte') }}
                                            · {{ $interacao['tipo_label'] }}
                                        </span>
                                            @if (trim((string) ($interacao['mensagem'] ?? '')) !== '')
                                                {!! nl2br(e((string) ($interacao['mensagem'] ?? ''))) !!}
                                            @endif

                                            @if (! empty($interacao['anexos']))
                                                <div class="portal-attachment-list">
                                                    @foreach ($interacao['anexos'] as $anexo)
                                                        <div>
                                                            <div class="portal-attachment-card">
                                                                <div class="portal-attachment-icon" aria-hidden="true">{{ strtoupper($anexo['extensao'] ?? 'ARQ') }}</div>
                                                                <div class="portal-attachment-info">
                                                                    <strong>{{ $anexo['nome_original'] }}</strong>
                                                                    <span>{{ $anexo['tamanho_label'] }} · {{ $anexo['mime'] }}</span>
                                                                </div>
                                                                <a class="portal-attachment-download" href="{{ $anexo['download_url'] }}">Baixar</a>
                                                            </div>

                                                            @if (! empty($anexo['is_imagem']))
                                                                <img class="portal-attachment-preview" src="{{ $anexo['preview_url'] }}" alt="Prévia do anexo {{ $anexo['nome_original'] }}">
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <span class="portal-message-time">{{ $interacao['created_at_label'] }}</span>
                                        </div>

                                        @if ($interacao['is_cliente'])
                                            <div class="portal-message-avatar" aria-hidden="true">{{ strtoupper(substr($clienteNome, 0, 1)) }}</div>
                                        @endif
                                    </article>
                                @endforeach
                            @endif
                        </div>

                        @if ($atendimentoAtual)
                            <div class="portal-typing-status" data-support-typing aria-live="polite">
                                <span class="portal-typing-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                                <span data-support-typing-text>Suporte está digitando...</span>
                            </div>
                            <div class="portal-chat-composer">
                                @if (! empty($atendimentoAtual['is_finalizado']))
                                    <div class="portal-chat-locked">Este atendimento está finalizado. Para falar novamente com o suporte, abra um novo atendimento.</div>
                                @else
                                    <form id="portal-chat-form" method="POST" enctype="multipart/form-data" action="{{ route('portal.cliente.atendimentos.mensagem', ['atendimento' => $atendimentoAtual['id']]) }}">
                                        @csrf
                                        <input type="text" name="website" value="" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px;height:1px;width:1px;opacity:0;">
                                        <div class="portal-chat-input-wrap">
                                            <textarea id="portal-chat-textarea" onkeydown="return window.portalClienteEnviarEnter(event, this);" class="portal-chat-textarea" name="mensagem" maxlength="6000" rows="1" placeholder="Digite sua mensagem para o suporte...">{{ old('mensagem') }}</textarea>
                                            <span class="portal-composer-hint">Enter envia a mensagem. Use Shift + Enter para quebrar linha.</span>
                                            <div class="portal-file-row">
                                                <label class="portal-file-button" for="portal-chat-file">📎 Anexar arquivo</label>
                                                <input id="portal-chat-file" class="portal-file-input" type="file" name="anexos[]" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,image/jpeg,image/png,image/webp,application/pdf">
                                                <span id="portal-file-name">Até 5 arquivos: imagem, PDF, Word, Excel, TXT ou CSV com 10 MB cada.</span>
                                            </div>
                                            @error('mensagem')
                                            <span class="portal-field-error">{{ $message }}</span>
                                            @enderror
                                            @error('anexo')
                                            <span class="portal-field-error">{{ $message }}</span>
                                            @enderror
                                            @error('anexos')
                                            <span class="portal-field-error">{{ $message }}</span>
                                            @enderror
                                            @error('anexos.*')
                                            <span class="portal-field-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <button id="portal-chat-send" class="portal-chat-send" type="submit">Enviar</button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if ($atendimentoAtual)
                        <div id="portal-tab-pendencias" class="portal-tab-panel" style="display:none;" hidden>
                            <div class="portal-info-wrap">
                                <div class="portal-info-card">
                                    <h3>Pendências do atendimento</h3>
                                    @if (! empty($atendimentoAtual['is_finalizado']))
                                        <p>Este atendimento está finalizado e não possui pendências abertas para o cliente.</p>
                                    @elseif (($atendimentoAtual['status'] ?? '') === 'aguardando_cliente')
                                        <p>O suporte está aguardando uma resposta sua. Envie uma mensagem na aba Atendimento para dar continuidade.</p>
                                    @elseif (($atendimentoAtual['status'] ?? '') === 'aguardando_suporte')
                                        <p>Sua solicitação já foi enviada. Agora o atendimento está aguardando análise da equipe de suporte.</p>
                                    @else
                                        <p>Não existe nenhuma ação obrigatória para você neste momento. Acompanhe as atualizações pela aba Atendimento.</p>
                                    @endif
                                </div>

                                <div class="portal-info-card">
                                    <h3>Resumo rápido</h3>
                                    <div class="portal-status-grid">
                                        <div class="portal-status-card"><span>Status atual</span><strong>{{ $atendimentoAtual['status_label'] }}</strong></div>
                                        <div class="portal-status-card"><span>SLA/Prazo</span><strong>{{ $atendimentoAtual['sla_limite_label'] }}</strong></div>
                                        <div class="portal-status-card"><span>Responsável</span><strong>{{ $atendimentoAtual['responsavel_nome'] }}</strong></div>
                                        <div class="portal-status-card"><span>Última atualização</span><strong>{{ $atendimentoAtual['updated_at_label'] }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="portal-tab-status" class="portal-tab-panel" style="display:none;" hidden>
                            <div class="portal-info-wrap">
                                <div class="portal-info-card">
                                    <h3>Status do atendimento</h3>
                                    <p>Acompanhe o andamento do protocolo selecionado.</p>
                                    <div class="portal-status-grid">
                                        <div class="portal-status-card"><span>Status</span><strong>{{ $atendimentoAtual['status_label'] }}</strong></div>
                                        <div class="portal-status-card"><span>Protocolo</span><strong>{{ $atendimentoAtual['protocolo'] }}</strong></div>
                                        <div class="portal-status-card"><span>Prioridade</span><strong>{{ $atendimentoAtual['prioridade_label'] }}</strong></div>
                                        <div class="portal-status-card"><span>Abertura</span><strong>{{ $atendimentoAtual['created_at_label'] }}</strong></div>
                                        <div class="portal-status-card"><span>Atualização</span><strong>{{ $atendimentoAtual['updated_at_label'] }}</strong></div>
                                        <div class="portal-status-card"><span>Responsável</span><strong>{{ $atendimentoAtual['responsavel_nome'] }}</strong></div>
                                        <div class="portal-status-card"><span>Prazo/SLA</span><strong>{{ $atendimentoAtual['sla_limite_label'] }}</strong></div>
                                        <div class="portal-status-card"><span>Interações</span><strong>{{ count($interacoes) }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="portal-tab-historico" class="portal-tab-panel" style="display:none;" hidden>
                            <div class="portal-info-wrap">
                                <div class="portal-info-card">
                                    <h3>Histórico completo</h3>
                                    <p>Lista completa das mensagens e movimentações registradas neste atendimento.</p>

                                    <div class="portal-history-list">
                                        @forelse ($interacoes as $interacao)
                                            <article class="portal-history-item">
                                                <div class="portal-history-head">
                                                    <strong>{{ $interacao['is_cliente'] ? $clienteNome : ($interacao['usuario_nome'] ?? 'Equipe de suporte') }} · {{ $interacao['tipo_label'] }}</strong>
                                                    <span>{{ $interacao['created_at_label'] }}</span>
                                                </div>
                                                @if (trim((string) ($interacao['mensagem'] ?? '')) !== '')
                                                    <div class="portal-history-message">{!! nl2br(e((string) ($interacao['mensagem'] ?? ''))) !!}</div>
                                                @else
                                                    <div class="portal-history-message">Interação registrada sem mensagem textual.</div>
                                                @endif
                                                @if (! empty($interacao['anexos']))
                                                    <div class="portal-attachment-list">
                                                        @foreach ($interacao['anexos'] as $anexo)
                                                            <div class="portal-attachment-card">
                                                                <div class="portal-attachment-icon" aria-hidden="true">{{ strtoupper($anexo['extensao'] ?? 'ARQ') }}</div>
                                                                <div class="portal-attachment-info">
                                                                    <strong>{{ $anexo['nome_original'] }}</strong>
                                                                    <span>{{ $anexo['tamanho_label'] }} · {{ $anexo['mime'] }}</span>
                                                                </div>
                                                                <a class="portal-attachment-download" href="{{ $anexo['download_url'] }}">Baixar</a>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </article>
                                        @empty
                                            <div class="portal-empty">Nenhuma interação registrada neste atendimento.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </section>

            <aside class="portal-panel portal-panel-pad portal-right-panel">
                <h2 class="portal-panel-title">Detalhes do atendimento</h2>
                <p class="portal-panel-muted">Informações seguras do atendimento selecionado.</p>

                @if ($abrirFormulario)
                    <div class="portal-empty">
                        <strong>Novo protocolo</strong><br>
                        Após o envio, o sistema cria o atendimento com status Aberto, SLA inicial e primeira interação registrada em nome do cliente.
                    </div>
                @elseif ($atendimentoAtual)
                    <div class="portal-detail-list">
                        <div class="portal-detail-row"><span>Status</span><strong>{{ $atendimentoAtual['status_label'] }}</strong></div>
                        <div class="portal-detail-row"><span>Protocolo</span><strong>{{ $atendimentoAtual['protocolo'] }}</strong></div>
                        <div class="portal-detail-row"><span>Prioridade</span><strong>{{ $atendimentoAtual['prioridade_label'] }}</strong></div>
                        <div class="portal-detail-row"><span>Abertura</span><strong>{{ $atendimentoAtual['created_at_label'] }}</strong></div>
                        <div class="portal-detail-row"><span>Atualização</span><strong>{{ $atendimentoAtual['updated_at_label'] }}</strong></div>
                        <div class="portal-detail-row"><span>Responsável</span><strong>{{ $atendimentoAtual['responsavel_nome'] }}</strong></div>
                        <div class="portal-detail-row"><span>Prazo/SLA</span><strong>{{ $atendimentoAtual['sla_limite_label'] }}</strong></div>
                    </div>
                    <button type="button" class="portal-primary-action" onclick="return window.portalClienteAtivarAba('historico')" data-portal-tab="historico">Ver histórico completo</button>
                @else
                    <div class="portal-empty">Nenhum atendimento selecionado.</div>
                @endif
            </aside>
        </section>
    @endif
</main>

@if(! empty($socketIoConfig['url']))
    <script src="{{ rtrim($socketIoConfig['url'], '/') }}/socket.io/socket.io.js"></script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.portalClienteDebugLog('dom_carregado', {
            href: window.location.href,
            tab_buttons: document.querySelectorAll('[data-portal-tab]').length,
            tab_panels: document.querySelectorAll('.portal-tab-panel').length,
            tem_textarea: !! document.getElementById('portal-chat-textarea'),
            tem_form: !! document.getElementById('portal-chat-form')
        });
        var chatBody = document.getElementById('portal-chat-body');
        var textarea = document.getElementById('portal-chat-textarea');
        var form = document.getElementById('portal-chat-form');
        var sendButton = document.getElementById('portal-chat-send');
        var fileInput = document.getElementById('portal-chat-file');
        var fileName = document.getElementById('portal-file-name');
        var socketConfig = @json($socketIoConfig ?? ['enabled' => false]);
        var portalAreaSocket = null;
        var typingState = { active: false, lastSent: 0, stopTimer: null, supportTimer: null };
        var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
        var tabButtons = Array.prototype.slice.call(document.querySelectorAll('[data-portal-tab]'));
        var tabPanels = Array.prototype.slice.call(document.querySelectorAll('.portal-tab-panel'));

        function activateTab(tabName) {
            return window.portalClienteAtivarAba(tabName);
        }

        function activateTabLegacy(tabName) {
            if (! tabName) return;

            tabButtons.forEach(function (button) {
                button.classList.toggle('is-active', button.getAttribute('data-portal-tab') === tabName);
            });

            tabPanels.forEach(function (panel) {
                panel.classList.toggle('is-active', panel.id === 'portal-tab-' + tabName);
            });

            if (tabName === 'atendimento' && chatBody) {
                setTimeout(function () { chatBody.scrollTop = chatBody.scrollHeight; }, 60);
            }
        }

        tabButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                window.portalClienteDebugLog('click_aba_listener', { tab: button.getAttribute('data-portal-tab'), texto: button.textContent.trim() });
                activateTab(button.getAttribute('data-portal-tab'));
            });
        });

        if (window.location.hash) {
            var hashTab = window.location.hash.replace('#', '').replace('tab-', '');
            if (document.getElementById('portal-tab-' + hashTab)) {
                activateTab(hashTab);
            }
        }

        if (chatBody) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function resizeTextarea() {
            if (! textarea) return;
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 160) + 'px';
        }

        if (textarea) {
            resizeTextarea();
            textarea.addEventListener('input', function () {
                resizeTextarea();
                announceClienteLogadoTyping(textarea.value);
            });
            textarea.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    window.portalClienteDebugLog('keydown_enter_listener', { shift: !! event.shiftKey, isComposing: !! event.isComposing, tamanho: textarea.value.length });
                }

                if (event.key === 'Enter' && ! event.shiftKey && ! event.isComposing) {
                    event.preventDefault();
                    window.portalClienteDebugLog('enter_interceptado_listener', { textarea_id: textarea.id });
                    var hasText = textarea.value.trim() !== '';
                    var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

                    if (form && (hasText || hasFile)) {
                        if (typeof form.requestSubmit === 'function') {
                            form.requestSubmit(sendButton || undefined);
                        } else if (sendButton) {
                            sendButton.click();
                        } else {
                            form.submit();
                        }
                    }
                }
            });
        }

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>'"]/g, function (char) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[char];
            });
        }

        function renderPortalAttachment(anexo) {
            var name = escapeHtml(anexo && anexo.name ? anexo.name : 'arquivo');
            var ext = escapeHtml(anexo && anexo.ext ? anexo.ext : 'ARQ');
            var size = escapeHtml(anexo && anexo.size ? anexo.size : '');
            var mime = escapeHtml(anexo && anexo.mime ? anexo.mime : '');
            var url = escapeHtml(anexo && anexo.url ? anexo.url : '#');
            var previewUrl = escapeHtml(anexo && anexo.preview_url ? anexo.preview_url : '');
            var preview = anexo && anexo.is_image && previewUrl !== ''
                ? '<img class="portal-attachment-preview" src="' + previewUrl + '" alt="Prévia do anexo ' + name + '">'
                : '';

            return '<div><div class="portal-attachment-card">'
                + '<div class="portal-attachment-icon" aria-hidden="true">' + ext + '</div>'
                + '<div class="portal-attachment-info"><strong>' + name + '</strong><span>' + size + (size && mime ? ' · ' : '') + mime + '</span></div>'
                + '<a class="portal-attachment-download" href="' + url + '">Baixar</a>'
                + '</div>' + preview + '</div>';
        }

        function renderPortalMessages(messages) {
            if (! chatBody || ! Array.isArray(messages)) return;

            var currentIds = Array.prototype.slice.call(chatBody.querySelectorAll('[data-interacao-id]'))
                .map(function (el) { return el.getAttribute('data-interacao-id'); })
                .join('|');
            var nextIds = messages.map(function (msg) { return String(msg.id || ''); }).join('|');

            if (currentIds === nextIds) return;

            if (messages.length === 0) {
                chatBody.innerHTML = '<div class="portal-empty">Nenhuma interação registrada ainda.</div>';
                return;
            }

            chatBody.innerHTML = messages.map(function (msg) {
                var isClient = !! msg.is_cliente;
                var attachments = Array.isArray(msg.attachments) && msg.attachments.length > 0
                    ? '<div class="portal-attachment-list">' + msg.attachments.map(renderPortalAttachment).join('') + '</div>'
                    : '';
                var text = escapeHtml(msg.text || '').replace(/\n/g, '<br>');
                var avatar = isClient ? '{{ strtoupper(substr($clienteNome, 0, 1)) }}' : 'S';
                var author = escapeHtml(isClient ? 'Você' : (msg.author || 'Equipe de suporte'));

                return '<article class="portal-message ' + (isClient ? 'is-client' : 'is-support') + '" data-interacao-id="' + escapeHtml(msg.id || '') + '">'
                    + (! isClient ? '<div class="portal-message-avatar" aria-hidden="true">S</div>' : '')
                    + '<div class="portal-message-bubble">'
                    + '<span class="portal-message-author">' + author + '</span>'
                    + (text !== '' ? '<div class="portal-message-text">' + text + '</div>' : '')
                    + attachments
                    + '<span class="portal-message-time">' + escapeHtml(msg.time || '') + '</span>'
                    + '</div>'
                    + (isClient ? '<div class="portal-message-avatar" aria-hidden="true">' + avatar + '</div>' : '')
                    + '</article>';
            }).join('');

            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function appendPortalPendingMessage(text, filesCount) {
            if (! chatBody) return null;
            var id = 'pendente-' + Date.now();
            var artigo = document.createElement('article');
            artigo.className = 'portal-message is-client is-pending';
            artigo.setAttribute('data-interacao-id', id);
            artigo.innerHTML = '<div class="portal-message-bubble">'
                + '<span class="portal-message-author">Você</span>'
                + (text ? '<div class="portal-message-text">' + escapeHtml(text).replace(/\n/g, '<br>') + '</div>' : '')
                + (filesCount > 0 ? '<div class="portal-message-text">📎 ' + filesCount + ' arquivo(s) em envio</div>' : '')
                + '<span class="portal-message-time">agora</span>'
                + '<span class="portal-message-status">Enviando...</span>'
                + '</div>'
                + '<div class="portal-message-avatar" aria-hidden="true">{{ strtoupper(substr($clienteNome, 0, 1)) }}</div>';
            chatBody.appendChild(artigo);
            chatBody.scrollTop = chatBody.scrollHeight;
            return artigo;
        }

        function setSupportTyping(isTyping, name) {
            var box = document.querySelector('[data-support-typing]');
            var text = document.querySelector('[data-support-typing-text]');
            if (! box) return;
            box.classList.toggle('is-visible', Boolean(isTyping));
            if (text) text.textContent = (name || 'Suporte') + ' está digitando...';
        }

        function announceClienteLogadoTyping(value) {
            if (! portalAreaSocket || ! portalAreaSocket.connected) return;

            var hasText = String(value || '').trim() !== '';
            var now = Date.now();

            if (hasText && (! typingState.active || now - typingState.lastSent >= 1800)) {
                typingState.active = true;
                typingState.lastSent = now;
                portalAreaSocket.emit('chat:typing:start', { nome: @json($clienteNome), room: socketConfig.room || '' });
            }

            window.clearTimeout(typingState.stopTimer);

            if (! hasText) {
                if (typingState.active) {
                    typingState.active = false;
                    portalAreaSocket.emit('chat:typing:stop', { room: socketConfig.room || '' });
                }
                return;
            }

            typingState.stopTimer = window.setTimeout(function () {
                if (! portalAreaSocket || ! portalAreaSocket.connected || ! typingState.active) return;
                typingState.active = false;
                portalAreaSocket.emit('chat:typing:stop', { room: socketConfig.room || '' });
            }, 1200);
        }

        function normalizarMensagemSocket(payload) {
            if (! payload || String(payload.scope || '') !== 'portal_cliente_logado') return null;
            if (Number(payload.atendimento_id || 0) !== Number(socketConfig.atendimentoId || 0)) return null;

            return {
                id: payload.interaction_id || payload.id || ('socket-' + Date.now()),
                message_id: payload.id || payload.message_id || null,
                source: payload.source || 'atendimento_interacoes',
                is_cliente: payload.origem === 'cliente',
                author: payload.nome || (payload.origem === 'cliente' ? 'Você' : 'Equipe de suporte'),
                text: payload.mensagem || '',
                attachments: Array.isArray(payload.attachments) ? payload.attachments : [],
                time: payload.created_at_label || 'agora'
            };
        }

        function adicionarMensagemSocket(payload) {
            var msg = normalizarMensagemSocket(payload);
            if (! msg || ! chatBody) return;

            var existente = chatBody.querySelector('[data-interacao-id="' + String(msg.id).replace(/"/g, '\\"') + '"]');
            if (existente) return;

            var atuais = Array.prototype.slice.call(chatBody.querySelectorAll('[data-interacao-id]')).map(function (el) {
                return {
                    id: el.getAttribute('data-interacao-id'),
                    is_cliente: el.classList.contains('is-client'),
                    author: el.querySelector('.portal-message-author') ? el.querySelector('.portal-message-author').textContent : '',
                    text: el.querySelector('.portal-message-text') ? el.querySelector('.portal-message-text').textContent : '',
                    attachments: [],
                    time: el.querySelector('.portal-message-time') ? el.querySelector('.portal-message-time').textContent : ''
                };
            });

            atuais.push(msg);
            renderPortalMessages(atuais);
        }

        function connectPortalClienteLogadoSocket() {
            if (! socketConfig || ! socketConfig.enabled || ! socketConfig.url || ! window.io) return;

            portalAreaSocket = window.io(socketConfig.url, {
                transports: ['websocket', 'polling'],
                auth: {
                    empresaId: socketConfig.empresaId,
                    actor: socketConfig.actor || 'cliente',
                    token: socketConfig.token || '',
                    signature: socketConfig.signature || '',
                    room: socketConfig.room || ''
                }
            });

            portalAreaSocket.on('chat:message:new', function (payload) {
                if (payload && payload.origem !== 'cliente') {
                    adicionarMensagemSocket(payload);
                    setSupportTyping(false);
                    portalAreaSocket.emit('chat:seen', { message_id: payload.id || null, room: socketConfig.room || '', at: new Date().toISOString() });
                }
            });

            portalAreaSocket.on('chat:typing:start', function (payload) {
                if (payload && payload.actor === 'cliente') return;
                setSupportTyping(true, (payload && payload.nome) ? payload.nome : 'Suporte');
                window.clearTimeout(typingState.supportTimer);
                typingState.supportTimer = window.setTimeout(function () { setSupportTyping(false); }, 8000);
            });

            portalAreaSocket.on('chat:typing:stop', function (payload) {
                if (payload && payload.actor === 'cliente') return;
                setSupportTyping(false);
            });

            portalAreaSocket.on('connect_error', function (error) {
                window.portalClienteDebugLog('socket_cliente_logado_erro', { erro: String(error && error.message ? error.message : error) });
            });
        }

        connectPortalClienteLogadoSocket();


        if (fileInput && fileName) {
            fileInput.addEventListener('change', function () {
                var files = fileInput.files ? Array.prototype.slice.call(fileInput.files) : [];

                if (files.length > 5) {
                    fileName.textContent = 'Selecione no máximo 5 arquivos por mensagem.';
                    fileInput.value = '';
                    return;
                }

                fileName.textContent = files.length > 0
                    ? files.map(function (file) { return file.name; }).join(', ')
                    : 'Até 5 arquivos: imagem, PDF, Word, Excel, TXT ou CSV com 10 MB cada.';
            });
        }

        if (form) {
            form.addEventListener('submit', async function (event) {
                window.portalClienteDebugLog('form_submit_disparado', {
                    action: form.getAttribute('action'),
                    method: form.getAttribute('method'),
                    textarea_len: textarea ? textarea.value.trim().length : null,
                    arquivos: fileInput && fileInput.files ? fileInput.files.length : 0,
                    modo: 'ajax_sem_reload'
                });

                var message = textarea ? textarea.value.trim() : '';
                var filesCount = fileInput && fileInput.files ? fileInput.files.length : 0;
                var hasText = message !== '';
                var hasFile = filesCount > 0;

                if (! hasText && ! hasFile) {
                    event.preventDefault();
                    window.portalClienteDebugLog('form_submit_bloqueado_vazio', {});
                    if (textarea) textarea.focus();
                    return;
                }

                if (! window.fetch || ! window.FormData) {
                    if (sendButton) {
                        sendButton.disabled = true;
                        sendButton.textContent = 'Enviando...';
                    }
                    return;
                }

                event.preventDefault();

                var pendingMessage = appendPortalPendingMessage(message, filesCount);
                var formData = new FormData(form);

                if (textarea) {
                    textarea.value = '';
                    resizeTextarea();
                    textarea.focus();
                }
                if (fileInput) fileInput.value = '';
                if (fileName) fileName.textContent = 'Até 5 arquivos: imagem, PDF, Word, Excel, TXT ou CSV com 10 MB cada.';

                if (sendButton) {
                    sendButton.disabled = true;
                    sendButton.textContent = 'Enviando...';
                }

                try {
                    var response = await fetch(form.getAttribute('action'), {
                        method: (form.getAttribute('method') || 'POST').toUpperCase(),
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData,
                        credentials: 'same-origin'
                    });

                    var data = null;
                    try { data = await response.json(); } catch (parseError) { data = null; }

                    if (! response.ok || (data && data.ok === false)) {
                        throw new Error((data && data.message) ? data.message : 'Não foi possível enviar a mensagem.');
                    }

                    if (data && Array.isArray(data.messages)) {
                        renderPortalMessages(data.messages);
                    }

                    if (data && data.chat_message && portalAreaSocket && portalAreaSocket.connected) {
                        data.chat_message.room = data.chat_message.room || socketConfig.room || '';
                        portalAreaSocket.emit('chat:message:new', data.chat_message);
                        typingState.active = false;
                        portalAreaSocket.emit('chat:typing:stop', { room: socketConfig.room || '' });
                    }
                } catch (error) {
                    if (pendingMessage) {
                        pendingMessage.classList.remove('is-pending');
                        pendingMessage.classList.add('is-failed');
                        var status = pendingMessage.querySelector('.portal-message-status');
                        if (status) status.textContent = 'Não enviado. Tente novamente.';
                    }
                    window.portalClienteDebugLog('chat_envio_ajax_erro', { erro: error && error.message ? error.message : String(error) });
                } finally {
                    if (sendButton) {
                        sendButton.disabled = false;
                        sendButton.textContent = 'Enviar';
                    }
                }
            });
        }
    });
</script>
</body>
</html>
