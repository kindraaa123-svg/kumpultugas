<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="overview-wrap">
                            <h2 class="title-1">Activity Logs</h2>
                        </div>
                    </div>
                </div>

                <div class="row m-t-25">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-end align-items-end m-b-15" style="gap: 12px;">
                            <div class="form-group mb-0" style="min-width: 220px;">
                                <label for="activityRoleFilter" class="mb-1">Filter Role</label>
                                <select id="activityRoleFilter" class="form-control">
                                    <option value="all" {{ ($roleFilter ?? 'all') === 'all' ? 'selected' : '' }}>Semua</option>
                                    <option value="admin" {{ ($roleFilter ?? 'all') === 'admin' ? 'selected' : '' }}>Admin</option>
                                    @if(!empty($canViewSuperadmin))
                                        <option value="superadmin" {{ ($roleFilter ?? 'all') === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                                    @endif
                                    <option value="kurikulum" {{ ($roleFilter ?? 'all') === 'kurikulum' ? 'selected' : '' }}>Kurikulum</option>
                                    <option value="guru" {{ ($roleFilter ?? 'all') === 'guru' ? 'selected' : '' }}>Guru</option>
                                    <option value="siswa" {{ ($roleFilter ?? 'all') === 'siswa' ? 'selected' : '' }}>Siswa</option>
                                </select>
                            </div>
                        </div>

                        <div id="activityTableWrapper">
                            @include('superadmin.partials.activity_log_table', ['logs' => $logs])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleEl = document.getElementById('activityRoleFilter');
    const wrapper = document.getElementById('activityTableWrapper');
    const storageKey = 'activity_role_filter';

    const urlState = new URL(window.location.href);
    const roleFromUrl = urlState.searchParams.get('role_filter');
    const roleFromStorage = localStorage.getItem(storageKey);
    let shouldReloadFromStorage = false;

    if (!roleFromUrl && roleFromStorage) {
        roleEl.value = roleFromStorage;
        if (!roleEl.value) {
            roleEl.value = 'all';
        }
        urlState.searchParams.set('role_filter', roleFromStorage);
        shouldReloadFromStorage = true;
        window.history.replaceState({}, '', urlState.toString());
    } else {
        localStorage.setItem(storageKey, roleEl.value);
    }

    async function loadLogs(url) {
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
        url.searchParams.set('role_filter', roleEl.value);
        return url.toString();
    }

    roleEl.addEventListener('change', function () {
        localStorage.setItem(storageKey, roleEl.value);
        const finalUrl = buildUrl('{{ route('activity.log') }}');
        loadLogs(finalUrl);
        window.history.replaceState({}, '', finalUrl);
    });

    wrapper.addEventListener('click', function (event) {
        const link = event.target.closest('.pagination a');
        if (!link) return;
        event.preventDefault();
        const finalUrl = buildUrl(link.href);
        loadLogs(finalUrl);
        window.history.replaceState({}, '', finalUrl);
    });

    if (shouldReloadFromStorage) {
        loadLogs(urlState.toString());
    }
});
</script>
