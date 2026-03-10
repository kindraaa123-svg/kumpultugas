<a href="{{ route('assignment.index', ['course_id' => $course->courseid, 'block_id' => $course->block_id, 'academic_year_id' => $course->academic_year_id]) }}" style="text-decoration:none; display:block;">
    <div class="card mb-3" style="border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.08);">
        <div style="background: linear-gradient(135deg, #6f42c1, #0d6efd); padding: 16px; color: white;">
            <h4 style="color: white; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $course->coursename }}
            </h4>
            <div style="color: rgba(255,255,255,0.85); font-size: 13px;">
                <div>Blok: {{ $course->block_name ?? ('Blok #' . ($course->block_id ?? '-')) }}</div>
                <div>Tahun Ajaran: {{ $course->academic_year_name ?? '-' }}</div>
                <div>Guru: {{ $course->teacher_names ?: '-' }}</div>
                <div>Total tugas: {{ $course->total }}</div>
            </div>
        </div>
    </div>
</a>
