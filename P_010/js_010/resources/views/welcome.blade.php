@extends('layouts.template')

@section('content')
<section class="content">
    <div class="container-fluid">

        {{-- Ringkasan Total --}}
        <div class="row mb-3">
            <div class="col-sm-12 col-md-4 mb-2">
                <div class="small-box bg-success shadow-lg rounded">
                    <div class="inner text-center">
                        <h4>@ribuan($ringkasan->sum('stok_ready'))</h4>
                        <p>Total Stok Ready</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-cubes"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-4 mb-2">
                <div class="small-box bg-info shadow-lg rounded">
                    <div class="inner text-center">
                        <h4>@ribuan($ringkasan->sum('total_masuk'))</h4>
                        <p>Total Stok Masuk</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-truck-loading"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-4 mb-2">
                <div class="small-box bg-warning shadow-lg rounded">
                    <div class="inner text-center">
                        <h4>@ribuan($ringkasan->sum('total_terjual'))</h4>
                        <p>Total Barang Terjual</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grafik --}}
        <div class="card">
            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title m-0 d-flex align-items-center">
                    <i class="fas fa-chart-bar mr-2"></i> Grafik Barang Masuk vs Terjual
                </h3>
                {{-- <div class="ml-auto d-flex align-items-center">
                    <label for="filterTipe" class="mb-0 mr-2">Filter:</label>
                    <select id="filterTipe" class="form-control form-control-sm">
                        <option value="barang" selected>Barang</option>
                        <option value="kategori">Kategori</option>
                    </select>
                </div> --}}

                {{-- Filter Bulan, Tahun, dan Tipe --}}
                <div class="ml-auto d-flex align-items-center flex-wrap justify-content-end" style="gap: 0.5rem;">

                    @php
                    $currentMonth = date('m');
                    $currentYear = date('Y');
                    $years = range($currentYear - 5, $currentYear + 1);
                    @endphp

                    <div class="form-group mb-0 mr-2">
                        <label for="filterBulan" class="mr-1 mb-0">Bulan:</label>
                        <select id="filterBulan" class="form-control form-control-sm w-auto d-inline-block">
                            <option value="">{{ DateTime::createFromFormat('!m',
                                $currentMonth)->format('F') }}</option>
                            @foreach(range(1, 12) as $month)
                            <option value="{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}">
                                {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-0 mr-2">
                        <label for="filterTahun" class="mr-1 mb-0">Tahun:</label>
                        <select id="filterTahun" class="form-control form-control-sm w-auto d-inline-block">
                            <option value="">{{ $currentYear }}</option>
                            @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-0 mr-2">
                        <label for="filterTipe" class="mr-1 mb-0">Tipe:</label>
                        <select id="filterTipe" class="form-control form-control-sm w-auto d-inline-block">
                            <option value="barang">Barang</option>
                            <option value="kategori">Kategori</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; width: 100%; height: 300px;">
                    <canvas id="stokChart"></canvas>
                </div>
            </div>
        </div>


        {{-- Tombol Lihat Detail --}}
        <div class="text-center mt-4">
            <a href="/stok" class="btn btn-outline-primary">
                <i class="fas fa-table mr-1"></i> Lihat Data Stok Lengkap
            </a>
        </div>

    </div>
</section>
@endsection


@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
    const dataBarang = {
        labels: {!! json_encode($ringkasan->pluck('barang_nama')) !!},
        masuk: {!! json_encode($ringkasan->pluck('total_masuk')) !!},
        terjual: {!! json_encode($ringkasan->pluck('total_terjual')) !!}
    };

    const dataKategori = {
        labels: {!! json_encode($kategoriRingkasan->pluck('kategori_nama')) !!},
        masuk: {!! json_encode($kategoriRingkasan->pluck('total_masuk')) !!},
        terjual: {!! json_encode($kategoriRingkasan->pluck('total_terjual')) !!}
    };

    const ctx = document.getElementById('stokChart').getContext('2d');
    const stokChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Barang Masuk',
                    data: [],
                    backgroundColor: 'rgba(54, 162, 235, 0.7)'
                },
                {
                    label: 'Barang Terjual',
                    data: [],
                    backgroundColor: 'rgba(255, 99, 132, 0.7)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        font: { size: 10 }
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    font: {
                        size: 10,
                        weight: 'bold'
                    },
                    formatter: function(value) {
                        return value.toLocaleString();
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    function updateChartData(type) {
        const source = type === 'kategori' ? dataKategori : dataBarang;

        stokChart.data.labels = source.labels;
        stokChart.data.datasets[0].data = source.masuk;
        stokChart.data.datasets[1].data = source.terjual;
        stokChart.update();
    }

    document.getElementById('filterTipe').addEventListener('change', function () {
        updateChartData(this.value);
    });

    document.addEventListener('DOMContentLoaded', function () {
        updateChartData(document.getElementById('filterTipe').value);
    });
</script>
@endpush