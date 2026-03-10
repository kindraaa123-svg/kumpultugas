<div class="page-wrapper">
    <div class="page-content--bge5">
        <div class="container">
            <div class="login-wrap" style="max-width: 640px;">
                <div class="login-content">
                    <div class="login-form">
                        <h4 class="mb-3">Reset Password via WhatsApp OTP</h4>

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        <form action="{{ route('password.forgot.phone.send') }}" method="POST" class="mb-3">
                            @csrf
                            <div class="form-group">
                                <label>No Telepon Terdaftar</label>
                                <input class="au-input au-input--full" type="text" name="phone" placeholder="08xxxx / 62xxxx" required value="{{ old('phone', $otpPhone ?? '') }}">
                            </div>
                            <button class="au-btn au-btn--block au-btn--blue m-b-10" type="submit">Kirim OTP WhatsApp</button>
                        </form>

                        @if(!empty($showOtpForm))
                            <hr>
                            <form action="{{ route('password.forgot.phone.verify') }}" method="POST">
                                @csrf
                                <input type="hidden" name="phone" value="{{ old('phone', $otpPhone ?? '') }}">
                                <div class="form-group">
                                    <label>Kode OTP</label>
                                    <input class="au-input au-input--full" type="text" name="otp" maxlength="6" required>
                                </div>
                                <button class="au-btn au-btn--block au-btn--green" type="submit">Verifikasi OTP</button>
                            </form>
                        @endif

                        <div class="mt-3 d-flex justify-content-between">
                            <a href="{{ route('password.forgot.email.page') }}">Reset via Email</a>
                            <a href="/login">Kembali ke Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
