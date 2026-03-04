<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0">Profil Saya</h4>
                            </div>
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif
                                @if(session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif
                                <form action="{{ route('profile.update') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" value="{{ $user ? $user->username : '' }}" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Nama</label>
                                        <input type="text" name="name" class="form-control" value="{{ $data ? $data->name : '' }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ $data ? $data->email : '' }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Nomor Telepon</label>
                                        <input type="text" name="phonenumber" class="form-control" value="{{ $data ? $data->phonenumber : '' }}" required>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0">Ganti Password</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('profile.password') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Password Lama</label>
                                        <input type="password" name="old_password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password Baru</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">Ubah Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
