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
                        <div class="table-responsive table--no-card m-b-30">
                            <table class="table table-borderless table-striped table-earning">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Action</th>
                                        <th>IP Address</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at }}</td>
                                        <td>{{ $log->username }}</td>
                                        <td>{{ $log->role }}</td>
                                        <td>{{ $log->action }}</td>
                                        <td>{{ $log->ip_address }}</td>
                                        <td>
                                            @if($log->latitude && $log->longitude)
                                                <a href="https://www.google.com/maps?q={{ $log->latitude }},{{ $log->longitude }}" target="_blank">
                                                    {{ $log->latitude }}, {{ $log->longitude }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
