@extends('layouts.auth')

@section('content')
<div class="register-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="{{ url('/') }}" class="h1"><b>PWL</b>POS</a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Daftar akun baru</p>
            <form action="{{ route('register.post') }}" method="POST" id="form-register">
                @csrf
                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" value="{{ old('username') }}" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                    <span class="error-text text-danger" id="error-username"></span>
                </div>
                <div class="input-group mb-3">
                    <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" value="{{ old('nama') }}" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-id-card"></span>
                        </div>
                    </div>
                    <span class="error-text text-danger" id="error-nama"></span>
                </div>
                <!-- Password Field dengan Toggle -->
                <div class="input-group mb-3">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                    <div class="input-group-append">
                        <!-- Tombol toggle password -->
                        <div class="input-group-text">
                            <a href="#" class="toggle-password" data-target="#password">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    <span class="error-text text-danger" id="error-password"></span>
                </div>
                <!-- Password Confirmation Field dengan Toggle -->
                <div class="input-group mb-3">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Konfirmasi Password" required>
                    <div class="input-group-append">
                        <!-- Tombol toggle password -->
                        <div class="input-group-text">
                            <a href="#" class="toggle-password" data-target="#password_confirmation">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    <span class="error-text text-danger" id="error-password_confirmation"></span>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Daftar</button>
            </form>
            <!-- Link untuk ke halaman login -->
            <p class="mb-0 mt-3">
                Sudah punya akun?<a href="{{ url('login') }}" class="text-center"> Masuk di sini</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Toggle password show/hide
        $('.toggle-password').on('click', function(e) {
            e.preventDefault();
            var targetInput = $($(this).data('target'));
            var icon = $(this).find('i');
            if (targetInput.attr('type') === 'password') {
                targetInput.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                targetInput.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Validasi form registrasi dengan jQuery Validate
        $("#form-register").validate({
            rules: {
                username: {
                    required: true,
                    minlength: 4,
                    maxlength: 20
                },
                nama: {
                    required: true
                },
                password: {
                    required: true,
                    minlength: 6,
                    maxlength: 20
                },
                password_confirmation: {
                    required: true,
                    equalTo: "#password"
                }
            },
            messages: {
                username: {
                    required: "Username wajib diisi",
                    minlength: "Username minimal 4 karakter",
                    maxlength: "Username maksimal 20 karakter"
                },
                nama: "Nama lengkap wajib diisi",
                password: {
                    required: "Password wajib diisi",
                    minlength: "Password minimal 6 karakter",
                    maxlength: "Password maksimal 20 karakter"
                },
                password_confirmation: {
                    required: "Konfirmasi password wajib diisi",
                    equalTo: "Konfirmasi password tidak cocok"
                }
            },
            onkeyup: function(element) {
                $(element).valid();
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.input-group').append(error);
            },
            highlight: function(element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function(form) {
                // Clear error messages
                $('.error-text').text('');
                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: $(form).serialize(),
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Registrasi Berhasil',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function() {
                                // Redirect atau reload halaman
                                window.location = response.redirect;
                            });
                        } else {
                            // Tampilkan error dari masing-masing field
                            if (response.msgField) {
                                $.each(response.msgField, function(prefix, val) {
                                    $('#error-' + prefix).text(val[0]);
                                });
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Registrasi',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan pada server'
                        });
                    }
                });
                return false;
            }
        });
    });
</script>
@endpush