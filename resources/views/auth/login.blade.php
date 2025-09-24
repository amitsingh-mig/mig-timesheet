<x-guest-layout>
    <div class="card auth-card p-4 p-md-4">
        <div class="text-center mb-3">
            <div class="brand-badge mb-2">TS</div>
            <h5 class="mb-0">Welcome back</h5>
            <small class="text-muted">Sign in to continue</small>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-3" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-3">
                <x-input-label for="email" :value="__('Email')" class="form-label" />
                <x-text-input id="email" class="form-control rounded-input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger small" />
            </div>

            <!-- Password -->
            <div class="mb-3">
                <x-input-label for="password" :value="__('Password')" class="form-label" />
                <x-text-input id="password" class="form-control rounded-input"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger small" />
            </div>

            <!-- Remember + Forgot -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                    <label for="remember_me" class="form-check-label">{{ __('Remember me') }}</label>
                </div>
                @if (Route::has('password.request'))
                    <a class="small text-decoration-none" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-rounded">
                {{ __('Log in') }}
            </button>
        </form>
    </div>
</x-guest-layout>
