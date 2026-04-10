@extends('layouts.app', ['title' => 'Profil Admin', 'pageClass' => 'theme-admin'])

@section('content')
    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <div class="row" style="justify-content: space-between; align-items: center;">
            <div>
                <h1 class="title" style="margin-bottom: 6px;">Profil Admin</h1>
                <p class="subtitle" style="margin-bottom: 0;">Kelola foto profil, nomor telepon, dan password akun admin.</p>
            </div>
            <a class="quick-link" href="{{ route('admin.dashboard') }}">Kembali ke Dashboard</a>
        </div>

        <div class="row mt-20">
            <div class="col" style="max-width: 280px;">
                <div class="stat-box" style="text-align: center;">
                    @if ($admin->profile_image_path)
                        <img src="{{ asset($admin->profile_image_path) }}" alt="Foto Profil Admin" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);">
                    @else
                        <div style="width: 150px; height: 150px; border-radius: 50%; margin: 0 auto; background: linear-gradient(135deg, #fdba74, #fb7185); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 42px; font-weight: 700; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);">
                            {{ strtoupper(substr($admin->name, 0, 1)) }}
                        </div>
                    @endif
                    <div style="margin-top: 10px; font-weight: 700;">{{ $admin->name }}</div>
                    <div class="subtitle" style="margin: 4px 0 0;">{{ $admin->email }}</div>
                </div>
            </div>

            <div class="col" style="min-width: 320px;">
                <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="card" style="padding: 16px;">
                    @csrf
                    <h2 class="title" style="font-size: 18px; margin-bottom: 10px;">Ubah Data Profil</h2>

                    <div class="mt-12">
                        <label for="phone_number">Nomor Telepon</label>
                        <input id="phone_number" name="phone_number" type="text" value="{{ old('phone_number', $admin->phone_number) }}" required>
                    </div>

                    <div class="mt-12">
                        <label for="profile_image">Foto Profil</label>
                        <input id="profile_image" name="profile_image" type="file" accept=".jpg,.jpeg,.png,.webp">
                        <small style="display:block; margin-top:6px; color:#6b7280;">Maksimal 2MB (jpg, jpeg, png, webp).</small>
                    </div>

                    @error('phone_number')
                        <div class="alert alert-error mt-12">{{ $message }}</div>
                    @enderror
                    @error('profile_image')
                        <div class="alert alert-error mt-12">{{ $message }}</div>
                    @enderror

                    <div class="mt-20">
                        <button type="submit">Simpan Profil</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('admin.profile.password.update') }}" class="card mt-20" style="padding: 16px;">
                    @csrf
                    <h2 class="title" style="font-size: 18px; margin-bottom: 10px;">Ubah Password</h2>

                    <div class="mt-12">
                        <label for="current_password">Password Saat Ini</label>
                        <input id="current_password" name="current_password" type="password" required>
                    </div>

                    <div class="mt-12">
                        <label for="new_password">Password Baru</label>
                        <input id="new_password" name="new_password" type="password" required>
                    </div>

                    <div class="mt-12">
                        <label for="new_password_confirmation">Konfirmasi Password Baru</label>
                        <input id="new_password_confirmation" name="new_password_confirmation" type="password" required>
                    </div>

                    @error('current_password')
                        <div class="alert alert-error mt-12">{{ $message }}</div>
                    @enderror
                    @error('new_password')
                        <div class="alert alert-error mt-12">{{ $message }}</div>
                    @enderror

                    <div class="mt-20">
                        <button type="submit">Simpan Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
