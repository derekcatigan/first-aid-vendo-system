{{-- resources/views/partials/header.blade.php --}}
<header class="bg-white shadow-sm">
    <nav class="border-b border-gray-300 flex items-center justify-between px-4 py-2">

        {{-- Left: Logo --}}
        <div class="flex items-center gap-3 border border-gray-300 rounded-sm shadow-sm px-3 py-1">
            <img src="{{ asset('assets/images/first-aid.png') }}" alt="Logo" class="w-10 h-10 object-cover">
            <h1 class="text-md font-semibold text-gray-800">First Aid Dispenser</h1>
        </div>

        {{-- CENTER + RIGHT GROUP (Fixes spacing issue) --}}
        <div class="flex items-center gap-4">

            {{-- Desktop Navigation --}}
            <div class="hidden md:flex items-center gap-6 text-gray-700 text-sm">

                <a href="{{ route('dashboard') }}" class="flex items-center gap-1 transition 
                {{ request()->routeIs('dashboard') ? 'text-blue-600 font-semibold' : 'hover:text-blue-600' }}">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>

                @if(auth()->check() && auth()->user()->role === 'admin')
                    <a href="{{ route('user.index') }}"
                        class="flex items-center gap-1 transition 
                                                {{ request()->routeIs('user.index') ? 'text-blue-600 font-semibold' : 'hover:text-blue-600' }}">
                        <i class="fa-solid fa-users"></i> Manage Users
                    </a>
                @endif

                 <a href="{{ route('item.index') }}" class="flex items-center gap-1 transition 
                        {{ request()->routeIs('item.*') ? 'text-blue-600 font-semibold' : 'hover:text-blue-600' }}">
                        <i class="fa-solid fa-boxes-stacked"></i> Manage Inventory
                    </a>
            </div>

            {{-- User Dropdown --}}
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button"
                    class="flex items-center gap-2 border border-gray-300 rounded-sm shadow-sm px-3 py-1 cursor-pointer bg-white">

                    <div
                        class="w-8 h-8 rounded-sm bg-blue-500 text-white flex items-center justify-center font-bold text-sm">
                        @auth
                            {{ collect(explode(' ', Auth::user()->name))->map(fn($n) => $n[0])->join('') }}
                        @endauth
                    </div>

                    <span class="text-sm font-medium text-gray-700 hidden sm:inline">
                        @auth
                            {{ Auth::user()->name }}
                        @endauth
                    </span>

                    <i class="fa-solid fa-chevron-down text-xs text-gray-600"></i>
                </div>

                <ul tabindex="0" class="dropdown-content z-100 menu p-2 shadow bg-base-100 rounded-box w-48">
                    <li class="menu-title"><span>Account</span></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="logout-btn flex items-center gap-2 text-red-600 text-sm">
                                <i class="fa-solid fa-right-from-bracket"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

            {{-- Mobile Menu Toggle --}}
            <button id="mobileMenuBtn" class="md:hidden text-gray-700 text-xl">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

    </nav>

    {{-- Mobile Navigation Menu --}}
    <div id="mobileMenu" class="hidden flex-col gap-3 px-4 py-3 bg-white border-b border-gray-300">

        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-gray-700 text-sm
       {{ request()->routeIs('dashboard') ? 'text-blue-600 font-semibold' : 'hover:text-blue-600' }}">
            <i class="fa-solid fa-gauge"></i>
            Dashboard
        </a>

        @if(auth()->check() && auth()->user()->role === 'admin')
            <a href="{{ route('user.index') }}"
                class="flex items-center gap-2 text-gray-700 text-sm
                                               {{ request()->routeIs('user.index') ? 'text-blue-600 font-semibold' : 'hover:text-blue-600' }}">
                <i class="fa-solid fa-users"></i>
                Manage Users
            </a>
        @endif

        <a href="{{ route('item.index') }}" class="flex items-center gap-2 text-gray-700 text-sm
       {{ request()->routeIs('item.*') ? 'text-blue-600 font-semibold' : 'hover:text-blue-600' }}">
            <i class="fa-solid fa-boxes-stacked"></i>
            Manage Inventory
        </a>
    </div>
</header>