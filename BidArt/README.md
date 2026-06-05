# BidArt - Sistem Lelang Karya Seni

BidArt merupakan platform lelang karya seni berbasis web yang menyediakan sarana bagi seniman untuk memasarkan karya mereka serta memungkinkan kolektor atau pembeli melakukan penawaran terhadap karya seni yang diminati. Sistem ini dikembangkan menggunakan PHP dan MySQL, serta mengimplementasikan stored procedure, trigger, transaction, dan stored function guna menjaga integritas data dan meningkatkan keamanan proses transaksi.

![BidArt](assets/img/beranda.png)

## 📌 Deskripsi Proyek

BidArt memiliki fitur-fitur utama sebagai berikut:

Pengelolaan Karya Seni: Seniman dapat menambahkan, memperbarui, serta menghapus karya seni yang mereka miliki melalui sistem.
Fitur Lelang: Setiap karya seni dapat dipasarkan melalui mekanisme lelang dengan penetapan harga awal dan batas waktu lelang yang telah ditentukan.
Pengelolaan Dana Akun: Pembeli dapat melakukan pengisian saldo untuk digunakan dalam proses penawaran dan transaksi.
Dashboard Pengguna: Sistem menyediakan dashboard yang berbeda bagi seniman dan pembeli sesuai dengan kebutuhan serta peran masing-masing pengguna.

Sistem ini mengimplementasikan konsep database lanjutan untuk memastikan keamanan dan integritas data.

## 📊 Detail Implementasi

### 🧠 Stored Procedure

Stored procedure digunakan untuk mengenkapsulasi logika bisnis kompleks di sisi database, memastikan konsistensi dan keamanan operasi.

![Procedure](assets/img/procedure.png)

#### 1. `sp_place_bid` - Prosedur untuk memasukkan penawaran

**Implementasi di file**: `place_bid.php`

```php
// Panggil stored procedure
$query = "CALL sp_place_bid($auction_id, $bidder_id, $bid_amount)";
$result = mysqli_query($conn, $query);
```

Prosedur ini menangani validasi penawaran, memastikan penawaran lebih tinggi dari harga saat ini, dan memperbarui harga lelang.

#### 2. `sp_tambah_karya_seni` - Prosedur untuk menambahkan karya seni baru

**Implementasi di file**: `upload.php`

```php
query = "CALL sp_tambah_karya_seni('$title', '$description', $artist_id, $starting_price, '$relativePath')";
mysqli_query($conn, $query);

$_SESSION['success'] = "Karya seni berhasil ditambahkan dan lelang dimulai!";
```

Prosedur ini menangani proses penambahan karya seni baru ke database dan membuat lelang baru secara otomatis.

#### 3. `sp_tutup_lelang` - Prosedur untuk menutup lelang

**Implementasi di file**: `force_close_auction.php` dan `close_auction.php`

```php
if (tutupLelang($auction_id)) {
        $_SESSION['success'] = 'Berhasil menutup lelang';
} else {
    $_SESSION['error'] = 'Gagal menutup lelang';
}
```

Prosedur ini menangani proses penutupan lelang, menentukan pemenang, dan memperbarui status lelang.

### 🚨 Trigger

Trigger adalah kode yang dijalankan secara otomatis di database ketika terjadi operasi tertentu (INSERT, UPDATE, DELETE) pada tabel. Dalam sistem BidArt, trigger diimplementasikan di level database MySQL, bukan di kode PHP. Namun, kita dapat melihat efek dari trigger tersebut dalam perilaku aplikasi.
![Trigger](assets/img/triggers.png)

#### 1. Trigger Pengecekan Akhir Lelang (`tr_check_auction_end`)

Secara otomatis menutup lelang yang telah mencapai waktu berakhir ketika penawaran baru ditempatkan.

```sql
DELIMITER //

CREATE TRIGGER tr_check_auction_end
AFTER INSERT ON bids
FOR EACH ROW
BEGIN
DECLARE v_end_time DATETIME;
DECLARE v_status VARCHAR(20);

    -- Ambil waktu berakhir dan status lelang
    SELECT end_time, status INTO v_end_time, v_status
    FROM auctions
    WHERE id = NEW.auction_id;

    -- Jika lelang sudah berakhir, tutup lelang
    IF NOW() >= v_end_time AND v_status = 'active' THEN
        CALL sp_tutup_lelang(NEW.auction_id);
    END IF;

END //

DELIMITER ;
```

**Tujuan**: Memastikan lelang ditutup secara otomatis ketika waktu berakhir.

#### 2. Pencatat Aktivitas Penawaran (`tr_bid_activity_log`)

