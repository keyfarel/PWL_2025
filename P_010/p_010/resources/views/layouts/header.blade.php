<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/') }}" class="nav-link">Home</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="#" class="nav-link">Contact</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Navbar Search -->
        <li class="nav-item">
            <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                <i class="fas fa-search"></i>
            </a>
            <div class="navbar-search-block">
                <form class="form-inline">
                    <div class="input-group input-group-sm">
                        <input class="form-control form-control-navbar" type="search" placeholder="Search"
                            aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-navbar" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        <!-- Fullscreen Button -->
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>

        <!-- Dropdown Menu untuk Profil dan Logout -->
        <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#" role="button"
                aria-haspopup="true" aria-expanded="false">
                @if(Auth::user()->photo)
                <!-- Jika user punya foto -->
                <img src="{{ Auth::user()->photo }}" class="rounded-circle"
                    alt="User Image" style="width: 35px; height: 35px; object-fit: cover;">
                @else
                <!-- Jika user belum punya foto -->
                <img src="{{ asset('adminlte/dist/img/default_user.webp') }}" class="rounded-circle" alt="profil"
                    style="width: 35px; height: 35px; object-fit: cover;">
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <!-- Menu Profil -->
                <a href="{{ url('user/edit_profile') }}" class="dropdown-item">
                    <i class="fas fa-user mr-2"></i> Profil
                </a>

                <!-- Menu Logout -->
                <a href="#" class="dropdown-item" onclick="logoutConfirm(event)">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>

                <!-- Form Logout (tersembunyi) -->
                <form id="logout-form" action="{{ url('/logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </li>
    </ul>
</nav>

<script>
    function logoutConfirm(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Logout',
            text: 'Apakah Anda yakin ingin logout?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit();
            }
        });
    }
</script>
