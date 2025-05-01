@if(is_null($supplier))
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
                Data supplier tidak ditemukan.
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        </div>
    </div>
</div>
@else
<form method="POST" action="{{ url('/supplier/' . $supplier->supplier_id . '/update_ajax') }}" id="form-edit">
    @csrf
    @method('PUT')
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data Supplier</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Kode Supplier -->
                <div class="form-group">
                    <label for="supplier_kode">Kode Supplier</label>
                    <input type="text" class="form-control" id="supplier_kode" name="supplier_kode"
                        value="{{ old('supplier_kode', $supplier->supplier_kode) }}" required maxlength="10">
                    <small id="error-supplier_kode" class="error-text form-text text-danger"></small>
                </div>
                <!-- Nama Supplier -->
                <div class="form-group">
                    <label for="supplier_nama">Nama Supplier</label>
                    <input type="text" class="form-control" id="supplier_nama" name="supplier_nama"
                        value="{{ old('supplier_nama', $supplier->supplier_nama) }}" required maxlength="100">
                    <small id="error-supplier_nama" class="error-text form-text text-danger"></small>
                </div>
                <!-- Alamat Supplier -->
                <div class="form-group">
                    <label for="supplier_alamat">Alamat Supplier</label>
                    <textarea class="form-control" id="supplier_alamat" name="supplier_alamat"
                        required>{{ old('supplier_alamat', $supplier->supplier_alamat) }}</textarea>
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
            $("#form-edit").validate({
                rules: {
                    supplier_kode:   { required: true, maxlength: 10 },
                    supplier_nama:   { required: true, maxlength: 100 },
                    supplier_alamat: { required: true }
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
                                    }).then(() => {
                                    // Reload DataTable supplier jika tersedia, atau reload halaman sebagai fallback
                                    if(window.dataSupplier){
                                        window.dataSupplier.ajax.reload();
                                    } else {
                                        location.reload();
                                    }
                            });
                                
                            } else {
                                $('.error-text').text('');
                                $.each(response.msgField, function(field, errors){
                                    $('#error-' + field).text(errors[0]);
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
                                text: 'Gagal memperbarui data supplier.'
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
</script>
@endif