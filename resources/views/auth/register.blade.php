<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Daftar Akun Pengelola 🚀</h2>
        <p class="text-xs text-slate-500 mt-1">Penyiapan awal: Buat akun pengelola untuk mulai mengelola dan memantau tugas di komputer kantor.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Nama Lengkap
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <input id="name" 
                       class="block w-full pl-9 pr-3 py-2.5 text-sm bg-slate-50 focus:bg-white text-slate-900 border {{ $errors->has('name') ? 'border-rose-400 ring-1 ring-rose-400' : 'border-slate-300' }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-xs" 
                       type="text" 
                       name="name" 
                       value="{{ old('name') }}" 
                       required autofocus autocomplete="name" 
                       placeholder="Nama lengkap Anda" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Alamat Email
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                    </svg>
                </div>
                <input id="email" 
                       class="block w-full pl-9 pr-3 py-2.5 text-sm bg-slate-50 focus:bg-white text-slate-900 border {{ $errors->has('email') ? 'border-rose-400 ring-1 ring-rose-400' : 'border-slate-300' }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-xs" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required autocomplete="username" 
                       placeholder="nama@email.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div x-data="{ showPassword: false }">
            <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Kata Sandi
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input id="password" 
                       class="block w-full pl-9 pr-10 py-2.5 text-sm bg-slate-50 focus:bg-white text-slate-900 border {{ $errors->has('password') ? 'border-rose-400 ring-1 ring-rose-400' : 'border-slate-300' }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-xs"
                       :type="showPassword ? 'text' : 'password'"
                       type="password"
                       name="password"
                       required autocomplete="new-password"
                       placeholder="Minimal 8 karakter" />
                <button type="button" 
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 focus:outline-none transition cursor-pointer"
                        :title="showPassword ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'"
                        aria-label="Toggle password visibility">
                    {{-- Eye Icon (Saat tersembunyi) --}}
                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    {{-- Eye Slash Icon (Saat terlihat) --}}
                    <svg x-show="showPassword" x-cloak class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div x-data="{ showConfirmPassword: false }">
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                Konfirmasi Kata Sandi
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <input id="password_confirmation" 
                       class="block w-full pl-9 pr-10 py-2.5 text-sm bg-slate-50 focus:bg-white text-slate-900 border {{ $errors->has('password_confirmation') ? 'border-rose-400 ring-1 ring-rose-400' : 'border-slate-300' }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-xs"
                       :type="showConfirmPassword ? 'text' : 'password'"
                       type="password"
                       name="password_confirmation" 
                       required autocomplete="new-password"
                       placeholder="Ulangi kata sandi" />
                <button type="button" 
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 focus:outline-none transition cursor-pointer"
                        :title="showConfirmPassword ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'"
                        aria-label="Toggle password confirmation visibility">
                    {{-- Eye Icon (Saat tersembunyi) --}}
                    <svg x-show="!showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    {{-- Eye Slash Icon (Saat terlihat) --}}
                    <svg x-show="showConfirmPassword" x-cloak class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full inline-flex justify-center items-center py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 hover:shadow-lg hover:shadow-indigo-200 transition-all duration-150 cursor-pointer">
                <span>Daftar Akun Pengelola</span>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>

        <div class="pt-4 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">
                Sudah ada akun pengelola? 
                <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline transition">
                    Masuk di sini &rarr;
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
