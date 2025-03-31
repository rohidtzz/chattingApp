@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@push('css')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endpush

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Chat'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">

                    <section>
                        <div class="container py-5">
                            <div class="row">
                                <div class="col-md-6 col-lg-5 col-xl-4 mb-4 mb-md-0">
                                    <h5 class="font-weight-bold mb-3 text-center text-lg-start">Member</h5>
                                    <div class="card">
                                        {{-- bg-body-tertiary --}}
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0" id="member-list">
                                                @foreach ($users as $user)
                                                    <li class="p-2 border-bottom" style="background-color:{{ $user->id === $receiver_id ? 'rgb(248, 249, 250)' : '' }} ">
                                                        <a href="{{ url('chat/' . $user->id) }}" class="d-flex justify-content-between text-decoration-none text-dark">
                                                            <div class="d-flex flex-row align-items-center" >
                                                                <!-- Avatar -->
                                                                <img
                                                                    src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/avatar-1.webp"
                                                                    alt="{{ $user->username }}"
                                                                    class="rounded-circle me-3 shadow-1-strong"
                                                                    width="60"
                                                                >
                                                                <!-- Username -->
                                                                <div>
                                                                    <p class="fw-bold mb-0">{{ $user->username }}</p>
                                                                    {{-- Uncomment the following line if you want to display a status message --}}
                                                                    {{-- <p class="small text-muted">Lorem ipsum dolor sit.</p> --}}
                                                                </div>
                                                            </div>
                                                            <!-- Timestamp (Optional) -->
                                                            <div>
                                                                {{-- Uncomment the following line if you want to display a timestamp --}}
                                                                {{-- <p class="small text-muted mb-1">5 mins ago</p> --}}
                                                            </div>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-7 col-xl-8">
                                    {{-- <div id="status" class="text-center text-muted mb-2">Connecting...</div> --}}
                                    <form id="messageForm">
                                        <ul class="list-unstyled overflow-auto" id="messages" style="max-height: 400px;"></ul>
                                        {{-- <div class="bg-white mb-3">
                                            <div class="form-outline">
                                                <div id="editor" class="form-control bg-body-tertiary"
                                                    style="height: 100px;"></div>
                                                <input type="hidden" id="messageInput">
                                            </div>
                                        </div> --}}
                                        <textarea id="messageInput" class="form-control bg-body-tertiary" style="height: 100px;"></textarea>
                                        <br>
                                        <button type="submit" class="btn btn-info btn-rounded float-end">Send</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')

    <script>
        let ws;
        let reconnectAttempts = 0;
        let reconnectTimeout;

        const currentUsername = "{{ auth()->user()->username }}";
        const currentUserId = "{{ auth()->user()->id }}";
        const receiverId = "{{ $receiver_id }}";
        const url = "{{ url('api/chat/'.$receiver_id ) }}";


        function connectWebSocket() {
            ws = new WebSocket('ws://localhost:8080');


            ws.onopen = function() {
                console.log('Connected to WebSocket');

                reconnectAttempts = 0;
                clearTimeout(reconnectTimeout);

                const user_id = currentUserId;
                ws.send(JSON.stringify({ type: "register", user_id }));
            };

            ws.onmessage = function(e) {
                try {
                    let parsedData = JSON.parse(e.data);

                    if (typeof parsedData === "string") {
                        parsedData = JSON.parse(parsedData);
                    }

                    // Only display if message is not from current user
                    if (parsedData.username !== currentUsername) {
                        displayMessage(parsedData.username, parsedData.message, parsedData.timestamp);
                    }
                } catch (error) {
                    console.error('Invalid message format:', e.data);
                }
            };

            ws.onerror = function(error) {
                console.error('WebSocket error:', error);
                ws.onclose = null;
                ws.close();
            };

            ws.onclose = function() {
                console.log('WebSocket Disconnected!');

                // Hitung waktu tunggu dengan exponential backoff
                reconnectAttempts++;
                let timeout = Math.min(5000 * Math.pow(2, reconnectAttempts), 60000); // Maks 60 detik

                console.log(`Reconnecting in ${timeout / 1000} seconds...`);

                // Pastikan hanya satu reconnect berjalan
                clearTimeout(reconnectTimeout);
                reconnectTimeout = setTimeout(() => {
                    ws.onclose = null; // Hindari event listener dobel sebelum reconnect
                    connectWebSocket();
                }, timeout);
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
            const isCurrentUser = username === currentUsername;
            const timeFormatted = formatTime(timestamp);
            const messageClass = isCurrentUser ? 'sent' : 'received';

            let messageElement = `
                <li class="d-flex justify-content-${isCurrentUser ? 'end' : 'start'} mb-4">

                    <div class="card w-75">
                        <div class="card-header d-flex justify-content-between p-3">
                            <p class="fw-bold mb-0">${username}</p>
                            <p class="text-muted small mb-0"><i class="far fa-clock"></i> ${timeFormatted}</p>
                        </div>
                        <div class="card-body p-2">
                            <p class="mb-0">${message}</p>
                        </div>
                    </div>
                </li>
            `;

            $('#messages').append(messageElement).scrollTop($('#messages')[0].scrollHeight);
        }

        $('#messageForm').on('submit', function(e) {
            e.preventDefault();
            let messageInput = $('#messageInput');
            const message = messageInput.val().trim();


            if (message) {
                const datas = {
                    message: message,
                    sender_id: currentUserId,
                    username: currentUsername,
                };

                $.ajax({
                    url: url,
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(datas),
                    success: function(response) {
                        // Display sent message only once

                        displayMessage(currentUsername, message, new Date().toISOString());
                        messageInput.val('');
                    },
                    error: function(error) {
                        console.error('Error sending message:', error);
                    }
                });
            }
        });

        function getMessages() {
            $.ajax({
                url: '/api/chat/'+receiverId+'/'+currentUserId,
                method: 'GET',
                success: function(data) {

                    if (data.status === "success" && Array.isArray(data.messages)) {
                        data.messages.forEach(function(message) {
                            displayMessage(message.username, message.message, message.created_at);
                        });
                    } else {
                        console.error("Data messages bukan array atau status bukan success");
                    }
                },
                error: function(error) {
                    console.error('Error fetching messages:', error);
                }
            });
        }

        $(document).ready(function() {
            connectWebSocket();

            getMessages();

        });

        document.getElementById("messageInput").addEventListener("keydown", function (event) {
            if (event.key === "Enter") {
                if (!event.shiftKey) {
                    event.preventDefault();
                    document.getElementById("messageForm").dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }));
                }
            }
        });

    </script>
@endpush
