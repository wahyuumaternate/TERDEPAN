 <!-- ======= Header ======= -->
 <header id="header" class="header fixed-top d-flex align-items-center">

     <div class="d-flex align-items-center justify-content-between">
         <a href="{{ url('/') }}" class="logo d-flex align-items-center">
             {{-- <i class="bi bi-folder2-open"></i> --}}
             <span class="d-none d-lg-block">TERDEPAN</span>
         </a>
         <i class="bi bi-list toggle-sidebar-btn"></i>
     </div><!-- End Logo -->

     <div class="search-bar">
         <form class="search-form d-flex align-items-center" method="POST" action="#">
             @csrf
             <input type="text" name="query" placeholder="Search anything here" title="Enter search keyword">
             <button type="submit" title="Search"><i class="bi bi-search"></i></button>
         </form>
     </div><!-- End Search Bar -->

     <nav class="header-nav ms-auto">
         <ul class="d-flex align-items-center">

             <li class="nav-item d-block d-lg-none">
                 <a class="nav-link nav-icon search-bar-toggle " href="#">
                     <i class="bi bi-search"></i>
                 </a>
             </li><!-- End Search Icon-->

             <!-- Download APK Button -->
             <li class="nav-item">
                 <a class="btn btn-primary text-white me-2" href="#">
                     Download APK
                 </a>
             </li>

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

             <li class="nav-item dropdown">
                 <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                     <i class="bi bi-chat-dots"></i>
                 </a>
             </li>

             <li class="nav-item dropdown pe-3">

                 <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#"
                     data-bs-toggle="dropdown">
                     @if (Auth::user() && Auth::user()->foto_profile_path)
                         <img src="{{ asset('storage/' . Auth::user()->foto_profile_path) }}"
                             alt="{{ Auth::user()->nama }}" class="rounded-circle">
                     @else
                         @if (Auth::user()->jenis_kelamin == 'L')
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
                         <span>{{ Auth::user()->jabatan->nama }}</span>
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
                                 <span>Sign Out</span>
                             </button>
                         </form>
                     </li>


                 </ul><!-- End Profile Dropdown Items -->
             </li><!-- End Profile Nav -->

         </ul>
     </nav><!-- End Icons Navigation -->

 </header><!-- End Header -->
