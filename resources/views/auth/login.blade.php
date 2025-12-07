<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="auth-session-status" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <!-- Email Address -->
        <div class="auth-form-group">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="auth-error-message" />
        </div>

        <!-- Password -->
        <div class="auth-form-group">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="auth-error-message" />
        </div>

        <!-- Remember Me -->
        <div class="auth-remember">
            <label for="remember_me" class="auth-checkbox-label">
                <input id="remember_me" type="checkbox" class="auth-checkbox" name="remember">
                <span>{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="auth-actions">
            @if (Route::has('password.request'))
                <a class="auth-link" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <div class="auth-button-group">
                @if (Route::has('register'))
                    <a class="auth-link" href="{{ route('register') }}">
                        {{ __('Register') }}
                    </a>
                @endif

                <x-primary-button>
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </div>
    </form>
</x-guest-layout>
