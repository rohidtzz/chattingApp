@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

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
@endsection

