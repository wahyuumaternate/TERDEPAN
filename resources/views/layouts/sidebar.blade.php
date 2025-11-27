 <!-- ======= Sidebar ======= -->
 <aside id="sidebar" class="sidebar">
     <ul class="sidebar-nav" id="sidebar-nav">

         <li class="nav-item">
             <a class="nav-link {{ Request::is('/terminal-data') ? 'active' : 'collapsed' }} bg-primary text-white"
                 href="{{ url('/terminal-data') }}">
                 <i class="bi bi-arrow-left text-white"></i>
                 <span>Terminal Data</span>
             </a>
         </li><!-- End Dashboard Nav -->

         <hr>

         <!-- Beranda Nav -->
         <li class="nav-item">
             <a class="nav-link {{ Request::is('/e-kinerja') ? 'active' : 'collapsed' }}" href="{{ url('/e-kinerja') }}">
                 <i class="bi bi-house"></i>
                 <span>Beranda</span>
             </a>
         </li><!-- End Beranda Nav -->

         <!-- E-Kinerja -->
         <li class="nav-heading">E-Kinerja Perencana</li>

         <!-- Perjanjian Kinerja Nav -->
         @php
             $kodeJabatan = auth()->user()->jabatan?->kode;
             $fullAccessPK = in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
             $kabidAccess = $kodeJabatan === 'KABID';
             $limitedAccessPK = in_array($kodeJabatan, ['KASUBAG', 'JF', 'PELAKSANA', 'GATEK']);
             $canAccessMasterData = in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
             $canAccessMonitoring = in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID']);
             $canAccessSistem = in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
             $canManagePeriode = in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
             $canValidatePK = in_array($kodeJabatan, ['KABAN', 'SEKBAN', 'KABID']);

             // Cek periode aktif
             $periodeAktif = \Modules\PerjanjianKinerja\Models\PkPeriode::getPeriodeAktif();

             // Count pending validations for current user
             $pendingValidasiCount = 0;
             if ($canValidatePK) {
                 $pendingValidasiCount = \Modules\PerjanjianKinerja\Models\PkPerjanjianKinerja::where(
                     'atasan_id',
                     auth()->id(),
                 )
                     ->where('status_validasi', 'Menunggu')
                     ->count();
             }
         @endphp

         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#pk-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-file-earmark-text"></i><span>Perjanjian Kinerja</span><i
                     class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="pk-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <!-- PK Saya - Semua role bisa akses -->
                 <li>
                     <a href="{{ route('perjanjian-kinerja.pk-saya') }}"
                         class="{{ Request::is('perjanjian-kinerja/pk-saya') ? 'active' : '' }}">
                         <i class="bi bi-circle"></i><span>PK Saya</span>
                     </a>
                 </li>

                 <!-- Buat PK - Semua pegawai bisa buat jika periode aktif -->
                 @if ($periodeAktif)
                     <li>
                         <a href="{{ route('perjanjian-kinerja.create') }}"
                             class="{{ Request::is('perjanjian-kinerja/create') ? 'active' : '' }}">
                             <i class="bi bi-circle"></i><span>Buat PK Baru</span>
                             <span class="badge bg-success badge-number ms-2">Aktif</span>
                         </a>
                     </li>
                 @endif

                 @if ($canValidatePK)
                     <!-- Validasi PK - Atasan (Admin, Kaban, Sekban, Kabid, Kasubag) -->
                     <li>
                         <a href="{{ route('perjanjian-kinerja.daftar-validasi') }}"
                             class="{{ Request::is('perjanjian-kinerja/validasi') ? 'active' : '' }}">
                             <i class="bi bi-circle"></i><span>Validasi PK</span>
                             @if ($pendingValidasiCount > 0)
                                 <span class="badge bg-danger badge-number ms-3">{{ $pendingValidasiCount }}</span>
                             @endif
                         </a>
                     </li>
                 @endif

                 @if ($fullAccessPK || $kabidAccess)
                     <!-- Daftar PK - Admin, Kaban, Sekban, Kabid -->
                     <li>
                         <a href="{{ route('perjanjian-kinerja.index') }}"
                             class="{{ Request::is('perjanjian-kinerja') && !Request::is('perjanjian-kinerja/*') ? 'active' : '' }}">
                             <i class="bi bi-circle"></i><span>Daftar Semua PK</span>
                         </a>
                     </li>
                 @endif

                 @if ($canManagePeriode)
                     <!-- Periode PK - Hanya Admin, Kaban, Sekban -->
                     <li>
                         <a href="{{ route('perjanjian-kinerja.periode.index') }}"
                             class="{{ Request::is('perjanjian-kinerja/periode*') ? 'active' : '' }}">
                             <i class="bi bi-circle"></i><span>Kelola Periode</span>
                             @if ($periodeAktif)
                                 <span class="badge bg-success badge-number ms-2">
                                     <i class="bi bi-circle-fill" style="font-size: 6px;"></i>
                                 </span>
                             @endif
                         </a>
                     </li>

                     <!-- Template PK - Hanya Admin, Kaban, Sekban -->
                     <li>
                         <a href="{{ route('perjanjian-kinerja.template.index') }}"
                             class="{{ Request::is('perjanjian-kinerja/template*') ? 'active' : '' }}">
                             <i class="bi bi-circle"></i><span>Kelola Template</span>
                         </a>
                     </li>
                 @endif
             </ul>
         </li><!-- End Perjanjian Kinerja Nav -->

         <!-- Penugasan Nav -->
         @php
             $canManagePenugasan = in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID']);
             $canManageTeam = in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID', 'KASUBAG']);
         @endphp

         <!-- ========================================== -->
         <!-- BAGIAN 1: TUGAS SAYA (Semua Role)         -->
         <!-- ========================================== -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#tugas-saya-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-person-check"></i><span>Tugas Saya</span><i class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="tugas-saya-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <!-- Tugas Pokok Saya -->
                 <li>
                     <a href="{{ route('penugasan.tugas-pokok.tugas-saya') }}">
                         <i class="bi bi-circle"></i><span>Tugas Pokok</span>
                     </a>
                 </li>

                 <!-- Tugas Harian Saya -->
                 <li>
                     <a href="{{ route('penugasan.tugas-harian.tugas-saya') }}">
                         <i class="bi bi-circle"></i><span>Tugas Harian</span>
                     </a>
                 </li>

                 <!-- Tugas Tambahan Saya -->
                 <li>
                     <a href="{{ route('penugasan.tugas-tambahan.tugas-saya') }}">
                         <i class="bi bi-circle"></i><span>Tugas Tambahan</span>
                     </a>
                 </li>
             </ul>
         </li><!-- End Tugas Saya Nav -->

         <!-- ========================================== -->
         <!-- BAGIAN 2: MANAJEMEN TUGAS (Admin/Atasan)  -->
         <!-- ========================================== -->
         @if ($canManagePenugasan)
             <li class="nav-item">
                 <a class="nav-link collapsed" data-bs-target="#manajemen-tugas-nav" data-bs-toggle="collapse"
                     href="#">
                     <i class="bi bi-list-check"></i><span>Manajemen Tugas</span><i
                         class="bi bi-chevron-down ms-auto"></i>
                 </a>
                 <ul id="manajemen-tugas-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                     <!-- Daftar Tugas Pokok -->
                     <li>
                         <a href="{{ route('penugasan.tugas-pokok.index') }}">
                             <i class="bi bi-circle"></i><span>Daftar Tugas Pokok</span>
                         </a>
                     </li>

                     <!-- Daftar Tugas Harian -->
                     <li>
                         <a href="{{ route('penugasan.tugas-harian.index') }}">
                             <i class="bi bi-circle"></i><span>Daftar Tugas Harian</span>
                         </a>
                     </li>

                     <!-- Daftar Tugas Tambahan -->
                     <li>
                         <a href="{{ route('penugasan.tugas-tambahan.index') }}">
                             <i class="bi bi-circle"></i><span>Daftar Tugas Tambahan</span>
                         </a>
                     </li>
                 </ul>
             </li><!-- End Manajemen Tugas Nav -->
         @endif

         <!-- ========================================== -->
         <!-- BAGIAN 3: MANAJEMEN TIM (Atasan)          -->
         <!-- ========================================== -->
         @if ($canManageTeam)
             <li class="nav-item">
                 <a class="nav-link collapsed" data-bs-target="#manajemen-tim-nav" data-bs-toggle="collapse"
                     href="#">
                     <i class="bi bi-people"></i><span>Manajemen Tim</span><i class="bi bi-chevron-down ms-auto"></i>
                 </a>
                 <ul id="manajemen-tim-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                     <!-- Tim Saya -->
                     <li>
                         <a href="{{ route('penugasan.tim.index') }}">
                             <i class="bi bi-circle"></i><span>Tim Saya</span>
                         </a>
                     </li>

                     <!-- Berikan Tugas -->
                     <li>
                         <a href="{{ route('penugasan.tim.form-berikan-tugas') }}">
                             <i class="bi bi-circle"></i><span>Berikan Tugas</span>
                         </a>
                     </li>

                     <!-- Validasi Tugas -->
                     <li>
                         <a href="{{ route('penugasan.tim.daftar-validasi') }}">
                             <i class="bi bi-circle"></i><span>Validasi Tugas</span>
                             @php
                                 // Count pending validation (example - adjust based on your logic)
                                 $pendingCount = 0; // You can add real count here
                             @endphp
                             @if ($pendingCount > 0)
                                 <span class="badge bg-danger badge-number">{{ $pendingCount }}</span>
                             @endif
                         </a>
                     </li>

                     <!-- Monitoring Tim -->
                     <li>
                         <a href="{{ route('penugasan.tim.monitoring') }}">
                             <i class="bi bi-circle"></i><span>Monitoring Tim</span>
                         </a>
                     </li>
                 </ul>
             </li><!-- End Manajemen Tim Nav -->
         @endif

         {{-- <!-- Penugasan Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#tugas-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-list-task"></i><span>Penugasan</span><i class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="tugas-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <li>
                     <a href="{{ route('penugasan.tugas-pokok.index') }}">
                         <i class="bi bi-circle"></i><span>Daftar Penugasan</span>
                     </a>
                 </li>
                 <li>
                     <a href="{{ route('penugasan.tugas-pokok.show', AUTH::user()->id) }}">
                         <i class="bi bi-circle"></i><span>Tugas Saya</span>
                     </a>
                 </li>
                 <li class="nav-item">
                     <a href="#">
                         <i class="bi bi-circle"></i><span>Penilaian Kinerja</span>
                     </a>
                 </li><!-- End Penilaian Nav -->
             </ul>
         </li><!-- End Penugasan Nav --> --}}

         <!-- Progress & Validasi Nav -->
         {{-- <li class="nav-item">
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
         </li><!-- End Progress & Validasi Nav --> --}}

         {{-- <!-- Penilaian Nav -->
         <li class="nav-item">
             <a class="nav-link collapsed" href="{{ url('penilaian/laporan') }}">
                 <i class="bi bi-award"></i><span>Penilaian Kinerja</span>
             </a>
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
         </li><!-- End Delegasi & Workload Nav --> --}}

         <!-- Master Data -->
         @if ($canAccessMasterData)
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
                 </ul>
             </li><!-- End Master Data Nav -->
         @endif

         <!-- Sistem -->
         @if ($canAccessMonitoring)
             <li class="nav-heading">Sistem & Monitoring</li>

             <!-- Monitoring Nav -->
             <li class="nav-item">
                 <a class="nav-link collapsed" href="#">
                     <i class="bi bi-activity"></i><span>Monitoring Aktifitas</span>
                 </a>
             </li><!-- End Monitoring Nav -->
         @endif

         @if ($canAccessSistem)
             <!-- Sistem Nav -->
             @if ($kodeJabatan === 'ADMIN')
                 <li class="nav-item">
                     <a class="nav-link collapsed" data-bs-target="#sistem-nav" data-bs-toggle="collapse"
                         href="#">
                         <i class="bi bi-sliders"></i><span>Sistem</span><i class="bi bi-chevron-down ms-auto"></i>
                     </a>
                     <ul id="sistem-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                         <li>
                             <a href="#">
                                 <i class="bi bi-circle"></i><span>Audit Log</span>
                             </a>
                         </li>
                         <li>
                             <a href="#">
                                 <i class="bi bi-circle"></i><span>Konfigurasi</span>
                             </a>
                         </li>
                     </ul>
                 </li><!-- End Sistem Nav -->
             @endif
         @endif

     </ul>

 </aside><!-- End Sidebar-->
