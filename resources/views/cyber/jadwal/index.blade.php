<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                @php
                    $role = strtolower(trim((string) session('role')));
                    $teacherRoleId = (int) (session('teacher_roleid') ?? 0);
                    $isKurikulum = in_array($role, ['kurikulum', 'curiculum'], true) || in_array($teacherRoleId, [4, 5], true);
                    $canManageJadwal = (int) session('level') === 1 || $isKurikulum;
                @endphp
                <div class="row">
                    <div class="col-md-12">
                        <div class="overview-wrap">
                            <h2 class="title-1">Jadwal Pelajaran</h2>
                            <div>
                                @if($canManageJadwal)
                                <button class="au-btn au-btn-icon au-btn--blue" data-bs-toggle="modal" data-bs-target="#modalSettingJadwal">
                                    <i class="zmdi zmdi-settings"></i>Setting</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row m-t-25">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header text-center">
                                <h3>{{ $activeBlock ? $activeBlock->name : 'No Block Active' }}</h3>
                                <p>{{ $activeYear ? $activeYear->name : 'No Year Active' }}</p>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <style>
                                        .session-cell {
                                            cursor: pointer;
                                            transition: all 0.3s;
                                            min-height: 80px;
                                            vertical-align: middle !important;
                                        }
                                        .session-cell:hover {
                                            background-color: #f0f4f8;
                                        }
                                        .btn-add-schedule {
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            width: 40px;
                                            height: 40px;
                                            border-radius: 50%;
                                            background-color: #e9ecef;
                                            color: #adb5bd;
                                            margin: 0 auto;
                                            border: 2px dashed #dee2e6;
                                            transition: all 0.3s;
                                        }
                                        .session-cell:hover .btn-add-schedule {
                                            background-color: #007bff;
                                            color: white;
                                            border-style: solid;
                                            border-color: #007bff;
                                            transform: scale(1.1);
                                        }
                                        .schedule-item {
                                            padding: 10px;
                                            background-color: #fff;
                                            border-left: 4px solid #007bff;
                                            border-radius: 4px;
                                            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                                            text-align: left;
                                        }
                                        .schedule-item strong {
                                            display: block;
                                            color: #333;
                                            font-size: 14px;
                                        }
                                        .schedule-item small {
                                            color: #666;
                                            font-size: 12px;
                                        }
                                    </style>
                                    <table class="table table-bordered text-center">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" style="vertical-align: middle; background: #f8f9fa;">No.</th>
                                                <th rowspan="2" style="vertical-align: middle; background: #f8f9fa;">Kelas</th>
                                                <th colspan="5" style="background: #f8f9fa;">Sesi</th>
                                            </tr>
                                            <tr>
                                                <th style="background: #f8f9fa;">Sesi 1</th>
                                                <th style="background: #f8f9fa;">Sesi 2</th>
                                                <th style="background: #f8f9fa;">Sesi 3</th>
                                                <th style="background: #f8f9fa;">Sesi 4</th>
                                                <th style="background: #f8f9fa;">Sesi 5</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                             @foreach($classes as $index => $class)
                                             <tr>
                                                 <td style="background: #f8f9fa; font-weight: bold;">{{ $index + 1 }}</td>
                                                 <td style="background: #f8f9fa; font-weight: bold;">{{ $class->classname }}</td>
                                                 @for($s = 1; $s <= 5; $s++)
                                                 <td class="session-cell" 
                                                     @if($canManageJadwal)
                                                     data-bs-toggle="modal" 
                                                     data-bs-target="#modalJadwal"
                                                     data-classid="{{ $class->classid }}"
                                                     data-classname="{{ $class->classname }}"
                                                     data-session="{{ $s }}"
                                                     data-courseid="{{ isset($schedules[$class->classid][$s]) ? $schedules[$class->classid][$s]->courseid : '' }}"
                                                     data-teacherid="{{ isset($schedules[$class->classid][$s]) ? $schedules[$class->classid][$s]->teacherid : '' }}"
                                                     @endif
                                                     >
                                                     @if(isset($schedules[$class->classid][$s]))
                                                         <div class="schedule-item">
                                                             <strong>{{ $schedules[$class->classid][$s]->course->coursename }}</strong>
                                                             <small><i class="fas fa-user-tie"></i> {{ $schedules[$class->classid][$s]->teacher->name }}</small>
                                                         </div>
                                                     @else
                                                        @if($canManageJadwal)
                                                         <div class="btn-add-schedule">
                                                             <i class="fas fa-plus"></i>
                                                         </div>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                     @endif
                                                 </td>
                                                 @endfor
                                             </tr>
                                             @endforeach
                                         </tbody>
                                     </table>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>

 <!-- Modal Setting Jadwal -->
 <div class="modal fade" id="modalSettingJadwal" tabindex="-1" aria-labelledby="modalSettingJadwalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSettingJadwalLabel">Pengaturan Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('jadwal.setting.update') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="academic_year_id" class="form-label">Tahun Ajaran Aktif</label>
                        <select name="academic_year_id" id="settingAcademicYearId" class="form-control" required>
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach($years as $year)
                            <option value="{{ $year->academic_year_id }}" {{ $year->is_active ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="block_id" class="form-label">Blok Aktif</label>
                        <select name="block_id" id="settingBlockId" class="form-control" required>
                            <option value="">-- Pilih Blok --</option>
                            @foreach($blocks as $block)
                            <option value="{{ $block->block_id }}" {{ ($activeBlock && $activeBlock->block_id == $block->block_id) ? 'selected' : '' }}>{{ $block->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
 </div>

 <!-- Modal Jadwal -->
 <div class="modal fade" id="modalJadwal" tabindex="-1" aria-labelledby="modalJadwalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalJadwalLabel">Atur Jadwal Pelajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('jadwal.update') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="academic_year_id" value="{{ $activeYear ? $activeYear->academic_year_id : '' }}">
                    <input type="hidden" name="block_id" value="{{ $activeBlock ? $activeBlock->block_id : '' }}">
                    <input type="hidden" name="classid" id="modalClassId">
                    <input type="hidden" name="session" id="modalSession">

                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <input type="text" id="modalClassName" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sesi</label>
                        <input type="text" id="modalSessionDisplay" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="courseid" class="form-label">Mata Pelajaran</label>
                        <select name="courseid" id="modalCourseId" class="form-control" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($courses as $course)
                            <option value="{{ $course->courseid }}">{{ $course->coursename }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="teacherid" class="form-label">Guru</label>
                        <select name="teacherid" id="modalTeacherId" class="form-control" required>
                            <option value="">-- Pilih Guru --</option>
                            @foreach($teachers as $teacher)
                            <option value="{{ $teacher->teacherid }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
 </div>

 <script>
    document.addEventListener('DOMContentLoaded', function() {
        var modalJadwal = document.getElementById('modalJadwal');
        modalJadwal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var classid = button.getAttribute('data-classid');
            var classname = button.getAttribute('data-classname');
            var session = button.getAttribute('data-session');
            var courseid = button.getAttribute('data-courseid');
            var teacherid = button.getAttribute('data-teacherid');

            document.getElementById('modalClassId').value = classid;
            document.getElementById('modalClassName').value = classname;
            document.getElementById('modalSession').value = session;
            document.getElementById('modalSessionDisplay').value = 'Sesi ' + session;
            document.getElementById('modalCourseId').value = courseid;
            document.getElementById('modalTeacherId').value = teacherid;
        });
    });
 </script>
