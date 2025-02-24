<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kategori Barang</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<h1>Data Kategori Barang</h1>

@if($data->isEmpty())
    <p>Tidak ada data kategori barang.</p>
@else
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Kode Kategori</th>
            <th>Nama Kategori</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($data as $d)
            <tr>
                <td>{{ $d->id }}</td>
                <td>{{ $d->kategori_kode }}</td>
                <td>{{ $d->kategori_nama }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
</body>
</html>
