<div class="table-responsive table--no-card m-b-30">
    <table class="table table-borderless table-striped table-earning">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Kelas</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($class as $c)
                <tr>
                    <td>{{ $c->classid }}</td>
                    <td>{{ $c->classname }}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditClass{{ $c->classid }}">
                            Detail
                        </button>
                    </td>
                </tr>
            @endforeach
            @if($class->count() === 0)
                <tr>
                    <td colspan="3">Belum ada data.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-end">
    {{ $class->links() }}
</div>

@foreach($class as $c)
<div class="modal fade" id="modalEditClass{{ $c->classid }}" tabindex="-1" aria-labelledby="modalEditClassLabel{{ $c->classid }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditClassLabel{{ $c->classid }}">Edit Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('class.update') }}" method="POST">
                @csrf
                <input type="hidden" name="classid" value="{{ $c->classid }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="classname{{ $c->classid }}" class="control-label mb-1">Nama Kelas</label>
                        <input id="classname{{ $c->classid }}" name="classname" type="text" class="form-control" value="{{ $c->classname }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('class.delete', $c->classid) }}" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
