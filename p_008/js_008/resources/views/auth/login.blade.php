@extends('layouts.auth')

@section('content')
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="{{ url('/') }}" class="h1"><b>PWL</b>POS</a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Sign in to start your session</p>
            <form action="{{ url('login') }}" method="POST" id="form-login">
                @csrf
                <div class="input-group mb-3">
                    <input type="text" id="username" name="username" class="form-control" placeholder="Username">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                    <small id="error-username" class="error-text text-danger"></small>
                </div>
                <div class="input-group mb-3">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password">
                    <div class="input-group-append">
                        <!-- Toggle password visibility -->
                        <div class="input-group-text">
                            <a href="#" class="toggle-password" data-target="#password">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    <small id="error-password" class="error-text text-danger"></small>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember Me</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                    </div>
                </div>
            </form>
            <!-- Link to register page -->
            <p class="mb-0 mt-3">
                Belum punya akun?<a href="{{ url('register') }}" class="text-center"> Daftar di sini</a>
            </p>

            <!-- Message for rate limiting -->
            <div id="rate-limit-message" class="text-center mt-3" style="display:none;">
                <p id="rate-limit-text"></p>
                <p id="countdown-timer"></p>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function() {
        // Toggle password visibility
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

        // Validate login form with jQuery Validate
        $("#form-login").validate({
            rules: {
                username: {
                    required: true,
                    minlength: 4,
                    maxlength: 20
                },
                password: {
                    required: true,
                    minlength: 6,
                    maxlength: 20
                }
            },
            submitHandler: function(form) {
                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: $(form).serialize(),
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1000
                            }).then(function() {
                                window.location = response.redirect;
                            });
                        } else {
                            $('.error-text').text('');
                            if (response.msgField) {
                                $.each(response.msgField, function(prefix, val) {
                                    $('#error-' + prefix).text(val[0]);
                                });
                            }

                            // If rate limit is hit
                            if (response.seconds_left) {
                                $('#rate-limit-message').show();
                                $('#rate-limit-text').text('Terlalu banyak percobaan. Coba lagi setelah ' + response.seconds_left + ' detik.');
                                startCountdown(response.seconds_left);
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 429) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Terlalu Banyak Percobaan',
                                text: xhr.responseJSON?.message ?? 'Tunggu beberapa saat sebelum mencoba lagi.'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: 'Gagal menghubungi server'
                            });
                        }
                    }
                });
                return false;
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
            }
        });

        // Function to handle countdown timer
        function startCountdown(seconds) {
            var timer = seconds;
            var countdownDisplay = $('#countdown-timer');
            var countdownInterval = setInterval(function() {
                var minutes = Math.floor(timer / 60);
                var remainingSeconds = timer % 60;
                countdownDisplay.text('Waktu tersisa: ' + minutes + ' menit ' + remainingSeconds + ' detik');
                timer--;
                if (timer < 0) {
                    clearInterval(countdownInterval);
                    countdownDisplay.text('');
                }
            }, 1000);
        }
    });
</script>
@endpush
@endsection