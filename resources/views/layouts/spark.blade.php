<!DOCTYPE html>
<html lang="id">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('page-title', 'Dashboard') - Sistem PPKS</title>

  <!-- Favicon -->
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
  <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon-192.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

  <!-- Local Third-Party Libraries (100% Offline Compatible) -->
  <link rel="stylesheet" href="{{ asset('spark-admin/assets/libs/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('spark-admin/assets/libs/bootstrap-icons/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('spark-admin/assets/libs/apexcharts/apexcharts.css') }}">
  <link rel="stylesheet" href="{{ asset('spark-admin/assets/libs/flatpickr/flatpickr.min.css') }}">

  <!-- Main Design System & Custom Stylesheet -->
  <link rel="stylesheet" href="{{ asset('spark-admin/assets/css/main.css') }}">

  <style>
  .navbar-notif-badge {
    position: absolute;
    top: -4px;
    right: -6px;
    min-width: 15px;
    height: 15px;
    padding: 0 3px;
    font-size: 9px;
    line-height: 15px;
    font-weight: 600;
    text-align: center;
    color: #fff;
    background-color: #dc3545;
    border-radius: 50rem;
    border: 1.5px solid #fff;
  }
</style>

<style>
    .sidebar-brand-logo {
        height: 32px;
        width: auto;
        transition: transform 0.3s ease;
    }

    .profile-placeholder {
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 50%;
        background-color: #dfe5e7;
        color: #869398;
    }

    .sidebar-profile-img {
        width: 42px;
        height: 42px;
        font-size: 1.8rem;
    }

    .navbar-profile-img {
        width: 38px;
        height: 38px;
        font-size: 1.65rem;
    }

    .dropdown-menu-profile .dropdown-header {
    text-transform: none;
    letter-spacing: normal;
}

    .sidebar-brand:hover .sidebar-brand-logo {
        transform: scale(1.12);
    }
</style>
  {{-- Tempat untuk CSS tambahan khusus per halaman, kalau dibutuhkan --}}
  @stack('styles')
</head>

<body>

<body class="@yield('body-class', 'bg-foto')">

  <style>
  /* Lapisan foto: menempel di layar dan berada di belakang semua konten */
  body.bg-foto::before {
    content: "";
    position: fixed;       /* tidak ikut ter-scroll */
    inset: 0;              /* menutupi seluruh layar */
    z-index: -1;           /* berada di belakang konten */
    background-image:
      linear-gradient(rgba(244, 246, 245, 0.55), rgba(244, 246, 245, 0.55)), /* lapisan terang */
      url('{{ asset('images/dashboard-bg.jpg') }}');                         /* fotonya */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
  }

  /* Panel tipis agar judul halaman dan footer tetap terbaca di atas foto */
  body.bg-foto .page-header,
  body.bg-foto .footer-custom {
    background: rgba(255, 255, 255, 0.78);
    -webkit-backdrop-filter: blur(8px);
    backdrop-filter: blur(8px);
    border-radius: 1rem;
    padding: 1rem 1.5rem;
  }
  body.bg-foto .footer-custom { border-top: 0; }
</style>

  <!-- ==========================================
         Sidebar Component
         ========================================== -->
  <div class="sidebar-wrapper" id="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
      <img src="{{ asset('images/favicon-tuban.png') }}" alt="Lambang Kabupaten Tuban" class="sidebar-brand-logo">
      <span class="sidebar-brand-copy">
        <span>Sistem PPKS</span>
        <small>LAYANAN SOSIAL</small>
      </span>
    </a>

    <div class="flex-grow-1 overflow-y-auto">
      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">Navigasi utama</div>
        <ul class="sidebar-menu-list">
          <li class="sidebar-menu-item">
            <a href="{{ route('dashboard') }}"
   class="sidebar-menu-link {{ request()->routeIs(['dashboard', 'statistik.index']) ? 'active' : '' }}">
    <i class="bi bi-grid-fill"></i>
    <span>Dashboard Statistik</span>
