<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="overview-wrap">
                            <h2 class="title-1">Review Pengumpulan Tugas</h2>
                            <a href="{{ route('assignment.index') }}" class="btn btn-link"><i class="fas fa-arrow-left"></i> Kembali</a>
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

                <div class="row m-t-25">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Pilih Kelas</div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('assignment.review') }}">
                                    <div class="mb-3">
                                        <select name="classid" class="form-control" onchange="this.form.submit()">
                                            <option value="">-- Pilih Kelas --</option>
                                            @foreach($classes as $c)
                                            <option value="{{ $c->classid }}" {{ $classid == $c->classid ? 'selected' : '' }}>{{ $c->classname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>
                                @if($classid)
                                <form method="GET" action="{{ route('assignment.review') }}">
                                    <input type="hidden" name="classid" value="{{ $classid }}">
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Tugas</label>
                                        <select name="assignmentid" class="form-control" onchange="this.form.submit()">
                                            <option value="">-- Pilih Tugas --</option>
                                            @foreach($assignments as $a)
                                            <option value="{{ $a->assignmentid }}" {{ $assignmentid == $a->assignmentid ? 'selected' : '' }}>
                                                {{ $a->name }} ({{ $a->coursename }}) - Deadline: {{ date('d M, H:i', strtotime($a->time_end)) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($classid && $assignmentid)
                <div class="row m-t-25">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Rekap Pengumpulan</strong>
                                    <div class="small text-muted">Kelas: 
                                        @php $cname = optional($classes->firstWhere('classid', (int)$classid))->classname; @endphp
                                        {{ $cname ?: 'Unknown' }}
                                    </div>
                                </div>
                                <div class="small">
                                    <span class="badge bg-success">Sudah: {{ count($submittedIds) }}</span>
                                    <span class="badge bg-danger">Belum: {{ count($students) - count($submittedIds) }}</span>
                                </div>
                            </div>
                            <div class="card-body">
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
                                                            <span class="text-danger">Belum</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($q && $q->score !== null)
                                                            {{ $q->score }}
                                                        @else
                                                            -
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
                                                                        @php $mid = 'filePreview'.$s->studentid.$idx; @endphp
                                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#{{ $mid }}">{{ basename($fp) }}</button>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                @php $mid = 'filePreview'.$s->studentid.'0'; @endphp
                                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#{{ $mid }}">{{ basename($q->file) }}</button>
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
                
                @if($classid && $assignmentid)
                @foreach($students as $s)
                    @php $q = $submissions->get($s->studentid); @endphp
                    @if($q)
                        @php
                            $files = null;
                            try {
                                $decoded = json_decode($q->file, true);
                                if (is_array($decoded)) $files = $decoded;
                            } catch (\Throwable $e) {}
                        @endphp
                        @if($files)
                            @foreach($files as $idx => $fp)
                                @php
                                    $ext = strtolower(pathinfo($fp, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                    $mid = 'filePreview'.$s->studentid.$idx;
                                @endphp
                                <div class="modal fade" id="{{ $mid }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ basename($fp) }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                @if($isImage)
                                                    <img src="{{ asset('storage/' . $fp) }}" class="img-fluid" alt="{{ basename($fp) }}">
                                                @else
                                                    <p class="mb-2">Pratinjau tidak tersedia. Silakan unduh file.</p>
                                                    <a href="{{ asset('storage/' . $fp) }}" class="btn btn-primary" download>Unduh</a>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            @php
                                $ext = strtolower(pathinfo($q->file, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                $mid = 'filePreview'.$s->studentid.'0';
                            @endphp
                            <div class="modal fade" id="{{ $mid }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ basename($q->file) }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            @if($isImage)
                                                <img src="{{ asset('storage/' . $q->file) }}" class="img-fluid" alt="{{ basename($q->file) }}">
                                            @else
                                                <p class="mb-2">Pratinjau tidak tersedia. Silakan unduh file.</p>
                                                <a href="{{ asset('storage/' . $q->file) }}" class="btn btn-primary" download>Unduh</a>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
