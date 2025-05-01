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
    <form method="POST" action="{{ url('/stok/' . $stok->stok_id . '/update_ajax') }}" id="form-edit">
        @csrf
        @method('PUT')
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Stok</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Pilih Supplier -->
                    <div class="form-group">
                        <label>Supplier</label>
                        <select class="form-control" id="supplier_id" name="supplier_id" required>
                            <option value="">- Pilih Supplier -</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->supplier_id }}"
                                    {{ old('supplier_id', $stok->supplier_id) == $supplier->supplier_id ? 'selected' : '' }}>
                                    {{ $supplier->supplier_nama }}
                                </option>
                            @endforeach
                        </select>
                        <small id="error-supplier_id" class="error-text form-text text-danger"></small>
                    </div>
                    <!-- Pilih User -->
                    <div class="form-group">
                        <label>User</label>
                        <select class="form-control" id="user_id" name="user_id" required>
                            <option value="">- Pilih User -</option>
                            @foreach($users as $user)
                                <option value="{{ $user->user_id }}"
                                    {{ old('user_id', $stok->user_id) == $user->user_id ? 'selected' : '' }}>
                                    {{ $user->nama }}
                                </option>
                            @endforeach
                        </select>
                        <small id="error-user_id" class="error-text form-text text-danger"></small>
                    </div>
                    <!-- Pilih Barang -->
                    <div class="form-group">
                        <label>Barang</label>
                        <select class="form-control" id="barang_id" name="barang_id" required>
                            <option value="">- Pilih Barang -</option>
                            @foreach($barangs as $barang)
                                <option value="{{ $barang->barang_id }}"
                                    {{ old('barang_id', $stok->barang_id) == $barang->barang_id ? 'selected' : '' }}>
                                    {{ $barang->barang_nama }}
                                </option>
                            @endforeach
                        </select>
                        <small id="error-barang_id" class="error-text form-text text-danger"></small>
                    </div>
                    <!-- Input Tanggal Stok -->
                    <div class="form-group">
                        <label>Tanggal Stok</label>
                        <input type="date" class="form-control" id="stok_tanggal" name="stok_tanggal"
                               value="{{ old('stok_tanggal', $stok->stok_tanggal) }}" required>
                        <small id="error-stok_tanggal" class="error-text form-text text-danger"></small>
                    </div>
                    <!-- Input Jumlah Stok -->
                    <div class="form-group">
                        <label>Jumlah Stok</label>
                        <input type="number" class="form-control" id="stok_jumlah" name="stok_jumlah"
                               value="{{ old('stok_jumlah', $stok->stok_jumlah) }}" required>
                        <small id="error-stok_jumlah" class="error-text form-text text-danger"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-warning">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </form>

    <script>
        $(document).ready(function(){
            // Inisialisasi validasi form edit menggunakan jQuery Validate
            $("#form-edit").validate({
                rules: {
                    supplier_id: { required: true, number: true },
                    user_id: { required: true, number: true },
                    barang_id: { required: true, number: true },
                    stok_tanggal: { required: true, date: true },
                    stok_jumlah: { required: true, number: true }
                },
                submitHandler: function(form) {
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        success: function(response) {
                            if(response.status){
                                $('#myModal').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message
                                });
                                // Reload DataTable stok (variabel global dataStok harus sudah dideklarasikan)
                                if(window.dataStok){
                                    window.dataStok.ajax.reload();
                                }
                            } else {
                                $('.error-text').text('');
                                $.each(response.msgField, function(prefix, val){
                                    $('#error-' + prefix).text(val[0]);
                                });
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Terjadi Kesalahan',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr, status, error){
                            console.error(error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal memperbarui data stok.'
                            });
                        }
                    });
                    return false;
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
@endif
