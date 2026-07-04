<?php
require_once 'db.php';

// Stats ringkas
$total_daftar = $conn->query("SELECT COUNT(*) as c FROM tbl_daftar")->fetch_assoc()['c'];
$total_antri  = $conn->query("SELECT COUNT(*) as c FROM tbl_daftar_ulang WHERE keterangan='OK'")->fetch_assoc()['c'];
$total_terima = $conn->query("SELECT COUNT(*) as c FROM tbl_pengurusan WHERE status='diterima'")->fetch_assoc()['c'];
$total_pendapatan = $conn->query("SELECT SUM(pembayaran) as s FROM tbl_pengurusan WHERE status='diterima' AND status_bayar='sudah'")->fetch_assoc()['s'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pengajuan Paspor – Kantor Imigrasi Cabang</title>
<link rel="stylesheet" href="style.css">
<style>
.welcome-hero {
    background: linear-gradient(135deg, #1a4fa0 0%, #1d4ed8 50%, #2563eb 100%);
    border-radius: 14px;
    color: white;
    padding: 40px 36px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}
.welcome-hero::after {
    content: '🛂';
    position: absolute;
    right: 36px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 80px;
    opacity: .15;
}
.welcome-hero h2 { font-size: 22px; font-weight: 700; margin-bottom: 6px; }
.welcome-hero p  { opacity: .82; font-size: 14px; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px 22px;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform .2s, box-shadow .2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }

.stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}

.stat-icon.blue   { background: #dbeafe; }
.stat-icon.green  { background: #dcfce7; }
.stat-icon.purple { background: #ede9fe; }
.stat-icon.gold   { background: #fef3c7; }

.stat-num  { font-size: 26px; font-weight: 700; color: var(--gray-900); line-height: 1; }
.stat-label{ font-size: 12px; color: var(--gray-500); margin-top: 4px; font-weight: 500; }

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}

.menu-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-200);
    text-decoration: none;
    color: inherit;
    display: flex;
    gap: 18px;
    align-items: flex-start;
    transition: all .2s;
    border-left: 4px solid transparent;
}
.menu-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-left-color: var(--primary);
}

.menu-icon {
    width: 52px; height: 52px;
    border-radius: 12px;
    background: var(--primary-light);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.menu-title { font-size: 15px; font-weight: 600; color: var(--primary-dark); margin-bottom: 4px; }
.menu-desc  { font-size: 13px; color: var(--gray-500); line-height: 1.55; }
</style>
</head>
<body>

<header class="site-header">
  <div class="header-inner">
    <div class="header-logo">🛂</div>
    <div class="header-text">
      <h1>PENGAJUAN PASPOR</h1>
      <p>Kantor Imigrasi Cabang</p>
    </div>
  </div>
</header>

<div class="programmer-bar">
  Programmer: <strong>Mohammad Nur Arief</strong> &nbsp;|&nbsp; Sistem Informasi Pengajuan Paspor
</div>

<nav class="site-nav">
  <div class="nav-inner">
    <a href="index.php" class="active">🏠 Beranda</a>
    <a href="daftar.php">📋 Daftar</a>
    <a href="daftar_ulang.php">🔁 Daftar Ulang</a>
    <a href="pengurusan.php">📄 Pengurusan</a>
  </div>
</nav>

<div class="container">

  <div class="welcome-hero">
    <h2>Selamat Datang di Sistem Pengajuan Paspor</h2>
    <p>Kantor Imigrasi Cabang &mdash; Kelola pendaftaran paspor secara digital, efisien, dan terstruktur.</p>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon blue">📋</div>
      <div>
        <div class="stat-num"><?= $total_daftar ?></div>
        <div class="stat-label">Total Pendaftar</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon purple">🎫</div>
      <div>
        <div class="stat-num"><?= $total_antri ?></div>
        <div class="stat-label">Antrian Aktif</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green">✅</div>
      <div>
        <div class="stat-num"><?= $total_terima ?></div>
        <div class="stat-label">Berkas Diterima</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon gold">💰</div>
      <div>
        <div class="stat-num">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
        <div class="stat-label">Total Pendapatan (Lunas)</div>
      </div>
    </div>
  </div>

  <!-- Menu -->
  <div style="font-size:13px;font-weight:600;color:var(--gray-500);text-transform:uppercase;letter-spacing:.8px;margin-bottom:14px;">Menu Utama</div>
  <div class="menu-grid">
    <a href="daftar.php" class="menu-card">
      <div class="menu-icon">📋</div>
      <div>
        <div class="menu-title">Input Pendaftaran</div>
        <div class="menu-desc">Daftarkan pemohon baru. Sistem akan otomatis menentukan jadwal hari, tanggal, dan jam kedatangan (kapasitas 5 orang/hari).</div>
      </div>
    </a>
    <a href="daftar_ulang.php" class="menu-card">
      <div class="menu-icon">🔁</div>
      <div>
        <div class="menu-title">Input Daftar Ulang</div>
        <div class="menu-desc">Input kelengkapan berkas (KTP, KK, Ijazah/Akta) sesuai jadwal yang ditentukan. Pemohon tepat waktu mendapat nomor antrian.</div>
      </div>
    </a>
    <a href="pengurusan.php" class="menu-card">
      <div class="menu-icon">📄</div>
      <div>
        <div class="menu-title">Pengurusan Paspor</div>
        <div class="menu-desc">Lihat status berkas, penentuan kelengkapan dokumen, status penerimaan, konfirmasi pembayaran, dan total pendapatan.</div>
      </div>
    </a>
  </div>

</div>

</body>
</html>
