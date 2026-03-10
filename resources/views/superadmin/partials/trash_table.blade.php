<div class="table-responsive table--no-card m-b-30">
    <table class="table table-borderless table-striped table-earning">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Oleh</th>
                <th>Role</th>
                <th>Aksi</th>
                <th>IP Address</th>
                <th>Data</th>
                <th>Perubahan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                @php
                    $before = [];
                    $after = [];
                    try {
                        $b = json_decode($log->before_json ?? '', true);
                        if (is_array($b)) $before = $b;
                    } catch (\Throwable $e) {}
                    try {
                        $a = json_decode($log->after_json ?? '', true);
                        if (is_array($a)) $after = $a;
                    } catch (\Throwable $e) {}

                    $beforeName = $before['coursename'] ?? ($before['classname'] ?? ($before['name'] ?? ''));
                    $afterName = $after['coursename'] ?? ($after['classname'] ?? ($after['name'] ?? ''));
                @endphp
                <tr>
                    <td>{{ $log->created_at ? date('d M Y, H:i', strtotime($log->created_at)) : '-' }}</td>
                    <td>{{ $log->performed_username ?? '-' }}</td>
                    <td>{{ $log->role_label ?? '-' }}</td>
                    <td>{{ $log->action === 'update' ? 'edit' : $log->action }}</td>
                    <td>{{ $log->ip_label ?? '-' }}</td>
                    <td>{{ $log->entity_type }} #{{ $log->entity_id }}</td>
                    <td>
                        @if($log->action === 'update')
                            {{ $beforeName }} -> {{ $afterName }}
                        @elseif($log->action === 'delete')
                            {{ $beforeName }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="d-flex gap-2">
                        <form method="POST" action="{{ route('trash.restore') }}">
                            @csrf
                            <input type="hidden" name="log_id" value="{{ $log->id }}">
                            <button type="submit" class="btn btn-sm btn-success">Restore</button>
                        </form>
                        <form method="POST" action="{{ route('trash.delete') }}" onsubmit="return confirm('Hapus permanen?')">
                            @csrf
                            <input type="hidden" name="log_id" value="{{ $log->id }}">
                            <button type="submit" class="btn btn-sm btn-danger">Delete Permanen</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            @if($logs->count() === 0)
                <tr>
                    <td colspan="8">Belum ada data.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-end">
    {{ $logs->links() }}
</div>
