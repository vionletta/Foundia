# Admin Panel - Foundia

## Akses Admin
Untuk mengakses panel admin, gunakan URL:
```
http://localhost/foundia(new)/admin/
```

## Fitur yang Tersedia

### 1. Beranda (`index.php`)
- Menampilkan statistik keseluruhan sistem
- Total barang, barang pending, barang terverifikasi
- Total user terdaftar
- Daftar barang terbaru

### 2. Cari Barang (`search_barang.php`)
- Pencarian barang berdasarkan nama, deskripsi, atau lokasi
- Menampilkan hasil pencarian dengan detail lengkap
- Filter berdasarkan status verifikasi

### 3. Daftar Barang (`daftar_barang.php`)
- Menampilkan semua barang yang dilaporkan
- **Popup Detail**: Klik pada kartu barang untuk melihat detail
- **Verifikasi**: Konfirmasi barang yang menunggu verifikasi
- **Hapus**: Hapus barang (dengan konfirmasi)
- Grid layout yang responsif

### 4. Dashboard Verifikasi (`dashboard.php`)
- Fokus pada barang yang menunggu verifikasi
- Quick action untuk verifikasi barang
- Notifikasi sukses/error

### 5. User Management (`user_management.php`)
- Daftar semua user terdaftar
- Statistik user (total, admin, regular)
- Informasi jumlah barang per user
- Hapus user (kecuali admin)
- Tidak dapat menghapus user admin untuk keamanan

## Cara Menggunakan

### Login sebagai Admin
1. Pastikan user memiliki role 'admin' di database
2. Login melalui halaman login biasa
3. Sistem akan otomatis redirect ke admin panel

### Verifikasi Barang
1. Pergi ke "Daftar Barang" atau "Dashboard"
2. Klik pada barang untuk melihat detail
3. Klik tombol "Verifikasi" untuk mengkonfirmasi
4. Atau gunakan "Dashboard" untuk verifikasi cepat

### Menghapus Barang
1. Di "Daftar Barang", klik barang yang ingin dihapus
2. Klik tombol "Hapus" di popup detail
3. Konfirmasi penghapusan

### Mengelola User
1. Pergi ke halaman "User"
2. Lihat statistik dan daftar user
3. Hapus user biasa (tidak bisa hapus admin)

## Struktur Database yang Digunakan

### Tabel `items`
- `itemID` - Primary Key
- `itemName` - Nama barang
- `description` - Deskripsi
- `location` - Lokasi
- `status` - 'posted' atau 'verified'
- `userID` - Foreign Key ke users
- `created_at` - Timestamp

### Tabel `users`
- `userID` - Primary Key
- `nama_lengkap` - Nama lengkap
- `email` - Email
- `password` - Password hash
- `role` - 'admin' atau 'user'
- `created_at` - Timestamp

## Keamanan
- Proteksi session untuk semua halaman admin
- Validasi role admin sebelum akses
- Konfirmasi untuk aksi penghapusan
- Prevent deletion admin users
- SQL injection prevention dengan prepared statements

## Troubleshooting

### Error "Unauthorized"
- Pastikan login sebagai user dengan role 'admin'
- Check session dan cookie browser

### Barang tidak muncul
- Pastikan ada data di tabel `items`
- Check koneksi database

### Popup tidak berfungsi
- Pastikan JavaScript enabled
- Check console untuk error
- Pastikan file `get_item_detail.php` ada dan accessible
