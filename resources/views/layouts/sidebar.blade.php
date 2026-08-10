 <!-- ======= Sidebar ======= -->
 <aside id="sidebar" class="sidebar">
     <ul class="sidebar-nav" id="sidebar-nav">

         <!-- Dashboard Nav -->
         <li class="nav-item">
             <a class="nav-link {{ Request::is('/') ? 'active' : 'collapsed' }}" href="{{ url('/') }}">
                 <i class="bi bi-house"></i>
                 <span>Dashboard</span>
             </a>
         </li><!-- End Dashboard Nav -->

         <!-- Terminal Data -->
         <li class="nav-heading">Terminal Data</li>

         @php
             $dokumenActive = request()->is('terminal-data/folders*') || request()->is('terminal-data/folder*');
         @endphp

         <li class="nav-item">
             <a class="nav-link {{ $dokumenActive ? '' : 'collapsed' }}"
                 href="{{ route('terminaldata.folders.index') }}">
                 <i class="bi bi-folder2-open"></i><span>Penyimpanan</span>
             </a>
         </li>

         <li class="nav-item">
             <a class="nav-link {{ Request::is('terminal-data/sampah*') ? '' : 'collapsed' }}"
                 href="{{ route('terminaldata.sampah.index') }}">
                 <i class="bi bi-trash"></i>
                 <span>Sampah</span>
             </a>
         </li>

         <!-- E-Kinerja -->
         <li class="nav-heading">E-Kinerja Perencana</li>

         @php
             // Variabel ini dipakai lintas menu (Master Data, Monitoring, Sistem di bawah),
             // jadi tetap dihitung di sini terlepas dari status modul PerjanjianKinerja.
             $kodeJabatan = auth()->user()->profile?->jabatan?->kode;
             $canAccessMasterData = in_array($kodeJabatan, ['ADMIN']);
             $canAccessMonitoring = in_array($kodeJabatan, ['ADMIN']);
             $canAccessSistem = in_array($kodeJabatan, ['ADMIN']);
         @endphp

         {{-- Perjanjian Kinerja Nav — modul untuk sementara dinonaktifkan (lihat modules_statuses.json).
              Route module tidak ter-register saat nonaktif, jadi seluruh menu ini disembunyikan
              agar tidak memicu RouteNotFoundException. --}}
         @if (\Route::has('perjanjian-kinerja.pk-saya'))
             @php
                 $fullAccessPK = in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN']);
                 $kabidAccess = $kodeJabatan === 'KABID';
                 $limitedAccessPK = in_array($kodeJabatan, ['KASUBAG', 'JF', 'PELAKSANA', 'GATEK']);
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
         @endif

         <!-- ========================================== -->
         <!-- Penugasan (Tugas Saya + tab "Tugas yang Saya Berikan" jika berwenang) -->
         <!-- ========================================== -->
         <li class="nav-item">
             <a href="{{ route('penugasan.tugas-saya') }}" class="nav-link collapsed">
                 <i class="bi bi-list"></i><span>Penugasan</span>
             </a>
         </li><!-- End Penugasan Nav -->

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
                         <a href="{{ route('master.sub-bidang.index') }}">
                             <i class="bi bi-circle"></i><span>Sub Bidang</span>
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
                         <li>
                             <a href="{{ route('l5-swagger.default.api') }}" target="_blank" rel="noopener">
                                 <i class="bi bi-circle"></i><span>Dokumentasi API</span>
                             </a>
                         </li>
                     </ul>
                 </li><!-- End Sistem Nav -->
             @endif
         @endif

     </ul>

 </aside><!-- End Sidebar-->
