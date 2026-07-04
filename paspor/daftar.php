<?php
require_once 'db.php';

$msg = '';
$msg_type = '';

// ── HAPUS ──
if (isset($_GET['hapus'])) {
    $nd = $_GET['hapus'];
    execStmt($conn, "DELETE FROM tbl_daftar WHERE no_daftar=?", "s", $nd);
    header("Location: daftar.php?msg=hapus_ok");
    exit;
}

// ── EDIT: ambil data ──
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_data = queryRow($conn, "SELECT * FROM tbl_daftar WHERE no_daftar=?", "s", $_GET['edit']);
}

// ── SIMPAN / UPDATE ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_daftar      = trim($_POST['no_daftar'] ?? '');
    $nama_pemohon   = trim($_POST['nama_pemohon'] ?? '');
    $tanggal_daftar = $_POST['tanggal_daftar'] ?? '';
    $is_edit        = isset($_POST['is_edit']) && $_POST['is_edit'] == '1';

    if ($no_daftar && $nama_pemohon && $tanggal_daftar) {
        if ($is_edit) {
            // UPDATE – jadwal tetap
            if (execStmt($conn,
                "UPDATE tbl_daftar SET nama_pemohon=?, tanggal_daftar=? WHERE no_daftar=?",
                "sss", $nama_pemohon, $tanggal_daftar, $no_daftar
            )) {
                $msg = "✅ Data berhasil diperbarui.";
                $msg_type = "success";
                $edit_data = null;
            } else {
                $msg = "❌ Gagal memperbarui data.";
                $msg_type = "danger";
            }
        } else {
            // CEK duplikat No. Daftar (prepared)
            $cek = queryRow($conn, "SELECT id FROM tbl_daftar WHERE no_daftar=?", "s", $no_daftar);
            if ($cek) {
                $msg = "⚠️ No. Daftar sudah digunakan. Gunakan nomor lain.";
                $msg_type = "danger";
            } else {
                $jadwal = hitungJadwal($conn, $tanggal_daftar);
                if (!$jadwal) {
                    $msg = "❌ Jadwal penuh untuk 30 hari ke depan.";
                    $msg_type = "danger";
                } else {
                    $stmt = $conn->prepare(
                        "INSERT INTO tbl_daftar (no_daftar, nama_pemohon, tanggal_daftar, hari_datang, tanggal_datang, jam_datang)
                         VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->bind_param("ssssss",
                        $no_daftar, $nama_pemohon, $tanggal_daftar,
                        $jadwal['hari'], $jadwal['tanggal'], $jadwal['jam']
                    );
                    if ($stmt->execute()) {
                        $jam_fmt = substr($jadwal['jam'], 0, 5);
                        $msg = "✅ Pendaftaran berhasil! Jadwal datang: <strong>{$jadwal['hari']}, {$jadwal['tanggal']} pukul $jam_fmt</strong>";
                        $msg_type = "success";
                    } else {
                        $msg = "❌ Gagal menyimpan: " . htmlspecialchars($conn->error);
                        $msg_type = "danger";
                    }
                    $stmt->close();
                }
            }
        }
    } else {
        $msg = "⚠️ Semua field wajib diisi.";
        $msg_type = "danger";
    }
}

// Pesan dari redirect
if (isset($_GET['msg']) && $_GET['msg'] === 'hapus_ok') {
    $msg = "🗑️ Data berhasil dihapus.";
    $msg_type = "success";
}

