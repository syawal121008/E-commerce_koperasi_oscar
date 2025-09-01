<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f2f2f2; padding:20px;">
    <div style="max-width:600px; margin:auto; background:#fff; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); overflow:hidden;">

        <!-- Logo -->
        <div style="background:#007bff; padding:20px; text-align:center;">
<img src="https://i.ibb.co.com/chDm83bJ/smk.png" alt="smk" border="0">    </div>

        <!-- Content -->
        <div style="padding:30px;">
            <h2 style="text-align:center; color:#007bff; margin-bottom:20px;">Reset Password</h2>
            <p>Halo <strong>{{ $user->full_name }}</strong>,</p>
            <p>Kami menerima permintaan reset password untuk akun kamu.</p>

            <div style="text-align:center; margin:30px 0;">
                <a href="{{ $url }}" style="background:#007bff; color:#fff; padding:12px 25px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;">
                    Reset Password
                </a>
            </div>

            <p>Link ini berlaku selama <strong>60 menit</strong>.</p>
            <p>Kalau kamu tidak merasa meminta reset password, abaikan email ini.</p>

            <p style="margin-top:30px;">Salam,<br><strong>Koperasi SMKIUTAMA</strong></p>
        </div>

        <!-- Footer -->
        <div style="background:#f1f1f1; text-align:center; padding:15px; font-size:12px; color:#666;">
            © {{ date('Y') }} Koperasi SMKIUTAMA. Semua hak dilindungi.
        </div>
    </div>
</body>
</html>
