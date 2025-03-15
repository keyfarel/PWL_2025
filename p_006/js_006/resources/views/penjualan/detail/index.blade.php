@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
        </div>
        <div class="card-body">
            @if (is_null($penjualan))
                <div class="alert alert-danger alert-dismissible">
                    <h5><i class="icon fas fa-ban"></i> Kesalahan!</h5>
                    Data penjualan tidak ditemukan.
                </div>
            @else
                <!-- Informasi Header Penjualan -->
                <h5>Data Penjualan</h5>
                <table class="table table-bordered table-striped table-hover table-sm">
                    <tr>
                        <th>Kode Penjualan</th>
                        <td>{{ $penjualan->penjualan_kode }}</td>
                    </tr>
                    <tr>
                        <th>Pembeli</th>
                        <td>{{ $penjualan->pembeli }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <td>{{ $penjualan->penjualan_tanggal }}</td>
                    </tr>
                    <tr>
                        <th>Total Harga (Header)</th>
                        <td>{{ $penjualan->total_harga }}</td>
                    </tr>
                    <tr>
                        <th>User</th>
                        <td>{{ $penjualan->user ? $penjualan->user->nama : '-' }}</td>
                    </tr>
                </table>

                <hr>

                <!-- Daftar Detail Penjualan (Read-Only) -->
                <h5>Detail Penjualan</h5>
                @if ($penjualan->detail->isEmpty())
                    <div class="alert alert-info">
                        Tidak ada detail penjualan.
                    </div>
                @else
                    @php
                        $grandTotal = 0; // Menampung total seluruh subtotal
                    @endphp

                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Barang</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($penjualan->detail as $index => $detail)
                            @php
                                // Hitung subtotal per baris
                                $subTotal = $detail->harga * $detail->jumlah;
                                // Tambahkan ke grand total
                                $grandTotal += $subTotal;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $detail->barang ? $detail->barang->barang_nama : '-' }}</td>
                                <td>{{ $detail->harga }}</td>
                                <td>{{ $detail->jumlah }}</td>
                                <td>{{ $subTotal }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="4" class="text-right">Grand Total (Hitungan Detail)</th>
                            <th>{{ $grandTotal }}</th>
                        </tr>
                        </tfoot>
                    </table>
                @endif
            @endif

            <a href="{{ url('penjualan') }}" class="btn btn-sm btn-default mt-2">Kembali</a>
        </div>
    </div>
@endsection

@push('css')
@endpush

@push('js')
@endpush
