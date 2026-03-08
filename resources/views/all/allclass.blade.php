<div class="page-container">


    <!-- MAIN CONTENT-->
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="overview-wrap">
                            <h2 class="title-1">Data Kelas</h2>
                            <button class="au-btn au-btn-icon au-btn--blue" data-bs-toggle="modal" data-bs-target="#modalAddClass">
                                <i class="zmdi zmdi-plus"></i>Tambah Kelas</button>
                        </div>
                    </div>
                </div>
                
                @if(session('success'))
                    <div class="alert alert-success m-t-25">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger m-t-25">{{ session('error') }}</div>
                @endif

                <div class="row m-t-25">
                    <div class="col-md-12">
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
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Class -->
<div class="modal fade" id="modalAddClass" tabindex="-1" aria-labelledby="modalAddClassLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddClassLabel">Tambah Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('class.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="classname" class="control-label mb-1">Nama Kelas</label>
                        <input id="classname" name="classname" type="text" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Class Loop -->
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
