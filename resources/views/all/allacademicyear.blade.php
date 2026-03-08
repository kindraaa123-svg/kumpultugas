<div class="page-container">


    <!-- MAIN CONTENT-->
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="overview-wrap">
                            <h2 class="title-1">Data Tahun Ajaran</h2>
                            <button class="au-btn au-btn-icon au-btn--blue" data-bs-toggle="modal" data-bs-target="#modalAddAcademicYear">
                                <i class="zmdi zmdi-plus"></i>Tambah Tahun Ajaran</button>
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
                                        <th>Tahun Ajaran</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($academic_year as $ay)
                                    <tr>
                                        <td>{{ $ay->academic_year_id }}</td>
                                        <td>{{ $ay->name }}</td>
                                        <td>{{ $ay->start_date }}</td>
                                        <td>{{ $ay->end_date }}</td>
                                        <td>
                                            @if($ay->is_active)
                                                Aktif
                                            @else
                                                Tidak Aktif
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditAcademicYear{{ $ay->academic_year_id }}">
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

<!-- Modal Add Academic Year -->
<div class="modal fade" id="modalAddAcademicYear" tabindex="-1" aria-labelledby="modalAddAcademicYearLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddAcademicYearLabel">Tambah Tahun Ajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('academicyear.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name" class="control-label mb-1">Nama Tahun Ajaran</label>
                        <input id="name" name="name" type="text" class="form-control" placeholder="YYYY/YYYY" required>
                    </div>
                    <div class="form-group">
                        <label for="start_date" class="control-label mb-1">Tanggal Mulai</label>
                        <input id="start_date" name="start_date" type="date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date" class="control-label mb-1">Tanggal Selesai</label>
                        <input id="end_date" name="end_date" type="date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="is_active" class="control-label mb-1">Status</label>
                        <select name="is_active" id="is_active" class="form-control">
                            <option value="0">Tidak Aktif</option>
                            <option value="1">Aktif</option>
                        </select>
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

<!-- Modal Edit Academic Year Loop -->
@foreach($academic_year as $ay)
<div class="modal fade" id="modalEditAcademicYear{{ $ay->academic_year_id }}" tabindex="-1" aria-labelledby="modalEditAcademicYearLabel{{ $ay->academic_year_id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditAcademicYearLabel{{ $ay->academic_year_id }}">Edit Tahun Ajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('academicyear.update') }}" method="POST">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $ay->academic_year_id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name{{ $ay->academic_year_id }}" class="control-label mb-1">Nama Tahun Ajaran</label>
                        <input id="name{{ $ay->academic_year_id }}" name="name" type="text" class="form-control" value="{{ $ay->name }}" required>
                    </div>
                    <div class="form-group">
                        <label for="start_date{{ $ay->academic_year_id }}" class="control-label mb-1">Tanggal Mulai</label>
                        <input id="start_date{{ $ay->academic_year_id }}" name="start_date" type="date" class="form-control" value="{{ $ay->start_date }}" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date{{ $ay->academic_year_id }}" class="control-label mb-1">Tanggal Selesai</label>
                        <input id="end_date{{ $ay->academic_year_id }}" name="end_date" type="date" class="form-control" value="{{ $ay->end_date }}" required>
                    </div>
                    <div class="form-group">
                        <label for="is_active{{ $ay->academic_year_id }}" class="control-label mb-1">Status</label>
                        <select name="is_active" id="is_active{{ $ay->academic_year_id }}" class="form-control">
                            <option value="0" {{ $ay->is_active == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                            <option value="1" {{ $ay->is_active == 1 ? 'selected' : '' }}>Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('academicyear.delete', $ay->academic_year_id) }}" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
