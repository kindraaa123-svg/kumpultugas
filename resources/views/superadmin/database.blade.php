        <div class="page-container">
            <header class="header-desktop">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Pengaturan Website</h4>


    <section class="section">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Export Database</h5>
                    </div>
                    <div class="card-body">
                        <p>Download current database as MySQL .sql file.</p>
                        <a href="/database/export" class="btn btn-primary">
                            Export .sql
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Import Database</h5>
                    </div>
                    <div class="card-body">
                        <p>Upload a .sql backup file to update database data.</p>
                        <form action="/database/import" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="backup_file" class="form-label">Backup file (.sql)</label>
                                <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".sql" required>
                            </div>
                            <button type="submit" class="btn btn-danger">
                                Import .sql
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if(session('success')) { ?>
            <div class="alert alert-success mt-3"><?= session('success') ?></div>
        <?php } ?>

        <?php if(session('error')) { ?>
            <div class="alert alert-danger mt-3"><?= session('error') ?></div>
        <?php } ?>
    </section>
</div>
</div>
</header>
</div>

