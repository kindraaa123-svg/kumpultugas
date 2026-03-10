@foreach($scheduleGroups as $g)
    <tr>
        <td>{{ $g->classname ?: '-' }}</td>
        <td>{{ $g->coursename ?: '-' }}</td>
        <td>{{ $g->block_name ?: ('Blok #' . ($g->block_id ?? '-')) }}</td>
        <td>
            {{ $g->academic_year_name ?: '-' }}
            @if($g->academic_year_start && $g->academic_year_end)
                <div class="small text-muted">{{ date('d M Y', strtotime($g->academic_year_start)) }} - {{ date('d M Y', strtotime($g->academic_year_end)) }}</div>
            @endif
        </td>
        <td>{{ $g->total_assignments }}</td>
        <td>
            <a class="btn btn-sm btn-primary" href="{{ route('assignment.review', ['classid' => $g->classid, 'courseid' => $g->courseid, 'block_id' => $g->block_id, 'academic_year_id' => $g->academic_year_id]) }}">
                Lihat Tugas
            </a>
        </td>
    </tr>
@endforeach

