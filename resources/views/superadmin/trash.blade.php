<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="overview-wrap">
                            <h2 class="title-1">Tong Sampah</h2>
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
                        <div class="d-flex justify-content-end align-items-end m-b-15" style="gap: 12px;">
                            <div class="form-group mb-0" style="min-width: 220px;">
                                <label for="actionFilter" class="mb-1">Filter Aksi</label>
                                <select id="actionFilter" class="form-control">
                                    <option value="all" {{ ($actionFilter ?? 'all') === 'all' ? 'selected' : '' }}>Semua</option>
                                    <option value="edit" {{ ($actionFilter ?? 'all') === 'edit' ? 'selected' : '' }}>Edit</option>
                                    <option value="delete" {{ ($actionFilter ?? 'all') === 'delete' ? 'selected' : '' }}>Delete</option>
                                </select>
                            </div>
                            <div class="form-group mb-0" style="min-width: 220px;">
                                <label for="roleFilter" class="mb-1">Filter Role</label>
                                <select id="roleFilter" class="form-control">
                                    <option value="all" {{ ($roleFilter ?? 'all') === 'all' ? 'selected' : '' }}>Semua</option>
                                    <option value="admin" {{ ($roleFilter ?? 'all') === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="superadmin" {{ ($roleFilter ?? 'all') === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                                    <option value="kurikulum" {{ ($roleFilter ?? 'all') === 'kurikulum' ? 'selected' : '' }}>Kurikulum</option>
                                    <option value="guru" {{ ($roleFilter ?? 'all') === 'guru' ? 'selected' : '' }}>Guru</option>
                                    <option value="siswa" {{ ($roleFilter ?? 'all') === 'siswa' ? 'selected' : '' }}>Siswa</option>
                                </select>
                            </div>
                        </div>

                        <div id="trashTableWrapper">
                            @include('superadmin.partials.trash_table', ['logs' => $logs])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterEl = document.getElementById('actionFilter');
    const roleEl = document.getElementById('roleFilter');
    const wrapper = document.getElementById('trashTableWrapper');
    const actionStorageKey = 'trash_action_filter';
    const roleStorageKey = 'trash_role_filter';

    const currentUrl = new URL(window.location.href);
    const actionFromUrl = currentUrl.searchParams.get('action_filter');
    const roleFromUrl = currentUrl.searchParams.get('role_filter');
    const actionFromStorage = localStorage.getItem(actionStorageKey);
    const roleFromStorage = localStorage.getItem(roleStorageKey);
    let shouldReloadFromStorage = false;

    if (!actionFromUrl && actionFromStorage) {
        filterEl.value = actionFromStorage;
        currentUrl.searchParams.set('action_filter', actionFromStorage);
        shouldReloadFromStorage = true;
    }
    if (!roleFromUrl && roleFromStorage) {
        roleEl.value = roleFromStorage;
        currentUrl.searchParams.set('role_filter', roleFromStorage);
        shouldReloadFromStorage = true;
    }
    window.history.replaceState({}, '', currentUrl.toString());
    localStorage.setItem(actionStorageKey, filterEl.value);
    localStorage.setItem(roleStorageKey, roleEl.value);

    async function loadTrash(url) {
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

    function buildUrl(baseUrl) {
        const url = new URL(baseUrl, window.location.origin);
        url.searchParams.set('action_filter', filterEl.value);
        url.searchParams.set('role_filter', roleEl.value);
        return url.toString();
    }

    function reloadFromFilters() {
        localStorage.setItem(actionStorageKey, filterEl.value);
        localStorage.setItem(roleStorageKey, roleEl.value);
        const url = new URL('{{ route('trash.index') }}', window.location.origin);
        const finalUrl = buildUrl(url.toString());
        loadTrash(finalUrl);
        window.history.replaceState({}, '', finalUrl);
    }

    filterEl.addEventListener('change', reloadFromFilters);
    roleEl.addEventListener('change', reloadFromFilters);

    wrapper.addEventListener('click', function (event) {
        const link = event.target.closest('.pagination a');
        if (!link) return;
        event.preventDefault();
        const finalUrl = buildUrl(link.href);
        loadTrash(finalUrl);
        window.history.replaceState({}, '', finalUrl);
    });

    if (shouldReloadFromStorage) {
        loadTrash(currentUrl.toString());
    }
});
</script>
