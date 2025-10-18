 {{-- <!-- ======= Sidebar ======= -->
 <aside id="sidebar" class="sidebar">


     <ul class="sidebar-nav" id="sidebar-nav">

         <!-- Dashboard Nav -->
         <li class="nav-item">
             <a class="nav-link" href="{{ url('/') }}">
                 <i class="bi bi-grid"></i>
                 <span>Dashboard</span>
             </a>
         </li><!-- End Dashboard Nav -->

         <!-- Bappeda Storage -->
         <li class="nav-heading">Bappeda Storage</li>

         <!-- Management File / Arsip Dokumen -->
         @php
             $dokumenActive = request()->is('dokumen*');
         @endphp

         <li class="nav-item">
             <a class="nav-link {{ $dokumenActive ? '' : 'collapsed' }}" data-bs-target="#dokumen-nav"
                 data-bs-toggle="collapse" href="#">
                 <i class="bi bi-folder2-open"></i><span>Arsip Dokumen</span><i class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="dokumen-nav" class="nav-content collapse {{ $dokumenActive ? 'show' : '' }}"
                 data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('dokumen') }}" class="{{ request()->is('dokumen') ? 'active' : '' }}">
                         <i class="bi bi-circle"></i><span>Semua Dokumen</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('dokumen/kategori') }}"
                         class="{{ request()->is('dokumen/kategori') ? 'active' : '' }}">
                         <i class="bi bi-circle"></i><span>Kategori Dokumen</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('dokumen/jenis') }}" class="{{ request()->is('dokumen/jenis') ? 'active' : '' }}">
                         <i class="bi bi-circle"></i><span>Jenis Dokumen</span>
                     </a>
                 <li>
                     <a href="{{ url('dokumen/folder') }}"
                         class="{{ request()->is('dokumen/folder') ? 'active' : '' }}">
                         <i class="bi bi-circle"></i><span>Folder Dokumen</span>
                     </a>
                 </li>
             </ul>
         </li>
         <!-- End Management File Nav -->


         <!-- E-Kinerja -->
         <li class="nav-heading">E-Kinerja</li>

         <!-- Perjanjian Kinerja Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#pk-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-file-earmark-text"></i><span>Perjanjian Kinerja</span><i
                     class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="pk-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('perjanjian-kinerja') }}">
                         <i class="bi bi-circle"></i><span>Daftar PK</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('perjanjian-kinerja/template') }}">
                         <i class="bi bi-circle"></i><span>Template PK</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('perjanjian-kinerja/create') }}">
                         <i class="bi bi-circle"></i><span>Buat PK Baru</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Perjanjian Kinerja Nav -->

         <!-- Penugasan Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#tugas-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-list-task"></i><span>Penugasan</span><i class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="tugas-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('penugasan/tugas-pokok') }}">
                         <i class="bi bi-circle"></i><span>Tugas Pokok</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('penugasan/tugas-harian') }}">
                         <i class="bi bi-circle"></i><span>Tugas Harian</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('penugasan/tugas-tambahan') }}">
                         <i class="bi bi-circle"></i><span>Tugas Tambahan</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('penugasan/mandiri') }}">
                         <i class="bi bi-circle"></i><span>Penugasan Mandiri</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Penugasan Nav -->

         <!-- Progress & Validasi Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#progress-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-graph-up"></i><span>Progress & Validasi</span><i
                     class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="progress-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('progress') }}">
                         <i class="bi bi-circle"></i><span>Input Progress</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('validasi') }}">
                         <i class="bi bi-circle"></i><span>Validasi Tugas</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('revisi') }}">
                         <i class="bi bi-circle"></i><span>Revisi</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Progress & Validasi Nav -->

         <!-- Penilaian Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#penilaian-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-award"></i><span>Penilaian</span><i class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="penilaian-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('penilaian/bulanan') }}">
                         <i class="bi bi-circle"></i><span>Nilai Bulanan</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('penilaian/tahunan') }}">
                         <i class="bi bi-circle"></i><span>Nilai Tahunan</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Penilaian Nav -->

         <!-- Delegasi & Workload Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#delegasi-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-arrow-left-right"></i><span>Delegasi & Workload</span><i
                     class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="delegasi-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('delegasi') }}">
                         <i class="bi bi-circle"></i><span>Delegasi Tugas</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('workload') }}">
                         <i class="bi bi-circle"></i><span>Monitor Workload</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Delegasi & Workload Nav -->

         <!-- Master Data -->
         <li class="nav-heading">Master Data</li>

         <!-- Master Data Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#master-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-database"></i><span>Master Data</span><i class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="master-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('master/pegawai') }}">
                         <i class="bi bi-circle"></i><span>Pegawai</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('master/jabatan') }}">
                         <i class="bi bi-circle"></i><span>Jabatan</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('master/bidang') }}">
                         <i class="bi bi-circle"></i><span>Bidang</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('master/ttd-digital') }}">
                         <i class="bi bi-circle"></i><span>TTD Digital</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Master Data Nav -->

         <!-- Sistem -->
         <li class="nav-heading">Sistem</li>

         <!-- Sistem Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#sistem-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-sliders"></i><span>Sistem</span><i class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="sistem-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('sistem/notifikasi') }}">
                         <i class="bi bi-circle"></i><span>Notifikasi</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('sistem/audit-log') }}">
                         <i class="bi bi-circle"></i><span>Audit Log</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('sistem/config') }}">
                         <i class="bi bi-circle"></i><span>Konfigurasi</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Sistem Nav -->

     </ul>

     <div class="sidebar-storage mt-auto p-3">
         <div class="text-center mb-3">
             <i class="bi bi-folder2-open text-primary" style="font-size: 3rem;"></i>
         </div>
         <h6 class="text-center mb-2">{{ $percentage }}% In-use</h6>
         <div class="progress mb-2" style="height: 8px;">
             <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentage }}%"
                 aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
         </div>
         <div class="d-flex justify-content-between">
             <small class="text-muted">{{ $used }}</small>
             <small class="text-muted">{{ $total }}</small>
         </div>
     </div>


 </aside><!-- End Sidebar--> --}}
 <!-- ======= Sidebar ======= -->
 <aside id="sidebar" class="sidebar">
     <ul class="sidebar-nav" id="sidebar-nav">

         <!-- Dashboard Nav -->
         <li class="nav-item">
             <a class="nav-link" href="{{ url('/') }}">
                 <i class="bi bi-grid"></i>
                 <span>Dashboard</span>
             </a>
         </li><!-- End Dashboard Nav -->

         <!-- Bappeda Storage -->
         <li class="nav-heading">Bappeda Storage</li>

         <!-- Management File / Arsip Dokumen -->
         @php
             $dokumenActive = request()->is('dokumen*');
         @endphp

         <li class="nav-item">
             <a class="nav-link {{ $dokumenActive ? '' : 'collapsed' }}" data-bs-target="#dokumen-nav"
                 data-bs-toggle="collapse" href="#">
                 <i class="bi bi-folder2-open"></i><span>Arsip Dokumen</span><i class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="dokumen-nav" class="nav-content collapse {{ $dokumenActive ? 'show' : '' }}"
                 data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('dokumen') }}" class="{{ request()->is('dokumen') ? 'active' : '' }}">
                         <i class="bi bi-circle"></i><span>Semua Dokumen</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('dokumen/kategori') }}"
                         class="{{ request()->is('dokumen/kategori') ? 'active' : '' }}">
                         <i class="bi bi-circle"></i><span>Kategori Dokumen</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('dokumen/jenis') }}" class="{{ request()->is('dokumen/jenis') ? 'active' : '' }}">
                         <i class="bi bi-circle"></i><span>Jenis Dokumen</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('dokumen/folder') }}"
                         class="{{ request()->is('dokumen/folder') ? 'active' : '' }}">
                         <i class="bi bi-circle"></i><span>Folder Dokumen</span>
                     </a>
                 </li>
                 {{-- <li>
                     <a href="{{ url('dokumen/log') }}" class="{{ request()->is('dokumen/log') ? 'active' : '' }}">
                         <i class="bi bi-circle"></i><span>Log Aktivitas Dokumen</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('dokumen/nomor') }}"
                         class="{{ request()->is('dokumen/nomor') ? 'active' : '' }}">
                         <i class="bi bi-circle"></i><span>Penomoran Dokumen</span>
                     </a>
                 </li> --}}
             </ul>
         </li>
         <!-- End Management File Nav -->

         <!-- E-Kinerja -->
         <li class="nav-heading">E-Kinerja</li>

         <!-- Perjanjian Kinerja Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#pk-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-file-earmark-text"></i><span>Perjanjian Kinerja</span><i
                     class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="pk-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('perjanjian-kinerja') }}">
                         <i class="bi bi-circle"></i><span>Daftar PK</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('perjanjian-kinerja/template') }}">
                         <i class="bi bi-circle"></i><span>Template PK</span>
                     </a>
                 </li>

                 <li>
                     <a href="{{ url('perjanjian-kinerja/create') }}">
                         <i class="bi bi-circle"></i><span>Buat PK Baru</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Perjanjian Kinerja Nav -->

         <!-- Penugasan Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#tugas-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-list-task"></i><span>Penugasan</span><i class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="tugas-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ route('penugasan.tugas-pokok.index') }}">
                         <i class="bi bi-circle"></i><span>Tugas Pokok</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('penugasan/indikator') }}">
                         <i class="bi bi-circle"></i><span>Indikator Tugas</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('penugasan/tugas-harian') }}">
                         <i class="bi bi-circle"></i><span>Tugas Harian</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('penugasan/tugas-tambahan') }}">
                         <i class="bi bi-circle"></i><span>Tugas Tambahan</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('penugasan/mandiri') }}">
                         <i class="bi bi-circle"></i><span>Penugasan Mandiri</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Penugasan Nav -->

         <!-- Progress & Validasi Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#progress-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-graph-up"></i><span>Progress & Validasi</span><i
                     class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="progress-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('progress') }}">
                         <i class="bi bi-circle"></i><span>Input Progress</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('progress/foto-bukti') }}">
                         <i class="bi bi-circle"></i><span>Foto Bukti</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('progress/dokumen-bukti') }}">
                         <i class="bi bi-circle"></i><span>Dokumen Bukti</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('validasi') }}">
                         <i class="bi bi-circle"></i><span>Validasi Tugas</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('revisi') }}">
                         <i class="bi bi-circle"></i><span>Revisi</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Progress & Validasi Nav -->

         <!-- Penilaian Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#penilaian-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-award"></i><span>Penilaian</span><i class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="penilaian-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('penilaian/bulanan') }}">
                         <i class="bi bi-circle"></i><span>Nilai Bulanan</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('penilaian/tahunan') }}">
                         <i class="bi bi-circle"></i><span>Nilai Tahunan</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('penilaian/laporan') }}">
                         <i class="bi bi-circle"></i><span>Laporan Penilaian</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Penilaian Nav -->

         <!-- Delegasi & Workload Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#delegasi-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-arrow-left-right"></i><span>Delegasi & Workload</span><i
                     class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="delegasi-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('delegasi') }}">
                         <i class="bi bi-circle"></i><span>Delegasi Tugas</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('delegasi/history') }}">
                         <i class="bi bi-circle"></i><span>History Delegasi</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('workload') }}">
                         <i class="bi bi-circle"></i><span>Monitor Workload</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('workload/report') }}">
                         <i class="bi bi-circle"></i><span>Laporan Beban Kerja</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Delegasi & Workload Nav -->

         <!-- Notifikasi Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#notif-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-bell"></i><span>Notifikasi</span><i class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="notif-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('notifikasi') }}">
                         <i class="bi bi-circle"></i><span>Daftar Notifikasi</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('notifikasi/preferensi') }}">
                         <i class="bi bi-circle"></i><span>Preferensi Notifikasi</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Notifikasi Nav -->

         <!-- Master Data -->
         <li class="nav-heading">Master Data</li>

         <!-- Master Data Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#master-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-database"></i><span>Master Data</span><i class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="master-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ route('master.pegawai.index') }}">
                         <i class="bi bi-circle"></i><span>Pegawai</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ route('master.bidang.index') }}">
                         <i class="bi bi-circle"></i><span>Bidang</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ route('master.jabatan.index') }}">
                         <i class="bi bi-circle"></i><span>Jabatan</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('master/ttd-digital') }}">
                         <i class="bi bi-circle"></i><span>TTD Digital</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Master Data Nav -->

         <!-- Sistem -->
         <li class="nav-heading">Sistem</li>

         <!-- Sistem Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#sistem-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-sliders"></i><span>Sistem</span><i class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="sistem-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ url('sistem/audit-log') }}">
                         <i class="bi bi-circle"></i><span>Audit Log</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ url('sistem/config') }}">
                         <i class="bi bi-circle"></i><span>Konfigurasi</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Sistem Nav -->

     </ul>

     <div class="sidebar-storage mt-auto p-3">
         <div class="text-center mb-3">
             <i class="bi bi-folder2-open text-primary" style="font-size: 3rem;"></i>
         </div>
         <h6 class="text-center mb-2">{{ $percentage }}% In-use</h6>
         <div class="progress mb-2" style="height: 8px;">
             <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentage }}%"
                 aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
         </div>
         <div class="d-flex justify-content-between">
             <small class="text-muted">{{ $used }}</small>
             <small class="text-muted">{{ $total }}</small>
         </div>
     </div>

 </aside><!-- End Sidebar-->
