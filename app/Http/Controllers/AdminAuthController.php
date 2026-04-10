<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (session()->has('admin_user_id')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = User::query()
            ->where('email', $credentials['email'])
            ->where('role', 'admin')
            ->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            return back()->withErrors([
                'email' => 'Email atau password tidak valid.',
            ])->withInput();
        }

        session()->put('admin_user_id', $admin->id);
        session()->put('admin_user_name', $admin->name);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['admin_user_id', 'admin_user_name']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function showProfile(Request $request): View|RedirectResponse
    {
        $admin = $this->getCurrentAdmin($request);

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        return view('admin.profile', compact('admin'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = $this->getCurrentAdmin($request);

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:25'],
            'profile_image' => ['nullable', 'file', 'max:2048'],
        ]);

        $profileImagePath = $admin->profile_image_path;

        if ($request->hasFile('profile_image')) {
            $uploadedImage = $request->file('profile_image');
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $extension = strtolower((string) $uploadedImage->getClientOriginalExtension());

            if (! in_array($extension, $allowedExtensions, true)) {
                return back()
                    ->withErrors(['profile_image' => 'Format file tidak didukung. Gunakan: jpg, jpeg, png, atau webp.'])
                    ->withInput();
            }

            $uploadDir = public_path('uploads/admin-profiles');

            if (! File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $fileName = 'admin-'.$admin->id.'-'.Str::uuid().'.'.$uploadedImage->getClientOriginalExtension();
            $uploadedImage->move($uploadDir, $fileName);

            if ($profileImagePath && File::exists(public_path($profileImagePath))) {
                File::delete(public_path($profileImagePath));
            }

            $profileImagePath = 'uploads/admin-profiles/'.$fileName;
        }

        $admin->update([
            'phone_number' => $validated['phone_number'],
            'profile_image_path' => $profileImagePath,
        ]);

        return back()->with('success', 'Profil admin berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $admin = $this->getCurrentAdmin($request);

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $admin->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        $admin->update([
            'password' => $validated['new_password'],
        ]);

        return back()->with('success', 'Password admin berhasil diperbarui.');
    }

    private function getCurrentAdmin(Request $request): ?User
    {
        $adminId = $request->session()->get('admin_user_id');

        if (! $adminId) {
            return null;
        }

        return User::query()
            ->where('id', $adminId)
            ->where('role', 'admin')
            ->first();
    }
}
