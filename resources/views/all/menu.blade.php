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
                            <a class="js-arrow" href="/login">
                                <i class="fas fa-tachometer-alt"></i>Login</a>
                        </li>
                        <li>
                            <a class="js-arrow" href="/home">
                                <i class="fas fa-tachometer-alt"></i>Dashboard</a>
                        </li>
                        <li>
                            <a href="/course">
                                <i class="fas fa-chart-bar"></i>Course</a>
                        </li>
                        <li>
                            <a href="/userdata">
                                <i class="fas fa-table"></i>Userdata</a>
                        </li>
                        <li class="has-sub">
                            <a class="js-arrow" href="#">
                                <i class="fas fa-desktop"></i>Cyber Menu</a>
                            <ul class="list-unstyled navbar__sub-list js-sub-list">
                                <li>
                                    <a href="{{ route('jadwal.index') }}">Jadwal</a>
                                </li>
                                <li>
                                    <a href="{{ route('assignment.index') }}">Tugas (Assignment)</a>
                                </li>
                                <li>
                                    <a href="/assignment/review">Data Nilai</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="/database">
                                <i class="fas fa-cog"></i>Back up Database</a>
                        </li>
                        <li>
                            <a href="/database">
                                <i class="fas fa-cog"></i>Hak Akses</a>
                        </li>
                        <li>
                            <a href="/setting">
                                <i class="fas fa-cog"></i>Trash Can</a>
                        </li>
                        <li>
                            <a href="/setting">
                                <i class="fas fa-cog"></i>Pengaturan</a>
                        </li>

                        <li class="has-sub">
                            <a class="js-arrow" href="#">
                                <i class="fas fa-copy"></i>Akun</a>
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