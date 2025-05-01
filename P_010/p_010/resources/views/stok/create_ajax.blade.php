<form action="{{ url('/stok/ajax') }}" method="POST" id="form-tambah">
    @csrf
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Stok</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Pilih Supplier -->
                <div class="form-group">
                    <label>Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="form-control" required>
                        <option value="">- Pilih Supplier -</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->supplier_id }}">{{ $supplier->supplier_nama }}</option>
                        @endforeach
                    </select>
                    <small id="error-supplier_id" class="error-text form-text text-danger"></small>
                </div>
                <!-- Pilih User -->
                <div class="form-group">
                    <label>User</label>
                    <p class="form-control-plaintext mb-1">{{ auth()->user()->level->level_nama }}</p>
                    <input type="hidden" name="user_id" value="{{ auth()->user()->user_id }}">
                    <small id="error-user_id" class="error-text form-text text-danger"></small>
                </div>

                <!-- Pilih Barang -->
                <div class="form-group">
                    <label>Barang</label>
                    <select name="barang_id" id="barang_id" class="form-control" required>
                        <option value="">- Pilih Barang -</option>
                        @foreach($barangs as $barang)
                        <option value="{{ $barang->barang_id }}">{{ $barang->barang_nama }}</option>
                        @endforeach
                    </select>
                    <small id="error-barang_id" class="error-text form-text text-danger"></small>
                </div>
                <!-- Input Tanggal Stok -->
                <div class="form-group">
                    <label>Tanggal Stok</label>
                    <input type="date" name="stok_tanggal" id="stok_tanggal" class="form-control"
                        value="{{ now()->toDateString() }}" required>
                    <small id="error-stok_tanggal" class="error-text form-text text-danger"></small>
                </div>
                <!-- Input Jumlah Stok -->
                <div class="form-group">
                    <label>Jumlah Stok</label>
                    <input type="number" name="stok_jumlah" id="stok_jumlah" class="form-control" required>
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
        // Validasi form dengan jQuery Validate
        $("#form-tambah").validate({
            rules: {
                supplier_id: { required: true, number: true },
                user_id: { required: true, number: true },
                barang_id: { required: true, number: true },
                stok_tanggal: { required: true, date: true },
                stok_jumlah: { required: true, number: true }
            },
            submitHandler: function(form) {
                // Mengirim data via AJAX
                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: $(form).serialize(),
                    success: function(response) {
                        if(response.status){
                            // Jika sukses, tutup modal dan tampilkan notifikasi
                            $('#myModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            });
                            // Reload DataTable stok jika variabel global dataStok ada
                            if(window.dataStok){
                                window.dataStok.ajax.reload();
                            } else {
                                location.reload();
                            }
                        } else {
                            // Bersihkan error teks dan tampilkan pesan validasi
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
                            text: 'Gagal menyimpan data stok.'
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