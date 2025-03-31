<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon.png">
    <link rel="icon" type="image/png" href="/img/favicon.png">
    <title>
        Argon Dashboard 2 by Creative Tim
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

</head>

<body>


    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">

                    <section>
                        <div class="container-fluid py-3">
                            <div class="row">
                                <h5 class="font-weight-bold mb-3 text-center text-lg-start">Member</h5>
                                @foreach ($users as $v)
                                <div class="col-md-6 col-lg-5 col-xl-4 mb-4 mb-md-0">

                                    <div class="card">
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0" id="member-list">


                                                <li class="p-2 border-bottom">
                                                    <a href="{{url('chat/'.$v->id)}}" class="d-flex justify-content-between">
                                                        <div class="d-flex flex-row align-items-center">
                                                            <img src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/avatar-1.webp"
                                                                alt="avatar"
                                                                class="rounded-circle d-flex align-self-center me-3 shadow-1-strong"
                                                                width="60">
                                                            <div class="">
                                                                <p class="fw-bold mb-0">{{ $v->username }}</p>
                                                                {{-- <p class="small text-muted">Lorem ipsum dolor sit.</p> --}}
                                                            </div>
                                                        </div>
                                                        <div class="pt-1">
                                                            {{-- <p class="small text-muted mb-1">5 mins ago</p> --}}
                                                        </div>
                                                    </a>
                                                </li>

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
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
    <script src="{{ asset('assets/js/argon-dashboard.js') }}"></script>

</body>

</html>

{{-- @extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Chat'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">

                    <section>
                        <div class="container py-5">
                            <div class="row">
                                <h5 class="font-weight-bold mb-3 text-center text-lg-start">Member</h5>
                                @foreach ($users as $v)
                                <div class="col-md-6 col-lg-5 col-xl-4 mb-4 mb-md-0">

                                    <div class="card">
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0" id="member-list">


                                                <li class="p-2 border-bottom">
                                                    <a href="{{url('chat/'.$v->id)}}" class="d-flex justify-content-between">
                                                        <div class="d-flex flex-row align-items-center">
                                                            <img src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/avatar-1.webp"
                                                                alt="avatar"
                                                                class="rounded-circle d-flex align-self-center me-3 shadow-1-strong"
                                                                width="60">
                                                            <div class="">
                                                                <p class="fw-bold mb-0">{{ $v->username }}</p>
                                                                <p class="small text-muted">Lorem ipsum dolor sit.</p>
                                                            </div>
                                                        </div>
                                                        <div class="pt-1">
                                                            <p class="small text-muted mb-1">5 mins ago</p>
                                                        </div>
                                                    </a>
                                                </li>

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection
 --}}
