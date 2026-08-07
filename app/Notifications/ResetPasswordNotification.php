<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Ganti pesan bawaan Illuminate\Auth\Notifications\ResetPassword (bahasa Inggris) dengan
 * versi Bahasa Indonesia — dipakai baik untuk alur "Lupa Password" mandiri maupun "Kirim
 * Email Login" oleh admin (docs/flow/02-alur-autentikasi.md), keduanya lewat mekanisme
 * Password::sendResetLink() yang sama. Diaktifkan lewat User::sendPasswordResetNotification().
 */
class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $menit = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Atur Kata Sandi Akun TERDEPAN')
            ->greeting('Halo, '.($notifiable->nama ?? $notifiable->email).'!')
            ->line('Kami menerima permintaan untuk mengatur kata sandi akun TERDEPAN Anda.')
            ->line('Klik tombol di bawah ini untuk membuat kata sandi baru.')
            ->action('Atur Kata Sandi', $url)
            ->line("Tautan ini akan kedaluwarsa dalam {$menit} menit.")
            ->line('Jika Anda tidak merasa meminta ini, abaikan saja email ini — kata sandi Anda tidak akan berubah.');
    }
}
