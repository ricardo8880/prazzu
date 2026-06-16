import http from 'node:http';
import crypto from 'node:crypto';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { Server } from 'socket.io';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

function loadDotEnvFile() {
    const envPath = path.join(__dirname, '.env');

    if (!fs.existsSync(envPath)) {
        socketLogger.warn('[socket.io] Arquivo .env não encontrado. Usando variáveis do ambiente atual.');
        return;
    }

    const content = fs.readFileSync(envPath, 'utf8');

    for (const rawLine of content.split(/\r?\n/)) {
        const line = rawLine.trim();

        if (!line || line.startsWith('#') || !line.includes('=')) {
            continue;
        }

        const index = line.indexOf('=');
        const key = line.slice(0, index).trim();
        let value = line.slice(index + 1).trim();

        if ((value.startsWith('"') && value.endsWith('"')) || (value.startsWith("'") && value.endsWith("'"))) {
            value = value.slice(1, -1);
        }

        if (process.env[key] === undefined) {
            process.env[key] = value;
        }
    }
}

loadDotEnvFile();

const port = Number(process.env.SOCKET_IO_PORT || 3001);
const host = process.env.SOCKET_IO_HOST || '0.0.0.0';
const appKey = process.env.APP_KEY || '';
const enableAdminUi = String(process.env.SOCKET_IO_ADMIN_UI || 'false').toLowerCase() === 'true';
const enableSocketDebug = String(process.env.SOCKET_IO_DEBUG || 'false').toLowerCase() === 'true';
const socketLogger = {
    info: (...args) => { if (enableSocketDebug) console.info(...args); },
    warn: (...args) => { if (enableSocketDebug) console.warn(...args); },
    error: (...args) => { if (enableSocketDebug) console.error(...args); },
};

const defaultAllowedOrigins = [
    process.env.APP_URL,
    'http://localhost',
    'http://localhost:8000',
    'http://127.0.0.1:8000',
    'http://127.0.0.1',
].filter(Boolean);

const allowedOrigins = String(process.env.SOCKET_IO_ALLOWED_ORIGINS || defaultAllowedOrigins.join(','))
    .split(',')
    .map((origin) => origin.trim().replace(/\/$/, ''))
    .filter(Boolean);

const httpServer = http.createServer((request, response) => {
    if (request.url === '/health') {
        response.writeHead(200, { 'Content-Type': 'application/json' });
        response.end(JSON.stringify({ ok: true, service: 'prazzu-chat-socket', uptime: process.uptime() }));
        return;
    }

    response.writeHead(404, { 'Content-Type': 'application/json' });
    response.end(JSON.stringify({ ok: false, error: 'not_found' }));
});

const io = new Server(httpServer, {
    cors: {
        origin: (origin, callback) => {
            const normalizedOrigin = String(origin || '').replace(/\/$/, '');
            if (!origin || allowedOrigins.includes('*') || allowedOrigins.includes(normalizedOrigin)) {
                callback(null, true);
                return;
            }

            callback(new Error(`Origem não permitida no Socket.IO: ${origin}`));
        },
        credentials: true,
        methods: ['GET', 'POST'],
    },
    transports: ['websocket', 'polling'],
});

function normalizeAppKey(key) {
    return String(key || '').trim();
}

function makeSignature({ empresaId, actor, token, room }) {
    const secret = normalizeAppKey(appKey);
    return crypto
        .createHmac('sha256', secret)
        .update(`${empresaId}|${actor}|${token || ''}|${room || ''}`)
        .digest('hex');
}

function makeMessageSignature({ empresaId, room, actor, messageId }) {
    const secret = normalizeAppKey(appKey);
    return crypto
        .createHmac('sha256', secret)
        .update(`${empresaId}|${room || ''}|${actor}|${messageId}`)
        .digest('hex');
}

