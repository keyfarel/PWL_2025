<form action="{{ url('/penjualan/ajax') }}" method="POST" id="form-tambah-penjualan">
    @csrf
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Penjualan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <!-- Nama Pembeli -->
                <div class="form-group">
                    <label>Pembeli</label>
                    <input type="text" name="pembeli" class="form-control" required>
                    <small id="error-pembeli" class="error-text form-text text-danger"></small>
                </div>

                <!-- User Login -->
                <div class="form-group">
                    <label>User</label>
                    <p class="form-control-plaintext mb-1">{{ auth()->user()->level->level_nama }}</p>
                    <input type="hidden" name="user_id" value="{{ auth()->user()->user_id }}">
                    <small id="error-user_id" class="error-text form-text text-danger"></small>
                </div>

                <!-- Kode Penjualan -->
                <div class="form-group">
                    <label>Kode Penjualan</label>
                    <input type="text" name="penjualan_kode" class="form-control" value="PNJ{{ date('YmdHis') }}"
                        readonly>
                    <small id="error-penjualan_kode" class="error-text form-text text-danger"></small>
                </div>

                <!-- Tanggal Penjualan -->
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="penjualan_tanggal" class="form-control" value="{{ now()->toDateString() }}"
                        required>
                    <small id="error-penjualan_tanggal" class="error-text form-text text-danger"></small>
                </div>

                <!-- Pilih Barang -->
                <div class="form-group">
                    <label>Pilih Barang</label>
                    <select name="barang_id[]" class="form-control" multiple required>
                        @foreach($barangs as $barang)
                        @php
                        $stok_tersedia = $barang->stok->sum('jumlah');
                        @endphp
                        @if($stok_tersedia > 0)
                        <option value="{{ $barang->barang_id }}" data-harga="{{ $barang->harga_jual }}"
                            data-stok="{{ $stok_tersedia }}">
                            {{ $barang->barang_nama }} (Stok: {{ $stok_tersedia }}, Harga: {{
                            number_format($barang->harga_jual) }})
                        </option>
                        @endif
                        @endforeach
                    </select>
                </div>

                <!-- Jumlah per Barang -->
                <div id="jumlah-barang-container"></div>

                <!-- Total Harga -->
                <div class="form-group">
                    <label>Total Harga</label>
                    <input type="number" name="total_harga" class="form-control" readonly required>
                    <small id="error-total_harga" class="error-text form-text text-danger"></small>
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
    const hargaMap = {};
    const stokMap = {};

    $('select[name="barang_id[]"]').change(function () {
        $('#jumlah-barang-container').empty();
        let totalHarga = 0;

        $(this).find('option:selected').each(function () {
            const barangId = $(this).val();
            const barangNama = $(this).text();
            const harga = parseFloat($(this).data('harga'));
            const stok = parseInt($(this).data('stok'));

            hargaMap[barangId] = harga;
            stokMap[barangId] = stok;

            $('#jumlah-barang-container').append(`
                <div class="form-group">
                    <label>${barangNama} - Jumlah (Max: ${stok})</label>
                    <input type="number" class="form-control jumlah-barang" name="jumlah[${barangId}]" max="${stok}" min="1" data-barang-id="${barangId}" required>
                </div>
            `);
        });
    });

    $(document).on('input', '.jumlah-barang', function () {
        let total = 0;
        $('.jumlah-barang').each(function () {
            const id = $(this).data('barang-id');
            const qty = parseInt($(this).val()) || 0;
            const harga = hargaMap[id] || 0;
            const stok = stokMap[id] || 0;

            if (qty > stok) {
                $(this).val(stok);
            }

            total += qty * harga;
        });

        $('input[name="total_harga"]').val(total);
    });

    // Ajax submit
    $("#form-tambah-penjualan").on('submit', function(e) {
        e.preventDefault();
        let form = this;

        $.ajax({
            url: form.action,
            method: form.method,
            data: $(form).serialize(),
            success: function(response) {
                if (response.status) {
                    $('#myModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message
                    });
                    if (window.dataPenjualan) {
                        window.dataPenjualan.ajax.reload();
                    } else {
                        location.reload();
                    }
                } else {
                    $('.error-text').text('');
                    $.each(response.msgField || {}, function(prefix, val) {
                        $('#error-' + prefix).text(val[0]);
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: 'Gagal menyimpan data penjualan.'
                });
            }
        });
    });
</script>