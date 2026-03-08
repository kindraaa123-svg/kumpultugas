<div class="page-container">


    <!-- MAIN CONTENT-->
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="overview-wrap">
                            <h2 class="title-1">Pengaturan Website</h2>
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
                    <div class="col-lg-8 offset-lg-2">
                        <div class="card">
                            <div class="card-header">
                                <strong>Form Pengaturan</strong>
                            </div>
                            <form action="/setting/update" method="post" enctype="multipart/form-data" class="form-horizontal">
                                <div class="card-body card-block">
                                    @csrf
                                    <input type="hidden" name="systemid" value="{{ $system->systemid }}">
                                    
                                    <div class="row form-group m-b-15">
                                        <div class="col col-md-3">
                                            <label for="name" class=" form-control-label">Nama Sistem</label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <input type="text" id="name" name="name" value="{{ $system->systemname }}" class="form-control">
                                        </div>
                                    </div>

                                    <div class="row form-group m-b-15">
                                        <div class="col col-md-3">
                                            <label for="logo" class=" form-control-label">Logo Sistem</label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <input type="file" id="logo" name="logo" class="form-control-file">
                                            @if($system->systemlogo)
                                                <div class="mt-2">
                                                    <small>Logo Saat Ini:</small><br>
                                                    <img src="{{ asset('storage/' . $system->systemlogo) }}" style="max-height: 80px;" alt="Current Logo">
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row form-group m-b-15">
                                        <div class="col col-md-3">
                                            <label for="address" class=" form-control-label">Alamat</label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <textarea name="address" id="address" rows="3" class="form-control">{{ $system->systemaddress }}</textarea>
                                        </div>
                                    </div>

                                    <div class="row form-group m-b-15">
                                        <div class="col col-md-3">
                                            <label for="manager" class=" form-control-label">Manager</label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <input type="text" id="manager" name="manager" value="{{ $system->systemmanager }}" class="form-control">
                                        </div>
                                    </div>

                                    <div class="row form-group m-b-15">
                                        <div class="col col-md-3">
                                            <label for="contact" class=" form-control-label">Kontak</label>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <input type="text" id="contact" name="contact" value="{{ $system->systemcontact }}" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fa fa-dot-circle-o"></i> Simpan
                                    </button>
                                    <button type="reset" class="btn btn-danger btn-sm">
                                        <i class="fa fa-ban"></i> Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
