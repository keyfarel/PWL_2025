@extends('layouts.template')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title mb-2 mb-md-0">{{ $page->title }}</h3>
        <div class="card-tools ml-auto">
            <button onclick="modalAction('{{ url('supplier/import') }}')" class="btn btn-sm btn-info mt-1">
                <i class="fa fa-file-excel mr-1"></i>Import Supplier
            </button>
            <a href="{{ url('/supplier/export_excel') }}" class="btn btn-sm btn-primary mt-1">
                <i class="fa fa-file-excel mr-1"></i>Export Supplier
            </a>
            <a href="{{ url('/supplier/export_pdf') }}" class="btn btn-sm btn-warning mt-1">
                <i class="fa fa-file-pdf mr-1"></i> Export Supplier
            </a>
            <button onclick="modalAction('{{ url('supplier/create_ajax') }}')" class="btn btn-sm btn-success mt-1">
                Tambah Supplier
            </button>
        </div>
    </div>
    <div class="card-body">
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        @endif
        @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        @endif
            <!-- Filter -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-group row">
                        <label class="col-1 control-label col-form-label">Filter</label>
                        <div class="col-3">
                            <select class="form-control" id="supplier_id" name="supplier_id">
                                <option value="">Semua</option>
                                @foreach($supplier as $item)
                                    <option value="{{ $item->supplier_id }}">{{ $item->supplier_nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Level ID</small>
                        </div>
                    </div>
                </div>
            </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-sm w-100" id="table_supplier">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 15%;">Kode Supplier</th>
                        <th style="width: 25%;">Nama Supplier</th>
                        <th style="width: 35%;">Alamat</th>
                        <th style="width: 20%;">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div id="myModal" class="modal fade animate shake" tabindex="-1" role="dialog" data-backdrop="static"
    data-keyboard="false" aria-hidden="true"></div>
@endsection


@push('js')
<script>
    function modalAction(url = '') {
        $('#myModal').load(url, function () {
            $('#myModal').modal('show');
        });
    }

    $(document).ready(function () {
        $('#table_supplier').DataTable({
            processing: true,
            serverSide: true,
            responsive: true, // ✅ Tambah biar mobile-friendly
            ajax: {
                url: "{{ url('supplier/list') }}",
                type: "POST",
                data: function(d) {
                    d.supplier_id = $('#supplier_id').val(); // Mengirim nilai filter
                }
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    className: 'text-center align-middle',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'supplier_kode',
                    className: 'align-middle'
                },
                {
                    data: 'supplier_nama',
                    className: 'align-middle'
                },
                {
                    data: 'supplier_alamat',
                    className: 'align-middle'
                },
                {
                    data: 'aksi',
                    className: 'text-center align-middle',
                    orderable: false,
                    searchable: false
                },
            ],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                zeroRecords: "Data tidak ditemukan",
                processing: "Memproses...",
                paginate: {
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            }
        });
        // Event listener untuk filter
        $('#supplier_id').change(function() {
            $('#table_supplier').DataTable().ajax.reload(); // Reload data table ketika filter berubah
        });
    });
</script>
@endpush
