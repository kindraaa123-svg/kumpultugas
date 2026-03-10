<div class="page-container">
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
                    <div class="col-md-12" id="classTableWrapper">
                        @include('all.partials.class_table', ['class' => $class])
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('classTableWrapper');

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
