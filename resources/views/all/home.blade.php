<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                @php
                    $sessionRole = strtolower((string) session('role'));
                    $isLoggedIn = !empty(session('userid'));
                    $teacherRoleId = (int) (session('teacher_roleid') ?? 0);
                    $roleLabel = 'Guest';
                    if ($isLoggedIn) {
                        if ((int) session('level') === 3) {
                            $roleLabel = 'Siswa';
                        } elseif ((int) session('level') === 2) {
                            $isKurikulum = in_array($sessionRole, ['kurikulum', 'curiculum'], true) || in_array($teacherRoleId, [4, 5], true);
                            $roleLabel = $isKurikulum ? 'Kurikulum' : 'Guru';
                        } elseif ((int) session('level') === 1) {
                            $roleLabel = $sessionRole === 'superadmin' ? 'Superadmin' : 'Admin';
                        }
                    }

                    $quickLinks = [];
                    if (!$isLoggedIn) {
                        $quickLinks[] = ['label' => 'Login', 'url' => '/login'];
                    } elseif ($roleLabel === 'Siswa') {
                        $quickLinks[] = ['label' => 'Lihat Tugas', 'url' => route('assignment.index')];
                        $quickLinks[] = ['label' => 'Profil', 'url' => route('profile')];
                    } elseif ($roleLabel === 'Guru') {
                        $quickLinks[] = ['label' => 'Kelola Tugas', 'url' => route('assignment.index')];
                        $quickLinks[] = ['label' => 'Jadwal', 'url' => route('jadwal.index')];
                    } elseif ($roleLabel === 'Kurikulum') {
                        $quickLinks[] = ['label' => 'Jadwal', 'url' => route('jadwal.index')];
                        $quickLinks[] = ['label' => 'Setting Jadwal', 'url' => route('jadwal.setting')];
                    } else {
                        $quickLinks[] = ['label' => 'Userdata', 'url' => '/userdata'];
                        $quickLinks[] = ['label' => 'Activity Log', 'url' => route('activity.log')];
                    }

                    $eventList = collect($events ?? [])->take(8);
                @endphp

                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                            <div class="card-body p-4">
                                <h3 class="mb-2" style="font-weight: 700;">Dashboard {{ $roleLabel }}</h3>
                                <p class="text-muted mb-0">Pantau ringkasan aktivitas dan deadline tugas dalam satu halaman.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                            <div class="card-body p-4">
                                <div class="small text-muted mb-2">Quick Access</div>
                                <div class="d-flex flex-wrap" style="gap:8px;">
                                    @foreach($quickLinks as $link)
                                        <a href="{{ $link['url'] }}" class="btn btn-outline-primary btn-sm">{{ $link['label'] }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    @if(isset($card1_label) && $card1_label !== '')
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                                <div class="card-body">
                                    <div class="text-muted small">{{ $card1_label }}</div>
                                    <h2 class="mb-0">{{ $card1_value }}</h2>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                            <div class="card-body">
                                <div class="text-muted small">{{ $card2_label }}</div>
                                <h2 class="mb-0">{{ $card2_value }}</h2>
                            </div>
                        </div>
                    </div>
                    @if(isset($card3_label) && $card3_label !== '')
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                                <div class="card-body">
                                    <div class="text-muted small">{{ $card3_label }}</div>
                                    <h2 class="mb-0">{{ $card3_value }}</h2>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="row">
                    <div class="col-lg-8 mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                            <div class="card-body">
                                <h5 class="mb-3">Kalender Pengingat Tugas</h5>
                                <div id="calendar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                            <div class="card-body">
                                <h5 class="mb-3">Deadline Terdekat</h5>
                                @if($eventList->count() === 0)
                                    <div class="text-muted">Belum ada deadline tugas.</div>
                                @else
                                    <ul class="list-group list-group-flush">
                                        @foreach($eventList as $ev)
                                            <li class="list-group-item px-0">
                                                <div class="fw-semibold">{{ $ev['title'] ?? '-' }}</div>
                                                <small class="text-muted">{{ $ev['start'] ?? '-' }}</small>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src='/vendor/fullcalendar-6.1.11/fullcalendar.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 560,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        events: @json($events ?? []),
        eventDisplay: 'block'
    });

    calendar.render();
});
</script>
