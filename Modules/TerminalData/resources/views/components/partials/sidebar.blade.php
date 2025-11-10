 <!-- ======= Sidebar ======= -->
 <aside id="sidebar" class="sidebar">
     <ul class="sidebar-nav" id="sidebar-nav">

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
             <a class="nav-link {{ Request::is('terminal-data') ? '' : 'collapsed' }}" href="{{ url('/terminal-data') }}">
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
             <a class="nav-link {{ $dokumenActive ? '' : 'collapsed' }}" href="{{ route('terminaldata.folders.index') }}">
                 <i class="bi bi-folder2-open"></i><span>Penyimpanan</span>
             </a>
         </li>

         <li class="nav-item">
             <a class="nav-link {{ Request::is('terminal-data/sampah*') ? '' : 'collapsed' }}" href="{{ route('terminaldata.sampah.index') }}">
                 <i class="bi bi-trash"></i>
                 <span>Sampah</span>
             </a>
         </li>

         <!-- Sistem -->
         <li class="nav-heading">Sistem</li>

         <!-- Sistem Nav -->
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