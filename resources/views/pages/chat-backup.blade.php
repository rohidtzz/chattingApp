@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@push('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Quill CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<!-- Quill JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<style>
    #messages {
        border: 1px solid #ccc;
        padding: 10px;
        margin-bottom: 20px;
        height: 430px;
        overflow-y: auto;
        background: #fff;
    }
    .message {
        margin: 5px 0;
        padding: 8px;
        border-radius: 5px;
        max-width: 70%;
        clear: both;
        word-wrap: break-word;
    }
    .message.sent {
        background: #dcf8c6;
        margin-left: auto;
        float: right;
        clear: both;
    }
    .message.received {
        background: #f0f0f0;
        float: left;
        clear: both;
    }
    .message-sender {
        font-weight: bold;
        margin-bottom: 5px;
        font-size: 0.8em;
        color: #666;
    }
    #status {
        font-weight: bold;
        margin-bottom: 10px;
    }
    .message-time {
        font-size: 0.75em;
        color: #888;
        text-align: right;
        margin-top: 3px;
    }
</style>
@endpush

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Chat'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="p-4">
                        <div id="status">Connecting to WebSocket...</div>
                        <div id="messages"></div>

                        <form id="messageForm">
                            <!-- Container untuk Editor -->
                            <div id="editor" style="height: 150px;"></div>

                            <!-- Input hidden untuk menyimpan isi editor -->
                            <input type="hidden" id="messageInput" name="message">

                            {{-- <input type="text" class="form-control" id="messageInput" name="message" placeholder="Enter message" autocomplete="off"> --}}
                            <br>
                            <button class="btn btn-primary" type="submit">Send</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    let ws;
    const currentUser = "{{ auth()->user()->username }}";

    function connectWebSocket() {
        ws = new WebSocket('ws://localhost:8080');

        ws.onopen = function () {
            console.log('Connected to WebSocket');
            $('#status').text('Connected to chat!').css('color', 'green');
        };

        ws.onmessage = function (e) {
            try {
                let parsedData = JSON.parse(e.data);

                if (typeof parsedData === "string") {
                    parsedData = JSON.parse(parsedData);
                }
                console.log(parsedData.timestamp);
                // Only display if message is not from current user
                if (parsedData.username !== currentUser) {
                    displayMessage(parsedData.username, parsedData.message, parsedData.timestamp);
                }
            } catch (error) {
                console.error('Invalid message format:', e.data);
            }
        };

        ws.onerror = function (error) {
            console.error('WebSocket error:', error);
        };

        ws.onclose = function () {
            console.log('WebSocket Disconnected!');
            $('#status').text('Disconnected! Reconnecting...').css('color', 'red');
            setTimeout(connectWebSocket, 5000);
        };
    }

    function formatTime(timestamp) {
        if (!timestamp) return '';

        const date = new Date(timestamp);
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');

        return `${hours}:${minutes}`;
    }


    function displayMessage(username, message, timestamp = null) {
        const messageClass = username === currentUser ? 'sent' : 'received';
        const timeFormatted = formatTime(timestamp);

        let messageElement = $(`<div class="message ${messageClass}">
            ${username !== currentUser ? `<div class="message-sender">${username}</div>` : ''}
            ${message}
            <div class="message-time">${timeFormatted}</div>
        </div>`);

        $('#messages').append(messageElement).scrollTop($('#messages')[0].scrollHeight);
    }

    $('#messageForm').on('submit', function (e) {
        e.preventDefault();
        let messageInput = $('#messageInput');
        const message = messageInput.val().trim();

        if (message) {
            const datas = {
                username: currentUser,
                message: message
            };

            $.ajax({
                url: '/chat',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                contentType: 'application/json',
                data: JSON.stringify(datas),
                success: function () {
                    // Display sent message only once
                    displayMessage(currentUser, message, new Date().toISOString());
                    messageInput.val('');
                },
                error: function (error) {
                    console.error('Error sending message:', error);
                }
            });
        }
    });

    function getMessages() {
        $.ajax({
            url: '/chat/message',
            method: 'GET',
            success: function (data) {

                if (data.status === "success" && Array.isArray(data.messages)) {
                    data.messages.forEach(function(message) {
                        displayMessage(message.username, message.message, message.created_at);
                    });
                } else {
                    console.error("Data messages bukan array atau status bukan success");
                }
            },
            error: function (error) {
                console.error('Error fetching messages:', error);
            }
        });
    }

    $(document).ready(function () {
        connectWebSocket();

        getMessages();

    });

    var quill = new Quill('#editor', {
        theme: 'snow', // 'bubble' juga bisa
        placeholder: 'Enter message...',
        modules: {
        toolbar: [
            [{ 'header': [1, 2, false] }],
            ['bold', 'italic', 'underline'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link'],
            ['clean'] // Hapus format
        ]
        }
    });

    // Simpan isi editor ke input hidden saat berubah
    quill.on('text-change', function() {
        document.querySelector("#messageInput").value = quill.root.innerHTML;
    });
</script>
@endpush
