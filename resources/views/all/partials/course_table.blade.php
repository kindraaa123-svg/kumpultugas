<div class="table-responsive table--no-card m-b-30">
    <table class="table table-borderless table-striped table-earning">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Mata Pelajaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($course as $c)
                <tr>
                    <td>{{ $c->courseid }}</td>
                    <td>{{ $c->coursename }}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditCourse{{ $c->courseid }}">
                            Detail
                        </button>
                    </td>
                </tr>
            @endforeach
            @if($course->count() === 0)
                <tr>
                    <td colspan="3">Belum ada data.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-end">
    {{ $course->links() }}
</div>

@foreach($course as $c)
<div class="modal fade" id="modalEditCourse{{ $c->courseid }}" tabindex="-1" aria-labelledby="modalEditCourseLabel{{ $c->courseid }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditCourseLabel{{ $c->courseid }}">Edit Mata Pelajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('course.update') }}" method="POST">
                @csrf
                <input type="hidden" name="courseid" value="{{ $c->courseid }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="coursename{{ $c->courseid }}" class="control-label mb-1">Nama Mata Pelajaran</label>
                        <input id="coursename{{ $c->courseid }}" name="coursename" type="text" class="form-control" value="{{ $c->coursename }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('course.delete', $c->courseid) }}" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
