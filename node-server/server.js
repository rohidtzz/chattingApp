require('dotenv').config();

const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const { v4: uuidv4 } = require('uuid');
const { body, validationResult } = require('express-validator');

const app = express();
const HTTP_PORT = process.env.HTTP_PORT || 3000;

const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: process.env.APP_CORS_ORIGIN || 'http://localhost:8000',
        methods: ['GET', 'POST']
    }
});

// Menyimpan koneksi pengguna dan status online
const clients = new Map();
const userStatus = new Map();

app.use(express.json());

app.post(
    '/send-message',
    [
        body('sender_id').isUUID().withMessage('Invalid sender ID'),
        body('username').isString().notEmpty().withMessage('Username is required'),
        body('receiver_id').isUUID().withMessage('Invalid receiver ID'),
        body('message').isString().notEmpty().withMessage('Message is required'),
        body('timestamp').isISO8601().withMessage('Invalid timestamp format'),
    ],
    (req, res) => {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({ errors: errors.array() });
        }

        const { sender_id, receiver_id, message, timestamp, username } = req.body;

        const data = {
            id: uuidv4(),
            sender_id,
            receiver_id,
            message,
            username,
            timestamp
        };

        sendPrivateMessage(sender_id, receiver_id, data);
        res.status(200).json({ success: true, message: 'Message sent successfully' });
    }
);

app.get('/online-users', (req, res) => {
    const onlineUsers = [...userStatus.entries()].filter(([_, status]) => status === 'online').map(([user]) => user);
    res.json({ online_users: onlineUsers });
});

io.on('connection', (socket) => {
    console.log('New socket connected:', socket.id);

    socket.on('register', (data) => {
        const { user_id } = data;
        if (user_id) {
            clients.set(user_id, socket.id);
            userStatus.set(user_id, 'online');
            console.log(`User ${user_id} registered with socket ${socket.id}`);

            broadcastStatusUpdate();
            sendOnlineUsers();
        }
    });

    socket.on('private_message', (data) => {
        const { sender_id, receiver_id } = data;
        sendPrivateMessage(sender_id, receiver_id, data);
    });

    socket.on('typing', (data) => {
        const { sender_id, receiver_id, is_typing } = data;
        const receiverSocketId = clients.get(receiver_id);
        if (receiverSocketId) {
            io.to(receiverSocketId).emit('typing', { sender_id, is_typing });
        }
    });

    socket.on('disconnect', () => {
        const userId = [...clients.entries()].find(([_, id]) => id === socket.id)?.[0];
        if (userId) {
            clients.delete(userId);
            userStatus.set(userId, 'offline');
            console.log(`User ${userId} disconnected`);

            broadcastStatusUpdate();
            sendOnlineUsers();
        }
    });
});

function sendPrivateMessage(sender_id, receiver_id, message) {
    const receiverSocketId = clients.get(receiver_id);
    if (receiverSocketId) {
        io.to(receiverSocketId).emit('private_message', message);
    } else {
        console.log(`User ${receiver_id} is offline`);
    }
}

function sendOnlineUsers() {
    const onlineUsers = [...userStatus.entries()]
        .filter(([_, status]) => status === 'online')
        .map(([user]) => user);

    io.emit('online_users', { type: 'online_users', online_users: onlineUsers });
}

function broadcastStatusUpdate() {
    const statusData = { type: 'status_update', users: Object.fromEntries(userStatus) };
    io.emit('status_update', statusData);
}

server.listen(HTTP_PORT, () => {
    console.log(`Socket.IO + HTTP server running on port ${HTTP_PORT}`);
});
