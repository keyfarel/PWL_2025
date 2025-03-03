<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User</title>
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
<h1>Data User</h1>

@if($data->isEmpty())
    <p>Tidak ada data user.</p>
@else
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Nama</th>
            <th>ID Level Pengguna</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($data as $d)
            <tr>
                <td>{{ $d->user_id }}</td>
                <td>{{ $d->username }}</td>
                <td>{{ $d->nama }}</td>
                <td>{{ $d->level_id }}</td>
                <td>
                    <a href="/user/ubah/{{ $d->user_id }}">Ubah</a> |
                    <a href="/user/hapus/{{ $d->user_id }}"
                       onclick="return confirm('Yakin ingin menghapus pengguna ini?');">Hapus</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
</body>
</html>
