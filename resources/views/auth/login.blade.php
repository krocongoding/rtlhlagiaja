<x-guest-layout>

    <div class="auth-header">
        <div style="width:48px; height:48px; border-radius:12px; background:var(--primary-100); color:var(--primary-700); display:flex; align-items:center; justify-content:center; font-size:22px; margin:0 auto 12px;">
            <i class="fa-solid fa-lock"></i>
        </div>
        <h2>Masuk Petugas</h2>
        <p class="muted" style="font-size:13px; margin-top:4px;">
            Sistem Informasi Geografis Teras Rutilahu Kab. Cirebon
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <label for="email">Alamat Email</label>
            <div style="position:relative;">
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nama@cirebonkab.go.id"
                    style="padding-left:36px;"
                />
                <i class="fa-solid fa-envelope" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--slate-400);"></i>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="form-group" style="margin-top:14px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <label for="password" style="margin:0;">Kata Sandi</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="font-size:12px; color:var(--primary-600); text-decoration:none; font-weight:600;">
                        Lupa Sandi?
                    </a>
                @endif
            </div>

            <div style="position:relative;">
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    style="padding-left:36px;"
                />
                <i class="fa-solid fa-key" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--slate-400);"></i>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-top:16px;">
            <label for="remember_me" style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-weight:500; font-size:13px; margin:0;">
                <input id="remember_me" type="checkbox" name="remember" style="width:auto; margin:0;">
                <span>Ingat Saya</span>
            </label>
        </div>

        <div style="margin-top: 24px;">
            <button type="submit" class="btn primary" style="width:100%; padding:12px; font-size:15px;">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Sistem
            </button>
        </div>
    </form>

    {{-- DEMO CREDENTIALS HINT --}}
    <div class="demo-credentials">
        <h6><i class="fa-solid fa-lightbulb text-amber-500"></i> Akun Pengujian Petugas:</h6>
        <div style="display:flex; flex-direction:column; gap:6px; margin-top:6px;">
            <div><strong>Admin:</strong> <code>admin@cirebon-rtlh.test</code> / <code>password</code></div>
            <div><strong>Surveyor:</strong> <code>surveyor@cirebon-rtlh.test</code> / <code>password</code></div>
        </div>
    </div>

    <div style="margin-top:20px; text-align:center;">
        <a href="{{ route('public.home') }}" style="font-size:13px; color:var(--slate-500); text-decoration:none;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Portal Publik
        </a>
    </div>

</x-guest-layout>
