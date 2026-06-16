<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal do Cliente</title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/prazzu-theme.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/prazzu-global.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/prazzu-ui-standard.css')); ?>">
    <style>
        :root {
            --pc-navy: #061735;
            --pc-navy-2: #082456;
            --pc-blue: #0f3f93;
            --pc-blue-2: #1557c0;
            --pc-bg: #f4f7fb;
            --pc-card: #ffffff;
            --pc-line: #dde5f0;
            --pc-text: #07142f;
            --pc-muted: #60708a;
            --pc-soft: #f7f9fc;
            --pc-success: #18b85b;
            --pc-danger: #ef3e36;
            --pc-warning: #ffb33c;
            --pc-radius: 14px;
            --pc-shadow: 0 18px 50px rgba(6, 23, 53, .08);
        }

        * { box-sizing: border-box; }
        html { min-height: 100%; scroll-behavior: smooth; }
        body {
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
            background: radial-gradient(circle at 45% 15%, rgba(15, 63, 147, .06), transparent 35rem), var(--pc-bg);
            color: var(--pc-text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        a { color: inherit; }
        button, input, textarea { font: inherit; }

        .portal-app { min-height: 100vh; display: grid; grid-template-rows: 94px 1fr; }
        .portal-topbar {
            height: 94px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 0 30px;
            background: linear-gradient(120deg, #061735 0%, #09265a 62%, #061735 100%);
            color: #fff;
            box-shadow: 0 12px 32px rgba(6, 23, 53, .22);
            position: sticky;
            top: 0;
            z-index: 20;
        }
        .brand { display: flex; align-items: center; gap: 22px; min-width: 0; }
        .brand-mark {
            width: 48px; height: 48px; border-radius: 12px;
            background: linear-gradient(135deg, #8b7cff, #102a67);
            position: relative; box-shadow: inset 0 0 18px rgba(255,255,255,.18);
            flex: 0 0 auto;
        }
        .brand-mark::after {
            content: ""; position: absolute; right: -4px; bottom: -4px;
            width: 23px; height: 23px; border-radius: 50%; background: #fff;
            box-shadow: 0 5px 16px rgba(0,0,0,.25);
        }
        .brand-divider { width: 1px; height: 42px; background: rgba(255,255,255,.12); }
        .brand h1 { margin: 0; font-size: 22px; line-height: 1; letter-spacing: .01em; font-weight: 900; text-transform: uppercase; }
        .brand p { margin: 9px 0 0; color: rgba(255,255,255,.78); font-size: 15px; }
        .top-actions { display: flex; align-items: center; gap: 22px; }
        .bell { width: 42px; height: 42px; display: grid; place-items: center; position: relative; border-left: 1px solid rgba(255,255,255,.12); padding-left: 18px; color: #fff; background: transparent; border-top: 0; border-right: 0; border-bottom: 0; cursor: pointer; }
        .bell:hover { color: #dbeafe; }
        .bell-badge { position: absolute; top: 6px; right: -6px; min-width: 20px; height: 20px; border-radius: 999px; display: grid; place-items: center; background: var(--pc-danger); color: #fff; font-size: 11px; font-weight: 900; }
        .profile { display: flex; align-items: center; gap: 12px; }
        .profile-note { color: rgba(255,255,255,.58); font-size: 12px; font-weight: 800; }
        .profile-status { font-size: 13px; color: rgba(255,255,255,.78); display: flex; align-items: center; gap: 7px; margin-top: 4px; }
        .profile-status::before { content: ""; width: 13px; height: 13px; border-radius: 50%; background: #22c55e; display: inline-block; }
        .profile strong { display: block; font-size: 15px; }
        .profile-avatar { width: 48px; height: 48px; border-radius: 50%; background: #123f91; display: grid; place-items: center; font-weight: 900; font-size: 17px; box-shadow: inset 0 0 0 1px rgba(255,255,255,.08); }

        .portal-body { display: grid; grid-template-columns: 94px minmax(0, 1fr); min-height: calc(100vh - 94px); }
        .portal-content { min-width: 0; min-height: calc(100vh - 94px); display: grid; grid-template-rows: auto minmax(0, 1fr); }
        .portal-cluster {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 20px 0;
            background: var(--pc-bg);
            overflow-x: auto;
            scrollbar-width: thin;
        }
        .portal-cluster-button {
            flex: 0 0 auto;
            min-height: 44px;
            border: 1px solid var(--pc-line);
            border-radius: 999px;
            padding: 0 16px;
            background: #fff;
            color: #061735;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 10px 24px rgba(6, 23, 53, .05);
            transition: .2s ease;
            white-space: nowrap;
        }
        .portal-cluster-button:hover { border-color: #b9c8df; background: #f8fbff; transform: translateY(-1px); }
        .portal-cluster-button.is-active { background: linear-gradient(135deg, #0f3f93, #07327b); border-color: #0f3f93; color: #fff; box-shadow: 0 12px 28px rgba(15,63,147,.22); }
        .portal-cluster-badge { min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; display: inline-grid; place-items: center; background: var(--pc-danger); color: #fff; font-size: 11px; font-weight: 900; }
        .portal-cluster-button.is-active .portal-cluster-badge { background: rgba(255,255,255,.22); color: #fff; }
        .side-nav {
            background: linear-gradient(180deg, #061735 0%, #031125 100%);
            color: #fff;
            padding: 18px 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            position: sticky;
            top: 94px;
            height: calc(100vh - 94px);
            z-index: 15;
        }
        .side-link {
            width: 82px; min-height: 96px; border-radius: 9px;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px;
            text-decoration: none; color: #fff; font-size: 13px; font-weight: 800;
            position: relative; border: 1px solid transparent; transition: .2s ease;
        }
        .side-link svg { width: 28px; height: 28px; }
        .side-link.active, .side-link.is-active, .side-link:hover { background: rgba(18, 63, 145, .72); border-color: rgba(255,255,255,.07); box-shadow: inset 0 0 0 1px rgba(255,255,255,.03); }
        .side-badge { position: absolute; top: 14px; right: 13px; min-width: 19px; height: 19px; border-radius: 999px; background: var(--pc-danger); color: #fff; display: grid; place-items: center; font-size: 11px; font-weight: 900; }
        .side-separator { width: 62px; height: 1px; background: rgba(255,255,255,.12); }
        .side-exit { margin-top: auto; }

        .workspace {
            min-width: 0;
            padding: 20px 20px 24px;
            display: grid;
            grid-template-columns: minmax(280px, 330px) minmax(420px, 1fr) minmax(300px, 380px);
            gap: 16px;
            align-items: stretch;
            height: calc(100vh - 94px - 58px);
        }
        .workspace.is-single-view { grid-template-columns: minmax(0, 1fr); }
        .workspace.is-single-view .portal-section-hidden { display: none !important; }
        .workspace.is-single-view .chat-panel { min-height: 0; }
        .workspace.is-single-view .right-column { overflow-y: auto; }
        .panel {
            min-width: 0; min-height: 0;
            background: rgba(255,255,255,.94);
            border: 1px solid var(--pc-line);
            border-radius: 9px;
            box-shadow: var(--pc-shadow);
            overflow: hidden;
        }
        .panel-pad { padding: 22px 18px; }
        .panel-title-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 6px; }
        .panel h2 { margin: 0; font-size: 20px; line-height: 1.2; font-weight: 900; letter-spacing: -.02em; }
        .panel-sub { margin: 14px 0 28px; color: #34415a; font-size: 14px; line-height: 1.55; }
        .action-banner {
            grid-column: 1 / -1;
            margin-bottom: 0;
            padding: 15px;
            border: 1px solid #cfe0ff;
            border-radius: 10px;
            background: linear-gradient(135deg, #f5f9ff, #ffffff);
            display: grid;
            gap: 10px;
        }
        .action-banner.is-urgent { border-color: #ffc7c2; background: linear-gradient(135deg, #fff7f6, #ffffff); }
        .action-banner strong { display: block; font-size: 15px; font-weight: 950; color: var(--pc-text); }
        .action-banner span { color: #44536e; font-size: 13px; line-height: 1.45; }
        .action-banner .outline-button { min-height: 42px; }
        .action-banner-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
        .action-banner-copy { min-width: 0; }
        .action-banner-kicker { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 7px; color: #0f3f93; font-size: 12px; font-weight: 950; text-transform: uppercase; letter-spacing: .04em; }
        .action-banner.is-urgent .action-banner-kicker { color: #b42318; }
        .journey-steps { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin-top: 4px; }
        .journey-step { min-width: 0; border: 1px solid #dbe6f5; border-radius: 9px; background: rgba(255,255,255,.78); padding: 10px; display: grid; grid-template-columns: 25px minmax(0, 1fr); gap: 8px; align-items: start; }
        .journey-step-number { width: 25px; height: 25px; border-radius: 50%; display: grid; place-items: center; background: #e8f0ff; color: #0f3f93; font-size: 12px; font-weight: 950; }
        .journey-step strong { font-size: 12px; line-height: 1.25; }
        .journey-step div > span { display: block; margin-top: 3px; font-size: 11px; color: #5a6980; }
        .journey-step.is-current { border-color: #b7cdf4; background: #f5f9ff; box-shadow: inset 0 0 0 1px rgba(15,63,147,.07); }
        .action-banner.is-urgent .journey-step.is-current { border-color: #ffc7c2; background: #fff7f6; }
        .count-dot { min-width: 26px; height: 26px; border-radius: 50%; display: grid; place-items: center; background: var(--pc-danger); color: #fff; font-size: 13px; font-weight: 900; }

        .pending-list { display: grid; gap: 16px; }
        .pending-card {
            border: 1px solid #eadcea;
            border-left-width: 3px;
            border-radius: 7px;
            padding: 17px;
            background: linear-gradient(180deg, #fff, #fff7f7);
        }
        .pending-card.warn { border-color: #f3dbb0; background: linear-gradient(180deg, #fff, #fffbf3); }
        .pending-card.is-primary { border-left-width: 5px; box-shadow: 0 16px 34px rgba(239, 62, 54, .10); }
        .pending-card.is-primary .pending-title::after { content: ' próxima ação'; margin-left: 7px; border-radius: 999px; padding: 4px 7px; background: #ffe0dd; color: #b42318; font-size: 10px; font-weight: 950; text-transform: uppercase; white-space: nowrap; }
        .pending-card.info { border-color: #cfe0ff; background: linear-gradient(180deg, #fff, #f5f9ff); }
        .pending-head { display: flex; gap: 12px; align-items: center; margin-bottom: 14px; }
        .pending-icon { width: 28px; height: 28px; border-radius: 7px; display: grid; place-items: center; color: #fff; background: var(--pc-danger); flex: 0 0 auto; }
        .pending-card.warn .pending-icon { background: var(--pc-warning); }
        .pending-card.info .pending-icon { background: #0b63e5; }
        .pending-title { margin: 0; font-size: 14px; font-weight: 900; }
        .priority { margin-left: auto; border-radius: 999px; padding: 7px 9px; background: #ffe0dd; color: var(--pc-danger); font-size: 11px; font-weight: 900; text-transform: uppercase; }
        .pending-card.warn .priority { background: #fff0cf; color: #e98700; }
        .pending-text { margin: 0 0 17px; color: #2f3d57; font-size: 14px; line-height: 1.55; }
        .pending-guidance { margin: -4px 0 16px; border-radius: 8px; padding: 10px 12px; background: #f6f8fc; color: #35435d; font-size: 13px; line-height: 1.45; border: 1px solid #e3eaf4; }
        .pending-flow-meta { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
        .pending-order { display: inline-flex; align-items: center; gap: 6px; border-radius: 999px; padding: 6px 9px; background: #eef4ff; color: #113e86; font-size: 12px; font-weight: 900; }
        .deadline { display: inline-flex; align-items: center; gap: 7px; color: var(--pc-danger); font-size: 13px; font-weight: 800; }
        .pending-card.warn .deadline { color: #f28a00; }
        .pending-card.info .deadline { color: #0b63e5; }
        .all-link, .outline-button {
            width: 100%; min-height: 46px; border: 1px solid var(--pc-line); background: #fff; border-radius: 8px;
            display: flex; align-items: center; justify-content: center; gap: 12px; text-decoration: none;
            font-size: 14px; font-weight: 900; color: #061735; cursor: pointer; transition: .2s ease;
        }
        .all-link:hover, .outline-button:hover { border-color: #b9c8df; background: #f8fbff; transform: translateY(-1px); }
        .pending-card.is-extra-hidden { display: none; }
        .pending-list.is-expanded .pending-card.is-extra-hidden { display: block; }
        .button-loading-text { display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .button-loading-text::before { content: ""; width: 14px; height: 14px; border: 2px solid currentColor; border-right-color: transparent; border-radius: 50%; animation: pcSpin .8s linear infinite; }
        @keyframes pcSpin { to { transform: rotate(360deg); } }
        .client-toast { position: fixed; left: 50%; bottom: 24px; transform: translateX(-50%) translateY(18px); z-index: 80; width: min(520px, calc(100vw - 32px)); border-radius: 12px; padding: 13px 15px; background: #061735; color: #fff; box-shadow: 0 18px 48px rgba(6,23,53,.24); font-weight: 850; line-height: 1.45; opacity: 0; pointer-events: none; transition: .24s ease; }
        .client-toast.is-visible { opacity: 1; transform: translateX(-50%) translateY(0); }
        .client-toast.is-error { background: #b42318; }

        .chat-panel { display: grid; grid-template-rows: 64px minmax(0, 1fr) auto; }
        .chat-header { padding: 0 18px; display: flex; align-items: center; justify-content: space-between; gap: 16px; border-bottom: 1px solid var(--pc-line); }
        .chat-title { display: flex; align-items: center; gap: 16px; min-width: 0; }
        .chat-title h2 { margin: 0; font-size: 20px; font-weight: 900; white-space: nowrap; }
        .online-badge, .status-pill { display: inline-flex; align-items: center; gap: 8px; border-radius: 999px; padding: 8px 14px; background: #dff7e9; color: #138844; font-size: 13px; font-weight: 900; white-space: nowrap; }
        .status-pill::before, .online-badge::before { content: ""; width: 11px; height: 11px; border-radius: 50%; background: currentColor; display: inline-block; }
        .status-pill.warn { background: #fff0cf; color: #b96800; }
        .status-pill.ok { background: #dff7e9; color: #138844; }
        .online-badge { background: #eef4ff; color: #0f3f93; }

        .chat-safe-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 0 14px;
            border-radius: 999px;
            background: rgba(22, 163, 74, .12);
            color: #166534;
            font-size: .82rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .chat-tools { display: flex; gap: 12px; color: #0c1935; }
        .tool-btn { border: 0; background: transparent; width: 34px; height: 34px; display: grid; place-items: center; border-radius: 8px; cursor: pointer; color: inherit; }
        .tool-btn:hover { background: #f1f5fb; }

        .chat-scroll { min-height: 0; overflow-y: auto; padding: 25px 20px 28px; scroll-behavior: smooth; background: #fff; }
        .message-row { display: flex; gap: 12px; margin-bottom: 14px; align-items: flex-start; }
        .message-row.cliente { justify-content: flex-end; }
        .message-avatar { width: 41px; height: 41px; border-radius: 50%; background: linear-gradient(135deg, #d8e3f3, #eef3fb); color: #061735; display: grid; place-items: center; font-weight: 900; flex: 0 0 auto; overflow: hidden; }
        .message-row.cliente .message-avatar { display: none; }
        .bubble-wrap { max-width: min(68%, 620px); }
        .bubble {
            border-radius: 9px;
            padding: 13px 15px 10px;
            background: #f0f2f6;
            color: #061735;
            box-shadow: 0 6px 18px rgba(6,23,53,.04);
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            line-height: 1.45;
            font-size: 14px;
        }
        .message-row.cliente .bubble { background: linear-gradient(135deg, #0f3f93, #07327b); color: #fff; border-bottom-right-radius: 5px; }
        .message-row.equipe .bubble { border-bottom-left-radius: 5px; }
        .bubble-name { display: block; margin-bottom: 8px; font-weight: 900; }
        .bubble-time { display: flex; align-items: center; gap: 6px; justify-content: flex-start; margin-top: 8px; color: #68758c; font-size: 12px; }
        .message-row.cliente .bubble-time { justify-content: flex-end; color: rgba(255,255,255,.88); }
        .attachment-grid { display: grid; grid-template-columns: repeat(2, minmax(180px, 1fr)); gap: 10px; margin-top: 12px; max-width: min(80%, 540px); }
        .message-row.cliente .attachment-grid { margin-left: auto; }
        .attachment-card { display: grid; grid-template-columns: 42px minmax(0, 1fr) 28px; gap: 10px; align-items: center; padding: 11px; border: 1px solid var(--pc-line); border-radius: 8px; background: #fff; text-decoration: none; color: var(--pc-text); min-width: 0; }
        .file-icon { width: 38px; height: 38px; border-radius: 6px; display: grid; place-items: center; background: #ef4444; color: #fff; font-weight: 900; font-size: 11px; text-transform: uppercase; }
        .file-icon.image { background: #18a957; }
        .file-info strong { display: block; font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .file-info span { display: block; margin-top: 4px; color: var(--pc-muted); font-size: 12px; }
        .download-icon { color: #0b63e5; font-size: 20px; text-align: center; }
        .attachment-preview { grid-column: 1 / -1; width: 100%; max-height: 150px; object-fit: cover; border-radius: 7px; border: 1px solid #edf1f6; margin-top: 4px; }
        .empty-chat { min-height: 300px; display: grid; place-items: center; text-align: center; color: var(--pc-muted); border: 1px dashed #cad4e3; border-radius: 10px; background: #fbfcff; padding: 24px; }
        .reaction { display: inline-flex; align-items: center; gap: 5px; margin-left: 54px; margin-bottom: 12px; border: 1px solid #e3e9f2; border-radius: 999px; padding: 4px 9px; background: #fff; font-size: 12px; color: #334155; }

        .composer { margin: 0 18px 16px; border: 1px solid #95b4e8; border-radius: 7px; padding: 10px 10px 8px; background: #fff; }
        .composer-client-summary { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 8px; padding: 9px 11px; border-radius: 7px; background: #f6f8fc; border: 1px solid #e1e7f0; color: var(--pc-muted); font-size: 12px; font-weight: 800; }
        .composer-client-summary strong { color: var(--pc-text); font-size: 13px; }
        .composer-row { display: grid; grid-template-columns: 32px 32px minmax(0, 1fr) 54px; align-items: center; gap: 9px; }
        .icon-input { width: 32px; height: 32px; border: 0; background: transparent; display: grid; place-items: center; border-radius: 7px; color: #31435f; cursor: pointer; }
        .icon-input:hover { background: #f2f6fb; }
        .file-control { position: relative; overflow: hidden; }
        .file-control input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .composer textarea { width: 100%; min-height: 38px; max-height: 150px; resize: none; border: 0; outline: none; padding: 9px 4px; color: var(--pc-text); }
        .send-button { width: 54px; height: 48px; border: 0; border-radius: 7px; background: linear-gradient(135deg, #0f3f93, #07327b); color: #fff; display: grid; place-items: center; cursor: pointer; transition: .2s ease; }
        .send-button:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(15,63,147,.25); }
        .composer-help { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: 6px 8px 0; color: var(--pc-muted); font-size: 12px; }
        .selected-files { display: none; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
        .selected-files.is-visible { display: flex; }
        .file-chip { display: inline-flex; align-items: center; gap: 5px; max-width: 100%; border-radius: 999px; background: #eef4ff; color: #113e86; padding: 6px 9px; font-size: 12px; font-weight: 800; }

        .right-column { display: grid; gap: 16px; min-height: 0; overflow-y: auto; padding-right: 2px; }
        .status-card { padding: 22px; }
        .status-card + .status-card { margin-top: 0; }
        .divider { height: 1px; background: var(--pc-line); margin: 18px 0; }
        .status-desc { margin: 18px 0 0; color: #263553; font-size: 14px; line-height: 1.55; }
        .info-list { display: grid; }
        .info-row { display: grid; grid-template-columns: 26px 1fr auto; gap: 10px; align-items: center; min-height: 48px; border-top: 1px solid var(--pc-line); font-size: 14px; }
        .info-row:first-child { border-top: 0; }
        .info-row strong { font-weight: 900; }
        .info-row span:last-child { text-align: right; }
        .person { display: flex; align-items: center; gap: 10px; }
        .small-avatar { width: 32px; height: 32px; border-radius: 50%; background: #0c1935; color: #fff; display: grid; place-items: center; font-size: 12px; font-weight: 900; }
        .person small { display: block; color: var(--pc-muted); margin-top: 2px; }
        .progress-line { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0; position: relative; margin: 22px 0 10px; }
        .progress-line::before { content: ""; position: absolute; left: 12%; right: 12%; top: 17px; height: 2px; background: #d7dde8; z-index: 0; }
        .step { position: relative; z-index: 1; text-align: center; color: #3f4e67; font-size: 12px; line-height: 1.35; }
        .step-circle { width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; margin: 0 auto 12px; background: #d7dde8; color: #0c1935; font-weight: 900; }
        .step.done .step-circle { background: var(--pc-success); color: #fff; }
        .step.active .step-circle { background: #123f91; color: #fff; }
        .summary-text { color: #1d2d49; line-height: 1.7; font-size: 14px; margin: 0; }
        .mini-form { display: none; margin-top: 14px; gap: 8px; }
        .pending-card.is-open .mini-form { display: grid; }
        .input, .textarea { width: 100%; border: 1px solid #d9e1ec; border-radius: 8px; padding: 10px 12px; background: #fff; color: var(--pc-text); outline: none; }
        .textarea { resize: vertical; min-height: 86px; }
        .btn { border: 0; border-radius: 8px; min-height: 42px; padding: 0 14px; background: #0f3f93; color: #fff; font-weight: 900; cursor: pointer; }
        .btn:hover { background: #07327b; }
        .alerts { position: fixed; right: 20px; bottom: 20px; z-index: 50; display: grid; gap: 10px; width: min(420px, calc(100vw - 40px)); }
        .alert, .errors { border-radius: 10px; padding: 13px 15px; box-shadow: 0 16px 40px rgba(6,23,53,.18); font-weight: 800; line-height: 1.45; }
        .alert { background: #dff7e9; color: #0f7d3c; border: 1px solid #a9e7c2; }
        .errors { background: #ffe7e5; color: #b42318; border: 1px solid #ffc7c2; }
        .errors ul { margin: 8px 0 0; padding-left: 18px; font-weight: 700; }
        .field-error { color: #b42318; font-size: 12px; font-weight: 800; margin-top: 4px; }
        .hp-field { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
        .is-processing { opacity: .75; pointer-events: none; }
        .mobile-tabs { display: none; }
        .side-link { border: 1px solid transparent; background: transparent; cursor: pointer; }

        @media (max-width: 1280px) {
            .workspace { grid-template-columns: 300px minmax(400px, 1fr) 330px; }
            .bubble-wrap { max-width: 76%; }
        }
        @media (max-width: 1120px) {
            .portal-body { grid-template-columns: 1fr; }
            .portal-content { min-height: auto; }
            .side-nav { display: none; }
            .portal-app { grid-template-rows: auto 1fr; }
            .portal-topbar { position: relative; height: auto; min-height: 82px; padding: 18px; flex-wrap: wrap; }
            .workspace { height: auto; min-height: calc(100vh - 94px); grid-template-columns: 1fr; padding: 14px; }
            .portal-cluster { padding: 10px 14px 0; }
            .chat-panel { min-height: min(620px, calc(100vh - 160px)); }
            .journey-steps { grid-template-columns: 1fr; }
            .right-column { overflow: visible; }
            .mobile-tabs { display: flex; gap: 8px; padding: 10px 14px 0; background: var(--pc-bg); overflow-x: auto; }
            .mobile-tabs a { flex: 0 0 auto; text-decoration: none; border-radius: 999px; padding: 9px 13px; background: #fff; border: 1px solid var(--pc-line); font-size: 13px; font-weight: 900; }
        }
        @media (max-width: 680px) {
            .brand-divider, .bell, .profile-copy { display: none; }
            .brand h1 { font-size: 18px; }
            .workspace { padding: 10px; gap: 10px; }
            .panel-pad, .status-card { padding: 16px; }
            .chat-panel { min-height: min(540px, calc(100vh - 135px)); }
            .action-banner-header { display: grid; }
            .pending-card.is-primary .pending-title::after { display: inline-block; margin: 6px 0 0; }
            .chat-header { padding: 0 12px; }
            .chat-title { gap: 8px; flex-wrap: wrap; }
            .chat-title h2 { font-size: 18px; }
            .online-badge { padding: 7px 10px; }
            .chat-scroll { padding: 16px 12px 20px; }
            .bubble-wrap { max-width: 86%; }
            .attachment-grid { grid-template-columns: 1fr; max-width: 92%; }
            .composer { margin: 0 10px 10px; }
            .composer-client-summary { align-items: flex-start; flex-direction: column; }
            .composer-row { grid-template-columns: 30px 30px minmax(0, 1fr) 48px; gap: 4px; }
            .send-button { width: 48px; height: 46px; }
            .info-row { grid-template-columns: 24px 1fr; }
            .info-row span:last-child { grid-column: 2; text-align: left; }
            .progress-line { grid-template-columns: repeat(2, minmax(0, 1fr)); row-gap: 18px; }
            .progress-line::before { display: none; }
        }


        /* LOTE 3 - Polimento profissional de UX/UI sem arquivo externo */
        .portal-topbar, .side-nav, .panel, .portal-cluster-button, .side-link, .pending-card, .bubble, .attachment-card, .composer, .status-card, .action-banner {
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease, transform .2s ease, opacity .2s ease;
        }
        .portal-cluster-button:focus-visible,
        .side-link:focus-visible,
        .bell:focus-visible,
        .outline-button:focus-visible,
        .all-link:focus-visible,
        .btn:focus-visible,
        .send-button:focus-visible,
        .icon-input:focus-within,
        .tool-btn:focus-visible {
            outline: 3px solid rgba(15, 63, 147, .24);
            outline-offset: 3px;
        }
        .panel:hover { box-shadow: 0 22px 56px rgba(6, 23, 53, .105); }
        .action-banner { position: relative; overflow: hidden; }
        .action-banner::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: linear-gradient(180deg, #0f3f93, #7da2ef);
        }
        .action-banner.is-urgent::before { background: linear-gradient(180deg, #ef3e36, #ffb33c); }
        .journey-step.is-current .journey-step-number { animation: pcPulseSoft 1.9s ease-in-out infinite; }
        @keyframes pcPulseSoft {
            0%, 100% { box-shadow: 0 0 0 0 rgba(15, 63, 147, .22); }
            50% { box-shadow: 0 0 0 8px rgba(15, 63, 147, 0); }
        }
        .pending-card { position: relative; overflow: hidden; }
        .pending-card:hover { transform: translateY(-2px); box-shadow: 0 18px 36px rgba(6, 23, 53, .09); }
        .pending-card.is-processing { opacity: .82; pointer-events: none; }
        .pending-card.is-processing::after,
        .composer.is-processing::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.64), transparent);
            animation: pcShimmer 1.1s linear infinite;
        }
        @keyframes pcShimmer {
            from { transform: translateX(-100%); }
            to { transform: translateX(100%); }
        }
        .mini-form { margin-top: 14px; display: none; gap: 10px; }
        .pending-card.is-open .mini-form { display: grid; animation: pcSlideIn .2s ease both; }
        .textarea {
            width: 100%;
            min-height: 96px;
            border: 1px solid #d8e2f0;
            border-radius: 9px;
            padding: 12px;
            color: var(--pc-text);
            outline: none;
            resize: vertical;
            background: #fff;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .textarea:focus { border-color: #7fa5e7; box-shadow: 0 0 0 4px rgba(15,63,147,.10); }
        .textarea.is-invalid, .composer.is-invalid { border-color: #ef3e36 !important; box-shadow: 0 0 0 4px rgba(239,62,54,.10); }
        .btn {
            min-height: 44px;
            border: 0;
            border-radius: 8px;
            background: linear-gradient(135deg, #0f3f93, #07327b);
            color: #fff;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            transition: .2s ease;
        }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(15,63,147,.22); }
        .btn:disabled, .send-button:disabled, .outline-button:disabled, .all-link:disabled { cursor: not-allowed; opacity: .72; transform: none; }
        .chat-header { position: relative; }
        .chat-header::after {
            content: "";
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: -1px;
            height: 1px;
            background: linear-gradient(90deg, rgba(15,63,147,.28), transparent);
        }
        .message-row { animation: pcMessageIn .22s ease both; }
        .message-row:nth-last-child(1) { animation-delay: .02s; }
        @keyframes pcMessageIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .bubble a { color: inherit; font-weight: 900; text-decoration-thickness: 2px; text-underline-offset: 3px; }
        .message-row.equipe .bubble a { color: #0f3f93; }
        .attachment-card:hover { border-color: #b9c8df; background: #f8fbff; transform: translateY(-1px); }
        .composer { position: relative; }
        .composer:focus-within { border-color: #0f3f93; box-shadow: 0 0 0 4px rgba(15,63,147,.09); }
        .composer.is-invalid { animation: pcShake .22s ease; }
        @keyframes pcShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-3px); }
            75% { transform: translateX(3px); }
        }
        .selected-files.is-visible { padding: 8px; border-radius: 8px; background: #f8fbff; border: 1px dashed #cddbf0; }
        .field-error { margin-top: 8px; color: #b42318; font-size: 12px; font-weight: 850; }
        .client-toast { display: flex; align-items: flex-start; gap: 10px; }
        .client-toast::before { content: "✓"; flex: 0 0 auto; width: 20px; height: 20px; border-radius: 999px; display: grid; place-items: center; background: rgba(255,255,255,.16); font-size: 12px; margin-top: 1px; }
        .client-toast.is-error::before { content: "!"; }
        .client-toast.is-success { background: #166534; }
        .alerts { position: fixed; right: 18px; top: 110px; z-index: 75; width: min(440px, calc(100vw - 32px)); display: grid; gap: 10px; pointer-events: none; }
        .alert, .errors { pointer-events: auto; border-radius: 12px; padding: 14px 16px; box-shadow: 0 18px 48px rgba(6,23,53,.18); background: #fff; border: 1px solid var(--pc-line); }
        .alert { border-color: rgba(24,184,91,.25); color: #14532d; }
        .errors { border-color: rgba(239,62,54,.26); color: #7f1d1d; }
        .errors ul { margin: 8px 0 0 18px; padding: 0; }
        .status-pill.warn::before { animation: pcPulseSoft 1.9s ease-in-out infinite; }
        .progress-line .step.active .step-circle { box-shadow: 0 0 0 5px rgba(15,63,147,.10); }
        @keyframes pcSlideIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
        @media (max-width: 720px) {
            .alerts { top: auto; right: 12px; left: 12px; bottom: 86px; width: auto; }
            .pending-card:hover, .attachment-card:hover, .panel:hover { transform: none; }
        }


        /* REFATORAÇÃO ESTRUTURAL UX/UI - layout respirável, scroll real e mobile-first */
        html,
        body {
            width: 100%;
            min-height: 100%;
            overflow-x: hidden;
            overflow-y: auto;
        }

        body {
            min-width: 320px;
            -webkit-text-size-adjust: 100%;
        }

        .portal-app {
            min-height: 100dvh;
            display: block;
        }

        .portal-topbar {
            min-height: 86px;
            height: auto;
            padding: 18px 28px;
        }

        .portal-body {
            min-height: auto;
            display: grid;
            grid-template-columns: 94px minmax(0, 1fr);
            align-items: start;
        }

        .portal-content {
            display: block;
            min-width: 0;
            min-height: auto;
        }

        .portal-cluster {
            position: sticky;
            top: 86px;
            z-index: 14;
            padding: 14px 24px 12px;
            background: rgba(244, 247, 251, .96);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(221, 229, 240, .85);
        }

        .side-nav {
            top: 86px;
            height: calc(100dvh - 86px);
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .workspace {
            width: 100%;
            max-width: 1680px;
            margin: 0 auto;
            height: auto !important;
            min-height: 0 !important;
            padding: 24px;
            display: grid;
            grid-template-columns: minmax(300px, 360px) minmax(0, 1fr) minmax(310px, 390px);
            gap: 22px;
            align-items: start;
            overflow: visible;
        }

        .workspace.is-single-view {
            grid-template-columns: minmax(0, 980px);
            justify-content: center;
        }

        .panel {
            min-width: 0;
            min-height: 0;
            overflow: visible;
            border-radius: 16px;
        }

        .panel-pad,
        .status-card {
            padding: 24px;
        }

        .action-banner {
            grid-column: 1 / -1;
            padding: 22px 24px;
        }

        .action-banner-header {
            align-items: center;
        }

        .action-banner .outline-button {
            width: auto;
            min-width: 220px;
            padding-inline: 18px;
            flex: 0 0 auto;
        }

        .journey-steps {
            gap: 12px;
        }

        .journey-step {
            padding: 14px;
            border-radius: 12px;
        }

        .pending-list,
        .right-column {
            gap: 18px;
        }

        .pending-card {
            border-radius: 14px;
            padding: 20px;
        }

        .pending-head {
            align-items: flex-start;
        }

        .pending-title {
            min-width: 0;
            line-height: 1.35;
        }

        .priority {
            flex: 0 0 auto;
        }

        .chat-panel {
            display: grid;
            grid-template-rows: auto minmax(280px, 1fr) auto;
            min-height: 620px;
            max-height: calc(100dvh - 174px);
            overflow: hidden;
            position: sticky;
            top: 158px;
        }

        .chat-header {
            min-height: 72px;
            padding: 16px 20px;
        }

        .chat-scroll {
            min-height: 280px;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 24px 22px 28px;
        }

        .composer {
            margin: 0 18px 18px;
        }

        .right-column {
            display: grid;
            min-height: 0;
            overflow: visible;
            padding-right: 0;
        }

        .bubble-wrap {
            max-width: min(76%, 680px);
            min-width: 0;
        }

        .attachment-grid {
            width: min(100%, 560px);
        }

        .info-row {
            grid-template-columns: 26px minmax(0, 1fr) minmax(90px, auto);
        }

        .profile,
        .brand,
        .top-actions,
        .chat-title,
        .composer-row,
        .info-row,
        .person,
        .pending-head {
            min-width: 0;
        }

        .brand p,
        .profile strong,
        .pending-text,
        .summary-text,
        .status-desc,
        .info-row span,
        .info-row strong {
            overflow-wrap: anywhere;
        }

        @media (max-width: 1360px) {
            .workspace {
                grid-template-columns: minmax(280px, 330px) minmax(0, 1fr) minmax(280px, 340px);
                gap: 18px;
            }

            .panel-pad,
            .status-card {
                padding: 22px;
            }
        }

        @media (max-width: 1180px) {
            .portal-topbar {
                position: relative;
                top: auto;
                padding: 16px 18px;
            }

            .portal-body {
                grid-template-columns: 1fr;
            }

            .side-nav {
                display: none;
            }

            .portal-cluster {
                top: 0;
                padding: 12px 16px;
            }

            .workspace,
            .workspace.is-single-view {
                max-width: 100%;
                grid-template-columns: 1fr;
                justify-content: stretch;
                padding: 18px 16px 26px;
                gap: 18px;
            }

            .workspace.is-single-view .portal-section-hidden {
                display: none !important;
            }

            .chat-panel {
                position: relative;
                top: auto;
                min-height: 560px;
                max-height: none;
            }

            .chat-scroll {
                max-height: min(62dvh, 620px);
            }

            .right-column {
                overflow: visible;
            }

            .action-banner-header {
                align-items: flex-start;
            }
        }

        @media (max-width: 760px) {
            :root {
                --pc-radius: 12px;
            }

            body {
                background: var(--pc-bg);
            }

            .portal-topbar {
                gap: 14px;
                padding: 14px 14px 12px;
                align-items: flex-start;
            }

            .brand {
                gap: 12px;
                flex: 1 1 180px;
            }

            .brand-mark {
                width: 40px;
                height: 40px;
                border-radius: 11px;
            }

            .brand-mark::after {
                width: 18px;
                height: 18px;
            }

            .brand h1 {
                font-size: 17px;
                line-height: 1.15;
            }

            .brand p {
                margin-top: 5px;
                font-size: 12px;
            }

            .top-actions {
                gap: 10px;
            }

            .profile-avatar {
                width: 40px;
                height: 40px;
                font-size: 14px;
            }

            .profile-note,
            .profile-copy,
            .brand-divider {
                display: none !important;
            }

            .bell {
                width: 40px;
                height: 40px;
                padding-left: 0;
                border-left: 0;
                border-radius: 12px;
                background: rgba(255,255,255,.08);
            }

            .bell-badge {
                top: -4px;
                right: -5px;
            }

            .portal-cluster {
                padding: 10px 12px;
                gap: 8px;
                scrollbar-width: none;
            }

            .portal-cluster::-webkit-scrollbar {
                display: none;
            }

            .portal-cluster-button {
                min-height: 40px;
                padding: 0 13px;
                font-size: 13px;
            }

            .workspace,
            .workspace.is-single-view {
                padding: 14px 12px 22px;
                gap: 14px;
            }

            .panel {
                border-radius: 14px;
                box-shadow: 0 12px 32px rgba(6, 23, 53, .07);
            }

            .panel-pad,
            .status-card {
                padding: 18px 16px;
            }

            .panel-title-row {
                gap: 10px;
            }

            .panel h2 {
                font-size: 18px;
            }

            .panel-sub {
                margin: 10px 0 18px;
                font-size: 13px;
            }

            .action-banner {
                padding: 18px 16px;
            }

            .action-banner-header {
                display: grid;
                gap: 14px;
            }

            .action-banner .outline-button {
                width: 100%;
                min-width: 0;
            }

            .journey-steps {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .pending-card {
                padding: 16px;
                border-radius: 12px;
            }

            .pending-head {
                display: grid;
                grid-template-columns: 30px minmax(0, 1fr);
                gap: 10px;
            }

            .priority {
                grid-column: 2;
                width: fit-content;
                margin-left: 0;
            }

            .pending-card.is-primary .pending-title::after {
                display: inline-flex;
                margin: 7px 0 0;
            }

            .pending-flow-meta {
                align-items: flex-start;
                display: grid;
            }

            .chat-panel {
                min-height: 0;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            .chat-header {
                min-height: auto;
                padding: 14px 14px 12px;
                display: grid;
                gap: 10px;
            }

            .chat-title {
                align-items: flex-start;
                display: grid;
                gap: 8px;
            }

            .chat-title h2 {
                font-size: 18px;
                white-space: normal;
            }

            .chat-tools {
                justify-content: flex-start;
            }

            .online-badge,
            .chat-safe-label,
            .status-pill {
                width: fit-content;
                max-width: 100%;
                white-space: normal;
                padding: 7px 10px;
                font-size: 12px;
            }

            .chat-scroll {
                min-height: 360px;
                max-height: 58dvh;
                padding: 16px 12px 18px;
                flex: 1 1 auto;
            }

            .message-row {
                gap: 9px;
                margin-bottom: 12px;
            }

            .message-avatar {
                width: 34px;
                height: 34px;
                font-size: 12px;
            }

            .bubble-wrap,
            .attachment-grid {
                max-width: calc(100vw - 76px);
                width: auto;
            }

            .message-row.cliente .bubble-wrap,
            .message-row.cliente .attachment-grid {
                max-width: calc(100vw - 42px);
            }

            .bubble {
                padding: 12px 13px 9px;
                font-size: 13px;
                border-radius: 12px;
            }

            .attachment-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .attachment-card {
                grid-template-columns: 38px minmax(0, 1fr) 24px;
                padding: 9px;
            }

            .composer {
                margin: 0 10px 10px;
                padding: 10px;
                border-radius: 12px;
            }

            .composer-client-summary {
                display: grid;
                gap: 4px;
                padding: 9px;
            }

            .composer-row {
                grid-template-columns: 34px 34px minmax(0, 1fr) 46px;
                gap: 6px;
            }

            .icon-input {
                width: 34px;
                height: 38px;
            }

            .composer textarea {
                min-height: 42px;
                font-size: 14px;
            }

            .send-button {
                width: 46px;
                height: 46px;
                border-radius: 10px;
            }

            .composer-help {
                display: grid;
                gap: 4px;
                margin: 8px 4px 0;
                font-size: 11px;
            }

            .info-row {
                grid-template-columns: 24px minmax(0, 1fr);
                align-items: start;
                padding: 12px 0;
            }

            .info-row span:last-child {
                grid-column: 2;
                text-align: left;
            }

            .progress-line {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 18px 8px;
            }

            .progress-line::before {
                display: none;
            }

            .alerts {
                top: auto;
                right: 12px;
                left: 12px;
                bottom: 82px;
                width: auto;
            }

            .client-toast {
                bottom: 16px;
                width: calc(100vw - 24px);
            }
        }

        @media (max-width: 420px) {
            .workspace,
            .workspace.is-single-view {
                padding-inline: 10px;
            }

            .panel-pad,
            .status-card,
            .action-banner {
                padding-inline: 14px;
            }

            .composer-row {
                grid-template-columns: 32px 32px minmax(0, 1fr) 42px;
                gap: 4px;
            }

            .send-button {
                width: 42px;
            }

            .portal-cluster-button {
                font-size: 12px;
                padding-inline: 11px;
            }
        }




        /* AJUSTE UX CHAT - envio instantâneo visual e bolhas compactas tipo aplicativo de mensagem */
        .message-row {
            width: 100%;
        }

        .bubble-wrap {
            width: fit-content;
            max-width: min(74%, 680px);
        }

        .message-row.cliente .bubble-wrap {
            margin-left: auto;
        }

        .bubble {
            display: inline-block;
            width: auto;
            max-width: 100%;
            min-width: 0;
            padding: 9px 12px 7px;
            border-radius: 16px;
            line-height: 1.42;
            box-shadow: 0 5px 14px rgba(6, 23, 53, .045);
        }

        .message-row.cliente .bubble {
            border-bottom-right-radius: 5px;
            background: #0f3f93;
        }

        .message-row.equipe .bubble {
            border-bottom-left-radius: 5px;
            background: #f1f4f8;
        }

        .message-row.cliente .bubble-name {
            display: none;
        }

        .bubble-time {
            margin-top: 5px;
            font-size: 11px;
            opacity: .82;
        }

        .message-row.is-optimistic .bubble {
            position: relative;
            opacity: .96;
        }


        .message-row.is-sent .bubble-time::after { content: '✓'; font-weight: 900; margin-left: 2px; }
        .message-row.is-failed .bubble { background: #b42318 !important; }
        .message-row.is-failed .bubble-time::after { content: 'toque para tentar novamente'; margin-left: 6px; font-weight: 900; }
        .composer.is-processing .send-button { opacity: .75; }
        .chat-typing-status { display:none; align-items:center; gap:8px; margin:8px 0 12px 46px; color:#64748b; font-size:12px; font-weight:800; }
        .chat-typing-status.is-visible { display:flex; }
        .chat-typing-dots { display:inline-flex; gap:3px; }
        .chat-typing-dots i { width:5px; height:5px; border-radius:999px; background:#94a3b8; animation: pcTypingDot 1s infinite ease-in-out; }
        .chat-typing-dots i:nth-child(2) { animation-delay:.15s; }
        .chat-typing-dots i:nth-child(3) { animation-delay:.3s; }
        .chat-seen-status { display:block; margin-top:4px; font-size:11px; font-weight:900; opacity:.78; text-align:right; }
        .message-row.cliente .chat-seen-status { color:rgba(255,255,255,.86); }
        @keyframes pcTypingDot { 0%, 80%, 100% { transform:translateY(0); opacity:.45; } 40% { transform:translateY(-3px); opacity:1; } }
        .message-row.is-optimistic:not(.is-failed) .bubble-time::after {
            content: '✓';
            font-weight: 900;
            margin-left: 2px;
        }

        .composer {
            margin: 0 16px 14px;
            border-color: #d6e0ef;
            border-radius: 18px;
            padding: 8px 9px;
            box-shadow: 0 10px 24px rgba(6, 23, 53, .07);
        }

        .composer:focus-within {
            border-color: #8fb2eb;
            box-shadow: 0 0 0 4px rgba(15, 63, 147, .10), 0 12px 28px rgba(6, 23, 53, .08);
        }

        .composer-client-summary {
            display: none;
        }

        .composer-row {
            grid-template-columns: 34px minmax(0, 1fr) 44px;
            gap: 7px;
        }

        .composer-row .file-control:nth-of-type(2) {
            display: none;
        }

        .icon-input {
            width: 34px;
            height: 38px;
            border-radius: 999px;
            background: #f4f7fb;
        }

        .composer textarea {
            min-height: 24px;
            max-height: 104px;
            padding: 8px 2px;
            line-height: 1.42;
            font-size: 14px;
        }

        .send-button {
            width: 44px;
            height: 40px;
            border-radius: 999px;
        }

        .send-button svg {
            width: 21px;
            height: 21px;
        }

        .composer-help {
            margin: 5px 6px 0 42px;
            font-size: 11px;
        }

        .composer.is-processing {
            opacity: 1;
        }

        .composer.is-processing .send-button {
            pointer-events: none;
        }

        .composer.is-processing .send-button svg {
            display: none;
        }

        .composer.is-processing .send-button::before {
            content: '';
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,.55);
            border-right-color: #fff;
            animation: pcSpin .75s linear infinite;
        }


        @media (max-width: 760px) {
            .bubble-wrap {
                max-width: 86%;
            }

            .bubble {
                padding: 8px 11px 6px;
                border-radius: 15px;
                font-size: 14px;
            }

            .message-row.cliente .bubble {
                border-bottom-right-radius: 5px;
            }

            .message-row.equipe .bubble {
                border-bottom-left-radius: 5px;
            }

            .composer {
                margin: 0 8px 8px;
                padding: 7px 8px;
                border-radius: 18px;
            }

            .composer-row {
                grid-template-columns: 32px minmax(0, 1fr) 42px;
                gap: 6px;
            }

            .icon-input {
                width: 32px;
                height: 38px;
            }

            .composer textarea {
                min-height: 24px;
                max-height: 92px;
                padding: 8px 0;
                font-size: 14px;
            }

            .send-button {
                width: 42px;
                height: 38px;
            }

            .composer-help {
                margin-left: 40px;
                grid-template-columns: minmax(0, 1fr) auto;
                display: grid;
                align-items: center;
            }
        }

        /* AJUSTE MOBILE LIMPO - reduz poluição visual e transforma a tela em fluxo por seções */
        .mobile-summary-toggle {
            display: none;
            width: 100%;
            min-height: 42px;
            border: 1px solid var(--pc-line);
            border-radius: 10px;
            background: #fff;
            color: #061735;
            font-weight: 900;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }

        @media (max-width: 760px) {
            .portal-content {
                padding-bottom: 14px;
            }

            .portal-cluster {
                position: sticky;
                top: 0;
                z-index: 30;
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 7px;
                padding: 8px 10px;
                background: rgba(244, 247, 251, .98);
                border-bottom: 1px solid rgba(221, 229, 240, .9);
                overflow: visible;
            }

            .portal-cluster-button {
                width: 100%;
                min-width: 0;
                min-height: 38px;
                padding: 0 8px;
                border-radius: 12px;
                font-size: 12px;
                box-shadow: none;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .portal-cluster-button[data-portal-cluster="historico"] {
                display: none !important;
            }

            .portal-cluster-badge {
                min-width: 17px;
                height: 17px;
                padding: 0 5px;
                font-size: 10px;
            }

            .workspace,
            .workspace.is-single-view {
                padding: 10px 10px 18px;
                gap: 10px;
            }

            .action-banner {
                padding: 14px;
                border-radius: 14px;
            }

            .action-banner-header {
                gap: 10px;
            }

            .action-banner-kicker {
                margin-bottom: 5px;
                font-size: 10px;
            }

            .action-banner strong {
                font-size: 14px;
                line-height: 1.35;
            }

            .action-banner span {
                font-size: 12px;
            }

            .action-banner .outline-button {
                min-height: 40px;
                font-size: 13px;
                margin-top: 2px;
            }

            .mobile-summary-toggle {
                display: inline-flex;
                margin-top: 10px;
            }

            .journey-steps[data-mobile-summary-content] {
                display: none;
                margin-top: 10px;
            }

            .action-banner.is-mobile-open .journey-steps[data-mobile-summary-content] {
                display: grid;
            }

            .action-banner.is-mobile-open .mobile-summary-toggle span {
                transform: rotate(180deg);
            }

            .panel:not(.chat-panel) {
                box-shadow: 0 8px 24px rgba(6, 23, 53, .055);
            }

            .panel-pad,
            .status-card {
                padding: 15px 14px;
            }

            .panel-title-row {
                margin-bottom: 2px;
            }

            .panel-sub {
                margin: 8px 0 12px;
            }

            .pending-guidance {
                display: none;
            }

            .pending-list {
                gap: 10px;
            }

            .pending-card {
                padding: 14px;
            }

            .pending-text {
                margin-bottom: 10px;
                font-size: 13px;
                line-height: 1.45;
            }

            .pending-flow-meta {
                margin-top: 8px;
                gap: 7px;
            }

            .pending-order,
            .deadline {
                font-size: 11px;
            }

            .pending-card .outline-button {
                min-height: 40px;
                margin-top: 10px !important;
                font-size: 13px;
            }

            .mini-form {
                gap: 8px;
            }

            .chat-header {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .chat-tools {
                display: none;
            }

            .online-badge {
                font-size: 11px;
                padding: 6px 9px;
            }

            .chat-scroll {
                min-height: 300px;
                max-height: 52dvh;
            }

            .composer-client-summary,
            .composer-help {
                display: none;
            }

            .composer-row {
                grid-template-columns: 34px minmax(0, 1fr) 46px;
            }

            .composer-row label.icon-input:nth-of-type(2) {
                display: none;
            }

            .status-card .divider {
                margin: 12px 0;
            }

            .info-row {
                min-height: auto;
                padding: 10px 0;
                font-size: 13px;
            }

            .progress-line {
                gap: 12px 8px;
                margin: 12px 0 6px;
            }

            .step {
                font-size: 11px;
            }

            .step-circle {
                width: 30px;
                height: 30px;
                margin-bottom: 8px;
            }
        }


        /* AJUSTE FINAL UX CHAT - precisa ficar por último para vencer estilos mobile anteriores */
        .bubble-wrap { width: fit-content; max-width: min(74%, 680px); }
        .message-row.cliente .bubble-wrap { margin-left: auto; }
        .bubble { display: inline-block; width: auto; max-width: 100%; min-width: 0; padding: 9px 12px 7px; border-radius: 16px; line-height: 1.42; }
        .message-row.cliente .bubble { background: #0f3f93; border-bottom-right-radius: 5px; }
        .message-row.equipe .bubble { background: #f1f4f8; border-bottom-left-radius: 5px; }
        .message-row.cliente .bubble-name { display: none; }
        .bubble-time { margin-top: 5px; font-size: 11px; opacity: .82; }
        .composer { border-color: #d6e0ef; border-radius: 18px; padding: 8px 9px; box-shadow: 0 10px 24px rgba(6, 23, 53, .07); }
        .composer-client-summary { display: none !important; }
        .composer-row { grid-template-columns: 34px minmax(0, 1fr) 44px !important; gap: 7px; }
        .composer-row label.icon-input:nth-of-type(2) { display: none !important; }
        .composer textarea { min-height: 24px; max-height: 104px; padding: 8px 2px; line-height: 1.42; }
        .send-button { width: 44px; height: 40px; border-radius: 999px; }
        .send-button svg { width: 21px; height: 21px; }
        .composer.is-processing .send-button svg { display: none; }
        .composer.is-processing .send-button::before { content: ''; width: 16px; height: 16px; border-radius: 50%; border: 2px solid rgba(255,255,255,.55); border-right-color: #fff; animation: pcSpin .75s linear infinite; }

        @media (max-width: 760px) {
            .bubble-wrap { max-width: 86%; }
            .bubble { padding: 8px 11px 6px; border-radius: 15px; font-size: 14px; }
            .message-row.cliente .bubble { border-bottom-right-radius: 5px; }
            .message-row.equipe .bubble { border-bottom-left-radius: 5px; }
            .composer { margin: 0 8px 8px; padding: 7px 8px; border-radius: 18px; }
            .composer-row { grid-template-columns: 32px minmax(0, 1fr) 42px !important; gap: 6px; }
            .icon-input { width: 32px; height: 38px; }
            .composer textarea { min-height: 24px; max-height: 92px; padding: 8px 0; font-size: 14px; }
            .send-button { width: 42px; height: 38px; }
            .composer-help { margin-left: 40px; display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; }
        }

    </style>
</head>
<body>
<?php
    $clientePublicoNome = old('nome', $empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? 'Cliente do portal');
    $clientePublicoEmail = old('email', $empresa['email'] ?? '');
    $percent = (int) ($progress['percent'] ?? 0);
    $empresaNome = $empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? 'Cliente';
    $pendenciasCount = (int) ($statusSummary['pendencias_cliente'] ?? 0);
    $atrasadosCount = (int) ($statusSummary['atrasados'] ?? 0);
    $solicitacoesAbertas = collect($supportQueue ?? [])->values();
    $chatMensagens = collect($chat ?? [])->values();
    $timelineItens = collect($timeline ?? [])->take(6)->values();
    $ultimaMensagem = $chatMensagens->last();
    $abertura = $timelineItens->first()['created_at_label'] ?? $ultimaMensagem['created_at_label'] ?? 'Ainda não informado';
    $atualizacao = $ultimaMensagem['created_at_label'] ?? $timelineItens->last()['created_at_label'] ?? 'Ainda não informado';
    $protocolo = '#ATD-' . now()->format('Y') . '-' . str_pad((string) (($empresaId ?? 0) ?: 1), 6, '0', STR_PAD_LEFT);
    $statusLabel = $pendenciasCount > 0 ? 'Aguardando você' : ($chatMensagens->isNotEmpty() ? 'Em andamento' : 'Aberto');
    $statusClasse = $pendenciasCount > 0 ? 'warn' : 'ok';
    $hasPendencias = $pendenciasCount > 0;
    $acaoPrincipalLabel = $hasPendencias ? 'Resolver pendências agora' : 'Falar com a equipe';
    $acaoPrincipalDestino = $hasPendencias ? 'pendencias' : 'chat';
    $statusAtendimentoDescricao = $hasPendencias
        ? 'O atendimento está aguardando sua resposta. Resolva as pendências abaixo para liberar a próxima etapa.'
        : 'O atendimento está em acompanhamento. Você pode enviar dúvidas ou documentos pelo chat quando precisar.';
    $responsavel = 'Equipe de Suporte';
    $iniciaisEmpresa = collect(preg_split('/\s+/', trim($empresaNome)) ?: [])->filter()->take(2)->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))->implode('') ?: 'CL';
    $iniciaisAutor = function (array $mensagem): string {
        $nome = trim((string) ($mensagem['nome'] ?? $mensagem['autor_label'] ?? ''));
        $partes = preg_split('/\s+/', $nome) ?: [];
        $iniciais = collect($partes)->filter()->take(2)->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))->implode('');
        return $iniciais !== '' ? $iniciais : (($mensagem['origem'] ?? 'cliente') === 'interno' ? 'EQ' : 'CL');
    };
    $mensagemComLinks = function (?string $texto): \Illuminate\Support\HtmlString {
        $seguro = e((string) $texto);
        $seguro = preg_replace('/(https?:\/\/[^\s<]+)/i', '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>', $seguro) ?? $seguro;
        return new \Illuminate\Support\HtmlString($seguro);
    };
    $formatarTamanho = function (?int $bytes): string {
        if (! $bytes) return 'Arquivo';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        return max(1, (int) ceil($bytes / 1024)) . ' KB';
    };
?>

<div class="portal-app">
    <header class="portal-topbar">
        <div class="brand">
            <div class="brand-mark" aria-hidden="true"></div>
            <div class="brand-divider" aria-hidden="true"></div>
            <div>
                <h1>Portal do Cliente</h1>
                <p>Acompanhamento do seu atendimento</p>
            </div>
        </div>
        <div class="top-actions">
            <button type="button" class="bell" aria-label="Abrir pendências" data-portal-cluster="pendencias">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"/><path d="M10 21h4"/></svg>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendenciasCount > 0): ?><span class="bell-badge"><?php echo e($pendenciasCount); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </button>
            <div class="profile" aria-label="Cliente identificado">
                <div class="profile-copy"><strong><?php echo e(\Illuminate\Support\Str::limit($empresaNome, 28)); ?></strong><span class="profile-status">Atendimento pelo portal</span></div>
                <div class="profile-avatar"><?php echo e($iniciaisEmpresa); ?></div>
                <span class="profile-note">Cliente</span>
            </div>
        </div>
    </header>

    <div class="portal-body">
        <nav class="side-nav" aria-label="Navegação lateral">
            <button type="button" class="side-link active" data-portal-cluster="chat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="M8 9h8M8 13h5"/></svg>
                <span>Atendimento</span>
            </button>
            <button type="button" class="side-link" data-portal-cluster="pendencias">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendenciasCount > 0): ?><span class="side-badge"><?php echo e($pendenciasCount); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5h6"/><path d="M9 12l2 2 4-4"/><path d="M5 7a2 2 0 0 1 2-2h1a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2h1a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2z"/></svg>
                <span>Pendências</span>
            </button>
            <div class="side-separator"></div>
            <button type="button" class="side-link" data-portal-cluster="status">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 20h18"/><path d="M6 16V9"/><path d="M12 16V4"/><path d="M18 16v-6"/><path d="m15 7 3-3 3 3"/></svg>
                <span>Status</span>
            </button>
        </nav>

        <div class="portal-content">
            <div class="portal-cluster" aria-label="Navegação do atendimento">
                <button type="button" class="portal-cluster-button is-active" data-portal-cluster="chat">Atendimento</button>
                <button type="button" class="portal-cluster-button" data-portal-cluster="pendencias">Pendências <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendenciasCount > 0): ?><span class="portal-cluster-badge"><?php echo e($pendenciasCount); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></button>
                <button type="button" class="portal-cluster-button" data-portal-cluster="status">Status</button>
                <button type="button" class="portal-cluster-button" data-portal-cluster="historico">Histórico do chat</button>
            </div>

            <main class="workspace is-single-view" data-portal-workspace>
            <section class="panel panel-pad action-banner <?php echo e($hasPendencias ? 'is-urgent' : ''); ?>" data-portal-section="chat" aria-label="Próxima ação recomendada">
                <div class="action-banner-header">
                    <div class="action-banner-copy">
                        <span class="action-banner-kicker">Próximo passo recomendado</span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasPendencias): ?>
                            <strong>Você tem <?php echo e($pendenciasCount); ?> <?php echo e($pendenciasCount === 1 ? 'pendência aguardando sua ação' : 'pendências aguardando sua ação'); ?>.</strong>
                            <span>Comece pela primeira pendência destacada. Depois de responder, a equipe consegue continuar o atendimento sem atrasos.</span>
                        <?php else: ?>
                            <strong>Seu atendimento está em acompanhamento.</strong>
                            <span>Não há pendências abertas agora. Use o chat para tirar dúvidas, enviar documentos ou acompanhar as atualizações da equipe.</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <button type="button" class="outline-button" data-portal-cluster="<?php echo e($acaoPrincipalDestino); ?>"><?php echo e($acaoPrincipalLabel); ?> <span>→</span></button>
                </div>
                <button type="button" class="mobile-summary-toggle" data-mobile-summary-toggle aria-expanded="false">Ver orientação rápida <span>⌄</span></button>
                <div class="journey-steps" data-mobile-summary-content aria-label="Fluxo recomendado do atendimento">
                    <div class="journey-step <?php echo e($hasPendencias ? 'is-current' : ''); ?>">
                        <span class="journey-step-number">1</span>
                        <div><strong>Resolva pendências</strong><span><?php echo e($hasPendencias ? 'Ação necessária agora' : 'Nada pendente no momento'); ?></span></div>
                    </div>
                    <div class="journey-step <?php echo e(! $hasPendencias ? 'is-current' : ''); ?>">
                        <span class="journey-step-number">2</span>
                        <div><strong>Fale pelo chat</strong><span>Envie dúvidas e documentos</span></div>
                    </div>
                    <div class="journey-step">
                        <span class="journey-step-number">3</span>
                        <div><strong>Acompanhe o status</strong><span>Veja protocolo e atualização</span></div>
                    </div>
                </div>
            </section>
            <section class="panel panel-pad portal-section-hidden" id="pendencias" data-portal-section="pendencias">
                <div class="panel-title-row">
                    <h2>Pendências para resolver</h2>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendenciasCount > 0): ?><span class="count-dot"><?php echo e($pendenciasCount); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <p class="panel-sub">Itens que precisam da sua ação ou informação. Responda de cima para baixo para seguir o fluxo recomendado.</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasPendencias): ?>
                    <div class="pending-guidance"><strong>Como resolver:</strong> abra uma pendência, escreva a resposta solicitada e envie. Se precisar mandar documento, use também o chat do atendimento.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="pending-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $solicitacoesAbertas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $solicitacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $cardClass = $index === 0 ? '' : ($index === 1 ? 'warn' : 'info');
                            $prioridade = mb_strtoupper((string) ($solicitacao['prioridade'] ?? $solicitacao['status_label'] ?? 'PENDENTE'));
                        ?>
                        <article class="pending-card <?php echo e($cardClass); ?> <?php echo e($index === 0 ? 'is-primary' : ''); ?> <?php echo e($index > 2 ? 'is-extra-hidden' : ''); ?>" data-pending-card>
                            <div class="pending-head">
                                <span class="pending-icon" aria-hidden="true">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h5"/></svg>
                                </span>
                                <h3 class="pending-title"><?php echo e($solicitacao['titulo'] ?? 'Pendência do atendimento'); ?></h3>
                                <span class="priority"><?php echo e($prioridade); ?></span>
                            </div>
                            <p class="pending-text"><?php echo e(\Illuminate\Support\Str::limit($solicitacao['descricao'] ?? 'A equipe solicitou uma resposta para continuar o atendimento.', 150)); ?></p>
                            <div class="pending-flow-meta">
                                <span class="deadline">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="4" width="18" height="18" rx="2"/></svg>
                                    <?php echo e(! empty($solicitacao['created_at_label']) ? 'Aberta em: ' . $solicitacao['created_at_label'] : 'Aguardando resposta'); ?>

                                </span>
                                <span class="pending-order"><?php echo e($index === 0 ? 'Comece por aqui' : 'Ordem sugerida: ' . ($index + 1)); ?></span>
                            </div>
                            <button type="button" class="outline-button" style="margin-top:14px" data-toggle-pending><?php echo e($index === 0 ? 'Responder agora' : 'Responder pendência'); ?> <span>→</span></button>
                            <form method="POST" action="<?php echo e(route('portal.cliente.pendencia.responder', ['token' => $token, 'solicitacao' => $solicitacao['id']])); ?>" class="mini-form js-feedback-form" data-processing="Enviando resposta...">
                                <?php echo csrf_field(); ?>
                                <div class="hp-field" aria-hidden="true"><label>Website</label><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                                <input type="hidden" name="nome" value="<?php echo e($clientePublicoNome); ?>">
                                <input type="hidden" name="email" value="<?php echo e($clientePublicoEmail); ?>">
                                <textarea class="textarea" name="resposta" rows="3" placeholder="Escreva sua resposta para esta pendência" required><?php echo e(old('resposta')); ?></textarea>
                                <button class="btn" type="submit">Enviar resposta</button>
                                <div class="field-error" data-form-feedback style="display:none"></div>
                            </form>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <article class="pending-card info">
                            <div class="pending-head">
                                <span class="pending-icon" aria-hidden="true">✓</span>
                                <h3 class="pending-title">Nenhuma pendência aberta</h3>
                            </div>
                            <p class="pending-text">Quando a equipe solicitar documento, aprovação ou informação, o item aparecerá aqui.</p>
                            <span class="deadline">Tudo certo por enquanto.</span>
                        </article>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($solicitacoesAbertas->count() > 3): ?>
                        <button type="button" class="all-link" data-toggle-all-pendings data-total-pendings="<?php echo e($solicitacoesAbertas->count()); ?>">Ver todas as <?php echo e($solicitacoesAbertas->count()); ?> pendências <span>→</span></button>
                    <?php else: ?>
                        <button type="button" class="all-link" data-portal-cluster="chat">Falar com a equipe <span>→</span></button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

            <section class="panel chat-panel" id="chat" data-portal-section="chat historico">
                <header class="chat-header">
                    <div class="chat-title">
                        <h2>Chat com a equipe</h2>
                        <span class="online-badge">Resposta pelo portal</span>
                    </div>
                    <div class="chat-tools">
                        <span class="chat-safe-label">Canal seguro</span>
                    </div>
                </header>

                <div class="chat-scroll" id="chatHistorico" role="log" aria-live="polite" aria-label="Histórico do atendimento">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $chatMensagens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mensagem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $classeMensagem = $mensagem['css_class'] ?? (($mensagem['origem'] ?? 'cliente') === 'interno' ? 'equipe' : 'cliente');
                            $autor = $mensagem['autor_label'] ?? ($classeMensagem === 'equipe' ? 'Equipe' : 'Cliente');
                            $textoMensagem = trim((string) ($mensagem['mensagem_texto'] ?? $mensagem['mensagem'] ?? ''));
                        ?>
                        <div class="message-row <?php echo e($classeMensagem); ?>" data-message-id="<?php echo e($mensagem['id'] ?? 0); ?>" data-message-class="<?php echo e($classeMensagem); ?>">
                            <span class="message-avatar" aria-hidden="true"><?php echo e($iniciaisAutor($mensagem)); ?></span>
                            <div class="bubble-wrap">
                                <div class="bubble">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($classeMensagem !== 'cliente'): ?><span class="bubble-name"><?php echo e($mensagem['nome'] ?? $autor); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($textoMensagem !== ''): ?><span><?php echo $mensagemComLinks($textoMensagem); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($mensagem['created_at_label'])): ?><span class="bubble-time"><?php echo e($mensagem['created_at_label']); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($classeMensagem === 'cliente'): ?>✓✓<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($classeMensagem === 'cliente'): ?><span class="chat-seen-status" data-seen-status style="display:none">Visualizado pelo suporte</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($mensagem['attachments'])): ?>
                                    <div class="attachment-grid" aria-label="Anexos da mensagem">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $mensagem['attachments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anexo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <?php
                                                $nomeAnexo = $anexo['nome'] ?? 'Anexo';
                                                $isImage = (bool) ($anexo['is_image'] ?? false);
                                                $extensao = strtoupper(pathinfo($nomeAnexo, PATHINFO_EXTENSION) ?: ($isImage ? 'IMG' : 'DOC'));
                                            ?>
                                            <a class="attachment-card" href="<?php echo e($anexo['url']); ?>" target="_blank" rel="noopener noreferrer" download>
                                                <span class="file-icon <?php echo e($isImage ? 'image' : ''); ?>"><?php echo e($isImage ? 'IMG' : $extensao); ?></span>
                                                <span class="file-info"><strong><?php echo e($nomeAnexo); ?></strong><span><?php echo e($anexo['mime_type'] ?? 'Arquivo anexado'); ?></span></span>
                                                <span class="download-icon">⌄</span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isImage): ?><img class="attachment-preview" src="<?php echo e($anexo['url']); ?>" alt="<?php echo e($nomeAnexo); ?>" loading="lazy"><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </a>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="empty-chat">
                            <div>
                                <strong>Nenhuma mensagem ainda.</strong><br>
                                Envie uma dúvida, documento ou atualização pelo campo abaixo. A equipe responderá por este canal.
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="chat-typing-status" data-support-typing aria-live="polite">
                    <span class="chat-typing-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                    <span data-support-typing-text>Suporte está digitando...</span>
                </div>

                <form method="POST" action="<?php echo e(route('portal.cliente.mensagem', $token)); ?>" class="composer js-feedback-form" data-processing="Enviando mensagem..." enctype="multipart/form-data" data-chat-form>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_portal_ajax" value="1">
                    <div class="hp-field" aria-hidden="true"><label>Website</label><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                    <input type="hidden" name="nome" value="<?php echo e($clientePublicoNome); ?>">
                    <input type="hidden" name="email" value="<?php echo e($clientePublicoEmail); ?>">
                    <div class="composer-client-summary" aria-label="Cliente identificado pelo link do portal">
                        <span>Cliente identificado</span>
                        <strong><?php echo e($clientePublicoNome); ?></strong>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="composer-row">
                        <label class="icon-input file-control" title="Anexar arquivo">
                            <input id="chatAnexos" class="js-chat-files" name="anexos[]" type="file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp,application/pdf">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.4 11.6-8.5 8.5a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7l-9.2 9.2a2 2 0 0 1-2.8-2.8l8.5-8.5"/></svg>
                        </label>
                        <label class="icon-input file-control" title="Enviar imagem">
                            <input class="js-chat-files-mirror" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" tabindex="-1">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                        </label>
                        <textarea class="js-chat-message" name="mensagem" rows="1" maxlength="5000" placeholder="Digite sua dúvida, resposta ou informe quais documentos está enviando..." aria-invalid="<?php echo e($errors->has('mensagem') ? 'true' : 'false'); ?>"><?php echo e(old('mensagem')); ?></textarea>
                        <button class="send-button" type="submit" aria-label="Enviar mensagem">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        </button>
                    </div>
                    <div class="selected-files" data-selected-files aria-live="polite"></div>
                    <div class="composer-help"><span data-composer-hint>Você pode enviar arquivos: PDF, JPG, PNG, DOC, DOCX (até 10MB cada)</span><span><span data-chat-count>0</span>/5000</span></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['mensagem'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['anexos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['anexos.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="field-error" data-form-feedback style="display:none"></div>
                </form>
            </section>

            <aside class="right-column portal-section-hidden" id="status" data-portal-section="status">
                <section class="panel status-card">
                    <h2>Status do atendimento</h2>
                    <div class="divider"></div>
                    <span class="status-pill <?php echo e($statusClasse); ?>"><?php echo e($statusLabel); ?></span>
                    <p class="status-desc"><?php echo e($statusAtendimentoDescricao); ?></p>
                    <div class="divider"></div>
                    <div class="info-list">
                        <div class="info-row"><span>♙</span><strong>Protocolo</strong><span><?php echo e($protocolo); ?></span></div>
                        <div class="info-row"><span>▣</span><strong>Abertura</strong><span><?php echo e($abertura); ?></span></div>
                        <div class="info-row"><span>◷</span><strong>Última atualização</strong><span><?php echo e($atualizacao); ?></span></div>
                        <div class="info-row"><span>♧</span><strong>Responsável</strong><span class="person"><span class="small-avatar">EQ</span><span><?php echo e($responsavel); ?><small>Suporte</small></span></span></div>
                    </div>
                </section>

                <section class="panel status-card">
                    <h2>Progresso do atendimento</h2>
                    <div class="divider"></div>
                    <div class="progress-line" aria-label="Progresso do atendimento">
                        <div class="step done"><span class="step-circle">✓</span><span>Recebido</span></div>
                        <div class="step <?php echo e($percent >= 35 ? 'active' : ''); ?>"><span class="step-circle">2</span><span>Em análise</span></div>
                        <div class="step <?php echo e($pendenciasCount > 0 ? 'active' : ''); ?>"><span class="step-circle">3</span><span>Aguardando<br>você</span></div>
                        <div class="step <?php echo e($percent >= 100 ? 'done' : ''); ?>"><span class="step-circle">4</span><span>Concluído</span></div>
                    </div>
                    <div class="divider"></div>
                    <h2 style="font-size:17px">Resumo da solicitação</h2>
                    <p class="summary-text">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($solicitacoesAbertas->isNotEmpty()): ?>
                            <?php echo e(\Illuminate\Support\Str::limit($solicitacoesAbertas->first()['descricao'] ?? $solicitacoesAbertas->first()['titulo'] ?? 'Atendimento em acompanhamento pela equipe.', 170)); ?>

                        <?php elseif($nextDelivery): ?>
                            <?php echo e(\Illuminate\Support\Str::limit($nextDelivery['titulo'] ?? 'Atendimento em acompanhamento pela equipe.', 170)); ?>

                        <?php else: ?>
                            Solicitação em acompanhamento pela equipe de suporte. Use o chat para enviar dúvidas, documentos e atualizações.
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                    <button type="button" class="all-link" style="margin-top:26px" data-portal-cluster="historico">Abrir histórico do chat <span>→</span></button>
                </section>
            </aside>
            </main>
        </div>
    </div>
</div>

<div class="alerts">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert"><strong>Pronto!</strong> <?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="errors" role="alert">
            <strong>Não foi possível continuar.</strong>
            <ul><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><li><?php echo e($erro); ?></li><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<div class="client-toast" data-client-toast role="status" aria-live="polite"></div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($socketIoConfig['url'])): ?>
    <script src="<?php echo e(rtrim($socketIoConfig['url'], '/')); ?>/socket.io/socket.io.js" onload="window.__portalSocketIoScriptLoaded=true" onerror="window.__portalSocketIoScriptError=true"></script>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?php echo e(csrf_token()); ?>';
    const debugUrl = '<?php echo e(route('portal.cliente.debug-log.publico', ['token' => $token])); ?>';
    const portalChatSocketConfig = <?php echo json_encode($socketIoConfig ?? [], 15, 512) ?>;
    let portalChatSocket = null;
    let portalSocketRetryTimer = null;
    let portalOfflineSyncTimer = null;

    const typingState = { timer: null, stopTimer: null, lastSent: 0, active: false };
    const debugState = { lastSent: 0, lastStep: null };
    let clientChatSendingBusy = false;

    function announceClientTyping(form) {
        if (!portalChatSocket || !portalChatSocket.connected) return;

        const now = Date.now();
        const nome = form?.querySelector('[name="nome"]')?.value || '';

        if (!typingState.active || now - typingState.lastSent >= 1800) {
            typingState.active = true;
            typingState.lastSent = now;
            portalChatSocket.emit('chat:typing:start', { nome: nome, room: portalChatSocketConfig.room || '' });
        }

        window.clearTimeout(typingState.stopTimer);
        typingState.stopTimer = window.setTimeout(function () {
            if (!portalChatSocket || !portalChatSocket.connected || !typingState.active) return;
            typingState.active = false;
            portalChatSocket.emit('chat:typing:stop', { room: portalChatSocketConfig.room || '' });
        }, 1200);
    }

    function portalDebug(step, extra = {}) {
        // Debug do navegador sem poluir storage/logs/laravel.log.
        // Para voltar a gravar no Laravel temporariamente, execute no console:
        // window.PORTAL_CHAT_DEBUG_LARAVEL = true
        const now = Date.now();
        const important = ['chat_ajax_error', 'chat_socket_emit_offline', 'socket_public_connect_error', 'socket_public_client_missing', 'socket_public_message_received'].includes(step);
        if (!important && debugState.lastStep === step && now - debugState.lastSent < 15000) {
            return;
        }
        debugState.lastStep = step;
        debugState.lastSent = now;

        const payload = {
            step: step,
            page: 'resources/views/portal/cliente/show.blade.php',
            url: window.location.href,
            pathname: window.location.pathname,
            timestamp: new Date().toISOString(),
            ...extra
        };

        if (window.PORTAL_CHAT_DEBUG === true || important) {
            console.log('[PORTAL_CLIENTE_SHOW]', payload);
        }

        if (window.PORTAL_CHAT_DEBUG_LARAVEL !== true) {
            return;
        }

        try {
            if (!debugUrl) return;
            fetch(debugUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload),
                keepalive: true
            }).catch(function (error) {
                console.warn('[PORTAL_CLIENTE_SHOW] Falha ao gravar debug no Laravel log.', error);
            });
        } catch (error) {
            console.warn('[PORTAL_CLIENTE_SHOW] Debug fetch indisponível.', error);
        }
    }

    function showClientToast(message, type = 'info') {
        const toast = document.querySelector('[data-client-toast]');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.toggle('is-error', type === 'error');
        toast.classList.toggle('is-success', type === 'success');
        toast.classList.add('is-visible');
        window.clearTimeout(toast.dataset.timer);
        toast.dataset.timer = window.setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 3800);
    }

    function setButtonProcessing(button, processingText) {
        if (!button || button.dataset.processingApplied === '1') return;
        button.dataset.processingApplied = '1';
        button.disabled = true;
        button.dataset.originalHtml = button.innerHTML;
        button.innerHTML = '<span class="button-loading-text">' + (processingText || 'Enviando...') + '</span>';
    }

    function setPortalCluster(target) {
        const workspace = document.querySelector('[data-portal-workspace]');
        const normalizedTarget = target === 'historico' ? 'historico' : (target || 'chat');
        const visibleSection = normalizedTarget === 'historico' ? 'historico' : normalizedTarget;

        document.querySelectorAll('[data-portal-cluster]').forEach(function (button) {
            const active = button.dataset.portalCluster === normalizedTarget;
            button.classList.toggle('is-active', active);
            button.classList.toggle('active', active);
        });

        document.querySelectorAll('[data-portal-section]').forEach(function (section) {
            const sectionTargets = String(section.dataset.portalSection || '').split(/\s+/).filter(Boolean);

            // Atendimento deve manter o layout original da tela: pendências à esquerda,
            // chat no centro e status na direita. As demais opções do cluster continuam
            // abrindo em visual único para evitar o problema antigo das abas por hash.
            const isMobileCompact = window.matchMedia('(max-width: 760px)').matches;
            const shouldShow = normalizedTarget === 'chat'
                ? (isMobileCompact
                    ? sectionTargets.includes('chat')
                    : sectionTargets.some(function (sectionTarget) {
                        return ['pendencias', 'chat', 'status'].includes(sectionTarget);
                    }))
                : sectionTargets.includes(visibleSection);

            section.classList.toggle('portal-section-hidden', !shouldShow);
        });

        if (workspace) {
            const isMobileCompact = window.matchMedia('(max-width: 760px)').matches;
            workspace.classList.toggle('is-single-view', isMobileCompact || normalizedTarget !== 'chat');
        }

        if (normalizedTarget === 'chat' || normalizedTarget === 'historico') {
            const chatHistoricoAtual = document.getElementById('chatHistorico');
            if (chatHistoricoAtual) {
                window.setTimeout(function () {
                    chatHistoricoAtual.scrollTop = chatHistoricoAtual.scrollHeight;
                }, 50);
            }
        }

        portalDebug('cluster_change', {
            target: normalizedTarget,
            atendimentoLayoutOriginal: normalizedTarget === 'chat'
        });
    }

    document.addEventListener('click', function (event) {
        const clusterButton = event.target.closest('[data-portal-cluster]');
        if (clusterButton) {
            event.preventDefault();
            event.stopPropagation();
            setPortalCluster(clusterButton.dataset.portalCluster || 'chat');
            return;
        }

        const clicked = event.target.closest('button, a, input, textarea, select, label, [role="button"]');
        if (clicked) {
            portalDebug('click', {
                tag: clicked.tagName,
                text: (clicked.innerText || clicked.value || clicked.getAttribute('aria-label') || clicked.title || '').trim().slice(0, 120),
                id: clicked.id || null,
                className: clicked.className || null,
                href: clicked.getAttribute('href') || null,
                name: clicked.getAttribute('name') || null,
                type: clicked.getAttribute('type') || null
            });
        }
    }, true);

    const chatHistorico = document.getElementById('chatHistorico');
    if (chatHistorico) chatHistorico.scrollTop = chatHistorico.scrollHeight;
    setPortalCluster('chat');

    document.querySelectorAll('[data-toggle-all-pendings]').forEach(function (button) {
        button.addEventListener('click', function () {
            const list = button.closest('.pending-list');
            if (!list) return;
            const expanded = !list.classList.contains('is-expanded');
            list.classList.toggle('is-expanded', expanded);
            button.innerHTML = expanded ? 'Mostrar apenas as 3 principais' : 'Ver todas as ' + (button.dataset.totalPendings || '') + ' pendências <span>→</span>';
            portalDebug('toggle_all_pendings', { expanded: expanded });
        });
    });

    document.querySelectorAll('[data-toggle-pending]').forEach(function (button) {
        button.addEventListener('click', function () {
            const card = button.closest('[data-pending-card]');
            if (!card) return;
            card.classList.toggle('is-open');
            button.innerHTML = card.classList.contains('is-open') ? 'Ocultar resposta' : 'Responder pendência <span>→</span>';
            if (!card.classList.contains('is-open') && card.classList.contains('is-primary')) {
                button.innerHTML = 'Responder agora <span>→</span>';
            }
        });
    });

    document.querySelectorAll('.js-chat-message').forEach(function (textarea) {
        const form = textarea.closest('form');
        const counter = form?.querySelector('[data-chat-count]');
        const grow = function () {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 150) + 'px';
            if (counter) counter.textContent = String(textarea.value.length);
        };
        textarea.addEventListener('input', function () {
            grow();
            if (textarea.value.trim().length > 0) {
                announceClientTyping(form);
            }
        });
        textarea.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
                event.preventDefault();
                const tamanhoMensagem = textarea.value.trim().length;
                portalDebug('chat_enter_submit', { tamanhoMensagem: tamanhoMensagem });
                if (tamanhoMensagem > 0) announceClientTyping(form);
                form?.requestSubmit();
            }
        });
        grow();
    });

    document.querySelectorAll('.js-chat-files-mirror').forEach(function (mirror) {
        mirror.addEventListener('change', function () {
            const mainInput = mirror.closest('form')?.querySelector('.js-chat-files');
            if (!mainInput || !mirror.files || mirror.files.length === 0) return;
            mainInput.files = mirror.files;
            mainInput.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

    document.querySelectorAll('.js-chat-files').forEach(function (input) {
        const form = input.closest('form');
        const list = form?.querySelector('[data-selected-files]');
        const hint = form?.querySelector('[data-composer-hint]');
        const renderFiles = function () {
            if (!list) return;
            list.innerHTML = '';
            const files = Array.from(input.files || []);
            if (files.length === 0) {
                list.classList.remove('is-visible');
                if (hint) hint.textContent = 'Você pode enviar arquivos: PDF, JPG, PNG, DOC, DOCX (até 10MB cada)';
                return;
            }
            list.classList.add('is-visible');
            if (hint) hint.textContent = files.length === 1 ? '1 arquivo selecionado para envio' : files.length + ' arquivos selecionados para envio';
            files.slice(0, 5).forEach(function (file) {
                const chip = document.createElement('span');
                chip.className = 'file-chip';
                const size = file.size >= 1048576 ? (file.size / 1048576).toFixed(1).replace('.', ',') + ' MB' : Math.max(1, Math.ceil(file.size / 1024)) + ' KB';
                chip.textContent = file.name + ' • ' + size;
                list.appendChild(chip);
            });
            if (files.length > 5) {
                const chip = document.createElement('span');
                chip.className = 'file-chip';
                chip.textContent = 'Envie no máximo 5 arquivos por mensagem.';
                list.appendChild(chip);
            }
        };
        input.addEventListener('change', renderFiles);
        renderFiles();
    });



    function appendOptimisticChatMessage(form, message, filesCount) {
        const chat = document.getElementById('chatHistorico');
        if (!chat) return;

        const empty = chat.querySelector('.empty-chat');
        if (empty) empty.remove();

        const row = document.createElement('div');
        row.className = 'message-row cliente is-optimistic is-sent';
        row.dataset.messageId = 'tmp-' + Date.now();

        const wrap = document.createElement('div');
        wrap.className = 'bubble-wrap';

        const bubble = document.createElement('div');
        bubble.className = 'bubble';

        const text = document.createElement('span');
        const cleanMessage = message || (filesCount > 0 ? (filesCount === 1 ? 'Arquivo anexado' : filesCount + ' arquivos anexados') : 'Mensagem enviada');
        text.textContent = cleanMessage;
        bubble.appendChild(text);

        const time = document.createElement('span');
        time.className = 'bubble-time';
        time.textContent = 'agora';
        bubble.appendChild(time);

        const seen = document.createElement('span');
        seen.className = 'chat-seen-status';
        seen.dataset.seenStatus = '1';
        seen.style.display = 'none';
        seen.textContent = 'Visualizado pelo suporte';
        bubble.appendChild(seen);

        wrap.appendChild(bubble);
        row.appendChild(wrap);
        chat.appendChild(row);
        chat.scrollTop = chat.scrollHeight;
        return row;
    }

    function setInlineFeedback(form, message, type = 'error') {
        const feedback = form?.querySelector('[data-form-feedback]');
        if (feedback) {
            feedback.textContent = message;
            feedback.style.display = message ? 'block' : 'none';
            feedback.setAttribute('role', type === 'error' ? 'alert' : 'status');
        }
        form?.classList.toggle('is-invalid', type === 'error' && Boolean(message));
        const textarea = form?.querySelector('textarea');
        textarea?.classList.toggle('is-invalid', type === 'error' && Boolean(message));
    }

    function resetChatComposer(form) {
        const textarea = form.querySelector('.js-chat-message');
        const fileInput = form.querySelector('.js-chat-files');
        const fileMirror = form.querySelector('.js-chat-files-mirror');
        const selectedFiles = form.querySelector('[data-selected-files]');
        const hint = form.querySelector('[data-composer-hint]');
        const counter = form.querySelector('[data-chat-count]');

        if (textarea) {
            textarea.value = '';
            textarea.style.height = 'auto';
            textarea.removeAttribute('readonly');
            textarea.focus({ preventScroll: true });
        }
        if (fileInput) fileInput.value = '';
        if (fileMirror) fileMirror.value = '';
        if (selectedFiles) {
            selectedFiles.innerHTML = '';
            selectedFiles.classList.remove('is-visible');
        }
        if (hint) hint.textContent = 'Você pode enviar arquivos: PDF, JPG, PNG, DOC, DOCX (até 10MB cada)';
        if (counter) counter.textContent = '0';
    }

    function markOptimisticMessage(row, status, text) {
        if (!row) return;
        row.classList.toggle('is-sent', status === 'sent');
        row.classList.toggle('is-failed', status === 'failed');
        const time = row.querySelector('.bubble-time');
        if (time) time.textContent = text || (status === 'sent' ? 'enviado agora' : 'falha no envio');
    }

    function applyServerMessageToOptimistic(row, msg) {
        if (!row || !msg) return;
        row.classList.remove('is-optimistic');
        row.dataset.messageId = String(msg.id || row.dataset.messageId || '');
        const text = row.querySelector('.bubble > span:first-child');
        if (text && msg.text) text.textContent = msg.text;
        const time = row.querySelector('.bubble-time');
        if (time) time.textContent = msg.time || 'agora';
    }


    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
        });
    }

    function renderAttachmentCard(anexo) {
        const nome = escapeHtml(anexo.name || 'Anexo');
        const url = escapeHtml(anexo.url || '#');
        const size = escapeHtml(anexo.size || 'Arquivo anexado');
        const isImage = Boolean(anexo.is_image);
        const ext = nome.includes('.') ? nome.split('.').pop().toUpperCase().slice(0, 5) : (isImage ? 'IMG' : 'DOC');
        return '<a class="attachment-card" href="' + url + '" target="_blank" rel="noopener noreferrer" download>' +
            '<span class="file-icon ' + (isImage ? 'image' : '') + '">' + (isImage ? 'IMG' : ext) + '</span>' +
            '<span class="file-info"><strong>' + nome + '</strong><span>' + size + '</span></span>' +
            '<span class="download-icon">⌄</span>' +
            (isImage ? '<img class="attachment-preview" src="' + url + '" alt="' + nome + '" loading="lazy">' : '') +
        '</a>';
    }

    function renderChatMessages(messages, clientSeenUntilId) {
        const chat = document.getElementById('chatHistorico');
        if (!chat || !Array.isArray(messages)) return;

        const rows = Array.from(chat.querySelectorAll('[data-message-id]'));
        const currentIds = rows
            .filter(function (el) { return !String(el.dataset.messageId || '').startsWith('tmp-'); })
            .map(function (el) { return String(el.dataset.messageId || ''); })
            .join('|');
        const nextIds = messages.map(function (msg) { return String(msg.id || ''); }).join('|');
        const hasOptimisticRows = rows.some(function (el) {
            return el.classList.contains('is-optimistic') || String(el.dataset.messageId || '').startsWith('tmp-');
        });

        portalDebug('chat_render_start', {
            current_ids: currentIds,
            next_ids: nextIds,
            has_optimistic: hasOptimisticRows,
            total_dom: rows.length,
            total_server: messages.length
        });

        if (currentIds === nextIds && !hasOptimisticRows) {
            updateSeenStatus(clientSeenUntilId);
            portalDebug('chat_render_skip', { motivo: 'ids_iguais', total_server: messages.length });
            return;
        }

        chat.innerHTML = '';

        if (messages.length === 0) {
            chat.innerHTML = '<div class="empty-chat"><div><strong>Nenhuma mensagem ainda.</strong><br>Envie uma dúvida, documento ou atualização pelo campo abaixo. A equipe responderá por este canal.</div></div>';
            return;
        }

        messages.forEach(function (msg) {
            const classe = msg.class === 'equipe' ? 'equipe' : 'cliente';
            const row = document.createElement('div');
            row.className = 'message-row ' + classe;
            row.dataset.messageId = String(msg.id || '');
            row.dataset.messageClass = classe;

            const avatar = document.createElement('span');
            avatar.className = 'message-avatar';
            avatar.setAttribute('aria-hidden', 'true');
            const author = String(msg.author || (classe === 'equipe' ? 'Equipe' : 'Cliente'));
            avatar.textContent = author.slice(0, 2).toUpperCase();

            const wrap = document.createElement('div');
            wrap.className = 'bubble-wrap';

            const bubble = document.createElement('div');
            bubble.className = 'bubble';

            if (classe !== 'cliente') {
                const name = document.createElement('span');
                name.className = 'bubble-name';
                name.textContent = author;
                bubble.appendChild(name);
            }

            if (msg.text) {
                const text = document.createElement('span');
                text.textContent = msg.text;
                bubble.appendChild(text);
            }

            const time = document.createElement('span');
            time.className = 'bubble-time';
            time.textContent = (msg.time || '') + (classe === 'cliente' ? ' ✓✓' : '');
            bubble.appendChild(time);

            if (classe === 'cliente') {
                const seen = document.createElement('span');
                seen.className = 'chat-seen-status';
                seen.dataset.seenStatus = '1';
                seen.style.display = Number(msg.id || 0) <= Number(clientSeenUntilId || 0) ? 'block' : 'none';
                seen.textContent = 'Visualizado pelo suporte';
                bubble.appendChild(seen);
            }

            wrap.appendChild(bubble);

            if (Array.isArray(msg.attachments) && msg.attachments.length > 0) {
                const grid = document.createElement('div');
                grid.className = 'attachment-grid';
                grid.setAttribute('aria-label', 'Anexos da mensagem');
                grid.innerHTML = msg.attachments.map(renderAttachmentCard).join('');
                wrap.appendChild(grid);
            }

            row.appendChild(avatar);
            row.appendChild(wrap);
            chat.appendChild(row);
        });

        chat.scrollTop = chat.scrollHeight;
        updateSeenStatus(clientSeenUntilId);
        portalDebug('chat_render_done', {
            total_renderizado: messages.length,
            ultimo_id: messages.length ? Number(messages[messages.length - 1].id || 0) : 0,
            client_seen_until_id: Number(clientSeenUntilId || 0)
        });
    }

    function updateSeenStatus(clientSeenUntilId) {
        const limit = Number(clientSeenUntilId || 0);
        document.querySelectorAll('#chatHistorico .message-row.cliente[data-message-id] [data-seen-status]').forEach(function (seen) {
            const row = seen.closest('[data-message-id]');
            const id = Number(row?.dataset.messageId || 0);
            seen.style.display = limit > 0 && id > 0 && id <= limit ? 'block' : 'none';
        });
    }

    function setSupportTyping(isTyping, name) {
        const box = document.querySelector('[data-support-typing]');
        const text = document.querySelector('[data-support-typing-text]');
        if (!box) return;
        box.classList.toggle('is-visible', Boolean(isTyping));
        if (text) text.textContent = (name || 'Suporte') + ' está digitando...';
    }

    function normalizeRealtimeMessage(payload) {
        const origem = String(payload?.origem || '').toLowerCase();
        const messageClass = payload?.class || (['cliente', 'portal_cliente', 'client'].includes(origem) ? 'cliente' : 'equipe');

        return {
            id: payload?.id || payload?.message_id || '',
            class: messageClass === 'cliente' ? 'cliente' : 'equipe',
            author: payload?.author || payload?.usuario_nome || payload?.nome || (messageClass === 'cliente' ? 'Cliente' : 'Equipe'),
            text: payload?.text || payload?.mensagem || '',
            time: payload?.time || payload?.created_at_label || 'agora',
            attachments: Array.isArray(payload?.attachments) ? payload.attachments : (Array.isArray(payload?.anexos) ? payload.anexos : []),
        };
    }

    function appendRealtimeChatMessage(payload) {
        const msg = normalizeRealtimeMessage(payload);
        if (!msg.id) return;

        const chat = document.getElementById('chatHistorico');
        if (!chat) return;

        if (chat.querySelector('[data-message-id="' + CSS.escape(String(msg.id)) + '"]')) {
            return;
        }

        const empty = chat.querySelector('.empty-chat');
        if (empty) empty.remove();

        const classe = msg.class === 'equipe' ? 'equipe' : 'cliente';
        const row = document.createElement('div');
        row.className = 'message-row ' + classe;
        row.dataset.messageId = String(msg.id);
        row.dataset.messageClass = classe;

        const avatar = document.createElement('span');
        avatar.className = 'message-avatar';
        avatar.setAttribute('aria-hidden', 'true');
        const author = String(msg.author || (classe === 'equipe' ? 'Equipe' : 'Cliente'));
        avatar.textContent = author.slice(0, 2).toUpperCase();

        const wrap = document.createElement('div');
        wrap.className = 'bubble-wrap';

        const bubble = document.createElement('div');
        bubble.className = 'bubble';

        if (classe !== 'cliente') {
            const name = document.createElement('span');
            name.className = 'bubble-name';
            name.textContent = author;
            bubble.appendChild(name);
        }

        if (msg.text) {
            const text = document.createElement('span');
            text.textContent = msg.text;
            bubble.appendChild(text);
        }

        const time = document.createElement('span');
        time.className = 'bubble-time';
        time.textContent = (msg.time || 'agora') + (classe === 'cliente' ? ' ✓✓' : '');
        bubble.appendChild(time);

        if (classe === 'cliente') {
            const seen = document.createElement('span');
            seen.className = 'chat-seen-status';
            seen.dataset.seenStatus = '1';
            seen.style.display = 'none';
            seen.textContent = 'Visualizado pelo suporte';
            bubble.appendChild(seen);
        }

        wrap.appendChild(bubble);

        if (Array.isArray(msg.attachments) && msg.attachments.length > 0) {
            const grid = document.createElement('div');
            grid.className = 'attachment-grid';
            grid.setAttribute('aria-label', 'Anexos da mensagem');
            grid.innerHTML = msg.attachments.map(renderAttachmentCard).join('');
            wrap.appendChild(grid);
        }

        row.appendChild(avatar);
        row.appendChild(wrap);
        chat.appendChild(row);
        chat.scrollTop = chat.scrollHeight;
    }


    function latestChatMessageId() {
        const rows = Array.from(document.querySelectorAll('#chatHistorico [data-message-id]'));
        return rows.reduce(function (max, row) {
            const id = Number(row.dataset.messageId || 0);
            return Number.isFinite(id) && id > max ? id : max;
        }, 0);
    }

    async function syncPublicMessagesWhenSocketOffline(reason = 'offline') {
        if (portalChatSocket && portalChatSocket.connected) return;
        if (!portalChatSocketConfig?.syncUrl || !window.fetch) return;

        const afterId = latestChatMessageId();
        const url = new URL(portalChatSocketConfig.syncUrl, window.location.origin);
        url.searchParams.set('after_id', String(afterId));

        try {
            portalDebug('socket_public_sync_start', { after_id: afterId, reason });
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const data = await response.json().catch(function () { return null; });
            const messages = Array.isArray(data?.messages) ? data.messages : [];
            portalDebug('socket_public_sync_response', { status: response.status, after_id: afterId, quantidade: messages.length, reason });
            messages.forEach(function (message) {
                appendRealtimeChatMessage(message);
            });
        } catch (error) {
            portalDebug('socket_public_sync_error', { erro: String(error && error.message ? error.message : error), reason });
        }
    }

    function startPublicOfflineSync(reason = 'socket_offline') {
        if (portalOfflineSyncTimer) return;
        portalDebug('socket_public_offline_sync_enabled', { reason });
        portalOfflineSyncTimer = window.setInterval(function () {
            syncPublicMessagesWhenSocketOffline(reason);
        }, 3000);
        syncPublicMessagesWhenSocketOffline(reason);
    }

    function stopPublicOfflineSync() {
        if (!portalOfflineSyncTimer) return;
        window.clearInterval(portalOfflineSyncTimer);
        portalOfflineSyncTimer = null;
        portalDebug('socket_public_offline_sync_disabled');
    }


    async function persistPublicSeen(messageId) {
        const id = Number(messageId || 0);
        if (!id || !portalChatSocketConfig?.seenUrl || !window.fetch) return;

        try {
            await fetch(portalChatSocketConfig.seenUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ message_id: id }),
            });
        } catch (error) {
            portalDebug('socket_public_seen_persist_error', { message_id: id, erro: String(error && error.message ? error.message : error) });
        }
    }

    function lastEquipeMessageId() {
        const rows = Array.from(document.querySelectorAll('#chatHistorico .message-row.equipe[data-message-id]'));
        return rows.reduce(function (max, row) {
            const id = Number(row.dataset.messageId || 0);
            return id > max ? id : max;
        }, 0);
    }

    function connectPublicChatSocket() {
        if (!portalChatSocketConfig?.enabled || !portalChatSocketConfig?.url) {
            portalDebug('socket_public_disabled');
            return;
        }

        if (!window.io) {
            portalDebug('socket_public_client_missing', { url: portalChatSocketConfig.url, script_error: Boolean(window.__portalSocketIoScriptError), script_loaded: Boolean(window.__portalSocketIoScriptLoaded) });
            startPublicOfflineSync('socket_client_missing');
            window.clearTimeout(portalSocketRetryTimer);
            portalSocketRetryTimer = window.setTimeout(function () {
                portalDebug('socket_public_script_retry', { url: portalChatSocketConfig.url });
                connectPublicChatSocket();
            }, 2500);
            return;
        }

        if (portalChatSocket && (portalChatSocket.connected || portalChatSocket.active)) return;

        portalChatSocket = window.io(portalChatSocketConfig.url, {
            transports: ['websocket', 'polling'],
            withCredentials: true,
            auth: {
                empresaId: portalChatSocketConfig.empresaId,
                actor: portalChatSocketConfig.actor || 'cliente',
                token: portalChatSocketConfig.token || '',
                signature: portalChatSocketConfig.signature || '',
                room: portalChatSocketConfig.room || '',
            },
        });

        portalChatSocket.on('connect', function () {
            stopPublicOfflineSync();
            portalDebug('socket_public_connected', { socket_id: portalChatSocket.id });
            const ultimoEquipe = lastEquipeMessageId();
            if (ultimoEquipe > 0) {
                portalChatSocket.emit('chat:seen', { message_id: ultimoEquipe, room: portalChatSocketConfig.room || '', at: new Date().toISOString() });
                persistPublicSeen(ultimoEquipe);
            }
        });

        portalChatSocket.on('connect_error', function (error) {
            portalDebug('socket_public_connect_error', { erro: String(error && error.message ? error.message : error) });
            startPublicOfflineSync('socket_connect_error');
        });

        portalChatSocket.on('disconnect', function (reason) {
            portalDebug('socket_public_disconnect', { reason: String(reason || '') });
            startPublicOfflineSync('socket_disconnect');
        });

        portalChatSocket.on('chat:message:new', function (payload) {
            const msg = normalizeRealtimeMessage(payload);
            portalDebug('socket_public_message_received', { message_id: Number(msg.id || 0), socket_connected: Boolean(portalChatSocket && portalChatSocket.connected), socket_id: portalChatSocket?.id || null });
            appendRealtimeChatMessage(payload);
            setSupportTyping(false);
            if (msg.class === 'equipe' && Number(msg.id || 0) > 0) {
                portalChatSocket.emit('chat:seen', { message_id: Number(msg.id), room: portalChatSocketConfig.room || '', at: new Date().toISOString() });
                persistPublicSeen(Number(msg.id));
            }
        });

        portalChatSocket.on('chat:typing:start', function (payload) {
            if (payload?.actor === 'cliente') return;
            setSupportTyping(true, payload?.nome || 'Suporte');
            window.clearTimeout(window.__supportTypingTimer);
            window.__supportTypingTimer = window.setTimeout(function () { setSupportTyping(false); }, 8000);
        });

        portalChatSocket.on('chat:typing:stop', function (payload) {
            if (payload?.actor === 'cliente') return;
            setSupportTyping(false);
        });

        portalChatSocket.on('chat:seen', function (payload) {
            if (payload?.actor === 'cliente') return;
            updateSeenStatus(Number(payload?.message_id || 0));
        });
    }

    connectPublicChatSocket();


    document.querySelectorAll('[data-chat-form]').forEach(function (form) {
        const textarea = form.querySelector('.js-chat-message');
        textarea?.addEventListener('input', function () {
            announceClientTyping(form);
        });

        form.addEventListener('submit', async function (event) {
            if (clientChatSendingBusy) {
                event.preventDefault();
                return;
            }

            const message = form.querySelector('.js-chat-message')?.value.trim() || '';
            const files = form.querySelector('.js-chat-files')?.files || [];
            const submitStartedAt = performance.now();
            portalDebug('chat_submit', { tamanhoMensagem: message.length, quantidadeArquivos: files.length, modo: 'ajax', fase: 'listener' });

            if (message === '' && files.length === 0) {
                event.preventDefault();
                const feedbackMessage = 'Digite uma mensagem ou anexe ao menos um arquivo antes de enviar.';
                showClientToast(feedbackMessage, 'error');
                setInlineFeedback(form, feedbackMessage, 'error');
                form.dataset.invalid = '1';
                form.querySelector('.js-chat-message')?.focus();
                window.setTimeout(function () { delete form.dataset.invalid; }, 120);
                return;
            }

            if (!window.fetch || !window.FormData) {
                return;
            }

            event.preventDefault();

            const requestData = new FormData(form);
            requestData.set('_portal_ajax', '1');
            const optimisticRow = appendOptimisticChatMessage(form, message, files.length);
            resetChatComposer(form);
            setInlineFeedback(form, '', 'success');

            clientChatSendingBusy = true;
            form.classList.add('is-processing');
            const submitButton = form.querySelector('button[type="submit"]');

            try {
                portalDebug('chat_ajax_start', { tamanhoMensagem: message.length, quantidadeArquivos: files.length, fase: 'fetch_inicio' });
                const response = await fetch(form.action, {
                    method: (form.method || 'POST').toUpperCase(),
                    headers: {
                        'Accept': 'application/json, text/html;q=0.9, */*;q=0.8',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Portal-Ajax': '1'
                    },
                    body: requestData,
                    credentials: 'same-origin'
                });

                let responseData = null;

                try {
                    const contentType = response.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        responseData = await response.json();
                    }
                } catch (parseError) {
                    responseData = null;
                }

                portalDebug('chat_ajax_response', {
                    status: response.status,
                    duration_ms: Math.round(performance.now() - submitStartedAt),
                    message_id: Number(responseData?.chat_message?.id || responseData?.message_id || 0),
                    fase: response.ok ? 'http_ok' : 'http_not_ok'
                });

                if (!response.ok || (responseData && responseData.ok === false)) {
                    let serverMessage = responseData?.message || responseData?.errors?.mensagem?.[0] || ('HTTP ' + response.status);
                    if (response.status === 429 || String(serverMessage).toLowerCase().includes('too many attempts')) {
                        serverMessage = 'O chat recebeu muitas atualizações ao mesmo tempo. Aguarde alguns segundos e tente enviar novamente.';
                    }
                    throw new Error(serverMessage);
                }

                markOptimisticMessage(optimisticRow, 'sent', 'agora');
                if (responseData?.chat_message) {
                    applyServerMessageToOptimistic(optimisticRow, responseData.chat_message);
                }
                if (portalChatSocket && portalChatSocket.connected && responseData?.chat_message) {
                    portalDebug('chat_socket_emit_start', { message_id: Number(responseData.chat_message.id || 0), socket_connected: true, socket_id: portalChatSocket.id });
                    responseData.chat_message.room = responseData.chat_message.room || portalChatSocketConfig.room || '';
                    portalChatSocket.emit('chat:message:new', responseData.chat_message, function (ack) {
                        portalDebug('chat_socket_emit_ack', { message_id: Number(responseData.chat_message.id || 0), socket_connected: Boolean(portalChatSocket && portalChatSocket.connected), socket_id: portalChatSocket?.id || null, ack: ack || null });
                    });
                    portalChatSocket.emit('chat:typing:stop', { room: portalChatSocketConfig.room || '' });
                } else if (responseData?.chat_message) {
                    portalDebug('chat_socket_emit_offline', { message_id: Number(responseData.chat_message.id || 0), socket_connected: Boolean(portalChatSocket && portalChatSocket.connected), socket_id: portalChatSocket?.id || null });
                    startPublicOfflineSync('emit_offline_after_send');
                }
                setInlineFeedback(form, '', 'success');
                portalDebug('chat_ajax_success', { status: response.status, duration_ms: Math.round(performance.now() - submitStartedAt), message_id: Number(responseData?.chat_message?.id || responseData?.message_id || 0) });
            } catch (error) {
                markOptimisticMessage(optimisticRow, 'failed', 'não enviado');
                portalDebug('chat_ajax_error', { erro: String(error && error.message ? error.message : error), duration_ms: Math.round(performance.now() - submitStartedAt) });
                const errorMessage = error?.message && !String(error.message).startsWith('HTTP')
                    ? error.message
                    : 'Não foi possível enviar agora. Sua mensagem ficou na tela; tente enviar novamente.';
                setInlineFeedback(form, errorMessage, 'error');
                showClientToast(errorMessage, 'error');
                portalDebug('chat_ajax_error', { message: error?.message || String(error) });
            } finally {
                clientChatSendingBusy = false;
                form.classList.remove('is-processing');
            }
        });
    });

    document.querySelectorAll('.mini-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const textarea = form.querySelector('textarea[name="resposta"]');
            const response = textarea?.value.trim() || '';
            if (response.length < 3) {
                event.preventDefault();
                const feedbackMessage = 'Escreva uma resposta para esta pendência antes de enviar.';
                showClientToast(feedbackMessage, 'error');
                setInlineFeedback(form, feedbackMessage, 'error');
                form.dataset.invalid = '1';
                textarea?.focus();
                window.setTimeout(function () { delete form.dataset.invalid; }, 120);
                return;
            }
            setInlineFeedback(form, 'Enviando resposta da pendência...', 'success');
        });
    });

    document.querySelectorAll('textarea').forEach(function (textarea) {
        textarea.addEventListener('input', function () {
            const form = textarea.closest('form');
            if (!form) return;
            form.classList.remove('is-invalid');
            textarea.classList.remove('is-invalid');
            const feedback = form.querySelector('[data-form-feedback]');
            if (feedback && feedback.style.display !== 'none') {
                feedback.textContent = '';
                feedback.style.display = 'none';
            }
        });
    });

    document.querySelectorAll('.js-feedback-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (form.dataset.invalid === '1' || form.matches('[data-chat-form]')) return;
            window.setTimeout(function () {
                form.classList.add('is-processing');
                const pendingCard = form.closest('[data-pending-card]');
                pendingCard?.classList.add('is-processing');
                form.querySelectorAll('button[type="submit"]').forEach(function (button) {
                    setButtonProcessing(button, form.dataset.processing || 'Enviando...');
                });
            }, 0);
        });
    });


    document.querySelectorAll('[data-mobile-summary-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const banner = button.closest('.action-banner');
            if (!banner) return;
            const isOpen = !banner.classList.contains('is-mobile-open');
            banner.classList.toggle('is-mobile-open', isOpen);
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            button.innerHTML = isOpen ? 'Ocultar orientação <span>⌃</span>' : 'Ver orientação rápida <span>⌄</span>';
        });
    });

    window.addEventListener('resize', function () {
        const active = document.querySelector('.portal-cluster-button.is-active, .side-link.is-active, .side-link.active');
        setPortalCluster(active?.dataset.portalCluster || 'chat');
    });

    <?php if(session('success')): ?>
        showClientToast(<?php echo json_encode(session('success'), 15, 512) ?>, 'success');
    <?php endif; ?>
    <?php if($errors->any()): ?>
        showClientToast('Revise os campos destacados para continuar.', 'error');
    <?php endif; ?>
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/portal/cliente/show.blade.php ENDPATH**/ ?>