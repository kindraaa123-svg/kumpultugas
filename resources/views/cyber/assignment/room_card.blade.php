<div id="room-card-{{ $room->scheduleid }}">
    <div class="card mb-3" style="border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.08);">
        <div style="background: linear-gradient(135deg, #6f42c1, #0d6efd); padding: 16px; color: white; position: relative;">
            @if(session('level') == 2 && strtolower((string) session('role')) == 'guru')
                <button type="button" class="btn btn-sm btn-light js-room-delete" data-scheduleid="{{ $room->scheduleid }}" data-url="{{ route('assignment.room.delete') }}" style="position:absolute; right:12px; top:12px;">
                    Hapus
                </button>
            @endif
            <a href="{{ route('assignment.index', ['scheduleid' => $room->scheduleid, 'academic_year_id' => $room->academic_year_id, 'block_id' => $room->block_id]) }}" style="text-decoration:none; display:block; color:inherit;">
                <h4 style="color: white; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $room->coursename }}
                </h4>
                <div style="color: rgba(255,255,255,0.85); font-size: 13px;">
                    <div>Kelas: {{ $room->classname ?: '-' }} (Sesi {{ $room->session ?? '-' }})</div>
                    <div>Blok: {{ $room->block_name ?? ('Blok #' . ($room->block_id ?? '-')) }}</div>
                    <div>Tahun Ajaran: {{ $room->academic_year_name ?? '-' }}</div>
                    <div>Guru: {{ $room->teacher_name ?: '-' }}</div>
                    <div>Total tugas: {{ $room->total_tasks }}</div>
                </div>
            </a>
        </div>
    </div>
</div>
