<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="overview-wrap">
                            <h2 class="title-1">Edit Jadwal: {{ $classroom->classname }} - Sesi {{ $session }}</h2>
                        </div>
                    </div>
                </div>
                
                <div class="row m-t-25">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <strong>{{ $activeBlock->name }} ({{ $activeYear->name }})</strong>
                            </div>
                            <div class="card-body card-block">
                                <form action="{{ route('jadwal.update') }}" method="post" class="form-horizontal">
                                    @csrf
                                    <input type="hidden" name="academic_year_id" value="{{ $activeYear->academic_year_id }}">
                                    <input type="hidden" name="block_id" value="{{ $activeBlock->block_id }}">
                                    <input type="hidden" name="classid" value="{{ $classroom->classid }}">
                                    <input type="hidden" name="session" value="{{ $session }}">
                                    
                                    <div class="row form-group">
                                        <div class="col col-md-3">
                                            <label for="courseid" class="form-control-label">Mata Pelajaran</label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <select name="courseid" id="courseid" class="form-control">
                                                @foreach($courses as $course)
                                                <option value="{{ $course->courseid }}" {{ (isset($schedule) && $schedule->courseid == $course->courseid) ? 'selected' : '' }}>{{ $course->coursename }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col col-md-3">
                                            <label for="teacherid" class="form-control-label">Guru</label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <select name="teacherid" id="teacherid" class="form-control">
                                                @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->teacherid }}" {{ (isset($schedule) && $schedule->teacherid == $teacher->teacherid) ? 'selected' : '' }}>{{ $teacher->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fa fa-dot-circle-o"></i> Submit
                                        </button>
                                        <a href="{{ route('jadwal.index') }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-ban"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>