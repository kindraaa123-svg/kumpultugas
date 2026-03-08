<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Email</title>
</head>
<body>
    <h1>Halo, {{ $name }}</h1>
    <p>Silakan klik link di bawah ini untuk memverifikasi email baru Anda:</p>
    <a href="{{ $link }}">Verifikasi Email</a>
    <p>Link ini akan kadaluarsa dalam 60 menit.</p>
</body>
</html>
