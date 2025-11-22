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
             <a class="nav-link {{ Request::is('/') ? 'active' : 'collapsed' }}" href="{{ url('/') }}">
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
         @endphp

         <li class="nav-item">
             <a class="nav-link collapsed" data-bs-target="#pk-nav" data-bs-toggle="collapse" href="#">
                 <i class="bi bi-file-earmark-text"></i><span>Perjanjian Kinerja</span><i
                     class="bi bi-chevron-down ms-auto"></i>
             </a>
             <ul id="pk-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                 <!-- PK Saya - Semua role bisa akses -->
                 <li>
                     <a href="#">
                         <i class="bi bi-circle"></i><span>PK Saya</span>
                     </a>
                 </li>

                 @if ($fullAccessPK || $kabidAccess)
                     <!-- Daftar PK - Admin, Kaban, Sekban, Kabid -->
                     <li>
                         <a href="{{ url('perjanjian-kinerja') }}">
                             <i class="bi bi-circle"></i><span>Daftar PK</span>
                         </a>
                     </li>
                 @endif

                 @if ($fullAccessPK)
                     <!-- Template PK - Hanya Admin, Kaban, Sekban -->
                     <li>
                         <a href="{{ url('perjanjian-kinerja/template') }}">
                             <i class="bi bi-circle"></i><span>Template PK</span>
                         </a>
                     </li>
                 @endif

                 @if ($fullAccessPK || $kabidAccess)
                     <!-- Buat PK Baru - Admin, Kaban, Sekban, Kabid -->
                     <li>
                         <a href="{{ url('perjanjian-kinerja/create') }}">
                             <i class="bi bi-circle"></i><span>Buat PK Baru</span>
                         </a>
                     </li>
                 @endif
             </ul>
         </li><!-- End Perjanjian Kinerja Nav -->

         <!-- Penugasan Pegawai Nav -->
         @php
             $canAccessPenugasan = !in_array($kodeJabatan, ['PELAKSANA', 'GATEK']);
         @endphp

         @if ($canAccessPenugasan)
             <li class="nav-item">
                 <a class="nav-link {{ Request::routeIs('penugasan.index') ? 'active' : 'collapsed' }}"
                     href="{{ route('penugasan.index') }}">
                     <i class="bi bi-person-lines-fill"></i>
                     <span>Penugasan Pegawai</span>
                 </a>
             </li><!-- End Penugasan Pegawai Nav -->
         @endif

         <!-- Tugas Saya Nav (Semua role) -->
         <li class="nav-item">
             <a class="nav-link {{ Request::routeIs('penugasan.show') ? 'active' : 'collapsed' }}"
                 href="{{ route('penugasan.show', AUTH::user()->id) }}">
                 <i class="bi bi-list-task"></i>
                 <span>Tugas Saya</span>
             </a>
         </li><!-- End Tugas Saya Nav -->

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
         @php
             $kodeJabatan = auth()->user()->jabatan?->kode;
             $canAccessMasterData = in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
         @endphp

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
         @php
             $canAccessMonitoring = in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID']);
             $canAccessSistem = in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
         @endphp

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
