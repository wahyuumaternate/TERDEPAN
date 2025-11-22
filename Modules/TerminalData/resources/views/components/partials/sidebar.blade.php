 <!-- ======= Sidebar ======= -->
 <aside id="sidebar" class="sidebar" style="display: flex; flex-direction: column; max-height: 100vh; overflow: hidden;">
     <ul class="sidebar-nav" id="sidebar-nav" style="flex: 1; overflow-y: auto; overflow-x: hidden;">

         <!-- E-Kinerja Nav -->
         <li class="nav-item">
             <a class="nav-link bg-primary text-white" href="{{ url('/e-kinerja') }}">
                 <i class="bi bi-arrow-left text-white"></i>
                 <span>E-Kinerja Perencana</span>
             </a>
         </li><!-- End E-Kinerja Nav -->

         <hr>

         <!-- Home Nav -->
         <li class="nav-item">
             <a class="nav-link {{ Request::is('terminal-data') ? '' : 'collapsed' }}"
                 href="{{ url('/terminal-data') }}">
                 <i class="bi bi-house"></i>
                 <span>Beranda</span>
             </a>
         </li><!-- End Home Nav -->

         <!-- Bappeda Storage -->
         <li class="nav-heading">Terminal Data</li>

         <!-- Management File / Arsip Dokumen -->
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


         <!-- Monitoring Nav -->
         @can('viewAny', \Modules\TerminalData\Models\TdFolder::class)
             @php
                 $kodeJabatan = auth()->user()->jabatan?->kode;
                 $canViewMonitoring = in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN', 'KABID']);
             @endphp

             @if ($canViewMonitoring)
                 <!-- Sistem -->
                 <li class="nav-heading">Sistem & Monitoring</li>

                 <!-- Monitoring Nav -->
                 <li class="nav-item">
                     <a class="nav-link collapsed" href="#">
                         <i class="bi bi-activity"></i><span>Monitoring Aktifitas</span>
                     </a>
                 </li><!-- End Monitoring Nav -->
             @endif
         @endcan

         <!-- Sistem Nav -->
         @php
             $kodeJabatan = auth()->user()->jabatan?->kode;
             $isAdmin = $kodeJabatan === 'ADMIN';
         @endphp

         @if ($isAdmin)
             <li class="nav-item">
                 <a class="nav-link collapsed" data-bs-target="#sistem-nav" data-bs-toggle="collapse" href="#">
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
     </ul>

     <div class="sidebar-storage p-3" style="flex-shrink: 0; border-top: 1px solid #dee2e6;">
         <div class="text-center mb-3">
             <i class="bi bi-folder2-open text-primary" style="font-size: 3rem;"></i>
         </div>
         <h6 class="text-center mb-2">{{ $percentage }}% Telah digunakan</h6>
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
