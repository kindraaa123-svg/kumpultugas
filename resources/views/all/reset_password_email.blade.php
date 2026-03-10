<div class="page-wrapper">
    <div class="page-content--bge5">
        <div class="container">
            <div class="login-wrap">
                <div class="login-content">
                    <div class="login-form">
                        <h4 class="mb-3">Reset Password via Email</h4>

                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        <form action="{{ route('password.forgot.email.reset') }}" method="POST">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="form-group">
                                <label>Email</label>
                                <input class="au-input au-input--full" type="email" value="{{ $email }}" disabled>
                            </div>
                            <div class="form-group">
                                <label>Password Baru</label>
                                <input class="au-input au-input--full" type="password" name="new_password" required>
                            </div>
                            <div class="form-group">
                                <label>Konfirmasi Password Baru</label>
                                <input class="au-input au-input--full" type="password" name="new_password_confirmation" required>
                            </div>
                            <button class="au-btn au-btn--block au-btn--green m-b-10" type="submit">Simpan Password Baru</button>
                        </form>

                        <div class="mt-3">
                            <a href="/login">Kembali ke Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
