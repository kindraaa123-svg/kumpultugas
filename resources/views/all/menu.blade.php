<div class="page-wrapper">
        <aside class="menu-sidebar d-none d-lg-block">
            <div class="logo">
                <a href="/home">
                    <img src="<?= asset('storage/' . $system->systemlogo) ?>" style="width: 60px; margin-right: 10px;">
                    <?=$system->systemname?>
                </a>
            </div>
            <div class="menu-sidebar__content js-scrollbar1">
                <nav class="navbar-sidebar">
                    <ul class="list-unstyled navbar__list">
                         <li>
                            <a href="/login">
                                <i class="fas fa-arrow-right-to-bracket"></i>Login</a>
                        </li>
                        <li>
                            <a href="/home">
                                <i class="fas fa-tachometer-alt"></i>Dashboard</a>
                        </li>
                        <li>
                            <a href="{{ route('assignment.index') }}">
                                <i class="fas fa-book"></i>Tugas</a>
                        </li>
                        <li>
                            <a href="{{ route('jadwal.index') }}">
                                <i class="fas fa-calendar"></i>Jadwal</a>
                        </li>
                        <li>
                            <a href="/userdata">
                                <i class="fas fa-table"></i>Userdata</a>
                        </li>
                         <li class="has-sub">
                            <a class="js-arrow" href="#">
                                <i class="fas fa-tachometer-alt"></i>Data</a>
                            <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                                <li>
                                    <a href="{{ route('course.index') }}">Mata Pelajaran</a>
                                </li>
                                <li>
                                    <a href="{{ route('class.index') }}">Kelas</a>
                                </li>
                                <li>
                                    <a href="{{ route('academicyear.index') }}">Tahun Ajaran</a>
                                </li>
                                <li>
                                    <a href="{{ route('block.index') }}">Blok</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="/assignment/review">
                                <i class="fas fa-file-pen"></i>Data Nilai</a>
                        </li>
                        <li>
                            <a href="{{ route('activity.log') }}">
                                <i class="fa-solid fa-chart-column"></i>Activity Log</a>
                        </li>
                        <li>
                            <a href="/database">
                                <i class="fas fa-database"></i>Back up Database</a>
                        </li>
                        <li>
                            <a href="/database">
                                <i class="fas fa-cog"></i>Hak Akses</a>
                        </li>
                        <li>
                            <a href="/setting">
                                <i class="fas fa-trash"></i>Trash Can</a>
                        </li>
                        <li>
                            <a href="/setting">
                                <i class="fas fa-cog"></i>Pengaturan</a>
                        </li>

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

                    </ul>
                </nav>
            </div>
        </aside>