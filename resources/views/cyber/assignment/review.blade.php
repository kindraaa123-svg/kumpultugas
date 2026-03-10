<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="overview-wrap">
                            <h2 class="title-1">Review Pengumpulan Tugas</h2>
                            @if(!empty($selectedGroup) && !empty($assignmentid))
                                <a href="{{ route('assignment.review', ['classid' => $classid, 'courseid' => $courseid, 'block_id' => $blockId, 'academic_year_id' => $academicYearId]) }}" class="btn btn-link"><i class="fas fa-arrow-left"></i> Kembali</a>
                            @elseif(!empty($selectedGroup))
                                <a href="{{ route('assignment.review') }}" class="btn btn-link"><i class="fas fa-arrow-left"></i> Kembali</a>
                            @else
                                <a href="{{ route('assignment.index') }}" class="btn btn-link"><i class="fas fa-arrow-left"></i> Kembali</a>
                            @endif
                        </div>
                    </div>
                </div>

                @if(session('error'))
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    </div>
                </div>
                @endif

                @if(empty($selectedGroup))
                    <div class="row m-t-25">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">Filter</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="reviewFilterClass" class="form-label">Kelas</label>
                                            <select id="reviewFilterClass" class="form-control">
                                                <option value="all">Semua Kelas</option>
                                                @foreach($filterClasses as $c)
                                                    <option value="{{ $c->classid }}">{{ $c->classname }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="reviewFilterBlock" class="form-label">Blok</label>
                                            <select id="reviewFilterBlock" class="form-control">
                                                <option value="all">Semua Blok</option>
                                                @foreach($filterBlocks as $b)
                                                    <option value="{{ $b->block_id }}">{{ $b->block_name ?: ('Blok #' . ($b->block_id ?? '-')) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="reviewFilterYear" class="form-label">Tahun Ajaran</label>
                                            <select id="reviewFilterYear" class="form-control">
                                                <option value="all">Semua Tahun Ajaran</option>
                                                @foreach($filterAcademicYears as $ay)
                                                    <option value="{{ $ay->academic_year_id }}">{{ $ay->academic_year_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row m-t-25">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">Daftar Kelas / Mapel / Blok</div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Kelas</th>
                                                    <th>Mata Pelajaran</th>
                                                    <th>Blok</th>
                                                    <th>Tahun Ajaran</th>
                                                    <th>Total Tugas</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="reviewGroupBody">
                                                @include('cyber.assignment.review_group_rows', ['scheduleGroups' => $scheduleGroups])
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const classSel = document.getElementById('reviewFilterClass');
                            const blockSel = document.getElementById('reviewFilterBlock');
                            const yearSel = document.getElementById('reviewFilterYear');

                            function fetchGroups() {
                                const url = new URL(`{{ route('assignment.review.filter') }}`, window.location.origin);
                                url.searchParams.set('classid', classSel ? classSel.value : 'all');
                                url.searchParams.set('block_id', blockSel ? blockSel.value : 'all');
                                url.searchParams.set('academic_year_id', yearSel ? yearSel.value : 'all');

                                fetch(url.toString())
                                    .then(r => r.json())
                                    .then(data => {
                                        const body = document.getElementById('reviewGroupBody');
                                        if (body) body.innerHTML = data.html || '';
                                    })
                                    .catch(err => console.error(err));
                            }

                            if (classSel) classSel.addEventListener('change', fetchGroups);
                            if (blockSel) blockSel.addEventListener('change', fetchGroups);
                            if (yearSel) yearSel.addEventListener('change', fetchGroups);
                        });
                    </script>
                @elseif(empty($assignmentid))
                    <div class="row m-t-25">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $selectedGroup->classname }}</strong>
                                        <div class="small text-muted">
                                            {{ $selectedGroup->coursename }} • {{ $selectedGroup->block_name ?: ('Blok #' . ($selectedGroup->block_id ?? '-')) }} • {{ $selectedGroup->academic_year_name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($assignments as $a)
                                            <div class="col-md-4">
                                                <a href="{{ route('assignment.review', ['classid' => $classid, 'courseid' => $courseid, 'block_id' => $blockId, 'academic_year_id' => $academicYearId, 'assignmentid' => $a->assignmentid]) }}" style="text-decoration:none;">
                                                    <div class="card mb-3" style="border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.08);">
                                                        <div style="background: linear-gradient(135deg, #198754, #0dcaf0); padding: 16px; color: white;">
                                                            <h5 style="color: white; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                                {{ $a->name }}
                                                            </h5>
                                                            <div style="color: rgba(255,255,255,0.85); font-size: 13px;">
                                                                <div>Deadline: {{ date('d M, H:i', strtotime($a->time_end)) }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                        @if($assignments->isEmpty())
                                            <div class="col-md-12">
                                                <div class="alert alert-info mb-0">Belum ada tugas untuk pilihan ini.</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if(!empty($selectedGroup) && $assignmentid)
                <div class="row m-t-25">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Rekap Pengumpulan</strong>
                                    <div class="small text-muted">
                                        {{ $selectedGroup->classname }} • {{ $selectedGroup->coursename }} • {{ $selectedGroup->block_name ?: ('Blok #' . ($selectedGroup->block_id ?? '-')) }} • {{ $selectedGroup->academic_year_name }}
                                    </div>
                                </div>
                                <div class="small">
                                    <span class="badge bg-success">Sudah: {{ count($submittedIds) }}</span>
                                    <span class="badge bg-danger">Belum: {{ count($students) - count($submittedIds) }}</span>
                                </div>
                            </div>
                            <div class="card-body">
                                @php
                                    $deadlinePassed = false;
                                    if (!empty($selectedAssignment) && !empty($selectedAssignment->time_end)) {
                                        $deadlinePassed = now()->gt(\Carbon\Carbon::parse($selectedAssignment->time_end));
                                    }
                                @endphp
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Nama Siswa</th>
                                                <th>Status</th>
                                                <th>Nilai</th>
                                                <th>Waktu Pengumpulan</th>
                                                <th>File</th>
                                                <th>Deskripsi</th>
                                                <th>Aksi Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($students as $s)
                                                @php $q = $submissions->get($s->studentid); @endphp
                                                <tr>
                                                    <td>{{ $s->name }}</td>
                                                    <td>
                                                        @if($q)
                                                            <span class="text-success">Sudah</span>
                                                        @else
                                                            @if($deadlinePassed)
                                                                <span class="text-danger">Tidak mengerjakan (melewati deadline)</span>
                                                            @else
                                                                <span class="text-danger">Belum</span>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($q && $q->score !== null)
                                                            {{ $q->score }}
                                                        @else
                                                            @if(!$q && $deadlinePassed)
                                                                <span class="text-muted"></span>
                                                            @else
                                                                -
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($q)
                                                            {{ $q->updated_at ? date('d M Y, H:i', strtotime($q->updated_at)) : ($q->created_at ? date('d M Y, H:i', strtotime($q->created_at)) : '-') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="small">
                                                        @if($q)
                                                            @php
                                                                $files = null;
                                                                try {
                                                                    $decoded = json_decode($q->file, true);
                                                                    if (is_array($decoded)) $files = $decoded;
                                                                } catch (\Throwable $e) {}
                                                            @endphp
                                                            @if($files)
                                                                <div class="d-flex flex-wrap gap-1">
                                                                    @foreach($files as $idx => $fp)
                                                                        @php
                                                                            $ext = strtolower(pathinfo($fp, PATHINFO_EXTENSION));
                                                                            $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                                                        @endphp
                                                                        <button
                                                                            type="button"
                                                                            class="btn btn-sm btn-outline-primary js-file-preview"
                                                                            data-file-url="{{ asset('storage/' . $fp) }}"
                                                                            data-file-name="{{ basename($fp) }}"
                                                                            data-is-image="{{ $isImage ? '1' : '0' }}"
                                                                        >{{ basename($fp) }}</button>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                @php
                                                                    $ext = strtolower(pathinfo($q->file, PATHINFO_EXTENSION));
                                                                    $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                                                @endphp
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-sm btn-outline-primary js-file-preview"
                                                                    data-file-url="{{ asset('storage/' . $q->file) }}"
                                                                    data-file-name="{{ basename($q->file) }}"
                                                                    data-is-image="{{ $isImage ? '1' : '0' }}"
                                                                >{{ basename($q->file) }}</button>
                                                            @endif
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="small">
                                                        @if($q && $q->description)
                                                            {{ $q->description }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td style="min-width: 220px;">
                                                        @if($q)
                                                        <form method="POST" action="{{ route('assignment.grade') }}" class="d-flex align-items-center gap-2">
                                                            @csrf
                                                            <input type="hidden" name="assignmentid" value="{{ $assignmentid }}">
                                                            <input type="hidden" name="studentid" value="{{ $s->studentid }}">
                                                            <input type="number" name="score" class="form-control form-control-sm" min="0" max="100" placeholder="Nilai" value="{{ $q->score }}">
                                                            <input type="text" name="feedback" class="form-control form-control-sm" placeholder="Feedback" value="{{ $q->feedback }}">
                                                            <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                                        </form>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="filePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filePreviewModalTitle">File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="filePreviewImg" src="" class="img-fluid" alt="" style="display:none;">
                <p id="filePreviewText" class="mb-2" style="display:none;">Pratinjau tidak tersedia. Silakan unduh file.</p>
                <a id="filePreviewDownload" href="#" class="btn btn-primary" download style="display:none;">Unduh</a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('filePreviewModal');
        if (!modalEl || !window.bootstrap) return;

        document.body.appendChild(modalEl);

        const modal = new window.bootstrap.Modal(modalEl, { backdrop: true, keyboard: true, focus: true });
        const titleEl = document.getElementById('filePreviewModalTitle');
        const imgEl = document.getElementById('filePreviewImg');
        const textEl = document.getElementById('filePreviewText');
        const dlEl = document.getElementById('filePreviewDownload');

        modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                modal.hide();
            });
        });

        function showImage(name, url) {
            if (titleEl) titleEl.textContent = name || 'File';
            if (imgEl) {
                imgEl.src = url || '';
                imgEl.alt = name || '';
                imgEl.style.display = '';
            }
            if (textEl) textEl.style.display = 'none';
            if (dlEl) {
                dlEl.href = url || '#';
                dlEl.style.display = 'none';
            }
        }

        function showDownload(name, url) {
            if (titleEl) titleEl.textContent = name || 'File';
            if (imgEl) {
                imgEl.src = '';
                imgEl.alt = '';
                imgEl.style.display = 'none';
            }
            if (textEl) textEl.style.display = '';
            if (dlEl) {
                dlEl.href = url || '#';
                dlEl.setAttribute('download', name || '');
                dlEl.style.display = '';
            }
        }

        document.addEventListener('click', function (e) {
            const btn = e.target instanceof HTMLElement ? e.target.closest('.js-file-preview') : null;
            if (!btn) return;

            e.preventDefault();
            const url = btn.getAttribute('data-file-url') || '';
            const name = btn.getAttribute('data-file-name') || 'File';
            const isImage = btn.getAttribute('data-is-image') === '1';

            if (isImage) {
                showImage(name, url);
            } else {
                showDownload(name, url);
            }

            modal.show();
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            if (imgEl) {
                imgEl.src = '';
                imgEl.alt = '';
            }
            if (dlEl) dlEl.href = '#';

            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
            document.querySelectorAll('.modal-backdrop').forEach(function (bd) {
                bd.remove();
            });
        });
    });
</script>
