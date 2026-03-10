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
                        <div class="table-responsive table--no-card m-b-30">
                            <table class="table table-borderless table-striped table-earning">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Oleh</th>
                                        <th>Aksi</th>
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

                                            $beforeName = $before['coursename'] ?? '';
                                            $afterName = $after['coursename'] ?? '';
                                        @endphp
                                        <tr>
                                            <td>{{ $log->created_at ? date('d M Y, H:i', strtotime($log->created_at)) : '-' }}</td>
                                            <td>{{ $log->performed_username ?? '-' }}</td>
                                            <td>{{ $log->action }}</td>
                                            <td>{{ $log->entity_type }} #{{ $log->entity_id }}</td>
                                            <td>
                                                @if($log->action === 'update')
                                                    {{ $beforeName }} → {{ $afterName }}
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
                                            <td colspan="6">Belum ada data.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end">
                            {{ $logs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

