@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools">
                <!-- Tombol untuk membuka form create stok via AJAX -->
                <button onclick="modalAction('{{ url('stok/create_ajax') }}')" class="btn btn-sm btn-success mt-1">
                    Tambah Ajax
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
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row">
                        <label class="col-1 control-label col-form-label">Filter</label>
                        <div class="col-3">
                            <select class="form-control" id="barang_id" name="barang_id" required>
                                <option value="">Semua</option>
                                @foreach($barang as $item)
                                    <option value="{{ $item->barang_id }}">{{ $item->barang_nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Stok Barang</small>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tabel Data Stok -->
            <table class="table table-bordered table-striped table-hover table-sm" id="table_stok">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>ID Barang</th>
                    <th>Nama Barang</th>
                    <th>Jumlah Stok</th>
                    <th>Tanggal Stok</th>
                    <th>Aksi</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    <div id="myModal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
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
            var dataStok = $('#table_stok').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: "{{ route('stok.list') }}",
                    type: "POST",
                    data: function (d) {
                        d.barang_id = $('#barang_id').val();
                    }
                },
                columns: [
                    {data: "DT_RowIndex", className: "text-center", orderable: false, searchable: false},
                    {data: "barang_id", orderable: true, searchable: true},
                    {data: "barang_nama", orderable: true, searchable: true},
                    {data: "stok_jumlah", orderable: true, searchable: false},
                    {data: "stok_tanggal", orderable: true, searchable: false},
                    {data: "aksi", className: "text-center", orderable: false, searchable: false}
                ]
            });

            $('#barang_id').on('change', function () {
                dataStok.ajax.reload();
            });
        });
    </script>
@endpush