Mencatat semua aktivitas penawaran ke tabel transaksi untuk tujuan audit.

```sql
DELIMITER //

CREATE TRIGGER tr_bid_activity_log
AFTER INSERT ON bids
FOR EACH ROW
BEGIN
INSERT INTO transactions (user_id, auction_id, type, amount, description, status)
VALUES (NEW.bidder_id, NEW.auction_id, 'deposit', NEW.bid_amount,
CONCAT('Penawaran ditempatkan untuk lelang ID: ', NEW.auction_id), 'completed');
END //

DELIMITER ;
```

**Tujuan**: Memelihara jejak audit lengkap dari semua aktivitas penawaran.

#### 3. Trigger Update Saldo (`tr_update_balance_after_bid`)

Mengelola update saldo pengguna ketika penawaran ditempatkan, termasuk mengembalikan dana penawar tertinggi sebelumnya.

```sql
DELIMITER //

CREATE TRIGGER tr_update_balance_after_bid
AFTER INSERT ON bids
FOR EACH ROW
BEGIN
-- Cadangkan jumlah penawaran (tahan dana)
UPDATE users
SET balance = balance - NEW.bid_amount
WHERE id = NEW.bidder_id;

    -- Kembalikan dana penawar tertinggi sebelumnya jika ada
    UPDATE users u
    INNER JOIN (
        SELECT bidder_id, bid_amount
        FROM bids
        WHERE auction_id = NEW.auction_id
        AND id < NEW.id
        ORDER BY bid_amount DESC, bid_time ASC
        LIMIT 1
    ) prev_bid ON u.id = prev_bid.bidder_id
    SET u.balance = u.balance + prev_bid.bid_amount
    WHERE prev_bid.bidder_id != NEW.bidder_id;

END //

DELIMITER ;
```

### 🔄 Transaction

Transaction memastikan bahwa serangkaian operasi database berjalan sebagai satu kesatuan yang utuh. Jika satu operasi gagal, semua operasi dibatalkan.

#### 1. Transaction untuk menambah dana

**Implementasi di file**: `add_funds.php`

```php
mysqli_begin_transaction($conn);

// Tambahkan saldo ke akun user
$update = "UPDATE users SET balance = balance + $amount WHERE id = $user_id";
mysqli_query($conn, $update);

// Simpan riwayat transaksi
$description = mysqli_real_escape_string($conn, "Deposit via " . ucfirst(str_replace('_', ' ', $payment_method)));
$log = "INSERT INTO transactions (user_id, type, amount, description, status)
            VALUES ($user_id, 'deposit', $amount, '$description', 'completed')";
mysqli_query($conn, $log);

// Ambil saldo terbaru
$balanceRes = mysqli_query($conn, "SELECT balance FROM users WHERE id = $user_id");
$row = mysqli_fetch_assoc($balanceRes);
$_SESSION['balance'] = $row['balance'];

mysqli_commit($conn);
```

Transaction ini memastikan bahwa penambahan saldo dan pencatatan riwayat transaksi berjalan sebagai satu kesatuan.

#### 2. Transaction untuk mengunggah karya seni

**Implementasi di file**: `upload.php`

```php
mysqli_begin_transaction($conn);

try {
    $artwork_sql = "INSERT INTO artworks (title, description, artist_id, image_path)
                    VALUES ('$title', '$description', $artist_id, '$relativePath')";
    mysqli_query($conn, $artwork_sql);
    $artwork_id = mysqli_insert_id($conn);

    $auction_sql = "INSERT INTO auctions (artwork_id, starting_price, current_price, status, start_time, end_time)
                    VALUES ($artwork_id, $starting_price, $starting_price, 'active', NOW(), '$end_time')";
    mysqli_query($conn, $auction_sql);

    mysqli_commit($conn);
    $_SESSION['success'] = "Karya berhasil ditambahkan";
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = "Error: " . $e->getMessage();

    // Hapus file yang sudah diupload jika terjadi error
    if (file_exists($targetPath)) {
        unlink($targetPath);
    }
}
```

Transaction ini memastikan bahwa penambahan karya seni dan pembuatan lelang baru berjalan sebagai satu kesatuan.

### 📊 Stored Function

Stored function digunakan untuk mengembalikan nilai berdasarkan perhitungan atau query tertentu.
![Function](assets/img/function.png)

#### 1. `hitung_total_bid` - Fungsi untuk menghitung total penawaran pada lelang

**Implementasi di file**: `auction_details.php`

```php
$total_bid_amount = hitungTotalBid($auction_id);
```

