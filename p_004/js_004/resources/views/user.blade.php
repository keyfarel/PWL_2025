<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengguna</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        .detail {
            margin-top: 10px;
        }
        .detail p {
            font-size: 16px;
            margin: 8px 0;
        }
        .back-btn {
            display: block;
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Detail Pengguna</h1>

    @if($data)
        <div class="detail">
            <p><strong>ID:</strong> {{ $data->user_id }}</p>
            <p><strong>Username:</strong> {{ $data->username }}</p>
            <p><strong>Nama:</strong> {{ $data->nama }}</p>
            <p><strong>ID Level Pengguna:</strong> {{ $data->level_id }}</p>
        </div>
    @else
        <p>Data pengguna tidak ditemukan.</p>
    @endif

    <a href="{{ url('/') }}" class="back-btn">Kembali</a>
</div>
</body>
</html>
