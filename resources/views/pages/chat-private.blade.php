

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon.png">
    <link rel="icon" type="image/png" href="/img/favicon.png">
    <title>
        Chatting App
    </title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    {{-- <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script> --}}
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="{{ asset('assets/css/argon-dashboard.css') }}" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>


</head>

<body>



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
                                                    <li class="p-2 border-bottom user-list" id="listuser-{{$user->id}}" data-user-id="{{ $user->id }}" style="background-color:{{ $user->id === $receiver_id ? 'rgb(248, 249, 250)' : '' }};">
                                                        <a href="{{ url('chat/' . $user->id) }}" class="d-flex justify-content-between text-decoration-none text-dark">
                                                            <div class="d-flex flex-row align-items-center">
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
                                                                    <span class="badge badge-sm status-badge bg-secondary">
                                                                        Offline
                                                                    </span>
                                                                </div>
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
                                        <ul class="list-unstyled overflow-auto" id="messages" style="max-height: 500px;">


                                            {{-- <li class="d-flex justify-content-start mb-4" id="typingStatus">
                                                <div class="card w-75">
                                                    <div class="card-body p-2">
                                                        <p class="mb-0">${message}</p>
                                                    </div>
                                                </div>
                                            </li> --}}

                                        </ul>
                                        {{-- <div class="bg-white mb-3">
                                            <div class="form-outline">
                                                <div id="editor" class="form-control bg-body-tertiary"
                                                    style="height: 100px;"></div>
                                                <input type="hidden" id="messageInput">
                                            </div>
                                        </div> --}}
                                        <ul class="list-unstyled overflow-auto" id="typingStatus" style="max-height: 400px;" hidden>
                                            <li class="d-flex justify-content-start mb-4" >
                                                <div class="card w-75">
                                                    <div class="card-body p-2">
                                                        <p class="mb-0">mengetik...</p>
                                                    </div>
                                                </div>
                                            </li>

                                        </ul>
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



    <!--   Core JS Files   -->
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    {{-- <script src="{{ asset('assets/js/argon-dashboard.js') }}"></script> --}}

    <script>
        let socket;
        let typingTimeout;

        const currentUsername = "{{ auth()->user()->username }}";
        const currentUserId = "{{ auth()->user()->id }}";
        const receiverId = "{{ $receiver_id }}";
        const url = "{{ config('app.env') === 'local' ? url('api/chat/'.$receiver_id) : secure_url('api/chat/'.$receiver_id) }}";
        const socketUrl = "{{ config('app.websocket_url') }}"; // ganti jadi alamat socket.io

        function connectSocketIO() {
            socket = io(socketUrl, { transports: ['websocket'], reconnection: true, path: '/socket.io/',});

            socket.on('connect', () => {
                console.log('Connected to Socket.IO');

                socket.emit('register', { user_id: currentUserId });
                getOnlineUser();
            });

            socket.on('disconnect', () => {
                console.log('Disconnected from Socket.IO');
            });

            socket.on('private_message', (data) => {
                if (data.sender_id !== currentUserId) {
                    displayMessage(data.username, data.message, data.timestamp);
                }
            });

            socket.on('typing', (data) => {
                updateTypingStatus(data.sender_id, data.is_typing);
            });

            socket.on('online_users', (data) => {
                getOnlineUser(); // atau bisa langsung update pakai data.online_users
            });

            socket.on('status_update', (data) => {
                // Optional: bisa gunakan untuk sinkron status semua user
                getOnlineUser();
            });
        }

        function displayMessage(username, message, timestamp = null) {
            const isCurrentUser = username === currentUsername;
            const timeFormatted = formatTime(timestamp);
            const messageClass = isCurrentUser ? 'sent' : 'received';

            if (username) {
                let messageElement = `
                    <li class="d-flex justify-content-${isCurrentUser ? 'end' : 'start'} mb-4">
                        <div class="card w-75">
                            <div class="card-header d-flex justify-content-between p-3">
                                <p class="fw-bold mb-0">${username}</p>
                                <p class="text-muted small mb-0"><i class="far fa-clock"></i> ${timeFormatted}</p>
                            </div>
                            <div class="card-body p-2">
                                <p class="mb-0" style="color:black">${message}</p>
                            </div>
                        </div>
                    </li>
                `;
                $('#messages').append(messageElement).scrollTop($('#messages')[0].scrollHeight);
            }
        }

        function formatTime(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            return `${hours}:${minutes}`;
        }

        $('#messageForm').on('submit', function (e) {
            e.preventDefault();
            const messageInput = $('#messageInput');
            const message = messageInput.val().trim();

            if (message) {
                const data = {
                    message: message,
                    sender_id: currentUserId,
                    username: currentUsername,
                };

                $.ajax({
                    url: url,
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    success: function () {
                        displayMessage(currentUsername, message, new Date().toISOString());
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
                url: '/api/chat/' + receiverId + '/' + currentUserId,
                method: 'GET',
                success: function (data) {
                    if (data.status === "success" && Array.isArray(data.messages)) {
                        data.messages.forEach(function (message) {
                            displayMessage(message.username, message.message, message.created_at);
                        });
                    }
                },
                error: function (error) {
                    console.error('Error fetching messages:', error);
                }
            });
        }

        function updateTypingStatus(sender_id, isTyping) {
            if (sender_id === receiverId) {
                $('#typingStatus').prop('hidden', !isTyping);
            }
        }

        function sendTypingStatus(isTyping) {
            if (socket && socket.connected) {
                socket.emit('typing', {
                    sender_id: currentUserId,
                    receiver_id: receiverId,
                    is_typing: isTyping
                });
            }
        }

        function getOnlineUser() {
            $.ajax({
                url: '/api/online-users',
                method: 'GET',
                success: function (data) {
                    let parsedData = JSON.parse(data.data || '{}');

                    if (parsedData.online_users) {
                        $('.user-list').each(function () {
                            const userElement = $(this);
                            const userId = userElement.data('user-id');

                            if (parsedData.online_users.includes(userId.toString())) {
                                userElement.find('.status-badge')
                                    .text('Online')
                                    .removeClass('bg-secondary')
                                    .addClass('bg-success');
                            } else {
                                userElement.find('.status-badge')
                                    .text('Offline')
                                    .removeClass('bg-success')
                                    .addClass('bg-secondary');
                            }
                        });
                    }
                },
                error: function (error) {
                    console.error('Error fetching online users:', error);
                }
            });
        }

        $(document).ready(function () {
            connectSocketIO();
            getMessages();
        });

        $('#messageInput').on('input', function () {
            clearTimeout(typingTimeout);
            sendTypingStatus(true);

            typingTimeout = setTimeout(() => {
                sendTypingStatus(false);
            }, 1000);
        });

        document.getElementById("messageInput").addEventListener("keydown", function (event) {
            if (event.key === "Enter" && !event.shiftKey) {
                event.preventDefault();
                document.getElementById("messageForm").dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }));
            }
        });
    </script>


</body>

</html>
