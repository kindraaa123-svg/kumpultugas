<div class="modal fade" id="editAssignment{{ $assignment->assignmentid }}" tabindex="-1" aria-labelledby="editAssignmentLabel{{ $assignment->assignmentid }}" aria-hidden="true" style="z-index:1060;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAssignmentLabel{{ $assignment->assignmentid }}">Edit Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('assignment.update') }}" method="POST" class="js-assignment-update" data-assignmentid="{{ $assignment->assignmentid }}">
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
                    <button type="button" class="btn btn-link text-danger js-assignment-delete" data-assignmentid="{{ $assignment->assignmentid }}" data-url="{{ route('assignment.delete.ajax') }}"><i class="zmdi zmdi-delete"></i> Hapus</button>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
