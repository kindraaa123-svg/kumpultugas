<div class="page-container">


    <!-- MAIN CONTENT-->
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="overview-wrap">
                            <h2 class="title-1">Data Blok</h2>
                            <button class="au-btn au-btn-icon au-btn--blue" data-bs-toggle="modal" data-bs-target="#modalAddBlock">
                                <i class="zmdi zmdi-plus"></i>Tambah Blok</button>
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
                                        <th>Nama Blok</th>
                                        <th>Tahun Ajaran</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($block as $b)
                                    <tr>
                                        <td>{{ $b->block_id }}</td>
                                        <td>{{ $b->name }}</td>
                                        <td>{{ $b->academic_year_name }}</td>
                                        <td>{{ $b->date_start }}</td>
                                        <td>{{ $b->date_end }}</td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditBlock{{ $b->block_id }}">
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

<!-- Modal Add Block -->
<div class="modal fade" id="modalAddBlock" tabindex="-1" aria-labelledby="modalAddBlockLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddBlockLabel">Tambah Blok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('block.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="academic_year_id" class="control-label mb-1">Tahun Ajaran</label>
                        <select name="academic_year_id" id="academic_year_id" class="form-control" required>
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach($years as $y)
                                <option value="{{ $y->academic_year_id }}">{{ $y->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name" class="control-label mb-1">Nama Blok</label>
                        <input id="name" name="name" type="text" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="date_start" class="control-label mb-1">Tanggal Mulai</label>
                        <input id="date_start" name="date_start" type="date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="date_end" class="control-label mb-1">Tanggal Selesai</label>
                        <input id="date_end" name="date_end" type="date" class="form-control" required>
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

<!-- Modal Edit Block Loop -->
@foreach($block as $b)
<div class="modal fade" id="modalEditBlock{{ $b->block_id }}" tabindex="-1" aria-labelledby="modalEditBlockLabel{{ $b->block_id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditBlockLabel{{ $b->block_id }}">Edit Blok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('block.update') }}" method="POST">
                @csrf
                <input type="hidden" name="block_id" value="{{ $b->block_id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="academic_year_id{{ $b->block_id }}" class="control-label mb-1">Tahun Ajaran</label>
                        <select name="academic_year_id" id="academic_year_id{{ $b->block_id }}" class="form-control" required>
                            @foreach($years as $y)
                                <option value="{{ $y->academic_year_id }}" {{ $b->academic_year_id == $y->academic_year_id ? 'selected' : '' }}>{{ $y->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name{{ $b->block_id }}" class="control-label mb-1">Nama Blok</label>
                        <input id="name{{ $b->block_id }}" name="name" type="text" class="form-control" value="{{ $b->name }}" required>
                    </div>
                    <div class="form-group">
                        <label for="date_start{{ $b->block_id }}" class="control-label mb-1">Tanggal Mulai</label>
                        <input id="date_start{{ $b->block_id }}" name="date_start" type="date" class="form-control" value="{{ $b->date_start }}" required>
                    </div>
                    <div class="form-group">
                        <label for="date_end{{ $b->block_id }}" class="control-label mb-1">Tanggal Selesai</label>
                        <input id="date_end{{ $b->block_id }}" name="date_end" type="date" class="form-control" value="{{ $b->date_end }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('block.delete', $b->block_id) }}" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
