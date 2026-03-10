<div class="table-responsive table--no-card m-b-30">
    <table class="table table-borderless table-striped table-earning">
        <thead>
            <tr>
                <th>Time</th>
                <th>Username</th>
                <th>Role</th>
                <th>Action</th>
                <th>IP Address</th>
                <th>Latitude</th>
                <th>Longitude</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at }}</td>
                    <td>{{ $log->username ?? '-' }}</td>
                    <td>{{ $log->role ?? '-' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->ip_address ?? '-' }}</td>
                    <td>{{ $log->latitude ?? '-' }}</td>
                    <td>{{ $log->longitude ?? '-' }}</td>
                </tr>
            @endforeach
            @if($logs->count() === 0)
                <tr>
                    <td colspan="7">Belum ada data.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-end">
    {{ $logs->links() }}
</div>