</a>
          </li>

          @hasanyrole('admin|super-admin')
          <li class="sidebar-menu-item">
            <a href="{{ route('clients.index') }}"
               class="sidebar-menu-link {{ request()->routeIs('clients.index', 'clients.show') ? 'active' : '' }}">
              <i class="bi bi-people"></i>
              <span>Data Klien</span>
            </a>
          </li>
          @endhasanyrole

          @hasanyrole('admin|super-admin')
          <li class="sidebar-menu-item">
            <a href="{{ route('clients.create') }}"
               class="sidebar-menu-link {{ request()->routeIs('clients.create') ? 'active' : '' }}">
              <i class="bi bi-person-plus"></i>
              <span>Registrasi Klien</span>
            </a>
          </li>
          @endhasanyrole

          @hasanyrole('admin|super-admin')
          <li class="sidebar-menu-item">
            <a href="{{ route('assessment.index') }}"
               class="sidebar-menu-link {{ request()->routeIs('assessment.*') ? 'active' : '' }}">
              <i class="bi bi-clipboard2-pulse"></i>
              <span>Assessment</span>
              @php($pendingAssessmentCount = \App\Models\Client::where('status', \App\Models\Client::STATUS_DRAFT)->count())
              @if ($pendingAssessmentCount > 0)
                <span class="sidebar-menu-badge">{{ $pendingAssessmentCount }}</span>
              @endif
            </a>
          </li>
          @endhasanyrole

          @hasanyrole('admin|super-admin')
          <li class="sidebar-menu-item">
            <a href="{{ route('penanganan.dashboard') }}"
               class="sidebar-menu-link {{ request()->routeIs('penanganan.*') ? 'active' : '' }}">
              <i class="bi bi-journal-text"></i>
              <span>Penanganan</span>
            </a>
          </li>
          <li class="sidebar-menu-item">
            <a href="{{ route('exit-documents.mine') }}"
               class="sidebar-menu-link {{ request()->routeIs('exit-documents.mine', 'exit-documents.edit', 'exit-documents.resubmit') ? 'active' : '' }}">
              <i class="bi bi-file-earmark-check"></i>
              <span>Surat Saya</span>
            </a>
          </li>
          @endhasanyrole

          @role('super-admin')
          <li class="sidebar-menu-item">
            <a href="{{ route('users.index') }}"
               class="sidebar-menu-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
              <i class="bi bi-person-gear"></i>
              <span>Manajemen Pengguna</span>
            </a>
          </li>
          @endrole

        </ul>
      </div>
    </div>

    <!-- Sidebar Profile Card -->
    <div class="sidebar-profile">
      <span class="sidebar-profile-img profile-placeholder" aria-hidden="true"><i class="bi bi-person-fill"></i></span>
      <div class="sidebar-profile-info">
        <div class="sidebar-profile-name">{{ auth()->user()->name }}</div>
        <div class="sidebar-profile-email">{{ auth()->user()->email }}</div>
      </div>
    </div>
  </div>
  <!-- END: Sidebar Component -->


  <!-- ==========================================
         Main Content Area
         ========================================== -->
  <div class="main-wrapper">

    <!-- Top Navbar Component -->
    <header class="navbar-custom">
      <div class="navbar-left">
        <button class="btn-desktop-toggle d-none d-xl-flex align-items-center justify-content-center me-3"
          id="desktop-sidebar-toggle" aria-label="Minimize Sidebar">
          <i class="bi bi-chevron-bar-left"></i>
        </button>
        <button class="sidebar-toggle-btn me-2" id="sidebar-toggle" aria-label="Toggle Navigation">
          <i class="bi bi-list"></i>
        </button>
      </div>

      <div class="navbar-search-wrapper">
        <form action="{{ route('clients.index') }}" method="GET" class="d-flex w-100">
          <input type="text" name="search" class="navbar-search-input" placeholder="Cari nama klien..."
                 value="{{ request('search') }}" id="main-search">
          <button type="submit" class="navbar-search-btn" aria-label="Cari">
            <i class="bi bi-search"></i>
          </button>
        </form>
      </div>

      <div class="navbar-actions">
        <div class="dropdown">
    <button class="navbar-action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
      aria-expanded="false" id="btn-notifications" data-bs-auto-close="outside">
      <i class="bi bi-bell"></i>
      @if (auth()->user()->unreadNotifications->count() > 0)
        <span class="navbar-notif-badge">{{ auth()->user()->unreadNotifications->count() }}</span>
      @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end dropdown-menu-notification p-0"
      aria-labelledby="btn-notifications">
      <div class="notification-header">
        <h6 class="notification-title">Notifikasi</h6>
        @if (auth()->user()->unreadNotifications->count() > 0)
          <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="btn-clear-all" type="submit">Tandai semua dibaca</button>
          </form>
        @endif
      </div>
      <div class="notification-list">
        @forelse (auth()->user()->notifications->take(6) as $notif)
          <a href="{{ route('notifications.click', $notif->id) }}" class="notification-item">
            <div class="notification-icon bg-primary text-white">
              <i class="bi bi-person-lines-fill"></i>
            </div>
            <div class="notification-content">
              <p class="notification-text">{{ $notif->data['message'] }}</p>
              <span class="notification-time">{{ $notif->created_at->diffForHumans() }}</span>
            </div>
            @if (is_null($notif->read_at))
              <span class="notification-unread-dot"></span>
            @endif
          </a>
        @empty
          <div class="p-3 text-center text-muted small">Belum ada notifikasi.</div>
        @endforelse
      </div>
    </div>
  </div>
        <!-- Profile Dropdown -->
        <div class="dropdown ms-2">
          <button class="navbar-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
            aria-expanded="false" id="profile-dropdown">
            <span class="navbar-profile-img profile-placeholder" aria-hidden="true"><i class="bi bi-person-fill"></i></span>
            <span class="navbar-profile-name d-none d-md-inline">{{ auth()->user()->name }}</span>
            <i class="bi bi-chevron-down navbar-profile-caret"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile" aria-labelledby="profile-dropdown">
            <li class="dropdown-header">Selamat datang, {{ auth()->user()->name }}</li>
            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Akun Saya</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item text-danger">
                  <i class="bi bi-box-arrow-right"></i> Keluar
                </button>
              </form>
            </li>
          </ul>
        </div>
      </div>
    </header>
    <!-- END: Top Navbar Component -->

    <!-- Page Header Banner -->
    <div class="page-header">
      <div>
        <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
        <p class="page-subtitle">@yield('page-subtitle', '')</p>
      </div>
    </div>
      

    {{-- Notifikasi sukses/gagal, tampil di semua halaman yang pakai layout ini --}}
    @if (session('success'))
      <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    
    @if (session('error'))
  <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif


    @if ($errors->any())
      <div class="alert alert-danger mb-4">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Konten setiap halaman ditaruh di sini --}}
    @yield('content')

    <!-- Footer Component -->
    <footer class="footer-custom">
      <div class="footer-left">
        <span class="footer-logo">
          <img src="{{ asset('images/favicon-tuban.png') }}" alt="" style="height: 18px; width: auto; margin-right: 6px; vertical-align: -3px;">
          Sistem PPKS — Dinas Sosial Kabupaten Tuban
        </span>
        <span class="text-muted small ms-2">&copy; {{ date('Y') }} Dinas Kominfo Kabupaten Tuban</span>
      </div>
    </footer>
    <!-- END: Footer Component -->

  </div>
  <!-- END: Main Content Area -->

  <!-- Local Third-Party Libraries Script dependencies -->
  <script src="{{ asset('spark-admin/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('spark-admin/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
  <script src="{{ asset('spark-admin/assets/libs/flatpickr/flatpickr.min.js') }}"></script>
  <script src="{{ asset('spark-admin/assets/js/dashboard.js') }}"></script>

  {{-- Tempat untuk JS tambahan khusus per halaman --}}
  @stack('scripts')
</body>

</html>