function validMessageSignature(payload, context) {
    if (!appKey) {
        socketLogger.error('[socket.io] APP_KEY ausente. Validação da mensagem falhou.');
        return false;
    }

    const empresaId = String(context.empresaId || '').trim();
    const room = String(context.room || '').trim();
    const actor = String(context.actor || '').trim();
    const messageId = String(payload.message_id || payload.id || '').trim();
    const signature = String(payload.server_signature || payload.socket_signature || '').trim();

    if (!empresaId || !room || !actor || !messageId || !signature) {
        socketLogger.warn('[socket.io] Mensagem sem assinatura backend completa', { empresaId, room, actor, messageId, temSignature: Boolean(signature) });
        return false;
    }

    const expected = makeMessageSignature({ empresaId, room, actor, messageId });

    try {
        return crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expected));
    } catch {
        return false;
    }
}

function validSignature(payload) {
    if (!appKey) {
        socketLogger.error('[socket.io] APP_KEY ausente. Autenticação do socket falhou.');
        return false;
    }

    const empresaId = String(payload.empresaId || '').trim();
    const actor = String(payload.actor || '').trim();
    const token = String(payload.token || '').trim();
    const signature = String(payload.signature || '').trim();
    const room = normalizeRoomName(payload.room, empresaId);

    if (!empresaId || !actor || !signature || !room) {
        socketLogger.warn('[socket.io] Payload de autenticação incompleto', { empresaId, actor, room, temSignature: Boolean(signature) });
        return false;
    }

    const expected = makeSignature({ empresaId, actor, token, room });

    try {
        return crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expected));
    } catch {
        return false;
    }
}

function normalizeRoomName(room, empresaId) {
    const empresa = String(empresaId || '').trim();
    const raw = String(room || '').trim();

    if (!empresa || !raw) {
        return null;
    }

    const allowed = [
        new RegExp(`^empresa:${empresa}:portal$`),
        new RegExp(`^empresa:${empresa}:item:[0-9]+$`),
        new RegExp(`^empresa:${empresa}:atendimento:[0-9]+$`),
        new RegExp(`^empresa:${empresa}:portal-cliente:[0-9]+$`),
    ];

    return allowed.some((pattern) => pattern.test(raw)) ? raw : null;
}

function cleanText(value, max = 5000) {
    return String(value ?? '').slice(0, max);
}

function cleanPayload(payload = {}, actor = '') {
    const origem = cleanText(payload.origem || payload.actor || payload.class || actor, 50);
    const isCliente = ['cliente', 'portal_cliente', 'client'].includes(String(origem).toLowerCase()) || payload.class === 'cliente' || actor === 'cliente';
    const classe = isCliente ? 'cliente' : 'equipe';
    const texto = cleanText(payload.text ?? payload.mensagem_texto ?? payload.mensagem ?? '', 5000);
    const anexos = Array.isArray(payload.attachments)
        ? payload.attachments.slice(0, 10)
        : (Array.isArray(payload.anexos) ? payload.anexos.slice(0, 10) : []);
    const nome = cleanText(payload.author ?? payload.usuario_nome ?? payload.nome ?? (classe === 'cliente' ? 'Cliente' : 'Equipe'), 120);
    const createdAt = payload.created_at ?? payload.created_at_label ?? payload.time ?? new Date().toISOString();

    return {
        id: payload.id ?? payload.message_id ?? null,
        message_id: payload.message_id ?? payload.id ?? null,
        empresa_id: payload.empresa_id ?? payload.empresaId ?? null,
        room: payload.room ?? null,
        item_controle_id: payload.item_controle_id ?? null,
        atendimento_id: payload.atendimento_id ?? payload.atendimentoId ?? null,
        class: classe,
        actor: classe === 'cliente' ? 'cliente' : 'suporte',
        origem: origem || (classe === 'cliente' ? 'cliente' : 'interno'),
        author: nome,
        nome,
        usuario_nome: nome,
        text: texto,
        mensagem: texto,
        time: payload.time ?? payload.created_at_label ?? payload.created_at ?? 'agora',
        created_at_label: payload.created_at_label ?? payload.time ?? payload.created_at ?? 'agora',
        created_at: createdAt,
        attachments: anexos,
        anexos,
    };
}

