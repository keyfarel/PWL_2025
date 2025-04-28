<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 Forbidden - {{ config('app.name', 'Aplikasi Anda') }}</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- AdminLTE style (opsional, jika ingin tetap konsisten dengan style AdminLTE) -->
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    <style>
        body {
            background-color: #eef1f5;
            font-family: 'Source Sans Pro', sans-serif;
        }

        .error-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .error-box {
            background: #fff;
            border-radius: 8px;
            max-width: 600px;
            width: 100%;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .error-box .error-code {
            font-size: 80px;
            font-weight: 700;
            color: #f39c12;
            margin: 0;
        }

        .error-box .error-title {
            font-size: 24px;
            margin-top: 0.5rem;
            font-weight: 600;
        }

        .error-box .error-desc {
            font-size: 16px;
            color: #6c757d;
            margin: 1rem 0;
        }

        .btn-dashboard i {
            margin-right: 6px;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-box">
            <h1 class="error-code">403</h1>
            <h3 class="error-title">
                <i class="fas fa-exclamation-triangle text-warning"></i> Forbidden
            </h3>
            <p class="error-desc">
                Anda tidak diizinkan untuk mengakses halaman ini.<br>
                Sementara itu, silakan kembali ke dashboard.
            </p>
            <a href="{{ url('/') }}" class="btn btn-primary btn-dashboard">
                <i class="fas fa-home"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- jQuery (jika diperlukan) -->
    <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 (jika diperlukan) -->
    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE (opsional) -->
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
</body>

</html>