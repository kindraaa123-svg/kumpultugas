<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="overview-wrap">
                            <h2 class="title-1">Tugas Saya</h2>
                            <div>
                                <button type="button" class="au-btn au-btn-icon au-btn--blue" data-bs-toggle="modal" data-bs-target="#modalAddAssignment">
                                    <i class="zmdi zmdi-plus"></i>Tambah Tugas
                                </button>
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
                
                <div class="row m-t-25">
                    @foreach($assignments as $assignment)
                    <div class="col-md-4">
                        <div class="card" style="border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s;">
                            <div style="background: linear-gradient(135deg, #4285f4, #34a853); padding: 20px; color: white;">
                                <h4 style="color: white; margin-bottom: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $assignment->name }}</h4>
                                <p style="color: rgba(255,255,255,0.8); margin: 0;">{{ $assignment->schedule->course->coursename }}</p>
                                <small style="color: rgba(255,255,255,0.7);">{{ $assignment->schedule->classroom->classname }}</small>
                            </div>
                            <div class="card-body">
                                <p class="card-text text-muted" style="height: 45px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                    {{ $assignment->description ?: 'Tidak ada deskripsi' }}
                                </p>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted"><i class="far fa-calendar-alt"></i> Deadline: {{ date('d M, H:i', strtotime($assignment->time_end)) }}</small>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 d-flex justify-content-between">
                                <a href="{{ route('assignment.show', $assignment->assignmentid) }}" class="btn btn-outline-primary btn-sm">Buka Tugas</a>
                                @if(session('level') != 3)
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editAssignment{{ $assignment->assignmentid }}">Detail</button>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('level') != 3)
@foreach($assignments as $assignment)
<div class="modal fade" id="editAssignment{{ $assignment->assignmentid }}" tabindex="-1" aria-labelledby="editAssignmentLabel{{ $assignment->assignmentid }}" aria-hidden="true" style="z-index:1060;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAssignmentLabel{{ $assignment->assignmentid }}">Edit Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('assignment.update') }}" method="POST">
                @csrf
                <input type="hidden" name="assignmentid" value="{{ $assignment->assignmentid }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name{{ $assignment->assignmentid }}" class="form-label">Nama Tugas</label>
                        <input type="text" name="name" id="name{{ $assignment->assignmentid }}" class="form-control" value="{{ $assignment->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="description{{ $assignment->assignmentid }}" class="form-label">Deskripsi</label>
                        <textarea name="description" id="description{{ $assignment->assignmentid }}" rows="3" class="form-control">{{ $assignment->description }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="grading_mode{{ $assignment->assignmentid }}" class="form-label">Mode Penilaian</label>
                        <select name="grading_mode" id="grading_mode{{ $assignment->assignmentid }}" class="form-control" required onchange="document.getElementById('auto-score-wrap-{{ $assignment->assignmentid }}').style.display = this.value === 'auto' ? 'block' : 'none'">
                            <option value="manual" {{ $assignment->grading_mode === 'manual' ? 'selected' : '' }}>Manual</option>
                            <option value="auto" {{ $assignment->grading_mode === 'auto' ? 'selected' : '' }}>Otomatis</option>
                        </select>
                    </div>
                    <div class="mb-3" id="auto-score-wrap-{{ $assignment->assignmentid }}" style="display: {{ $assignment->grading_mode === 'auto' ? 'block' : 'none' }};">
                        <label for="auto_score{{ $assignment->assignmentid }}" class="form-label">Nilai Otomatis</label>
                        <input type="number" name="auto_score" id="auto_score{{ $assignment->assignmentid }}" class="form-control" min="0" max="100" value="{{ $assignment->auto_score }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="time_start{{ $assignment->assignmentid }}" class="form-label">Mulai</label>
                            <input type="datetime-local" name="time_start" id="time_start{{ $assignment->assignmentid }}" class="form-control" value="{{ date('Y-m-d\\TH:i', strtotime($assignment->time_start)) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="time_end{{ $assignment->assignmentid }}" class="form-label">Selesai (Deadline)</label>
                            <input type="datetime-local" name="time_end" id="time_end{{ $assignment->assignmentid }}" class="form-control" value="{{ date('Y-m-d\\TH:i', strtotime($assignment->time_end)) }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <a href="{{ route('assignment.delete', $assignment->assignmentid) }}" class="btn btn-link text-danger" onclick="return confirm('Hapus tugas ini?')"><i class="zmdi zmdi-delete"></i> Hapus</a>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif

<!-- Modal Tambah Tugas -->
<div class="modal fade" id="modalAddAssignment" tabindex="-1" aria-labelledby="modalAddAssignmentLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddAssignmentLabel">Tambah Tugas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('assignment.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="scheduleid" class="form-label">Pilih Jadwal</label>
                        <select name="scheduleid" id="scheduleid" class="form-control" required>
                            <option value="">-- Pilih Jadwal --</option>
                            @foreach($schedules as $schedule)
                            <option value="{{ $schedule->scheduleid }}">{{ $schedule->classroom->classname }} - {{ $schedule->course->coursename }} (Sesi {{ $schedule->session }})</option>
                            @endforeach
                        </select>
                    </div>
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
                        <small class="text-muted">Jika Mode Penilaian = Otomatis, siswa akan mendapat nilai ini saat mengumpulkan.</small>
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
