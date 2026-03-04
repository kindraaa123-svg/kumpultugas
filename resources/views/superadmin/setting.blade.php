        <div class="page-container">
            <!-- HEADER DESKTOP-->
            <header class="header-desktop">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Pengaturan Website</h4>
                                <form class="mt-4" action="/setting/update" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="systemid" value="<?= $system->systemid ?>">
                                    <div class="form-group mb-3">
                                        <label class="form-control-label">System Name</label>
                                        <input type="text" class="form-control" name="name" value="<?=$system->systemname ?>">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-control-label">System Logo</label>
                                        <input type="file" class="form-control" value="<?=$system->systemlogo ?>" name="logo">
                                    </div>
                                    <label class="form-control-label"> Current Logo </label>
                                    <div class="form-group mb-3">
                                        <img src="<?= asset('storage/' . $system->systemlogo) ?>" style="width: 80px;">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-control-label">System Address</label>
                                        <input type="text" class="form-control" value="<?=$system->systemaddress ?>" name="address">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-control-label">System Manager</label>
                                        <input type="text" class="form-control" value="<?=$system->systemmanager ?>" name="manager">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-control-label">System Contact</label>
                                        <input type="text" class="form-control" value="<?=$system->systemcontact ?>" name="contact">
                                    </div>
                                    <div class="form-group mb-3">
                                        <button type="submit" class="btn btn-primary w-100">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
</header>
</div>