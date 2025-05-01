@if(is_null($supplier))
<div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Kesalahan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="alert alert-danger mb-0">
                Data supplier tidak ditemukan.
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
        <div class="modal-header">
            <h5 class="modal-title">Detail Supplier</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-hover table-sm">
                    <tbody>
                        <tr>
                            <th style="width: 35%;">Kode Supplier</th>
                            <td>{{ $supplier->supplier_kode }}</td>
                        </tr>
                        <tr>
                            <th>Nama Supplier</th>
                            <td>{{ $supplier->supplier_nama }}</td>
                        </tr>
                        <tr>
                            <th>Alamat Supplier</th>
                            <td>{{ $supplier->supplier_alamat }}</td>
                        </tr>
                        {{-- Tambahkan kolom lain jika ada --}}
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer d-flex justify-content-end">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        </div>
    </div>
</div>
@endif