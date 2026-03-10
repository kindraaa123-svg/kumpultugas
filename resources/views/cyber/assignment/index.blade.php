<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                @php
                    $canManageAssignments = session('level') == 2 && strtolower((string) session('role')) == 'guru';
                @endphp
                <div class="row">
                    <div class="col-md-12">
                        <div class="overview-wrap">
                            <h2 class="title-1">
                                @if(!empty($selectedRoom))
                                    {{ $selectedRoom->coursename }}
                                @else
                                    Ruang Mapel
                                @endif
                            </h2>
                            <div>
                                @if(!empty($selectedScheduleId))
                                    <a href="{{ route('assignment.index', ['block_id' => $selectedBlockId ?? null, 'academic_year_id' => $selectedAcademicYearId ?? null]) }}" class="au-btn au-btn-icon au-btn--blue">
                                        Kembali
                                    </a>
                                @endif
                                @if($canManageAssignments)
                                    @if(empty($selectedScheduleId))
                                        <button type="button" class="au-btn au-btn-icon au-btn--blue" data-bs-toggle="modal" data-bs-target="#modalAddRoom">
                                            <i class="zmdi zmdi-plus"></i>Tambah Ruang Mapel
                                        </button>
                                    @else
                                        <button type="button" class="au-btn au-btn-icon au-btn--blue" data-bs-toggle="modal" data-bs-target="#modalAddAssignment">
                                            <i class="zmdi zmdi-plus"></i>Tambah Tugas
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                <div class="row m-t-25">
                    <div class="col-md-12">
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
                @endif
                <div class="row m-t-25" id="ajaxAlert" style="display:none;">
                    <div class="col-md-12">
                        <div class="alert" role="alert"></div>
                    </div>
                </div>
                
                @if(empty($selectedScheduleId))
                    <div class="row m-t-25">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="filterYear" class="form-label">Filter Tahun Ajaran</label>
                                            <select id="filterYear" class="form-control">
                                                <option value="all">Semua Tahun Ajaran</option>
                                                @foreach($academicYears as $ay)
                                                    <option value="{{ $ay->academic_year_id }}" {{ (string) $selectedAcademicYearId === (string) $ay->academic_year_id ? 'selected' : '' }}>
                                                        {{ $ay->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="filterBlock" class="form-label">Filter Blok</label>
                                            <select id="filterBlock" class="form-control">
                                                <option value="all">Semua Blok</option>
                                                @foreach($blocks as $block)
                                                    <option value="{{ $block->block_id }}" {{ (string) $selectedBlockId === (string) $block->block_id ? 'selected' : '' }}>
                                                        {{ $block->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row m-t-25" id="roomCardList">
                        @include('cyber.assignment.room_list', ['rooms' => $rooms])
                    </div>
                @else
                    <div class="row m-t-25">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="small text-muted">
                                                {{ $selectedRoom?->classname ?? '-' }} • {{ $selectedRoom?->block_name ?? '-' }} • {{ $selectedRoom?->academic_year_name ?? '-' }}
                                            </div>
                                        </div>
                                        @if(session('level') == 3)
                                            <div class="col-md-4">
                                                <label for="filterStatus" class="form-label">Filter Status</label>
                                                <select id="filterStatus" class="form-control">
                                                    <option value="all">Semua Status</option>
                                                    <option value="completed">Sudah Dikerjakan</option>
                                                    <option value="pending">Belum Dikerjakan</option>
                                                    <option value="not_done">Tidak dikerjakan</option>
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row m-t-25" id="assignmentList">
                        @include('cyber.assignment.assignment_list', ['assignments' => $assignments, 'submittedIds' => $submittedIds ?? []])
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterYear = document.getElementById('filterYear');
    const filterBlock = document.getElementById('filterBlock');
    const filterStatus = document.getElementById('filterStatus');

    function filterRooms(page) {
        if (!filterBlock || !filterYear) return;
        const currentPage = page || 1;
        fetch(`{{ route('assignment.courses.filter') }}?academic_year_id=${filterYear.value}&block_id=${filterBlock.value}&page=${currentPage}`)
            .then(response => response.json())
            .then(data => {
                const list = document.getElementById('roomCardList');
                if (list) list.innerHTML = data.html;

                if (data.blocksOptionsHtml && filterBlock) {
                    const current = filterBlock.value;
                    filterBlock.innerHTML = data.blocksOptionsHtml;
                    if ([...filterBlock.options].some(o => o.value === current)) {
                        filterBlock.value = current;
                    } else {
                        filterBlock.value = 'all';
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function filterAssignments(page) {
        const scheduleid = '{{ $selectedScheduleId ?? '' }}';
        const status = filterStatus ? filterStatus.value : 'all';
        const currentPage = page || 1;

        fetch(`{{ route('assignment.filter') }}?scheduleid=${scheduleid}&status=${status}&page=${currentPage}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('assignmentList').innerHTML = data.html;
            })
            .catch(error => console.error('Error:', error));
    }

    if(filterStatus) {
        filterStatus.addEventListener('change', filterAssignments);
    }

    if(filterBlock) {
        filterBlock.addEventListener('change', filterRooms);
    }

    if(filterYear) {
        filterYear.addEventListener('change', function() {
            if (filterBlock) filterBlock.value = 'all';
            filterRooms();
        });
    }

    const roomList = document.getElementById('roomCardList');
    if (roomList) {
        roomList.addEventListener('click', function (event) {
            const link = event.target.closest('.pagination a');
            if (!link) return;
            event.preventDefault();
            const url = new URL(link.href, window.location.origin);
            const page = url.searchParams.get('page') || 1;
            filterRooms(page);
        });
    }

    const assignmentList = document.getElementById('assignmentList');
    if (assignmentList) {
        assignmentList.addEventListener('click', function (event) {
            const link = event.target.closest('.pagination a');
            if (!link) return;
            event.preventDefault();
            const url = new URL(link.href, window.location.origin);
            const page = url.searchParams.get('page') || 1;
            filterAssignments(page);
        });
    }
});
</script>

@if($canManageAssignments && !empty($selectedScheduleId))
<div id="assignmentModals">
    @foreach($assignments as $assignment)
        @include('cyber.assignment.edit_modal', ['assignment' => $assignment])
    @endforeach
</div>
@endif

@if($canManageAssignments && empty($selectedScheduleId))
<div class="modal fade" id="modalAddRoom" tabindex="-1" aria-labelledby="modalAddRoomLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddRoomLabel">Tambah Ruang Mapel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('assignment.room.store') }}" method="POST" class="js-room-create" id="addRoomForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addRoomAcademicYear" class="form-label">Tahun Ajaran</label>
                        <select id="addRoomAcademicYear" class="form-control" required>
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->academic_year_id }}" {{ (string) $selectedAcademicYearId === (string) $ay->academic_year_id ? 'selected' : '' }}>
                                    {{ $ay->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="addRoomBlock" class="form-label">Blok</label>
                        <select id="addRoomBlock" class="form-control" required>
                            <option value="">-- Pilih Blok --</option>
                            @foreach($blocks as $block)
                                <option value="{{ $block->block_id }}" {{ ($selectedBlockId !== 'all' && (string) $selectedBlockId === (string) $block->block_id) ? 'selected' : '' }}>
                                    {{ $block->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="addRoomScheduleid" class="form-label">Pilih Jadwal</label>
                        <select name="scheduleid" id="addRoomScheduleid" class="form-control" required>
                            <option value="">-- Pilih Jadwal --</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Ruang</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addYear = document.getElementById('addRoomAcademicYear');
        const addBlock = document.getElementById('addRoomBlock');
        const addSchedule = document.getElementById('addRoomScheduleid');

        function refreshModalData(mode) {
            if (!addYear || !addBlock || !addSchedule) return;
            if (!addYear.value) return;

            const yearValue = addYear.value;
            const blockValue = addBlock.value;
            const url = new URL(`{{ route('assignment.schedules.filter') }}`, window.location.origin);
            url.searchParams.set('academic_year_id', yearValue);
            url.searchParams.set('block_id', mode === 'year' ? '' : blockValue);

            fetch(url.toString())
                .then(r => r.json())
                .then(data => {
                    if (data.blocksOptionsHtml && addBlock) {
                        const prev = addBlock.value;
                        addBlock.innerHTML = data.blocksOptionsHtml;
                        if (mode === 'year') {
                            addBlock.value = '';
                        } else if ([...addBlock.options].some(o => o.value === prev)) {
                            addBlock.value = prev;
                        }
                    }

                    if (data.scheduleOptionsHtml && addSchedule) {
                        addSchedule.innerHTML = data.scheduleOptionsHtml;
                    }
                })
                .catch(err => console.error(err));
        }

        if (addYear) {
            addYear.addEventListener('change', function () {
                if (addBlock) addBlock.value = '';
                refreshModalData('year');
            });
        }

        if (addBlock) {
            addBlock.addEventListener('change', function () {
                refreshModalData('block');
            });
        }
    });
</script>
@endif

@if($canManageAssignments && !empty($selectedScheduleId))
<div class="modal fade" id="modalAddAssignment" tabindex="-1" aria-labelledby="modalAddAssignmentLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddAssignmentLabel">Tambah Tugas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('assignment.store') }}" method="POST" class="js-assignment-create" id="addAssignmentForm">
                @csrf
                <input type="hidden" name="scheduleid" value="{{ $selectedScheduleId }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Tugas</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea name="description" id="description" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="grading_mode" class="form-label">Mode Penilaian</label>
                        <select name="grading_mode" id="grading_mode" class="form-control" required onchange="document.getElementById('auto-score-wrap').style.display = this.value === 'auto' ? 'block' : 'none'">
                            <option value="manual">Manual (guru menilai)</option>
                            <option value="auto">Otomatis (nilai langsung)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="auto-score-wrap" style="display: none;">
                        <label for="auto_score" class="form-label">Nilai Otomatis</label>
                        <input type="number" name="auto_score" id="auto_score" class="form-control" min="0" max="100" placeholder="Contoh: 100">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="time_start" class="form-label">Mulai</label>
                            <input type="datetime-local" name="time_start" id="time_start" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="time_end" class="form-label">Selesai (Deadline)</label>
                            <input type="datetime-local" name="time_end" id="time_end" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Tugas</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const alertWrap = document.getElementById('ajaxAlert');
        const alertBox = alertWrap ? alertWrap.querySelector('.alert') : null;

        function showAjaxAlert(type, message) {
            if (!alertWrap || !alertBox) return;
            alertBox.className = `alert alert-${type}`;
            alertBox.textContent = message || '';
            alertWrap.style.display = 'block';
        }

        function hideAjaxAlert() {
            if (!alertWrap || !alertBox) return;
            alertWrap.style.display = 'none';
            alertBox.textContent = '';
        }

        document.addEventListener('submit', async function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;

            if (!form.classList.contains('js-assignment-create') && !form.classList.contains('js-assignment-update') && !form.classList.contains('js-room-create')) return;
            e.preventDefault();
            hideAjaxAlert();

            const formData = new FormData(form);

            let response;
            try {
                response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
            } catch (err) {
                showAjaxAlert('danger', 'Gagal terhubung ke server');
                return;
            }

            if (!response.ok) {
                let msg = 'Terjadi kesalahan';
                try {
                    const data = await response.json();
                    if (data && data.message) msg = data.message;
                } catch (err) {}
                showAjaxAlert('danger', msg);
                return;
            }

            let data;
            try {
                data = await response.json();
            } catch (err) {
                showAjaxAlert('danger', 'Respon server tidak valid');
                return;
            }

            if (data.message) showAjaxAlert('success', data.message);

            if (form.classList.contains('js-room-create')) {
                if (data.roomHtml) {
                    const list = document.getElementById('roomCardList');
                    if (list) list.insertAdjacentHTML('afterbegin', data.roomHtml);
                }

                form.reset();
                const modalEl = document.getElementById('modalAddRoom');
                if (modalEl && window.bootstrap) {
                    const inst = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
                    inst.hide();
                }
                return;
            }

            if (form.classList.contains('js-assignment-create')) {
                if (data.cardHtml) {
                    const list = document.getElementById('assignmentList');
                    if (list) list.insertAdjacentHTML('afterbegin', data.cardHtml);
                }
                if (data.modalHtml) {
                    const modals = document.getElementById('assignmentModals');
                    if (modals) modals.insertAdjacentHTML('beforeend', data.modalHtml);
                }

                form.reset();
                const modalEl = document.getElementById('modalAddAssignment');
                if (modalEl && window.bootstrap) {
                    const inst = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
                    inst.hide();
                }
                return;
            }

            if (form.classList.contains('js-assignment-update')) {
                const assignmentId = form.getAttribute('data-assignmentid');
                if (assignmentId) {
                    const currentModalEl = document.getElementById(`editAssignment${assignmentId}`);
                    if (currentModalEl && window.bootstrap) {
                        const inst = window.bootstrap.Modal.getInstance(currentModalEl);
                        if (inst) inst.hide();
                    }

                    if (data.cardHtml) {
                        const card = document.getElementById(`assignment-card-${assignmentId}`);
                        if (card) card.outerHTML = data.cardHtml;
                    }
                    if (data.modalHtml) {
                        const modal = document.getElementById(`editAssignment${assignmentId}`);
                        if (modal) modal.outerHTML = data.modalHtml;
                    }
                }
            }
        });

        document.addEventListener('click', async function (e) {
            const btn = e.target instanceof HTMLElement ? e.target.closest('.js-assignment-delete, .js-room-delete') : null;
            if (!btn) return;
            e.preventDefault();
            hideAjaxAlert();

            const tokenInput = document.querySelector('input[name="_token"]');
            const token = tokenInput ? tokenInput.value : '';

            if (btn.classList.contains('js-assignment-delete')) {
                const assignmentId = btn.getAttribute('data-assignmentid');
                const url = btn.getAttribute('data-url');
                if (!assignmentId || !url) return;
                if (!confirm('Hapus tugas ini?')) return;

                const formData = new FormData();
                formData.append('_token', token);
                formData.append('assignmentid', assignmentId);

                let response;
                try {
                    response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                } catch (err) {
                    showAjaxAlert('danger', 'Gagal terhubung ke server');
                    return;
                }

                if (!response.ok) {
                    let msg = 'Terjadi kesalahan';
                    try {
                        const data = await response.json();
                        if (data && data.message) msg = data.message;
                    } catch (err) {}
                    showAjaxAlert('danger', msg);
                    return;
                }

                let data;
                try {
                    data = await response.json();
                } catch (err) {
                    showAjaxAlert('danger', 'Respon server tidak valid');
                    return;
                }

                if (data.message) showAjaxAlert('success', data.message);

                const currentModalEl = document.getElementById(`editAssignment${assignmentId}`);
                if (currentModalEl && window.bootstrap) {
                    const inst = window.bootstrap.Modal.getInstance(currentModalEl);
                    if (inst) inst.hide();
                }

                const card = document.getElementById(`assignment-card-${assignmentId}`);
                if (card) card.remove();
                const modal = document.getElementById(`editAssignment${assignmentId}`);
                if (modal) modal.remove();
                return;
            }

            if (btn.classList.contains('js-room-delete')) {
                const scheduleId = btn.getAttribute('data-scheduleid');
                const url = btn.getAttribute('data-url');
                if (!scheduleId || !url) return;
                if (!confirm('Hapus ruang mapel ini? Semua tugas di dalamnya akan ikut terhapus.')) return;

                const formData = new FormData();
                formData.append('_token', token);
                formData.append('scheduleid', scheduleId);

                let response;
                try {
                    response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                } catch (err) {
                    showAjaxAlert('danger', 'Gagal terhubung ke server');
                    return;
                }

                if (!response.ok) {
                    let msg = 'Terjadi kesalahan';
                    try {
                        const data = await response.json();
                        if (data && data.message) msg = data.message;
                    } catch (err) {}
                    showAjaxAlert('danger', msg);
                    return;
                }

                let data;
                try {
                    data = await response.json();
                } catch (err) {
                    showAjaxAlert('danger', 'Respon server tidak valid');
                    return;
                }

                if (data.message) showAjaxAlert('success', data.message);
                const card = document.getElementById(`room-card-${scheduleId}`);
                if (card) card.remove();
            }
        });
    });
</script>
@endif
