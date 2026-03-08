<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Perubahan Email</title>
</head>
<body>
    <h1>Halo, {{ $name }}</h1>
    <p>Kami menerima permintaan untuk mengubah alamat email Anda.</p>
    <p>Silakan klik link di bawah ini untuk memverifikasi email baru Anda:</p>
    <a href="{{ $link }}">Verifikasi Perubahan Email</a>
    <p>Jika Anda tidak meminta perubahan ini, abaikan email ini.</p>
</body>
</html>
