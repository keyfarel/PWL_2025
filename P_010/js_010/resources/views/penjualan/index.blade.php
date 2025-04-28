@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools">
                <a href="{{ url('/penjualan/export_excel') }}" class="btn btn-sm btn-primary mt-1">
                    <i class="fa fa-file-excel mr-1"></i>Export Detail Penjualan
                </a>
                <a href="{{ url('/penjualan/export_pdf') }}" class="btn btn-sm btn-warning mt-1">
                    <i class="fa fa-file-pdf mr-1"></i> Export Detail Penjualan
                </a>

                <button onclick="modalAction('{{ url('penjualan/create_ajax') }}')" class="btn btn-sm btn-success mt-1">
                    Tambah Penjualan
                </button>
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

            <!-- Filter -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-group row">
                        <label class="col-1 control-label col-form-label">Filter</label>
                        <div class="col-3">
                            <select class="form-control" id="level_id" name="level_id">
                                <option value="">Semua</option>
                                {{--                            @foreach($levels as $item)--}}
                                {{--                                <option value="{{ $item->level_id }}">{{ $item->level_nama }}</option>--}}
                                {{--                            @endforeach--}}
                            </select>
                            <small class="form-text text-muted">User</small>
                        </div>
                    </div>
                </div>
            </div>

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
