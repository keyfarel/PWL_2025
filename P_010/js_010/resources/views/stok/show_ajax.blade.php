@if(is_null($stok))
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
                Data stok tidak ditemukan.
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
            <h5 class="modal-title">Detail Stok</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <!-- Informasi Stok -->
            <h6>Informasi Stok</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped table-hover table-sm"
                    style="table-layout: fixed; width: 100%;">
                    <colgroup>
                        <col style="width: 30%;">
                        <col style="width: 70%;">
                    </colgroup>
                    <tbody>
                        <tr>
                            <th>ID Stok</th>
                            <td>{{ $stok->stok_id }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Stok</th>
                            <td>{{ $stok->stok_tanggal }}</td>
                        </tr>
                        <tr>
                            <th>Jumlah Stok</th>
                            <td>{{ $stok->stok_jumlah }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Detail Barang -->
            <h6>Detail Barang</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped table-hover table-sm"
                    style="table-layout: fixed; width: 100%;">
                    <colgroup>
                        <col style="width: 30%;">
                        <col style="width: 70%;">
                    </colgroup>
                    <tbody>
                        <tr>
                            <th>Kode Barang</th>
                            <td>{{ $stok->barang ? $stok->barang->barang_kode : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Nama Barang</th>
                            <td>{{ $stok->barang ? $stok->barang->barang_nama : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td>
                                @if($stok->barang && $stok->barang->kategori)
                                {{ $stok->barang->kategori->kategori_nama }}
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Harga Beli</th>
                            <td>@rupiah($stok->barang->harga_beli ?? null)</td>
                        </tr>
                        <tr>
                            <th>Harga Jual</th>
                            <td>@rupiah($stok->barang->harga_jual ?? null)</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Detail Supplier -->
            <h6>Detail Supplier</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped table-hover table-sm"
                    style="table-layout: fixed; width: 100%;">
                    <colgroup>
                        <col style="width: 30%;">
                        <col style="width: 70%;">
                    </colgroup>
                    <tbody>
                        <tr>
                            <th>Kode Supplier</th>
                            <td>{{ $stok->supplier ? $stok->supplier->supplier_kode : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Nama Supplier</th>
                            <td>{{ $stok->supplier ? $stok->supplier->supplier_nama : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Alamat Supplier</th>
                            <td>{{ $stok->supplier ? $stok->supplier->supplier_alamat : '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Detail User yang Mencatat -->
            <h6>Detail User yang Mencatat</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped table-hover table-sm"
                    style="table-layout: fixed; width: 100%;">
                    <colgroup>
                        <col style="width: 30%;">
                        <col style="width: 70%;">
                    </colgroup>
                    <tbody>
                        <tr>
                            <th>Username</th>
                            <td>{{ $stok->user ? $stok->user->username : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Nama User</th>
                            <td>{{ $stok->user ? $stok->user->nama : '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        </div>
    </div>
</div>
@endif