// Semua data
$rows = $conn->query("SELECT * FROM tbl_daftar ORDER BY tanggal_datang ASC, jam_datang ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Daftar – Pengajuan Paspor</title>
<link rel="stylesheet" href="style.css">
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
  Programmer: <strong>Mohammad Nur Arief</strong>
</div>

<nav class="site-nav">
  <div class="nav-inner">
    <a href="index.php">🏠 Beranda</a>
    <a href="daftar.php" class="active">📋 Daftar</a>
    <a href="daftar_ulang.php">🔁 Daftar Ulang</a>
    <a href="pengurusan.php">📄 Pengurusan</a>
  </div>
</nav>

<div class="container">

  <?php if ($msg): ?>
  <div class="alert alert-<?= $msg_type ?>">
    <span><?= $msg ?></span>
  </div>
  <?php endif; ?>

  <!-- Form Input -->
  <div class="card" style="margin-bottom:24px;">
    <div class="card-header">
      <div class="icon">📋</div>
      <h2><?= $edit_data ? 'Edit Data Pendaftar' : 'Input Pendaftaran' ?></h2>
    </div>
    <div class="card-body">

      <div class="info-box">
        <span>ℹ️</span>
        <span>Kapasitas <strong>5 orang per hari</strong>. Sistem otomatis menentukan jadwal hari, tanggal, dan jam kedatangan berdasarkan slot yang tersedia.</span>
      </div>

      <form method="POST">
        <?php if ($edit_data): ?>
          <input type="hidden" name="is_edit" value="1">
        <?php endif; ?>

        <div class="form-grid">
          <label>No. Daftar <span style="color:var(--danger)">*</span></label>
          <input type="text" name="no_daftar"
                 value="<?= htmlspecialchars($edit_data['no_daftar'] ?? '') ?>"
                 <?= $edit_data ? 'readonly style="background:var(--gray-100)"' : '' ?>
                 placeholder="Contoh: PSP-001" required maxlength="20">

          <label>Nama Pemohon <span style="color:var(--danger)">*</span></label>
          <input type="text" name="nama_pemohon"
                 value="<?= htmlspecialchars($edit_data['nama_pemohon'] ?? '') ?>"
                 placeholder="Nama lengkap pemohon" required maxlength="100">

          <label>Tanggal Daftar <span style="color:var(--danger)">*</span></label>
          <input type="date" name="tanggal_daftar"
                 value="<?= htmlspecialchars($edit_data['tanggal_daftar'] ?? date('Y-m-d')) ?>"
                 required>
        </div>

        <?php if ($edit_data): ?>
        <div style="margin-top:14px;padding:12px 16px;background:var(--gray-50);border-radius:8px;border:1px solid var(--gray-200);font-size:13px;color:var(--gray-600);">
          📅 Jadwal datang: <strong><?= htmlspecialchars($edit_data['hari_datang']) ?>, <?= date('d/m/Y', strtotime($edit_data['tanggal_datang'])) ?> pukul <?= substr($edit_data['jam_datang'], 0, 5) ?></strong>
          <br><small style="color:var(--gray-400);">Jadwal tidak berubah saat edit</small>
        </div>
        <?php endif; ?>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <?= $edit_data ? '💾 Perbarui Data' : '➕ Simpan Pendaftaran' ?>
          </button>
          <?php if ($edit_data): ?>
          <a href="daftar.php" class="btn btn-secondary">✖ Batal</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabel Data -->
  <div class="card">
    <div class="card-header">
      <div class="icon">📊</div>
      <h2>Data Pendaftar</h2>
    </div>
    <div class="card-body">
      <?php if ($rows->num_rows === 0): ?>
        <div class="empty-state">
          <div class="icon">📭</div>
          <p>Belum ada data pendaftar. Silakan tambahkan pendaftar baru.</p>
        </div>
      <?php else: ?>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>No. Daftar</th>
              <th>Nama Pemohon</th>
              <th>Tgl. Daftar</th>
              <th>Hari Datang</th>
              <th>Tanggal Datang</th>
              <th>Jam</th>
              <th style="width:140px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($r = $rows->fetch_assoc()): ?>
            <tr>
              <td><span class="badge badge-blue"><?= htmlspecialchars($r['no_daftar']) ?></span></td>
              <td><strong><?= htmlspecialchars($r['nama_pemohon']) ?></strong></td>
              <td><?= date('d/m/Y', strtotime($r['tanggal_daftar'])) ?></td>
              <td><?= htmlspecialchars($r['hari_datang']) ?></td>
              <td><?= date('d/m/Y', strtotime($r['tanggal_datang'])) ?></td>
              <td><span class="badge badge-gray"><?= substr($r['jam_datang'], 0, 5) ?></span></td>
              <td>
                <div class="action-group">
                  <a href="daftar.php?edit=<?= urlencode($r['no_daftar']) ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                  <a href="daftar.php?hapus=<?= urlencode($r['no_daftar']) ?>"
                     class="btn btn-danger btn-sm"
                     onclick="return confirm('Hapus data <?= htmlspecialchars(addslashes($r['nama_pemohon'])) ?>?\nSemua data terkait juga akan dihapus.')">
                     🗑️ Hapus
                  </a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>
</body>
</html>
