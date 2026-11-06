# Sistem Monitoring Presensi Guru

Aplikasi web untuk mencatat presensi guru per jam pelajaran, memantau status kelas oleh guru piket,
dan mengamankan data dengan kontrol akses berbasis peran, deteksi anomali berbasis aturan, serta audit log
berantai hash. Prototipe akademik, Capstone Project Informatika Universitas Siliwangi,
mitra SMK Fadris.

## Kebutuhan
- PHP 8.2 dengan ekstensi `intl`, `zip`, `gd`, `fileinfo`, `mbstring`, `pdo_mysql`
- Composer
- MariaDB 10.4 atau MySQL (XAMPP)
- Node.js hanya bila mengubah tema (tidak wajib)

## Pemasangan
```bash
git clone https://github.com/indramf/presensi-guru.git
cd presensi-guru
composer install
cp .env.example .env
php artisan key:generate
# buat database kosong, lalu isi DB_* di .env
php artisan migrate --seed
php artisan serve
```
Buka http://127.0.0.1:8000/admin.

## Akun uji (hanya lingkungan uji)
| Peran | Email | Kata sandi |
|---|---|---|
| Administrator | admin@presensiguru.local | password |
| Guru piket | piket1@presensiguru.local | password |
| Guru | rs001@presensiguru.local sampai rs0025@presensiguru.local | password |

## Perintah penting
| Perintah | Fungsi |
|---|---|
| `php artisan test` | Menjalankan seluruh test |
| `php artisan audit:verify` | Memeriksa integritas rantai audit log |
| `php artisan uat:prepare --fresh` | Menyiapkan data UAT di sekitar waktu sekarang |
| `php artisan uat:check` | Memeriksa kesiapan lingkungan UAT |

## Konfigurasi
| Variabel | Arti | Nilai awal |
|---|---|---|
| `ATTENDANCE_OPEN_BEFORE` | Menit sebelum jam mulai presensi dibuka | 15 |
| `ATTENDANCE_LATE_AFTER` | Menit setelah jam mulai dianggap terlambat | 10 |
| `MONITORING_UNCONFIRMED_AFTER` | Menit tanpa presensi sebelum "belum terkonfirmasi" | 5 |
| `ANOMALY_OFFICE_START` / `ANOMALY_OFFICE_END` | Jam kerja administrator untuk aturan AN-07 | 06:00 / 18:00 |
| `AUDIT_HMAC_KEY` | Kunci opsional untuk memperkuat hash audit log | kosong |

## Keterbatasan
- Prototipe akademik, bukan untuk produksi sebelum ada tinjauan keamanan, pencadangan, dan kebijakan retensi.
- Deteksi anomali berbasis aturan dengan ambang awal; hasilnya adalah petunjuk, bukan bukti pelanggaran.
- Integritas audit log dideteksi dengan hash berantai dan checkpoint; ini bukan jaminan log tak dapat dimanipulasi.
- Hari libur dan kalender akademik belum dikenali.

## Lisensi dan atribusi
Dibangun dengan Laravel, Filament, Livewire, dan Spatie Laravel Permission (lisensi sumber terbuka masing-masing).
