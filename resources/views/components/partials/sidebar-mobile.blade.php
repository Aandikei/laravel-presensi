{{-- <div x-show="isSideMenuOpen" x-transition:enter="transition ease-in-out duration-150" x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in-out duration-150"
  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
  class="fixed inset-0 z-10 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"></div>
<aside class="fixed inset-y-0 z-20 flex-shrink-0 w-64 mt-16 overflow-y-auto bg-white dark:bg-gray-800 md:hidden"
  x-show="isSideMenuOpen" x-transition:enter="transition ease-in-out duration-150"
  x-transition:enter-start="opacity-0 transform -translate-x-20" x-transition:enter-end="opacity-100"
  x-transition:leave="transition ease-in-out duration-150" x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0 transform -translate-x-20" @click.away="closeSideMenu"
  @keydown.escape="closeSideMenu">
  <div class="py-4 text-gray-500 dark:text-gray-400">
    <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="#">
      Windmill
    </a>
    <ul class="mt-6">
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
          aria-hidden="true"></span>
        <a class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
          href="index.html">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
            </path>
          </svg>
          <span class="ml-4">Dashboard</span>
        </a>
      </li>
    </ul>
    <ul>
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
          href="forms.html">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
            </path>
          </svg>
          <span class="ml-4">Forms</span>
        </a>
      </li>
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
          href="cards.html">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
            </path>
          </svg>
          <span class="ml-4">Cards</span>
        </a>
      </li>
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
          href="charts.html">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
            <path d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
          </svg>
          <span class="ml-4">Charts</span>
        </a>
      </li>
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
          href="buttons.html">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122">
            </path>
          </svg>
          <span class="ml-4">Buttons</span>
        </a>
      </li>
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
          href="modals.html">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
            </path>
          </svg>
          <span class="ml-4">Modals</span>
        </a>
      </li>
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
          href="tables.html">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
          </svg>
          <span class="ml-4">Tables</span>
        </a>
      </li>
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        <button
          class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
          @click="togglePagesMenu" aria-haspopup="true">
          <span class="inline-flex items-center">
            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
              stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
              <path
                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z">
              </path>
            </svg>
            <span class="ml-4">Pages</span>
          </span>
          <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
              clip-rule="evenodd"></path>
          </svg>
        </button>
        <template x-if="isPagesMenuOpen">
          <ul x-transition:enter="transition-all ease-in-out duration-300"
            x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl"
            x-transition:leave="transition-all ease-in-out duration-300"
            x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0"
            class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50 dark:text-gray-400 dark:bg-gray-900"
            aria-label="submenu">
            <li
              class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
              <a class="w-full" href="pages/login.html">Login</a>
            </li>
            <li
              class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
              <a class="w-full" href="pages/create-account.html">
                Create account
              </a>
            </li>
            <li
              class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
              <a class="w-full" href="pages/forgot-password.html">
                Forgot password
              </a>
            </li>
            <li
              class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
              <a class="w-full" href="pages/404.html">404</a>
            </li>
            <li
              class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
              <a class="w-full" href="pages/blank.html">Blank</a>
            </li>
          </ul>
        </template>
      </li>
    </ul>
    <div class="px-6 my-6">
      <button
        class="flex items-center justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
        Create account
        <span class="ml-2" aria-hidden="true">+</span>
      </button>
    </div>
  </div>
</aside> --}}


<div x-show="isSideMenuOpen" x-transition:enter="transition ease-in-out duration-150" x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in-out duration-150"
  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
  class="fixed inset-0 z-10 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"></div>

