<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="overview-wrap">
                            <h2 class="title-1">Setting Tahun Ajaran dan Blok</h2>
                        </div>
                    </div>
                </div>
                
                <div class="row m-t-25">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <strong>Update Setting</strong>
                            </div>
                            <div class="card-body card-block">
                                <form action="{{ route('jadwal.setting.update') }}" method="post" class="form-horizontal">
                                    @csrf
                                    <div class="row form-group">
                                        <div class="col col-md-3">
                                            <label for="academic_year_id" class="form-control-label">Tahun Ajaran</label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <select name="academic_year_id" id="academic_year_id" class="form-control">
                                                @foreach($years as $year)
                                                <option value="{{ $year->academic_year_id }}" {{ $year->is_active ? 'selected' : '' }}>{{ $year->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col col-md-3">
                                            <label for="block_id" class="form-control-label">Blok Aktif</label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <select name="block_id" id="block_id" class="form-control">
                                                @foreach($blocks as $block)
                                                <option value="{{ $block->block_id }}">{{ $block->name }} ({{ $block->date_start }} - {{ $block->date_end }})</option>
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