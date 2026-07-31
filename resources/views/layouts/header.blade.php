 @push('styles')
     <style>
         /* Make button normal size on medium and larger screens */
         @media (min-width: 768px) {
             .btn-md-normal {
                 padding: 0.375rem 0.75rem;
                 font-size: 1rem;
             }
         }
     </style>
 @endpush

 <!-- ======= Header ======= -->
 <header id="header" class="header fixed-top d-flex align-items-center">

     <div class="d-flex align-items-center justify-content-between">
         <a href="{{ url('/') }}" class="logo d-flex align-items-center">
             {{-- <i class="bi bi-folder2-open"></i> --}}
             <img src="{{ asset('assets/img/logo.webp') }}" alt="Logo TERDEPAN" height="75" width="auto">
             <span class="d-none d-lg-block">TERDEPAN</span>
         </a>
         <i class="bi bi-list toggle-sidebar-btn"></i>
     </div><!-- End Logo -->

     <!-- Action Buttons -->
     <div class="d-flex align-items-center gap-2 ms-3">
         <!-- Modul Penggunaan Button (Visible on all devices) -->
         <button class="btn btn-success text-white btn-sm btn-md-normal" data-bs-toggle="modal"
             data-bs-target="#modalModulPenggunaan">
             <i class="bi bi-download me-1"></i>
             <span class="d-none d-md-inline">Modul Penggunaan</span>
             <span class="d-inline d-md-none">Modul</span>
         </button>

         <!-- Download APK Button (Only visible on mobile) -->
         <button class="btn btn-primary text-white btn-sm d-block d-lg-none" data-bs-toggle="modal"
             data-bs-target="#modalApkDownload">
             <i class="bi bi-download me-1"></i>
             <span class="d-none d-sm-inline">Download APK</span>
             <span class="d-inline d-sm-none">APK</span>
         </button>
     </div>

     <nav class="header-nav ms-auto">
         <ul class="d-flex align-items-center">

             <li class="nav-item dropdown">
                 <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                     <i class="bi bi-bell"></i>
                     <span class="badge bg-primary badge-number">4</span>
                 </a><!-- End Notification Icon -->

                 <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                     <li class="dropdown-header">
                         You have 4 new notifications
                         <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
                     </li>
                     <li>
                         <hr class="dropdown-divider">
                     </li>

                     <li class="notification-item">
                         <i class="bi bi-exclamation-circle text-primary"></i>
                         <div>
                             <h4>Lorem Ipsum</h4>
                             <p>Quae dolorem earum veritatis oditseno</p>
                             <p>30 min. ago</p>
                         </div>
                     </li>

                     <li>
                         <hr class="dropdown-divider">
                     </li>
                     <li class="dropdown-footer">
                         <a href="#">Show all notifications</a>
                     </li>

                 </ul><!-- End Notification Dropdown Items -->

             </li><!-- End Notification Nav -->


             <li class="nav-item dropdown pe-3">

                 <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#"
                     data-bs-toggle="dropdown">
                     @if (Auth::user() && Auth::user()->profile?->foto_profile_path)
                         <img src="{{ asset('storage/' . Auth::user()->profile->foto_profile_path) }}"
                             alt="{{ Auth::user()->nama }}" class="rounded-circle">
                     @else
                         @if (Auth::user()->profile?->jenis_kelamin == 'L')
                             <img src="{{ asset('assets/img/avatar-laki-laki.webp') }}" alt="{{ Auth::user()->nama }}"
                                 class="rounded-circle">
                         @else
                             <img src="{{ asset('assets/img/avatar-perempuan.webp') }}" alt="{{ Auth::user()->nama }}"
                                 class="rounded-circle">
                         @endif
                     @endif

                     <span class="d-none d-md-block dropdown-toggle ps-2">{{ Auth::user()->nama }}</span>
                 </a><!-- End Profile Iamge Icon -->

                 <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                     <li class="dropdown-header">
                         <h6>{{ Auth::user()->nama }}</h6>
                         <span>{{ Auth::user()->profile?->jabatan?->nama }}</span>
                     </li>
                     <li>
                         <hr class="dropdown-divider">
                     </li>

                     <li>
                         <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.edit') }}">
                             <i class="bi bi-person"></i>
                             <span>Profil Saya</span>
                         </a>
                     </li>
                     <li>
                         <hr class="dropdown-divider">
                     </li>


                     <!-- Step 2: HTML Logout Button -->
                     <li>
                         <form method="POST" action="{{ route('logout') }}" id="logout-form">
                             @csrf
                             <button type="button" class="dropdown-item d-flex align-items-center"
                                 style="border: none; background: none; width: 100%; text-align: left; cursor: pointer;"
                                 onclick="confirmLogout()">
                                 <i class="bi bi-box-arrow-right"></i>
                                 <span>Log Out</span>
                             </button>
                         </form>
                     </li>


                 </ul><!-- End Profile Dropdown Items -->
             </li><!-- End Profile Nav -->

         </ul>
     </nav><!-- End Icons Navigation -->

 </header><!-- End Header -->

 <!-- Modal Download APK -->
 <div class="modal fade" id="modalApkDownload" tabindex="-1" aria-labelledby="modalApkDownloadLabel"
     aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
             <div class="modal-header border-0">
                 <h5 class="modal-title" id="modalApkDownloadLabel">
                     <i class="bi bi-info-circle text-primary me-2"></i>Informasi
                 </h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body text-center py-4">
                 <i class="bi bi-cone-striped text-warning" style="font-size: 4rem;"></i>
                 <h5 class="mt-3 mb-2">Aplikasi Dalam Tahap Pengembangan</h5>
                 <p class="text-muted">
                     Aplikasi mobile TERDEPAN sedang dalam tahap pengembangan.<br>
                     Mohon tunggu hingga aplikasi siap untuk diunduh.
                 </p>
             </div>
             <div class="modal-footer border-0">
                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
             </div>
         </div>
     </div>
 </div>

 <!-- Modal Modul Penggunaan -->
 <div class="modal fade" id="modalModulPenggunaan" tabindex="-1" aria-labelledby="modalModulPenggunaanLabel"
     aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
             <div class="modal-header border-0">
                 <h5 class="modal-title" id="modalModulPenggunaanLabel">
                     <i class="bi bi-book text-success me-2"></i>Modul Penggunaan
                 </h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body text-center py-4">
                 <i class="bi bi-cone-striped text-warning" style="font-size: 4rem;"></i>
                 <h5 class="mt-3 mb-2">Modul Dalam Tahap Pengembangan</h5>
                 <p class="text-muted">
                     Modul penggunaan aplikasi TERDEPAN sedang dalam tahap penyusunan.<br>
                     Mohon tunggu hingga modul siap untuk diunduh.
                 </p>
             </div>
             <div class="modal-footer border-0">
                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
             </div>
         </div>
     </div>
 </div>
