@extends('layouts.template')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">{{ $page->title }}</h3>
        <div class="card-tools"></div>
    </div>
    <div class="card-body">
        @empty($user)
        <div class="alert alert-danger alert-dismissible">
            <h5><i class="icon fas fa-ban"></i> Kesalahan!</h5>
            Data yang Anda cari tidak ditemukan.
        </div>
        <a href="{{ url('/') }}" class="btn btn-sm btn-default mt-2">Kembali</a>
        @else
        <form id="updateProfileForm" method="POST" action="{{ route('update_profil', $user->user_id) }}"
            class="form-horizontal" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Opsi Upload Foto Profil -->
            <div class="form-group row">
                <label class="col-1 control-label col-form-label">Foto</label>
                <div class="col-11">
                    <div class="mb-2">
                        @if($user->photo)
                        <img id="photo-preview" src="{{ asset('storage/images/profiles/' . $user->photo) }}"
                            alt="Foto Profil" class="rounded-circle"
                            style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                        <img id="photo-preview" src="{{ asset('adminlte/dist/img/default_user.webp') }}"
                            alt="Foto Profil Default" class="rounded-circle"
                            style="width: 100px; height: 100px; object-fit: cover;">
                        @endif
                    </div>
                    <input type="file" class="form-control-file" id="photo" name="photo" accept="image/*">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Username -->
            <div class="form-group row">
                <label class="col-1 control-label col-form-label">Username</label>
                <div class="col-11">
                    <input type="text" class="form-control" id="username" name="username"
                        value="{{ old('username', $user->username) }}" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Nama -->
            <div class="form-group row">
                <label class="col-1 control-label col-form-label">Nama</label>
                <div class="col-11">
                    <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama', $user->nama) }}"
                        required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group row">
                <label class="col-1 control-label col-form-label">Password</label>
                <div class="col-11">
                    <input type="password" class="form-control" id="password" name="password">
                    <small class="form-text text-muted">Abaikan (jangan diisi) jika tidak ingin mengganti password
                        user.</small>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="form-group row">
                <label class="col-1 control-label col-form-label"></label>
                <div class="col-11">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                    <a class="btn btn-sm btn-default ml-1" href="{{ url('user') }}">Kembali</a>
                </div>
            </div>
        </form>

        @endif
    </div>
</div>
@endsection

@push('css')
@endpush

@push('js')
<script>
    // Preview foto ketika file dipilih
    document.getElementById('photo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('photo-preview').setAttribute('src', event.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    // AJAX submission untuk form update profil
    $(document).ready(function(){
        $('#updateProfileForm').on('submit', function(e){
            e.preventDefault(); // cegah submit form default
            let formData = new FormData(this);
            // Bersihkan pesan error sebelumnya
            $('.invalid-feedback').html('');
            $('.form-control, .form-control-file').removeClass('is-invalid');

            $.ajax({
                url: $(this).attr('action'),
                type: $(this).attr('method'),
                data: formData,
                processData: false,
                contentType: false,
                success: function(response){
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.success || 'Data user berhasil diperbarui.'
                    }).then(() => {
                        // Jika perlu, reload halaman atau lakukan aksi lain
                        location.reload();
                    });
                },
                error: function(xhr){
                    if(xhr.status === 422){
                        // Validasi error
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages){
                            let input = $('[name="'+field+'"]');
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').html(messages.join('<br>'));
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan. Silakan coba lagi.'
                        });
                    }
                }
            });
        });
    });
</script>
@endpush