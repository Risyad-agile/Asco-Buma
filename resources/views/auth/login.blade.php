@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row no-gutter">
        <!-- The image half -->
        <div class="col-md-7 d-none d-md-flex bg-image">
            <img src="https://bromo.agile.co.id/images/asri_login.png?h=8191a1102043cb38759584ba4e876e12" 
            style="height: 100%; width: 100%;  border-radius: 14px;filter: sepia(25%);border-style: none;box-shadow: 0px 0px 2px 1px;position: relative;"
            class="media-object" > 
        </div>
        <!-- The content half -->
        <div class="col-md-5 bg-light">
            <div class="login d-flex align-items-center py-5">
                <!-- Demo content-->
                <div class="container">
                    <div class="row">
                        <div class="col-lg-10 col-xl-7 mx-auto">
                            <h3 class="display-4">{{ __('Login') }}</h3>
                            <p class="text-muted mb-4">ASRI Middleware Login</p>
                            {{-- <p class="text-muted mb-8">GCP Agung Server</p> --}}
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="form-group mb-3">
                                    <label for="email">{{ __('Email') }}</label>
                                    <input id="email"  name="email" type="text" placeholder="Email" required="" autofocus="" class="form-control rounded-pill border-0 shadow-sm px-4 @error('email') is-invalid @enderror">
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label for="password">{{ __('Password') }}</label>
                                    <input id="password" type="password" class="form-control rounded-pill border-0 shadow-sm px-4 text-primary @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="custom-control custom-checkbox mb-3">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                                {{-- <button type="submit" class="btn btn-primary btn-block text-uppercase mb-2 rounded-pill shadow-sm">Sign in</button> --}}
                                <button type="submit" class="btn btn-success btn-block text-uppercase mb-2 rounded-pill shadow-sm">
                                    {{ __('Login') }}
                                </button>
                                {{-- <div class="text-center d-flex justify-content-between mt-4"><p>Snippet by <a href="https://bootstrapious.com/snippets" class="font-italic text-muted"> 
                                        <u>Boostrapious</u></a></p></div> --}}
                            </form>
                            <div class="row" style="margin-top: 15px;">
                                <div class="col"><span
                                        style="font-size: 10px;margin-top: 15px;">Copyright : Anagile Kharisma Utama @2020</span></div>
                            </div>
                        </div>
                    </div>
                </div><!-- End -->

            </div>
        </div><!-- End -->

    </div>

</div>
<div class="ml-4 text-left text-sm text-gray-500 dark:text-gray-400 sm:text-right sm:ml-0">
    <span style="font-size: 12px; margin-left: 20px;">ASRI Midleware Build on Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</span>
</div>
@endsection