<aside class="fixed inset-y-0 z-20 flex-shrink-0 w-64 mt-16 bg-white dark:bg-gray-800 dark:border-r dark:border-gray-700 md:hidden flex flex-col"
  x-show="isSideMenuOpen" x-transition:enter="transition ease-in-out duration-150"
  x-transition:enter-start="opacity-0 transform -translate-x-20" x-transition:enter-end="opacity-100"
  x-transition:leave="transition ease-in-out duration-150" x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0 transform -translate-x-20" @click.away="closeSideMenu"
  @keydown.escape="closeSideMenu">
  <div class="flex-1 overflow-y-auto min-h-0 py-4 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 sidebar-scroll">

    {{-- Logo --}}
    <a class="ml-6 text-lg font-bold text-gray-800 dark:text-white" href="#">
      Boilerplate
    </a>

    {{-- Menu Utama --}}
    <ul class="mt-6">
      {{-- Dashboard --}}
      @php $isDashboard = request()->routeIs('*.dashboard'); @endphp
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg {{ $isDashboard ? '' : '' }}">
        @if ($isDashboard)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
            aria-hidden="true"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isDashboard ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="@if (auth()->user()->hasRole('super_admin')) {{ route('superadmin.dashboard') }}
            @elseif(auth()->user()->hasRole('admin')) {{ route('admin.dashboard') }}
            @elseif(auth()->user()->hasRole('wali_kelas')) {{ route('guru.wali-kelas.dashboard') }}
            @elseif(auth()->user()->hasRole('guru')) {{ route('guru.dashboard') }}
            @elseif(auth()->user()->hasRole('siswa')) {{ route('siswa.dashboard') }}
            @elseif(auth()->user()->hasRole('orang_tua')) {{ route('orangtua.dashboard') }}
            @else {{ route('user.dashboard') }} @endif">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
            </path>
          </svg>
          <span class="ml-4">Dashboard</span>
        </a>
      </li>


    </ul>

    {{--
      ===================================================
      CATATAN: TAMBAH MENU SESUAI PROJECT
      ===================================================
      Contoh tambah menu baru:
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        <a class="inline-flex items-center w-full text-sm font-semibold..."
          href="{{ route('nama.route') }}">
          <svg ...></svg>
          <span class="ml-4">Nama Menu</span>
        </a>
      </li>

      Untuk menu khusus role tertentu:
      @role('admin')
        <li>...</li>
      @endrole
      ===================================================
    --}}

    {{-- Super Admin Only --}}
    @role('super_admin')
      <div class="mt-4" x-data="{ open: sidebarState('sidebar-superadmin', @json(request()->routeIs('superadmin.*'))) }">
        <button @click="open = !open; localStorage.setItem('sidebar-superadmin', open)" class="flex items-center justify-between w-full px-6 py-2 text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300">
          <span>Super Admin</span>
          <svg class="w-3 h-3 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>
        <ul x-show="open" x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden">
          {{-- Dashboard --}}
        <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
          @php $isDashboard = request()->routeIs('superadmin.dashboard'); @endphp
          @if ($isDashboard)
            <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
          @endif
          <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isDashboard ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
            href="{{ route('superadmin.dashboard') }}">
            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span class="ml-4">Dashboard</span>
          </a>
        </li>
        {{-- Sekolah --}}
        <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
          @php $isSekolah = request()->routeIs('superadmin.sekolah.*'); @endphp
          @if ($isSekolah)
            <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
          @endif
          <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isSekolah ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
            href="{{ route('superadmin.dashboard') }}">
            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <span class="ml-4">Kelola Sekolah</span>
          </a>
        </li>
        {{-- Hari Libur --}}
        <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
          @php $isLibur = request()->routeIs('superadmin.hari-libur.*'); @endphp
          @if ($isLibur)
            <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
          @endif
          <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isLibur ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
            href="{{ route('superadmin.hari-libur.index') }}">
            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span class="ml-4">Libur Nasional</span>
          </a>
        </li>
        {{-- Roles --}}
        <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
          @php $isRoles = request()->routeIs('superadmin.roles.*'); @endphp
          @if ($isRoles)
            <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
          @endif
          <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isRoles ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
            href="{{ route('superadmin.roles.index') }}">
            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <span class="ml-4">Manage Roles</span>
          </a>
        </li>
        </ul>
      </div>
    @endrole

    {{-- Admin Only --}}
    @role('admin')
    {{-- Master Data --}}
    <div class="mt-4" x-data="{ open: sidebarState('sidebar-masterdata', @json(request()->routeIs('admin.tahun-ajaran.*') || request()->routeIs('admin.guru.*') || request()->routeIs('admin.siswa.*') || request()->routeIs('admin.kelas.*') || request()->routeIs('admin.naik-kelas.*') || request()->routeIs('admin.mata-pelajaran.*') || request()->routeIs('admin.kurikulum.*'))) }">
      <button @click="open = !open; localStorage.setItem('sidebar-masterdata', open)" class="flex items-center justify-between w-full px-6 py-2 text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300">
        <span>Master Data</span>
        <svg class="w-3 h-3 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>
      <ul x-show="open" x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden">

      {{-- Tahun Ajaran --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isTahunAjaran = request()->routeIs('admin.tahun-ajaran.*'); @endphp
        @if ($isTahunAjaran)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isTahunAjaran ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.tahun-ajaran.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
            </path>
          </svg>
          <span class="ml-4">Tahun Ajaran</span>
        </a>
      </li>

      {{-- Guru --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isGuru = request()->routeIs('admin.guru.*'); @endphp
        @if ($isGuru)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isGuru ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.guru.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
            </path>
          </svg>
          <span class="ml-4">Guru</span>
        </a>
      </li>

      {{-- Siswa --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isSiswa = request()->routeIs('admin.siswa.*'); @endphp
        @if ($isSiswa)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isSiswa ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.siswa.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
            </path>
          </svg>
          <span class="ml-4">Siswa</span>
        </a>
      </li>

      {{-- Kelas --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isKelas = request()->routeIs('admin.kelas.*'); @endphp
        @if ($isKelas)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isKelas ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.kelas.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
            </path>
          </svg>
          <span class="ml-4">Kelas</span>
        </a>
      </li>

      {{-- Naik Kelas --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isNaikKelas = request()->routeIs('admin.naik-kelas.*'); @endphp
        @if ($isNaikKelas)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isNaikKelas ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.naik-kelas.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z"></path>
          </svg>
          <span class="ml-4">Naik Kelas</span>
        </a>
      </li>

      {{-- Mata Pelajaran --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isMapel = request()->routeIs('admin.mata-pelajaran.*'); @endphp
        @if ($isMapel)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isMapel ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.mata-pelajaran.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
            </path>
          </svg>
          <span class="ml-4">Mata Pelajaran</span>
        </a>
      </li>

      {{-- Kurikulum --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isKurikulum = request()->routeIs('admin.kurikulum.*'); @endphp
        @if ($isKurikulum)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isKurikulum ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.kurikulum.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
            </path>
          </svg>
          <span class="ml-4">Kurikulum</span>
        </a>
      </li>
      </ul>
    </div>

    {{-- Akademik --}}
    <div class="mt-4" x-data="{ open: sidebarState('sidebar-akademik', @json(request()->routeIs('admin.jadwal.*') || request()->routeIs('admin.registrasi.*') || request()->routeIs('admin.hari-libur.*'))) }">
      <button @click="open = !open; localStorage.setItem('sidebar-akademik', open)" class="flex items-center justify-between w-full px-6 py-2 text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300">
        <span>Akademik</span>
        <svg class="w-3 h-3 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>
      <ul x-show="open" x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden">

      {{-- Jadwal --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isJadwal = request()->routeIs('admin.jadwal.*'); @endphp
        @if ($isJadwal)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isJadwal ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.jadwal.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
            </path>
          </svg>
          <span class="ml-4">Jadwal</span>
        </a>
      </li>

      {{-- Registrasi --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isRegistrasi = request()->routeIs('admin.registrasi.*'); @endphp
        @if ($isRegistrasi)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isRegistrasi ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.registrasi.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
            </path>
          </svg>
          <span class="ml-4">Registrasi</span>
        </a>
      </li>

      {{-- Hari Libur --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isHariLibur = request()->routeIs('admin.hari-libur.*'); @endphp
        @if ($isHariLibur)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isHariLibur ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.hari-libur.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
            </path>
          </svg>
          <span class="ml-4">Hari Libur</span>
        </a>
      </li>
      </ul>
    </div>

    {{-- Pelanggaran --}}
    <div class="mt-4" x-data="{ open: sidebarState('sidebar-pelanggaran', @json(request()->routeIs('admin.master-poin.*') || request()->routeIs('admin.log-poin.*'))) }">
      <button @click="open = !open; localStorage.setItem('sidebar-pelanggaran', open)" class="flex items-center justify-between w-full px-6 py-2 text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300">
        <span>Pelanggaran</span>
        <svg class="w-3 h-3 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>
      <ul x-show="open" x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden">

      {{-- Master Poin --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isMasterPoin = request()->routeIs('admin.master-poin.*'); @endphp
        @if ($isMasterPoin)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isMasterPoin ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.master-poin.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
            </path>
          </svg>
          <span class="ml-4">Master Poin</span>
        </a>
      </li>

      {{-- Log Poin --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isLogPoin = request()->routeIs('admin.log-poin.*'); @endphp
        @if ($isLogPoin)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isLogPoin ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.log-poin.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
            </path>
          </svg>
          <span class="ml-4">Log Poin</span>
        </a>
      </li>
      </ul>
    </div>

    {{-- Monitoring --}}
    <div class="mt-4" x-data="{ open: sidebarState('sidebar-monitoring', @json(request()->routeIs('admin.absensi.*') || request()->routeIs('admin.laporan.*'))) }">
      <button @click="open = !open; localStorage.setItem('sidebar-monitoring', open)" class="flex items-center justify-between w-full px-6 py-2 text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300">
        <span>Monitoring</span>
        <svg class="w-3 h-3 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>
      <ul x-show="open" x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden">

      {{-- Monitor Absensi --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isAbsensiAdmin = request()->routeIs('admin.absensi.*'); @endphp
        @if ($isAbsensiAdmin)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isAbsensiAdmin ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.absensi.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
            </path>
          </svg>
          <span class="ml-4">Monitor Absensi</span>
        </a>
      </li>

      {{-- Laporan --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isLaporan = request()->routeIs('admin.laporan.*'); @endphp
        @if ($isLaporan)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isLaporan ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.laporan.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
            </path>
          </svg>
          <span class="ml-4">Laporan</span>
        </a>
      </li>
      </ul>
    </div>

    {{-- Pengaturan --}}
    <div class="mt-4" x-data="{ open: sidebarState('sidebar-pengaturan', @json(request()->routeIs('admin.settings.*'))) }">
      <button @click="open = !open; localStorage.setItem('sidebar-pengaturan', open)" class="flex items-center justify-between w-full px-6 py-2 text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300">
        <span>Pengaturan</span>
        <svg class="w-3 h-3 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>
      <ul x-show="open" x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden">

      {{-- Pengaturan --}}
      <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
        @php $isSettings = request()->routeIs('admin.settings.*'); @endphp
        @if ($isSettings)
          <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
        @endif
        <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isSettings ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
          href="{{ route('admin.settings.index') }}">
          <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
            <path
              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
            </path>
            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
          <span class="ml-4">Pengaturan</span>
        </a>
      </li>
      </ul>
    </div>
    @endrole

    {{-- Wali Kelas --}}
    @if(auth()->user()->hasRole('wali_kelas') || auth()->user()->guru?->isWaliKelas())
      <div class="mt-4" x-data="{ open: sidebarState('sidebar-walikelas', @json(request()->routeIs('guru.wali-kelas.*'))) }">
        <button @click="open = !open; localStorage.setItem('sidebar-walikelas', open)" class="flex items-center justify-between w-full px-6 py-2 text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300">
          <span>Wali Kelas</span>
          <svg class="w-3 h-3 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>
        <ul x-show="open" x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden">
        <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
          @php $isWaliDash = request()->routeIs('guru.wali-kelas.dashboard'); @endphp
          @if ($isWaliDash)
            <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
          @endif
          <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isWaliDash ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
            href="{{ route('guru.wali-kelas.dashboard') }}">
            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span class="ml-4">Dashboard Kelas</span>
          </a>
        </li>
        <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
          @php $isPoinSiswa = request()->routeIs('guru.wali-kelas.siswa-poin'); @endphp
          @if ($isPoinSiswa)
            <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
          @endif
          <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isPoinSiswa ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
            href="{{ route('guru.wali-kelas.siswa-poin') }}">
            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <span class="ml-4">Poin Siswa</span>
          </a>
        </li>
        <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
          @php $isLogPoin = request()->routeIs('guru.wali-kelas.log-poin'); @endphp
          @if ($isLogPoin)
            <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
          @endif
          <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isLogPoin ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
            href="{{ route('guru.wali-kelas.log-poin') }}">
            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
            <span class="ml-4">Log Poin</span>
          </a>
        </li>
        </ul>
      </div>
    @endif

    {{-- Guru & Wali Kelas --}}
    @role('guru|wali_kelas')
      <div class="mt-4" x-data="{ open: sidebarState('sidebar-mengajar', @json(request()->routeIs('guru.absensi.*'))) }">
        <button @click="open = !open; localStorage.setItem('sidebar-mengajar', open)" class="flex items-center justify-between w-full px-6 py-2 text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300">
          <span>Mengajar</span>
          <svg class="w-3 h-3 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>
        <ul x-show="open" x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden">

        <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
          @php $isInputAbsen = request()->routeIs('guru.absensi.index'); @endphp
          @if ($isInputAbsen)
            <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
          @endif
          <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isInputAbsen ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
            href="{{ route('guru.absensi.index') }}">
            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
              stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
              <path
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
              </path>
            </svg>
            <span class="ml-4">Input Absensi</span>
          </a>
        </li>
        <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
          @php $isRekapAbsen = request()->routeIs('guru.absensi.rekap*'); @endphp
          @if ($isRekapAbsen)
            <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
          @endif
          <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isRekapAbsen ? 'text-gray-800 dark:text-gray-100 ' : '' }}"
            href="{{ route('guru.absensi.rekap') }}">
            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
              stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="ml-4">Rekap Absensi</span>
          </a>
        </li>
        </ul>
      </div>
    @endrole

    {{-- Siswa --}}
    @role('siswa')
      <div class="mt-4" x-data="{ open: sidebarState('sidebar-siswa', @json(request()->routeIs('siswa.*'))) }">
        <button @click="open = !open; localStorage.setItem('sidebar-siswa', open)" class="flex items-center justify-between w-full px-6 py-2 text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300">
          <span>Menu</span>
          <svg class="w-3 h-3 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>
        <ul x-show="open" x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden">
        @foreach ([['route' => 'siswa.absensi', 'label' => 'Riwayat Absensi', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'], ['route' => 'siswa.poin', 'label' => 'Riwayat Poin', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z']] as $item)
          <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
            @php $isActive = request()->routeIs($item['route']); @endphp
            @if ($isActive)
              <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
            @endif
            <a href="{{ route($item['route']) }}"
              class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isActive ? 'text-gray-800 dark:text-gray-100 ' : '' }}">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-linecap="round"
                stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                <path d="{{ $item['icon'] }}" />
              </svg>
              <span class="ml-4">{{ $item['label'] }}</span>
            </a>
          </li>
        @endforeach
        </ul>
      </div>
    @endrole

    {{-- Orang Tua --}}
    @role('orang_tua')
      <div class="mt-4" x-data="{ open: sidebarState('sidebar-orangtua', @json(request()->routeIs('orangtua.*'))) }">
        <button @click="open = !open; localStorage.setItem('sidebar-orangtua', open)" class="flex items-center justify-between w-full px-6 py-2 text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-300">
          <span>Menu</span>
          <svg class="w-3 h-3 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>
        <ul x-show="open" x-transition:enter="transition-all ease-in-out duration-300" x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl" x-transition:leave="transition-all ease-in-out duration-300" x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden">
        @foreach ([['route' => 'orangtua.absensi', 'label' => 'Absensi Anak', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'], ['route' => 'orangtua.poin', 'label' => 'Poin Anak', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z']] as $item)
          <li class="relative px-6 py-3 dark:hover:bg-gray-700/30 transition-colors duration-150 rounded-lg">
            @php $isActive = request()->routeIs($item['route']); @endphp
            @if ($isActive)
              <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"></span>
            @endif
            <a href="{{ route($item['route']) }}"
              class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ $isActive ? 'text-gray-800 dark:text-gray-100 ' : '' }}">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-linecap="round"
                stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                <path d="{{ $item['icon'] }}" />
              </svg>
              <span class="ml-4">{{ $item['label'] }}</span>
            </a>
          </li>
        @endforeach
        </ul>
      </div>
    @endrole
  </div>

</aside>
