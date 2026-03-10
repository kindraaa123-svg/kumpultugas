<div class="page-wrapper">
        <aside class="menu-sidebar d-none d-lg-block">
            <div class="logo">
                <a href="/home">
                    <img src="<?= asset('storage/'.$system->systemlogo) ?>" style="width: 60px; margin-right: 10px;">
                    <?= $system->systemname?>
                </a>
            </div>
            <div class="menu-sidebar__content js-scrollbar1">
                <nav class="navbar-sidebar">
                    <ul class="list-unstyled navbar__list">
                        <?php
                            $isLogin = session('userid') ? true : false;
                    $level = session('level');
                    $role = session('role');
                    $groupKey = 'guest';
                    if ($isLogin) {
                        if ($level == 1) {
                            $groupKey = $role === 'Superadmin' ? 'superadmin' : 'admin';
                        } elseif ($level == 2) {
                            $teacher = null;
                            try {
                                $teacher = \Illuminate\Support\Facades\DB::table('teacher')->where('userid', session('userid'))->first();
                            } catch (\Throwable $e) {
                            }

                            if ($teacher && isset($teacher->roleid) && (int) $teacher->roleid === 5) {
                                $groupKey = 'curiculum';
                            } else {
                                $groupKey = in_array($role, ['Curiculum', 'Kurikulum']) ? 'curiculum' : 'guru';
                            }
                        } else {
                            $groupKey = 'siswa';
                        }
                    }

                    $menuKeys = ['tugas', 'jadwal', 'userdata', 'data', 'data_course', 'data_class', 'data_academicyear', 'data_block', 'data_nilai', 'trash', 'activity_log', 'backup_database', 'hak_akses', 'pengaturan'];
                    $perm = array_fill_keys($menuKeys, false);

                    try {
                        $rows = \Illuminate\Support\Facades\DB::table('hakakses')
                            ->where('group_key', $groupKey)
                            ->whereIn('menu_key', $menuKeys)
                            ->pluck('allowed', 'menu_key')
                            ->toArray();
                        if (! empty($rows)) {
                            foreach ($rows as $menuKey => $allowed) {
                                $perm[$menuKey] = (bool) $allowed;
                            }
                        }
                    } catch (\Throwable $e) {
                        $perm = [
                            'tugas' => true,
                            'jadwal' => true,
                            'userdata' => $level == 1,
                            'data' => $level == 1 || in_array($role, ['Curiculum', 'Kurikulum']),
                            'data_course' => $level == 1 || in_array($role, ['Curiculum', 'Kurikulum']),
                            'data_class' => $level == 1 || in_array($role, ['Curiculum', 'Kurikulum']),
                            'data_academicyear' => $level == 1 || in_array($role, ['Curiculum', 'Kurikulum']),
                            'data_block' => $level == 1 || in_array($role, ['Curiculum', 'Kurikulum']),
                            'data_nilai' => $level == 2,
                            'activity_log' => $level == 1,
                            'backup_database' => $level == 1,
                            'hak_akses' => $level == 1 && $role === 'Superadmin',
                            'pengaturan' => $level == 1,
                        ];
                    }
                    ?>

                        <?php if (! $isLogin) { ?>
                            <li>
                                <a href="/home">
                                    <i class="fas fa-tachometer-alt"></i>Dashboard</a>
                            </li>
                        <?php } else { ?>
                            <li>
                                <a href="/home">
                                    <i class="fas fa-tachometer-alt"></i>Dashboard</a>
                            </li>
                            <?php if (! empty($perm['tugas'])) { ?>
                                <li>
                                    <a href="{{ route('assignment.index') }}">
                                        <i class="fas fa-book"></i>Tugas</a>
                                </li>
                            <?php } ?>
                            <?php if (! empty($perm['jadwal'])) { ?>
                                <li>
                                    <a href="{{ route('jadwal.index') }}">
                                        <i class="fas fa-calendar"></i>Jadwal</a>
                                </li>
                            <?php } ?>

                            <?php if ($level == 1 && ! empty($perm['userdata'])) { ?>
                                <li>
                                    <a href="/userdata">
                                        <i class="fas fa-table"></i>Userdata</a>
                                </li>
                            <?php } ?>

                            <?php if (! empty($perm['data']) && (! empty($perm['data_course']) || ! empty($perm['data_class']) || ! empty($perm['data_academicyear']) || ! empty($perm['data_block']))) { ?>
                                <li class="has-sub">
                                    <a class="js-arrow" href="#">
                                        <i class="fas fa-tachometer-alt"></i>Data</a>
                                    <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                                        <?php if (! empty($perm['data_course'])) { ?>
                                            <li>
                                                <a href="{{ route('course.index') }}">Mata Pelajaran</a>
                                            </li>
                                        <?php } ?>
                                        <?php if (! empty($perm['data_class'])) { ?>
                                            <li>
                                                <a href="{{ route('class.index') }}">Kelas</a>
                                            </li>
                                        <?php } ?>
                                        <?php if (! empty($perm['data_academicyear'])) { ?>
                                            <li>
                                                <a href="{{ route('academicyear.index') }}">Tahun Ajaran</a>
                                            </li>
                                        <?php } ?>
                                        <?php if (! empty($perm['data_block'])) { ?>
                                            <li>
                                                <a href="{{ route('block.index') }}">Blok</a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } ?>

                            <?php if ($level == 2 && ! empty($perm['data_nilai'])) { ?>
                                <li>
                                    <a href="/assignment/review">
                                        <i class="fas fa-file-pen"></i>Data Nilai</a>
                                </li>
                            <?php } ?>

                            <?php if ($level == 1 && ! empty($perm['trash'])) { ?>
                                <li>
                                    <a href="{{ route('trash.index') }}">
                                        <i class="fas fa-trash"></i>Tong Sampah</a>
                                </li>
                            <?php } ?>

                            <?php if ($level == 1 && ! empty($perm['activity_log'])) { ?>
                                <li>
                                    <a href="{{ route('activity.log') }}">
                                        <i class="fa-solid fa-chart-column"></i>Activity Log</a>
                                </li>
                            <?php } ?>
                            <?php if ($level == 1 && ! empty($perm['backup_database'])) { ?>
                                <li>
                                    <a href="/database">
                                        <i class="fas fa-database"></i>Back up Database</a>
                                </li>
                            <?php } ?>
                            <?php if ($role === 'Superadmin' && ! empty($perm['hak_akses'])) { ?>
                                <li>
                                    <a href="{{ route('hakakses.index') }}">
                                        <i class="fas fa-cog"></i>Hak Akses</a>
                                </li>
                            <?php } ?>
                            <?php if ($level == 1 && ! empty($perm['pengaturan'])) { ?>
                                <li>
                                    <a href="/setting">
                                        <i class="fas fa-cog"></i>Pengaturan</a>
                                </li>
                            <?php } ?>

                            <li class="has-sub">
                                <a class="js-arrow" href="#">
                                    <i class="fas fa-circle-user"></i>Akun</a>
                                <ul class="list-unstyled navbar__sub-list js-sub-list">
                                    <li>
                                        <a href="/profile">Profil</a>
                                    </li>
                                    <li>
                                        <a href="/logout">Logout</a>
                                    </li>
                                </ul>
                            </li>
                        <?php } ?>

                    </ul>
                </nav>
            </div>
        </aside>
