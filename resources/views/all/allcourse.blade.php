<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="overview-wrap">
                            <h2 class="title-1">Data Mata Pelajaran</h2>
                            <button class="au-btn au-btn-icon au-btn--blue" data-bs-toggle="modal" data-bs-target="#modalAddCourse">
                                <i class="zmdi zmdi-plus"></i>Tambah Mapel</button>
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
                    <div class="col-md-12" id="courseTableWrapper">
                        @include('all.partials.course_table', ['course' => $course])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Course -->
<div class="modal fade" id="modalAddCourse" tabindex="-1" aria-labelledby="modalAddCourseLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddCourseLabel">Tambah Mata Pelajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('course.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="coursename" class="control-label mb-1">Nama Mata Pelajaran</label>
                        <input id="coursename" name="coursename" type="text" class="form-control" required>
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
    const wrapper = document.getElementById('courseTableWrapper');

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
