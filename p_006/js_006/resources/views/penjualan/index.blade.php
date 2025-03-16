@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools">
            </div>
        </div>
        <div class="card-body">
            <!-- Pesan Sukses dan Error -->
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- Tabel Data Penjualan -->
            <table class="table table-bordered table-striped table-hover table-sm" id="table_penjualan">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Penjualan Kode</th>
                    <th>Pembeli</th>
                    <th>Tanggal</th>
                    <th>Total Harga</th>
                    <th>User</th>
                    <th>Aksi</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Modal Global untuk AJAX (gunakan id "myModal") -->
    <div id="myModal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <!-- Konten modal akan dimuat secara dinamis melalui AJAX -->
            </div>
        </div>
    </div>
@endsection

@push('css')
@endpush

@push('js')
    <script>
        // Fungsi untuk memuat konten modal via AJAX
        function modalAction(url = '') {
            $('#myModal').load(url, function () {
                $('#myModal').modal('show');
            });
        }

        $(document).ready(function () {
            var dataPenjualan = $('#table_penjualan').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: "{{ route('penjualan.list') }}",
                    type: "POST"
                },
                columns: [
                    {data: "DT_RowIndex", className: "text-center", orderable: false, searchable: false},
                    {data: "penjualan_kode", orderable: true, searchable: true},
                    {data: "pembeli", orderable: true, searchable: true},
                    {data: "penjualan_tanggal", orderable: true, searchable: true},
                    {data: "total_harga", orderable: true, searchable: true},
                    {data: "user_name", orderable: false, searchable: false},
                    {data: "aksi", className: "text-center", orderable: false, searchable: false}
                ]
            });
            window.dataPenjualan = dataPenjualan;
        });
    </script>
@endpush
