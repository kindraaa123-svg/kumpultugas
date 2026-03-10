<div class="page-container">
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
                    <div class="col-md-12" id="academicYearTableWrapper">
                        @include('all.partials.academic_year_table', ['academic_year' => $academic_year])
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('academicYearTableWrapper');

    async function loadPage(url) {
        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            if (data && data.html) {
                wrapper.innerHTML = data.html;
            }
        } catch (e) {
            window.location.href = url;
        }
    }

    wrapper.addEventListener('click', function (event) {
        const link = event.target.closest('.pagination a');
        if (!link) return;
        event.preventDefault();
        loadPage(link.href);
        window.history.replaceState({}, '', link.href);
    });
});
</script>
