<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Detail Tugas -->
                        <div class="card" style="border-radius: 12px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.12);">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <div style="background-color: #4285f4; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0">{{ $assignment->name }}</h3>
                                        <p class="text-muted mb-0">{{ $assignment->schedule->teacher->name }} • {{ date('d M Y', strtotime($assignment->created_at)) }}</p>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span style="font-weight: bold; color: #333;">Mata Pelajaran: {{ $assignment->schedule->course->coursename }}</span>
                                    <span style="font-weight: bold; color: #3c4043;">Deadline: {{ date('d M Y, H:i', strtotime($assignment->time_end)) }}</span>
                                </div>
                                <hr>
                                <div class="assignment-description" style="color: #3c4043; line-height: 1.6;">
                                    {!! nl2br(e($assignment->description)) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Status Pengumpulan -->
                        <div class="card" style="border-radius: 12px; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 style="font-size: 1.2rem;">Tugas Anda</h4>
                                    @if($quest)
                                        <span class="text-success" style="font-weight: 500;">Diserahkan</span>
                                    @else
                                        <span class="text-danger" style="font-weight: 500;">Ditugaskan</span>
                                    @endif
                                </div>

                                @if(session('success'))
                                    <div class="alert alert-success py-2 px-3 small">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger py-2 px-3 small">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                @if($quest)
                                    <div class="p-3 mb-3" style="border: 1px solid #e0e0e0; border-radius: 8px; background-color: #f8f9fa;">
                                        @php
                                            $files = null;
                                            try {
                                                $decoded = json_decode($quest->file, true);
                                                if (is_array($decoded)) $files = $decoded;
                                            } catch (\Throwable $e) {}
                                        @endphp
                                        @if($files)
                                            <ul class="list-unstyled mb-2">
                                                @foreach($files as $fp)
                                                <li class="d-flex align-items-center mb-2">
                                                    <i class="far fa-file-alt fa-lg text-primary mr-2"></i>
                                                    <span class="text-truncate">{{ basename($fp) }}</span>
                                                </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="far fa-file-alt fa-2x text-primary mr-3"></i>
                                                <div style="overflow: hidden; flex: 1;">
                                                    <p class="mb-0 text-truncate" style="font-weight: 500;">{{ basename($quest->file) }}</p>
                                                </div>
                                            </div>
                                        @endif
                                        <small class="text-muted d-block">Dikirim: {{ date('d M, H:i', strtotime($quest->updated_at)) }}</small>
                                        @if($quest->description)
                                            <div class="mt-2 p-2 bg-white border rounded small">
                                                <strong>Catatan:</strong><br>
                                                {{ $quest->description }}
                                            </div>
                                        @endif
                                    </div>
                                    
                                    @if($quest->score)
                                        <div class="alert alert-info py-2 px-3 mb-3">
                                            <strong>Nilai: {{ $quest->score }}/100</strong>
                                            @if($quest->feedback)
                                                <p class="mb-0 small mt-1">Feedback: {{ $quest->feedback }}</p>
                                            @endif
                                        </div>
                                    @endif
                                @endif

                                @if(session('level') == 3) <!-- Jika Siswa -->
                                    @if(!$isLate)
                                        <form action="{{ route('assignment.upload') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="assignmentid" value="{{ $assignment->assignmentid }}">
                                            
                                            <div class="form-group mb-3">
                                                <label for="description" class="form-label small font-weight-bold">Tambahkan Komentar/Catatan</label>
                                                <textarea name="description" id="description" class="form-control form-control-sm" rows="2" placeholder="Tulis catatan di sini...">{{ $quest ? $quest->description : '' }}</textarea>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="files" class="btn btn-outline-primary btn-block py-2" style="cursor: pointer; border-style: dashed; border-width: 2px;">
                                                    <i class="fas fa-upload mr-2"></i> {{ $quest ? 'Ganti File' : 'Pilih File' }} (boleh lebih dari satu)
                                                </label>
                                                <input type="file" name="files[]" id="files" class="d-none" multiple onchange="(function(el){ const names=[...el.files].map(f=>f.name).join(', '); document.getElementById('file-name-display').innerText = names; })(this)">
                                                <div id="file-name-display" class="small text-center text-primary mt-1 font-italic"></div>
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-block py-2 shadow-sm">
                                                {{ $quest ? 'Update Pengiriman' : 'Kirim Tugas' }}
                                            </button>
                                        </form>
                                        
                                        @if($quest)
                                            <p class="text-center small text-muted mt-3">
                                                <i class="fas fa-info-circle"></i> Anda masih bisa mengganti tugas sebelum deadline.
                                            </p>
                                        @endif
                                    @else
                                        @if(!$quest)
                                            <div class="alert alert-danger text-center mb-0">
                                                <i class="fas fa-clock mb-2 d-block fa-2x"></i>
                                                <strong>Waktu Habis!</strong><br>
                                                Anda tidak dapat lagi mengirimkan tugas ini.
                                            </div>
                                        @else
                                            <div class="alert alert-warning text-center mb-0 small">
                                                <i class="fas fa-lock mb-2 d-block fa-lg"></i>
                                                <strong>Pengiriman Terkunci</strong><br>
                                                Deadline telah lewat. Anda tidak dapat mengubah file yang sudah dikirim.
                                            </div>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <a href="{{ route('assignment.index') }}" class="btn btn-link"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Tugas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
