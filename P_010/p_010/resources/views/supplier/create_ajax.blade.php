<form action="{{ url('/supplier/ajax') }}" method="POST" id="form-tambah-supplier">
    @csrf
    <div id="modal-supplier" class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Supplier</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Kode Supplier -->
                <div class="form-group">
                    <label>Kode Supplier</label>
                    <input type="text" name="supplier_kode" id="supplier_kode" class="form-control" required>
                    <small id="error-supplier_kode" class="error-text form-text text-danger"></small>
                </div>
                <!-- Nama Supplier -->
                <div class="form-group">
                    <label>Nama Supplier</label>
                    <input type="text" name="supplier_nama" id="supplier_nama" class="form-control" required>
                    <small id="error-supplier_nama" class="error-text form-text text-danger"></small>
                </div>
                <!-- Alamat Supplier -->
                <div class="form-group">
                    <label>Alamat Supplier</label>
                    <textarea name="supplier_alamat" id="supplier_alamat" class="form-control" required></textarea>
                    <small id="error-supplier_alamat" class="error-text form-text text-danger"></small>
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
        $("#form-tambah-supplier").validate({
            rules: {
                supplier_kode: { required: true, maxlength: 10 },
                supplier_nama: { required: true, maxlength: 100 },
                supplier_alamat: { required: true }
            },
            submitHandler: function(form) {
                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: $(form).serialize(),
                    success: function(response) {
                        if(response.status){
                            $('#modal-supplier').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            });
                            if(window.dataSupplier){
                                window.dataSupplier.ajax.reload();
                            } else {
                                location.reload();
                            }
                        } else {
                            $('.error-text').text('');
                            $.each(response.msgField, function(prefix, val){
                                $('#error-' + prefix).text(val[0]);
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Gagal',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr, status, error){
                        console.error(error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menyimpan data supplier.'
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
            highlight: function(element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid');
            }
        });
    });

    // 🛠 Logging tambahan untuk clean console
    window.addEventListener("unhandledrejection", function(event) {
        const reason = event.reason;
        if (reason instanceof TypeError && reason.message.includes("NetworkError")) {
            console.warn("[Ignored] Network error dari script eksternal, tidak berdampak ke sistem utama.");
        } else {
            console.warn("Unhandled Promise Rejection:", reason);
        }
    });
</script>