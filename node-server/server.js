const express = require('express');
const WebSocket = require('ws');
const cors = require('cors');
const { v4: uuidv4 } = require('uuid');
const { body, validationResult } = require('express-validator');

const app = express();
const HTTP_PORT = 3000;
const WS_PORT = 8080;

// Simpan koneksi pengguna berdasarkan user_id
const clients = new Map();

app.use(cors({ origin: 'http://localhost:8000' }));
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

const httpServer = app.listen(HTTP_PORT, () => {
    console.log(`HTTP server running on port ${HTTP_PORT}`);
});

const wss = new WebSocket.Server({ port: WS_PORT });

wss.on('connection', (ws, req) => {
    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);
            if (data.type === 'register' && data.user_id) {
                // Simpan koneksi dengan user_id
                clients.set(data.user_id, ws);
                console.log(`User ${data.user_id} connected`);
            } else if (data.type === 'private_message') {
                // Kirim pesan ke penerima yang dituju
                sendPrivateMessage(data.sender_id, data.receiver_id, data);
            }
        } catch (error) {
            console.error('Invalid message format:', message);
        }
    });

    ws.on('close', () => {
        // Hapus user_id saat koneksi terputus
        clients.forEach((client, userId) => {
            if (client === ws) {
                clients.delete(userId);
                console.log(`User ${userId} disconnected`);
            }
        });
    });
});

function sendPrivateMessage(sender_id, receiver_id, message) {
    const receiverSocket = clients.get(receiver_id);
    if (receiverSocket && receiverSocket.readyState === WebSocket.OPEN) {
        receiverSocket.send(JSON.stringify(message));
    } else {
        console.log(`User ${receiver_id} is offline, message not delivered`);
    }
}

console.log(`WebSocket server running on port ${WS_PORT}`);


// const express = require('express');
// const WebSocket = require('ws');
// const cors = require('cors');
// const app = express();
// const { body, validationResult } = require('express-validator');
// const HTTP_PORT = 3000;
// const WS_PORT = 8080;

// app.use(cors({
//   origin: 'http://localhost:8000'
// }));

// app.use(express.json());

// app.post(
//     '/send-message',
//     [
//         body('username').isString().notEmpty().withMessage('Username is required and must be a string'),
//         body('message').isString().notEmpty().withMessage('Message is required and must be a string'),
//         body('timestamp').isISO8601().withMessage('Invalid timestamp format'),
//     ],
//     (req, res) => {
//         const errors = validationResult(req);
//         if (!errors.isEmpty()) {
//             return res.status(400).json({ errors: errors.array() });
//         }

//         const { username, message, timestamp } = req.body;
//         const data = { username, message, timestamp };

//         broadcastMessage(data);

//         res.status(200).json({ success: true, message: 'Message sent successfully' });
//     }
// );

// const httpServer = app.listen(HTTP_PORT, () => {
//   console.log(`HTTP server running on port ${HTTP_PORT}`);
// });

// const wss = new WebSocket.Server({ port: WS_PORT });

// function broadcastMessage(message) {
//   wss.clients.forEach(client => {
//     if (client.readyState === WebSocket.OPEN) {
//       client.send(JSON.stringify(message));
//     }
//   });
// }

// wss.on('connection', (ws, req) => {
//     const origin = req.headers.origin || 'Unknown Origin';
//     console.log(`Connected from: ${origin}`);

//     ws.on('message', (message) => {
//         try {
//             let data = JSON.parse(message);

//             const datas = {
//                 username: data.username,
//                 message: data.message,
//                 timestamp: data.timestamp,
//             }

//             console.log(`[User ${username}]: ${text}`);

//             broadcastMessage(datas);
//         } catch (error) {
//             console.error('Invalid message format:', message);
//         }
//     });

//     ws.on('close', () => {
//         console.log('Client disconnected');
//     });
// });

// console.log(`WebSocket server running on port ${WS_PORT}`);

