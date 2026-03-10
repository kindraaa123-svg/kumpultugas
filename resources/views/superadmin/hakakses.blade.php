<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">Hak Akses</h4>
                        </div>
                        <div class="table-responsive table--no-card m-b-30">
                            <form action="{{ route('hakakses.update') }}" method="post">
                                @csrf
                            <table class="table table-borderless table-striped table-earning">
                                <thead>
                                    <tr>
                                        <th>Permission</th>
                                        <?php foreach ($groups as $row) { ?>
                                            <th><?= $row['label'] ?></th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($menus as $menuKey => $menuLabel) { ?>
                                        <tr>
                                            <td><?= $menuLabel ?></td>
                                            <?php foreach ($groups as $groupKey => $_row) { ?>
                                                <td>
                                                    <input type="checkbox" name="perm[<?= $groupKey ?>][<?= $menuKey ?>]" value="1" <?= !empty($matrix[$groupKey][$menuKey]) ? 'checked' : '' ?>>
                                                </td>
                                            <?php } ?>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
