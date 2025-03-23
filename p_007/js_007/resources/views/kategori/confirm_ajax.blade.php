@if(empty($kategori))
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kesalahan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    Data Kategori tidak ditemukan.
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Tampilkan modal konfirmasi hapus -->
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus Kategori</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus kategori <strong>{{ $kategori->kategori_nama }}</strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">Batal</button>
                <button type="button" onclick="deleteKategori('{{ $kategori->kategori_id }}')" class="btn btn-danger">
                    Hapus
                </button>
            </div>
        </div>
    </div>
@endif

<script>
    // Fungsi deleteLevel didefinisikan secara global
    function deleteKategori(id) {
        // Konfirmasi menggunakan SweetAlert
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menghapus kategori ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/kategori/' + id + '/delete_ajax', // Sesuaikan URL jika perlu
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}' // Sertakan token CSRF
                    },
                    success: function (response) {
                        if (response.status) {
                            $('#myModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            });
                            // Reload DataTable jika variabel dataLevel tersedia
                            if (window.dataKategori) {
                                window.dataKategori.ajax.reload();
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: response.message
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menghapus level.'
                        });
                    }
                });
            }
        });
    }
</script>
