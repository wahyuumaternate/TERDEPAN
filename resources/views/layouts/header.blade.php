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

             @php
                 $notifikasiHeader = auth()->check()
                     ? app(\Modules\Penugasan\Services\NotifikasiService::class)->untuk(auth()->user())
                     : collect();
                 $warnaNotifHeader = [
                     'info' => 'text-info',
                     'warning' => 'text-warning',
                     'danger' => 'text-danger',
                     'success' => 'text-success',
                 ];
             @endphp

             <li class="nav-item dropdown">
                 <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown" aria-label="Notifikasi">
                     <i class="bi bi-bell"></i>
                     @if ($notifikasiHeader->isNotEmpty())
                         <span class="badge bg-primary badge-number">{{ $notifikasiHeader->count() }}</span>
                     @endif
                 </a><!-- End Notification Icon -->

                 <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                     <li class="dropdown-header">
                         @if ($notifikasiHeader->isNotEmpty())
                             Anda memiliki {{ $notifikasiHeader->count() }} notifikasi
                         @else
                             Tidak ada notifikasi baru
                         @endif
                         <a href="{{ route('notifications.index') }}"><span class="badge rounded-pill bg-primary p-2 ms-2">Lihat semua</span></a>
                     </li>
                     <li>
                         <hr class="dropdown-divider">
                     </li>

                     @forelse ($notifikasiHeader->take(5) as $item)
                         <li class="notification-item">
                             <i class="bi {{ $item['ikon'] }} {{ $warnaNotifHeader[$item['tipe']] ?? 'text-primary' }}"></i>
                             <a href="{{ $item['link'] }}" class="d-block text-reset text-decoration-none">
                                 <p class="mb-0">{{ $item['pesan'] }}</p>
                             </a>
                         </li>
                         @if (!$loop->last)
                             <li>
                                 <hr class="dropdown-divider">
                             </li>
                         @endif
                     @empty
                         <li class="notification-item">
                             <i class="bi bi-check-circle text-success"></i>
                             <div>
                                 <p class="mb-0">Tidak ada yang perlu ditindaklanjuti saat ini.</p>
                             </div>
                         </li>
                     @endforelse

                     <li>
                         <hr class="dropdown-divider">
                     </li>
                     <li class="dropdown-footer">
                         <a href="{{ route('notifications.index') }}">Lihat semua notifikasi</a>
                     </li>
                     <li>
                         <hr class="dropdown-divider">
                     </li>
                     <li class="dropdown-footer">
                         <a href="#" id="push-toggle-btn" class="d-flex align-items-center justify-content-center gap-1">
                             <i class="bi bi-bell-slash" id="push-toggle-icon"></i>
                             <span id="push-toggle-label">Aktifkan Notifikasi Browser</span>
                         </a>
                     </li>

                 </ul><!-- End Notification Dropdown Items -->

             </li><!-- End Notification Nav -->

             <li class="nav-item dropdown pe-3">

                 <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#"
                     data-bs-toggle="dropdown">
                     @if (Auth::user() && Auth::user()->profile?->foto_profile_url)
                         <img src="{{ Auth::user()->profile->foto_profile_url }}"
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

 @push('scripts')
     <script>
         (function () {
             const VAPID_PUBLIC_KEY = @json(config('webpush.vapid.public_key'));
             const SUBSCRIBE_URL = @json(route('penugasan.api.push-subscription.store'));
             const UNSUBSCRIBE_URL = @json(route('penugasan.api.push-subscription.destroy'));
             const btn = document.getElementById('push-toggle-btn');
             const icon = document.getElementById('push-toggle-icon');
             const label = document.getElementById('push-toggle-label');

             if (!btn || !('serviceWorker' in navigator) || !('PushManager' in window) || !VAPID_PUBLIC_KEY) {
                 return;
             }

             function urlBase64ToUint8Array(base64String) {
                 const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
                 const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                 const rawData = window.atob(base64);
                 return Uint8Array.from([...rawData].map((c) => c.charCodeAt(0)));
             }

             function csrfToken() {
                 return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
             }

             function updateIcon(subscribed) {
                 icon.classList.toggle('bi-bell-slash', !subscribed);
                 icon.classList.toggle('bi-bell-fill', subscribed);
                 label.textContent = subscribed ? 'Nonaktifkan Notifikasi Browser' : 'Aktifkan Notifikasi Browser';
             }

             async function getSubscription() {
                 const registration = await navigator.serviceWorker.register('/sw.js');
                 return registration.pushManager.getSubscription();
             }

             async function subscribe() {
                 const registration = await navigator.serviceWorker.register('/sw.js');
                 const subscription = await registration.pushManager.subscribe({
                     userVisibleOnly: true,
                     applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
                 });

                 await fetch(SUBSCRIBE_URL, {
                     method: 'POST',
                     headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                     body: JSON.stringify(subscription.toJSON()),
                 });

                 updateIcon(true);
             }

             async function unsubscribe() {
                 const subscription = await getSubscription();
                 if (!subscription) {
                     updateIcon(false);
                     return;
                 }

                 await fetch(UNSUBSCRIBE_URL, {
                     method: 'DELETE',
                     headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                     body: JSON.stringify({ endpoint: subscription.endpoint }),
                 });

                 await subscription.unsubscribe();
                 updateIcon(false);
             }

             btn.addEventListener('click', async function (event) {
                 event.preventDefault();

                 try {
                     const existing = await getSubscription();

                     if (existing) {
                         await unsubscribe();
                         return;
                     }

                     if (Notification.permission === 'denied') {
                         alert('Izin notifikasi browser diblokir. Aktifkan lewat pengaturan browser Anda.');
                         return;
                     }

                     const permission = await Notification.requestPermission();
                     if (permission !== 'granted') {
                         return;
                     }

                     await subscribe();
                 } catch (error) {
                     console.error('Gagal mengatur notifikasi push:', error);
                 }
             });

             getSubscription().then((subscription) => updateIcon(!!subscription));
         })();
     </script>
 @endpush
