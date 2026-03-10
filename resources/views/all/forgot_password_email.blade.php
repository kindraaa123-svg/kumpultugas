<div class="page-wrapper">
    <div class="page-content--bge5">
        <div class="container">
            <div class="login-wrap">
                <div class="login-content">
                    <div class="login-form">
                        <h4 class="mb-3">Reset Password via Email</h4>

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        <form action="{{ route('password.forgot.email.send') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Email Terdaftar</label>
                                <input class="au-input au-input--full" type="email" name="email" placeholder="contoh@email.com" required>
                            </div>
                            <button class="au-btn au-btn--block au-btn--green m-b-10" type="submit">Kirim Link Reset</button>
                        </form>
                        <a href="{{ route('password.forgot.phone.page') }}">Reset Password via Nomor Telepon</a>

                        <div class="mt-3 d-flex justify-content-between">
                            <span></span>
                            <a href="/login">Kembali ke Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
