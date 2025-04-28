@if(empty($penjualan))
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
                Data penjualan tidak ditemukan.
            </div>
        </div>
    </div>
</div>
@else
<form id="formEditPenjualan">
    @csrf
    @method('PUT')
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Penjualan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" class="form-control" name="tanggal" id="tanggal"
                        value="{{ $penjualan->tanggal }}">
                </div>
                <div class="form-group">
                    <label for="total">Total</label>
                    <input type="number" class="form-control" name="total" id="total" value="{{ $penjualan->total }}">
                </div>
                {{-- Tambahkan field lain sesuai kebutuhan --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</form>

<script>
    $('#formEditPenjualan').on('submit', function(e) {
            e.preventDefault();
            var id = '{{ $penjualan->id }}';
            var formData = $(this).serialize();

            $.ajax({
                url: '/penjualan/' + id + '/update_ajax',
                type: 'POST',
                data: formData,
                success: function(response) {
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
                            title: 'Gagal',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal menyimpan perubahan.'
                    });
                }
            });
        });
</script>
@endif