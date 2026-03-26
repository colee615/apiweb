@extends('layouts.admin')

@section('content')
<div class="login-wrap">
    <div class="panel login-card">
        <div class="panel-body stack">
            <div>
                <span class="hero-kicker">Acceso privado</span>
                <h1 style="margin:16px 0 8px; font-size:36px;">Studio Admin</h1>
                <p class="muted" style="margin:0; line-height:1.7;">Ingresa al centro de control para administrar diseño, historial editorial, usuarios y contenidos del frontend.</p>
            </div>

            @if ($errors->any())
                <div class="notice notice-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="stack">
                @csrf
                <div class="field">
                    <label>Correo</label>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                </div>
                <div class="field">
                    <label>Contraseña</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="button button-primary">Ingresar al panel</button>
            </form>
        </div>
    </div>
</div>
@endsection
