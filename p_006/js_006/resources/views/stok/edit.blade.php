@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools"></div>
        </div>
        <div class="card-body">
            @empty($stok)
                <div class="alert alert-danger alert-dismissible">
                    <h5><i class="icon fas fa-ban"></i> Kesalahan!</h5>
                    Data yang Anda cari tidak ditemukan.
                </div>
                <a href="{{ url('stok') }}" class="btn btn-sm btn-default mt-2">Kembali</a>
            @else
                <form method="POST" action="{{ url('/stok/'.$stok->stok_id) }}" class="form-horizontal">
                    @csrf
                    @method('PUT')
                    <!-- Pilih Supplier -->
                    <div class="form-group row">
                        <label class="col-2 control-label col-form-label">Supplier</label>
                        <div class="col-10">
                            <select class="form-control" id="supplier_id" name="supplier_id" required>
                                <option value="">- Pilih Supplier -</option>
                                @foreach($suppliers as $supplier)
                                    <option
                                        value="{{ $supplier->supplier_id }}" {{ old('supplier_id', $stok->supplier_id) == $supplier->supplier_id ? 'selected' : '' }}>
                                        {{ $supplier->supplier_nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                            <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <!-- Pilih User -->
                    <div class="form-group row">
                        <label class="col-2 control-label col-form-label">User</label>
                        <div class="col-10">
                            <select class="form-control" id="user_id" name="user_id" required>
                                <option value="">- Pilih User -</option>
                                @foreach($users as $user)
                                    <option
                                        value="{{ $user->user_id }}" {{ old('user_id', $stok->user_id) == $user->user_id ? 'selected' : '' }}>
                                        {{ $user->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                            <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <!-- Pilih Barang -->
                    <div class="form-group row">
                        <label class="col-2 control-label col-form-label">Barang</label>
                        <div class="col-10">
                            <select class="form-control" id="barang_id" name="barang_id" required>
                                <option value="">- Pilih Barang -</option>
                                @foreach($barangs as $barang)
                                    <option
                                        value="{{ $barang->barang_id }}" {{ old('barang_id', $stok->barang_id) == $barang->barang_id ? 'selected' : '' }}>
                                        {{ $barang->barang_nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('barang_id')
                            <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <!-- Input Tanggal Stok -->
                    <div class="form-group row">
                        <label class="col-2 control-label col-form-label">Tanggal Stok</label>
                        <div class="col-10">
                            <input type="date" class="form-control" id="stok_tanggal" name="stok_tanggal"
                                   value="{{ old('stok_tanggal', $stok->stok_tanggal) }}" required>
                            @error('stok_tanggal')
                            <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <!-- Input Jumlah Stok -->
                    <div class="form-group row">
                        <label class="col-2 control-label col-form-label">Jumlah Stok</label>
                        <div class="col-10">
                            <input type="number" class="form-control" id="stok_jumlah" name="stok_jumlah"
                                   value="{{ old('stok_jumlah', $stok->stok_jumlah) }}" required>
                            @error('stok_jumlah')
                            <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <!-- Tombol Simpan -->
                    <div class="form-group row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                            <a href="{{ url('stok') }}" class="btn btn-sm btn-default ml-1">Kembali</a>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection

@push('css')
@endpush

@push('js')
@endpush
