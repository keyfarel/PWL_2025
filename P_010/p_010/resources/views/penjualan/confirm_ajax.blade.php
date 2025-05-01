@if(is_null($penjualan))
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Kesalahan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="alert alert-danger">
                Data penjualan tidak ditemukan.
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        </div>
    </div>
</div>
@else
<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
        <!-- Header Modal -->
        <div class="modal-header">
            <h5 class="modal-title">Konfirmasi Hapus Penjualan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <!-- Body Modal -->
        <div class="modal-body">
            Apakah Anda yakin ingin menghapus penjualan dengan kode
            <strong>{{ $penjualan->penjualan_kode }}</strong>?
        </div>
        <!-- Footer Modal -->
        <div class="modal-footer">
            <button type="button" class="btn btn-warning" data-dismiss="modal">Batal</button>
            <button type="button" onclick="deletePenjualan('{{ $penjualan->penjualan_id }}')"
                class="btn btn-danger">Hapus
            </button>
        </div>
    </div>
</div>

<script>
    function deletePenjualan(id) {
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah Anda yakin ingin menghapus penjualan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/penjualan/' + id + '/delete_ajax', // Pastikan route DELETE /penjualan/{id}/delete_ajax sudah didefinisikan
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (response.status) {
                                $('#myModal').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message
                                });
                                if (window.dataPenjualan) {
                                    window.dataPenjualan.ajax.reload();
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
                                text: 'Gagal menghapus penjualan.'
                            });
                        }
                    });
                }
            });
        }
</script>
@endif