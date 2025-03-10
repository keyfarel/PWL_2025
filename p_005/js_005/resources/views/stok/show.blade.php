@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools"></div>
        </div>
        <div class="card-body">
            @if(is_null($stok))
                <div class="alert alert-danger alert-dismissible">
                    <h5><i class="icon fas fa-ban"></i> Kesalahan!</h5>
                    Data yang Anda cari tidak ditemukan.
                </div>
            @else
                <!-- Informasi Stok -->
                <h5 class="mt-2">Informasi Stok</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <tr>
                            <th width="200">ID Stok</th>
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
                    </table>
                </div>

                <!-- Detail Barang -->
                <h5 class="mt-4">Detail Barang</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <tr>
                            <th width="200">Kode Barang</th>
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
                            <td>{{ $stok->barang ? $stok->barang->harga_beli : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Harga Jual</th>
                            <td>{{ $stok->barang ? $stok->barang->harga_jual : '-' }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Detail Supplier -->
                <h5 class="mt-4">Detail Supplier</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <tr>
                            <th width="200">Kode Supplier</th>
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
                    </table>
                </div>

                <!-- Detail User yang Mencatat -->
                <h5 class="mt-4">Detail User yang Mencatat</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <tr>
                            <th width="200">Username</th>
                            <td>{{ $stok->user ? $stok->user->username : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Nama User</th>
                            <td>{{ $stok->user ? $stok->user->nama : '-' }}</td>
                        </tr>
                    </table>
                </div>
            @endif

            <a href="{{ url('stok') }}" class="btn btn-sm btn-default mt-2">Kembali</a>
        </div>
    </div>
@endsection

@push('css')
@endpush

@push('js')
@endpush
