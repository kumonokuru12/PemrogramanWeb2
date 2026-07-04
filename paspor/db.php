<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_paspor');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="background:#fee;color:#c00;padding:20px;font-family:sans-serif;border-radius:8px;margin:20px;">
        <strong>Koneksi Database Gagal!</strong><br>
        Pastikan XAMPP (Apache + MySQL) sudah berjalan dan database sudah dibuat.<br>
        Error: ' . htmlspecialchars($conn->connect_error) . '
    </div>');
}

$conn->set_charset("utf8mb4");

// Helper: Nama hari dari tanggal
function getNamaHari($tanggal) {
    $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    return $hari[date('w', strtotime($tanggal))];
}

// Helper: Hitung jadwal datang otomatis (kapasitas 5 orang/hari, skip Minggu)
function hitungJadwal($conn, $tanggal_daftar) {
    $kapasitas = 5;
    $jam_slot  = ['08:00:00','09:00:00','10:00:00','11:00:00','13:00:00'];
    $tgl_start = strtotime($tanggal_daftar);

    for ($i = 0; $i < 30; $i++) {
        $check_date = date('Y-m-d', strtotime("+$i day", $tgl_start));
        // Skip Minggu
        if (date('w', strtotime($check_date)) == 0) continue;

        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM tbl_daftar WHERE tanggal_datang = ?");
        $stmt->bind_param("s", $check_date);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        if ($count < $kapasitas) {
            return [
                'hari'    => getNamaHari($check_date),
                'tanggal' => $check_date,
                'jam'     => $jam_slot[$count],
            ];
        }
    }
    return null; // Semua slot penuh dalam 30 hari
}

// Helper: Prepared-statement helper untuk query satu baris
function queryRow($conn, $sql, $types = '', ...$params) {
    $stmt = $conn->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row;
}

// Helper: Prepared-statement helper untuk execute tanpa result
function execStmt($conn, $sql, $types = '', ...$params) {
    $stmt = $conn->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
?>
