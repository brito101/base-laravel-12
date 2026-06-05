@extends('adminlte::auth.login')

@section('auth_footer')
    @parent

    @if(config('services.azure.client_id'))
        <div class="my-0 text-center">
            <a href="{{ route('azure.redirect') }}"
               class="btn btn-block btn-outline-secondary"
               style="display:flex;align-items:center;justify-content:center;gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 23 23">
                    <path fill="#f3f3f3" d="M0 0h23v23H0z"/>
                    <path fill="#f35325" d="M1 1h10v10H1z"/>
                    <path fill="#81bc06" d="M12 1h10v10H12z"/>
                    <path fill="#05a6f0" d="M1 12h10v10H1z"/>
                    <path fill="#ffba08" d="M12 12h10v10H12z"/>
                </svg>
                {{ __('Entrar com Microsoft (SSO)') }}
            </a>
        </div>
    @endif
@endsection
