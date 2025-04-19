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
            <div class="card-header bg-secondary text-white">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-1"></i> Grafik Barang Masuk vs Terjual
                </h3>
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
    const ctx = document.getElementById('stokChart').getContext('2d');
    const stokChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($ringkasan->pluck('barang_nama')) !!},
            datasets: [
                {
                    label: 'Barang Masuk',
                    data: {!! json_encode($ringkasan->pluck('total_masuk')) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.7)'
                },
                {
                    label: 'Barang Terjual',
                    data: {!! json_encode($ringkasan->pluck('total_terjual')) !!},
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
                        font: {
                            size: 10
                        }
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
</script>
@endpush