io.use((socket, next) => {
    const auth = socket.handshake.auth || {};
    const query = socket.handshake.query || {};
    const payload = {
        empresaId: auth.empresaId || query.empresaId,
        actor: auth.actor || query.actor,
        token: auth.token || query.token || '',
        signature: auth.signature || query.signature || '',
        room: auth.room || query.room || '',
    };

    if (!validSignature(payload)) {
        next(new Error('socket_auth_invalid'));
        return;
    }

    socket.data.empresaId = String(payload.empresaId).trim();
    socket.data.actor = String(payload.actor).trim();
    socket.data.publicToken = String(payload.token || '').trim();
    socket.data.room = normalizeRoomName(payload.room, payload.empresaId);

    if (!socket.data.room) {
        next(new Error('socket_room_invalid'));
        return;
    }

    next();
});

io.on('connection', (socket) => {
    const empresaId = socket.data.empresaId;
    const actor = socket.data.actor;
    const room = socket.data.room;

    socket.join(room);

    socketLogger.info('[socket.io] conectado', { socketId: socket.id, empresaId, actor, room });

    socket.emit('chat:connected', {
        ok: true,
        empresa_id: empresaId,
        actor,
        room,
    });

    socket.to(room).emit('chat:presence', {
        empresa_id: empresaId,
        room,
        actor,
        online: true,
    });

    socket.on('chat:message:new', (payload = {}, callback) => {
        if (!validMessageSignature(payload, { empresaId, room, actor })) {
            const messageId = payload.message_id || payload.id || null;
            socketLogger.warn('[socket.io] chat:message:new rejeitado por assinatura inválida', { empresaId, actor, room, messageId });
            callback?.({ ok: false, error: 'message_signature_invalid' });
            return;
        }

        const normalized = cleanPayload({ ...payload, empresa_id: empresaId, room }, actor);
        socketLogger.info('[socket.io] chat:message:new', { empresaId, actor, messageId: normalized.id, room, clientesNaSala: io.sockets.adapter.rooms.get(room)?.size || 0 });
        // Envia para todos na sala, inclusive quem enviou. Os clientes ignoram duplicado por data-message-id.
        io.to(room).emit('chat:message:new', normalized);
        callback?.({ ok: true, delivered_to_room: io.sockets.adapter.rooms.get(room)?.size || 0 });
    });

    socket.on('chat:typing:start', (payload = {}) => {
        socket.to(room).emit('chat:typing:start', {
            empresa_id: empresaId,
            room,
            actor,
            nome: cleanText(payload.nome || '', 120),
            at: new Date().toISOString(),
        });
    });

    socket.on('chat:typing:stop', () => {
        socket.to(room).emit('chat:typing:stop', {
            empresa_id: empresaId,
            room,
            actor,
            at: new Date().toISOString(),
        });
    });

    socket.on('chat:seen', (payload = {}) => {
        socket.to(room).emit('chat:seen', {
            empresa_id: empresaId,
            room,
            actor,
            message_id: payload.message_id ?? null,
            at: payload.at || new Date().toISOString(),
        });
    });

    socket.on('disconnect', (reason) => {
        socketLogger.info('[socket.io] desconectado', { socketId: socket.id, empresaId, actor, reason });
        socket.to(room).emit('chat:presence', {
            empresa_id: empresaId,
            room,
            actor,
            online: false,
        });
    });
});

if (enableAdminUi) {
    import('@socket.io/admin-ui')
        .then(({ instrument }) => {
            instrument(io, {
                auth: false,
                mode: process.env.APP_ENV === 'production' ? 'production' : 'development',
            });
            socketLogger.info('[socket.io] Admin UI ativado.');
        })
        .catch((error) => {
            socketLogger.warn('[socket.io] Admin UI não foi ativado. Instale @socket.io/admin-ui ou deixe SOCKET_IO_ADMIN_UI=false.', error.message);
        });
}

httpServer.listen(port, host, () => {
    socketLogger.info(`[socket.io] Chat online em http://${host}:${port}`);
    socketLogger.info(`[socket.io] Origens permitidas: ${allowedOrigins.join(', ') || '*'}`);
});