```php
<small class="text-muted">Total Bid Amount: Rp. <?php echo number_format($total_bid_amount, 2, ',', '.'); ?></small>

```

#### 2. `get_highest_bid` - Fungsi untuk menghitung total penawaran pada lelang

**Implementasi di file**: `auction_details.php`

```php
$is_winning = false;
if (isset($_SESSION['user_id'])) {
    $top_bidder = getHighestBid($auction_id);
    if ($top_bidder == $_SESSION['user_id']) {
        $is_winning = true;
    }
}
```

Fungsi ini mengembalikan jumlah total penawaran yang telah dilakukan pada suatu lelang.

## 🔄 Backup Otomatis

Sistem backup otomatis diimplementasikan untuk memastikan keamanan dan integritas data dalam sistem BidArt. Backup dilakukan secara berkala untuk mencegah kehilangan data akibat kegagalan sistem atau kesalahan operasional.

#### Implementasi Backup Database

**1. Script Backup Otomatis (backup.cmd)**

```batch
@echo off
:: Set tanggal dan waktu (format: YYYY-MM-DD_HH-MM-SS)
for /f "tokens=1-4 delims=/ " %%a in ("%date%") do (
    set YYYY=%%d
    set MM=%%b
    set DD=%%c
)
for /f "tokens=1-3 delims=:. " %%a in ("%time%") do (
    set HH=%%a
    set Min=%%b
    set Sec=%%c
)

:: Hilangkan spasi di jam jika <10
if "%HH:~0,1%"==" " set HH=0%HH:~1,1%

:: Lokasi file backup
set FILE_BACKUP=C:\laragon\www\BidArt-auction\backups\BidArt_%YYYY%-%MM%-%DD%_%HH%-%Min%-%Sec%.sql

:: Jalankan mysqldump untuk database BidArt_db
mysqldump -u root --routines BidArt_db > "%FILE_BACKUP%"

echo Backup selesai: %FILE_BACKUP%
pause
```

**2. Interface Backup melalui Web (backup.php)**

```php
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

$batFile = realpath(__DIR__ . '/../../scripts/backup.cmd');

if (!file_exists($batFile)) {
    $_SESSION['error'] = 'File backup.cmd tidak ditemukan.';
    header('Location: dashboard.php');
    exit();
}

pclose(popen("start /B \"\" \"$batFile\"", "r"));

$_SESSION['success'] = 'Backup database sedang diproses di background.';
header('Location: dashboard.php');
exit();
```

#### Fitur Backup yang Diimplementasikan

1. **Backup Database Lengkap**: Menggunakan `mysqldump` untuk membuat backup lengkap database termasuk stored procedures, functions, dan triggers
2. **Penamaan File Otomatis**: File backup diberi nama dengan timestamp untuk memudahkan identifikasi
3. **Akses Terbatas**: Hanya admin yang dapat menjalankan proses backup
4. **Eksekusi Background**: Backup dijalankan di background untuk tidak mengganggu operasi sistem
5. **Notifikasi Status**: Sistem memberikan feedback kepada admin tentang status backup

#### Keuntungan Sistem Backup

- **Disaster Recovery**: Memungkinkan pemulihan data jika terjadi kegagalan sistem
- **Data Integrity**: Menjaga konsistensi data dengan backup yang teratur
- **Compliance**: Memenuhi standar keamanan data untuk sistem lelang
- **Audit Trail**: Menyimpan riwayat perubahan data untuk keperluan audit

## 🧩 Relevansi Proyek dengan Pemrosesan Data Terdistribusi

Meskipun BidArt saat ini diimplementasikan sebagai sistem monolitik, proyek ini memiliki relevansi yang kuat dengan konsep pemrosesan data terdistribusi dan dapat dikembangkan ke arah tersebut.

#### Aspek Terdistribusi dalam Sistem Lelang

**1. Skalabilitas Horizontal**

- Sistem lelang memerlukan kemampuan menangani banyak pengguna secara bersamaan
- Database dapat didistribusikan berdasarkan region atau kategori karya seni
- Load balancing untuk menangani traffic tinggi saat lelang populer

**2. Konsistensi Data Terdistribusi**

- Penawaran lelang memerlukan konsistensi yang ketat (ACID properties)
- Implementasi distributed transactions untuk memastikan integritas data
- Conflict resolution untuk penawaran yang terjadi bersamaan

**3. Real-time Processing**

- Sistem lelang memerlukan update real-time untuk harga dan status lelang
- Event streaming untuk notifikasi penawaran baru
- Distributed caching untuk performa yang optimal
