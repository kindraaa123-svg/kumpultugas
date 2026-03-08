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
                
                @if(session('level') == 3)
                    @if(in_array($assignment->assignmentid, $submittedIds ?? []))
                        <span class="badge bg-success">Sudah Dikerjakan</span>
                    @else
                        <span class="badge bg-warning text-dark">Belum Dikerjakan</span>
                    @endif
                @endif
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