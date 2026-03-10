<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5;">
    <h3>Reset Password</h3>
    <p>Kami menerima permintaan reset password untuk akun dengan email <strong>{{ $email }}</strong>.</p>
    <p>Klik link berikut untuk mengatur password baru:</p>
    <p><a href="{{ $link }}">{{ $link }}</a></p>
    <p>Link berlaku selama 60 menit.</p>
    <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
</body>
</html>
