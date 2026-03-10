        <div class="page-container">
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="mb-0">User Data</h4>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddUser">
                                        <i class="zmdi zmdi-plus"></i> Tambah User
                                    </button>
                                </div>
                                <div class="table-responsive table--no-card m-b-30">
                                    <table class="table table-borderless table-striped table-earning">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phonenumber</th>
                                                <th>Level</th>
                                                <th>Role</th>
                                                <th>Class</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data as $key) { ?>
                                            <tr>
                                                <td><?= $key->username ?></td>
                                                <td><?= $key->name ?></td>
                                                <td><?= $key->email ?></td>
                                                <td><?= $key->phonenumber ?></td>
                                                <td><?= $key->levelname ?></td>
                                                <td><?= $key->rolename ?></td>
                                                <td><?= $key->classname ?></td>
                                                <td><button class="btn btn-secondary mb-1" data-bs-toggle="modal" data-bs-target="#moreModal<?= $key->userid ?>">Detail</button></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalAddUser" tabindex="-1" aria-labelledby="modalAddUserLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddUserLabel">Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/userdata/add" method="post">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="number" name="phonenumber" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Level</label>
                        <select name="level" id="level-select" class="form-control" required onchange="(function(sel){ const roleWrap=document.getElementById('role-wrap'); const classWrap=document.getElementById('class-wrap'); const classSelect=document.getElementById('classid-select'); const v=sel.value; if(v==='3'){ roleWrap.style.display='none'; if(classWrap) classWrap.style.display='block'; if(classSelect) classSelect.required = true; } else { roleWrap.style.display='block'; if(classWrap) classWrap.style.display='none'; if(classSelect) { classSelect.required = false; classSelect.value = ''; } const allowed=v==='1' ? [1,2] : [3,4]; const opts=[...document.querySelectorAll('#role-select option')]; opts.forEach(o=>{ o.style.display = allowed.includes(parseInt(o.value)) ? 'block' : 'none'; }); const first = allowed[0]; document.getElementById('role-select').value = first; } })(this)">
                            @foreach($level as $lv)
                            <option value="{{ $lv->levelid }}">{{ $lv->levelname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2" id="role-wrap">
                        <label class="form-label">Role</label>
                        <select name="role" id="role-select" class="form-control">
                            @foreach($role as $rl)
                            <option value="{{ $rl->roleid }}">{{ $rl->rolename }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2" id="class-wrap" style="display:none;">
                        <label class="form-label">Kelas</label>
                        <select name="classid" id="classid-select" class="form-control">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->classid }}">{{ $c->classname }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const levelSelect = document.getElementById('level-select');
        if (levelSelect) levelSelect.dispatchEvent(new Event('change'));
    });
</script>

<?php foreach ($data as $key) { ?>
<div class="modal fade" id="moreModal<?= $key->userid ?>" tabindex="-1" aria-labelledby="moreModalLabel<?= $key->userid ?>" aria-hidden="true" style="z-index:1060;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moreModalLabel<?= $key->userid ?>">Aksi untuk <?= $key->username ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Pilih salah satu aksi:</p>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <a href="<?= route('userdata.delete', $key->userid) ?>" class="btn btn-link text-danger" onclick="return confirm('Hapus user ini?')"><i class="zmdi zmdi-delete"></i> Hapus User</a>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a href="<?= route('userdata.reset', $key->userid) ?>" class="btn btn-primary">Reset Password</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
