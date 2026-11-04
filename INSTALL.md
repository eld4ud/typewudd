# Typewud · Landing Page 3D v5

Patch ini **khusus landing page** untuk Typewud Laravel. Tidak mengubah database, `.env`, autentikasi, leaderboard, typing engine, atau sistem duel.

## Perubahan

- Tema gelap editorial terinspirasi video yang kamu kirim: navbar ringan, judul serif, tombol sederhana, pencahayaan studio.
- `public/models/typewud-keyboard.glb`: model 3D *orisinal* dengan 75 keycap, chassis metal, key legends dan switch bases.
- `public/js/typewud-hero-3d.js`: model dimuat dengan **Three.js**, melayang dan otomatis berputar perlahan.
- **Klik-drag mouse** atau geser jari untuk memutar keyboard; putaran otomatis lanjut setelah 4,5 detik.
- Responsif mobile/desktop, dukungan pengurangan animasi (`prefers-reduced-motion`), pemberitahuan jika WebGL/CDN gagal.
- Bukan React, tidak memerlukan `npm install` atau migrasi baru. Three.js diunduh browser dari jsDelivr pada saat membuka halaman, sehingga membutuhkan internet.

## File yang berubah

```text
resources/views/layout.blade.php
resources/views/home.blade.php
public/css/typewud.css
public/js/typewud-hero-3d.js
public/models/typewud-keyboard.glb
```

## Pemasangan dengan PowerShell (di root proyek Laravel)

1. Stop `php artisan serve` dengan `Ctrl+C`.
2. Download ZIP patch dan tempatkan di folder `Downloads`.
3. Cadangkan file lama:

```powershell
$backup = "..\typewud_landing_backup_$(Get-Date -Format yyyyMMdd_HHmmss).zip"
Compress-Archive -Path resources\views\layout.blade.php,resources\views\home.blade.php,public\css\typewud.css -DestinationPath $backup
```

4. Extract dan salin file patch:

```powershell
$zip = "$env:USERPROFILE\Downloads\typewud_landing_3d_v5.zip"
$temp = "$env:TEMP\typewud_landing_3d_v5"
Expand-Archive -LiteralPath $zip -DestinationPath $temp -Force
New-Item -Path "public\models" -ItemType Directory -Force | Out-Null
Copy-Item "$temp\resources\views\layout.blade.php" "resources\views\layout.blade.php" -Force
Copy-Item "$temp\resources\views\home.blade.php" "resources\views\home.blade.php" -Force
Copy-Item "$temp\public\css\typewud.css" "public\css\typewud.css" -Force
Copy-Item "$temp\public\js\typewud-hero-3d.js" "public\js\typewud-hero-3d.js" -Force
Copy-Item "$temp\public\models\typewud-keyboard.glb" "public\models\typewud-keyboard.glb" -Force
php artisan view:clear
php artisan serve
```

5. Buka `http://127.0.0.1:8000` dan tekan `Ctrl+F5`.

## Diagnostik

- Jika layar masih desain lama: pastikan terminal berada di folder Typewud, file `resources/views/home.blade.php` sudah tertimpa, lalu `php artisan view:clear`.
- Jika teks tampil tetapi 3D kosong: buka Developer Tools (`F12`) → Console dan Network. Cek file `/models/typewud-keyboard.glb` serta apakah CDN `cdn.jsdelivr.net` bisa diakses. WebGL perlu aktif.
- CDN three.js versi 0.160.1 dipin di `resources/views/home.blade.php`; modul di-load hanya pada landing page.
- Kalau di hosting, pastikan `.glb` disajikan sebagai file statis dan cache versi baru disegarkan.

## Pengujian

- Struktur `.glb` diverifikasi melalui `trimesh` (scene 3D dengan lebih dari 100 mesh), bukan file gambar yang diganti ekstensi.
- JavaScript dan PHP sintaks diperiksa.
- Rendering Three.js dan gesture perlu diperiksa di browser lokal milikmu karena koneksi CDN tidak tersedia pada lingkungan pengujian paket ini.
