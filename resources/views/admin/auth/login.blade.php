@extends('layouts.admin')

@section('content')
<div class="login-wrap">
    <div class="login-orb login-orb-lg" aria-hidden="true"></div>
    <div class="login-orb login-orb-sm" aria-hidden="true"></div>

    <div class="login-card">
        <div class="login-brand">
            <img src="{{ asset('LOGO 19-2-26 B.png') }}" alt="Logo corporativo">
        </div>

        <h1 class="login-title">Que bueno verte de nuevo</h1>
        <p class="login-subtitle">Inicia sesion para gestionar contenidos.</p>

        <div class="panel login-panel">
            <div class="panel-body stack">
                @if ($errors->any())
                    <div class="notice notice-error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('admin.login.store') }}" class="stack">
                    @csrf
                    <div class="field login-field">
                        <label>Correo corporativo</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="ejemplo@tuempresa.com" required>
                    </div>

                    <div class="field login-field">
                        <label>Contrasena</label>
                        <input type="password" name="password" placeholder="Ingresa tu contrasena" required>
                    </div>

                    <button type="submit" class="button login-button">Ingresar</button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
