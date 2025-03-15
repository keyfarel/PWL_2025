@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="card-tools"></div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ url('barang') }}" class="form-horizontal">
                @csrf
                <!-- Pilih Kategori -->
                <div class="form-group row">
                    <label class="col-2 control-label col-form-label">Kategori</label>
                    <div class="col-10">
                        <select class="form-control" id="kategori_id" name="kategori_id" required>
                            <option value="">- Pilih Kategori -</option>
                            @foreach($kategori as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('kategori_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->kategori_nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_id')
                        <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <!-- Input Kode Barang -->
                <div class="form-group row">
                    <label class="col-2 control-label col-form-label">Kode Barang</label>
                    <div class="col-10">
                        <input type="text" class="form-control" id="barang_kode" name="barang_kode"
                               value="{{ old('barang_kode') }}" placeholder="Masukkan kode barang" required>
                        @error('barang_kode')
                        <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <!-- Input Nama Barang -->
                <div class="form-group row">
                    <label class="col-2 control-label col-form-label">Nama Barang</label>
                    <div class="col-10">
                        <input type="text" class="form-control" id="barang_nama" name="barang_nama"
                               value="{{ old('barang_nama') }}" placeholder="Masukkan nama barang" required>
                        @error('barang_nama')
                        <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <!-- Input Harga Beli -->
                <div class="form-group row">
                    <label class="col-2 control-label col-form-label">Harga Beli</label>
                    <div class="col-10">
                        <input type="number" class="form-control" id="harga_beli" name="harga_beli"
                               value="{{ old('harga_beli') }}" placeholder="Masukkan harga beli" required>
                        @error('harga_beli')
                        <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <!-- Input Harga Jual -->
                <div class="form-group row">
                    <label class="col-2 control-label col-form-label">Harga Jual</label>
                    <div class="col-10">
                        <input type="number" class="form-control" id="harga_jual" name="harga_jual"
                               value="{{ old('harga_jual') }}" placeholder="Masukkan harga jual" required>
                        @error('harga_jual')
                        <small class="form-text text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <!-- Tombol Simpan -->
                <div class="form-group row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                        <a class="btn btn-sm btn-default ml-1" href="{{ url('barang') }}">Kembali</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('css')
@endpush

@push('js')
@endpush
