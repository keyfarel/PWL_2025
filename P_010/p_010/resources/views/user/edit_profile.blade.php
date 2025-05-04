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

            <!-- Foto Profil -->
            <div class="form-group row">
                <label class="col-12 col-md-2 col-form-label">Foto</label>
                <div class="col-12 col-md-10 text-center text-md-left">
                    <div class="mb-2">
                        @if($user->photo)
                        <img id="photo-preview" src="{{  $user->photo }}"
                            alt="Foto Profil" class="img-fluid rounded-circle border border-primary shadow-sm"
                            style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;">
                        @else
                        <img id="photo-preview" src="{{ asset('adminlte/dist/img/default_user.webp') }}"
                            alt="Foto Profil Default" class="img-fluid rounded-circle border border-secondary shadow-sm"
                            style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;">
                        @endif
                    </div>
                    <input type="file" class="form-control-file" id="photo" name="photo" accept="image/*">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Username -->
            <div class="form-group row">
                <label class="col-12 col-md-2 col-form-label">Username</label>
                <div class="col-12 col-md-10">
                    <input type="text" class="form-control" id="username" name="username"
                        value="{{ old('username', $user->username) }}" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Nama -->
            <div class="form-group row">
                <label class="col-12 col-md-2 col-form-label">Nama</label>
                <div class="col-12 col-md-10">
                    <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama', $user->nama) }}"
                        required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group row">
                <label class="col-12 col-md-2 col-form-label">Password</label>
                <div class="col-12 col-md-10">
                    <input type="password" class="form-control" id="password" name="password">
                    <small class="form-text text-muted">Abaikan jika tidak ingin mengganti password.</small>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Tombol -->
            <div class="form-group row">
                <div class="col-12 col-md-10 offset-md-2">
                    <button type="submit" class="btn btn-primary btn-sm d-block d-md-inline-block">Simpan</button>
                    <a class="btn btn-default btn-sm mt-2 mt-md-0 ml-md-2 d-block d-md-inline-block"
                        href="{{ url('/') }}">Kembali</a>
                </div>
            </div>
        </form>

        <!-- Modal Foto -->
        <div id="photoModal" class="photo-modal">
            <button id="closeModal" type="button" class="close-modal">&times;</button>
            <div class="photo-modal-content">
                <img id="modal-photo" src="#" alt="Modal Preview" />
            </div>
        </div>
        @endif
    </div>
</div>
@endsection


@push('css')
<style>
    .photo-modal {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 9999;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(6px);
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .photo-modal.show {
        opacity: 1;
        visibility: visible;
    }

    .photo-modal-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        overflow: hidden;
        /* penting untuk zoom */
        animation: fadeInImage 0.3s ease;
    }

    .photo-modal-content img {
        width: auto;
        height: auto;
        max-width: 90vw;
        max-height: 80vh;
        border-radius: 8px;
        object-fit: contain;
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.5);
        transition: transform 0.25s ease;
        cursor: zoom-in;
        will-change: transform;
    }


    .close-modal {
        position: absolute;
        top: 8px;
        right: 8px;
        font-size: 24px;
        color: white;
        background: rgba(0, 0, 0, 0.4);
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        line-height: 30px;
        text-align: center;
        cursor: pointer;
        z-index: 1;
    }

    .close-modal:hover {
        background: rgba(0, 0, 0, 0.7);
    }

    .photo-modal-content img.zoomable {
        transition: transform 0.25s ease;
        cursor: zoom-in;
        will-change: transform;
    }

    .photo-modal-content img.zoomed {
        cursor: move;
        transform: scale(2);
    }

    .photo-modal-content .close-modal {
        position: fixed;
        /* fix posisinya ke layar, bukan ke dalam kontainer zoom */
        top: 20px;
        right: 20px;
        z-index: 10000;
    }

    @keyframes fadeInImage {
        from {
            transform: scale(0.95);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    @media (max-width: 600px) {
        .photo-modal-content img {
            max-width: 95vw;
            max-height: 75vh;
        }

        .close-modal {
            width: 28px;
            height: 28px;
            font-size: 20px;
            top: 10px;
            right: 10px;
        }
    }
</style>
@endpush

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const photoPreview = document.getElementById('photo-preview');
        const modal = document.getElementById('photoModal');
        const modalImg = document.getElementById('modal-photo');
        const closeBtn = document.getElementById('closeModal');

        let isZoomed = false;
        let isDragging = false;
        let startX, startY, currentX = 0, currentY = 0;

        // Klik gambar untuk buka modal
        photoPreview.addEventListener('click', function () {
            if (this.src && this.src !== '') {
                modalImg.src = this.src;
                modalImg.classList.remove('zoomed');
                modalImg.classList.add('zoomable');
                modalImg.style.transform = '';
                modal.classList.add('show');
                isZoomed = false;
                currentX = 0;
                currentY = 0;
            }
        });

        // Klik gambar untuk toggle zoom
        modalImg.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!isZoomed) {
                isZoomed = true;
                this.classList.add('zoomed');
                this.style.transform = `scale(2) translate(0px, 0px)`;
                this.style.cursor = 'move';
            } else {
                isZoomed = false;
                currentX = 0;
                currentY = 0;
                this.classList.remove('zoomed');
                this.style.transform = '';
                this.style.cursor = 'zoom-in';
            }
        });

        // Drag saat zoom
        modalImg.addEventListener('mousedown', function (e) {
            if (!isZoomed) return;
            isDragging = true;
            startX = e.pageX - currentX;
            startY = e.pageY - currentY;
            this.style.cursor = 'grabbing';
        });

        document.addEventListener('mousemove', function (e) {
            if (!isDragging) return;
            currentX = e.pageX - startX;
            currentY = e.pageY - startY;
            modalImg.style.transform = `scale(2) translate(${currentX}px, ${currentY}px)`;
        });

        document.addEventListener('mouseup', function () {
            if (isDragging) {
                isDragging = false;
                modalImg.style.cursor = 'move';
            }
        });

        // Klik tombol close
        closeBtn.addEventListener('click', function () {
            modal.classList.remove('show');
            setTimeout(() => {
                modalImg.src = '#';
                isZoomed = false;
                currentX = 0;
                currentY = 0;
                modalImg.style.transform = '';
                modalImg.classList.remove('zoomed');
            }, 300);
        });

        // Klik di luar gambar
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modalImg.src = '#';
                    isZoomed = false;
                    currentX = 0;
                    currentY = 0;
                    modalImg.style.transform = '';
                    modalImg.classList.remove('zoomed');
                }, 300);
            }
        });

        // Preview saat file diubah
        document.getElementById('photo').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    photoPreview.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // AJAX Submit
        $('#updateProfileForm').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $('.invalid-feedback').html('');
            $('.form-control, .form-control-file').removeClass('is-invalid');

            $.ajax({
                url: $(this).attr('action'),
                type: $(this).attr('method'),
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.success || 'Data berhasil diperbarui.'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function (field, messages) {
                            let input = $('[name="' + field + '"]');
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
