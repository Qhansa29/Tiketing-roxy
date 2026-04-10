@extends('layouts.app', ['title' => 'Login Admin', 'pageClass' => 'theme-admin'])

@section('content')
    <div class="card" style="max-width: 520px; margin: 20px auto;">
        <h1 class="title">Login Admin</h1>
        <p class="subtitle">Masuk untuk mengelola antrean pelanggan dengan tampilan dashboard bergaya retail modern.</p>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="mt-12">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>
            </div>
            <div class="mt-12">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
            </div>
            <div class="mt-20">
                <button type="submit">Login</button>
            </div>
        </form>

        <div class="mt-20">
            <a class="quick-link" href="{{ route('queue.create') }}">Kembali ke halaman pelanggan</a>
        </div>
    </div>
@endsection
