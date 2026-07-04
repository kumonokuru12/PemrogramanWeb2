<?php
require_once 'db.php';

$msg = '';
$msg_type = '';

// ── HAPUS ──
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    execStmt($conn, "DELETE FROM tbl_daftar_ulang WHERE id=?", "i", $id);
    header("Location: daftar_ulang.php?msg=hapus_ok");
    exit;
}

// ── EDIT: ambil data ──
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_data = queryRow($conn, "SELECT * FROM tbl_daftar_ulang WHERE id=?", "i", $id);
}

// ── SIMPAN / UPDATE ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_daftar   = trim($_POST['no_daftar'] ?? '');
    $keperluan   = trim($_POST['keperluan'] ?? '');
    $hari_datang = trim($_POST['hari_datang'] ?? '');
    $tgl_datang  = $_POST['tgl_datang'] ?? '';
    $ktp         = isset($_POST['ktp'])         ? 1 : 0;
    $kk          = isset($_POST['kk'])          ? 1 : 0;
    $ijazah_akta = isset($_POST['ijazah_akta']) ? 1 : 0;
    $is_edit     = isset($_POST['is_edit']) && $_POST['is_edit'] == '1';
    $edit_id     = (int)($_POST['edit_id'] ?? 0);

    if ($no_daftar && $keperluan && $hari_datang && $tgl_datang) {
        // Lookup data daftar (prepared)
        $daftar = queryRow($conn, "SELECT * FROM tbl_daftar WHERE no_daftar=?", "s", $no_daftar);

        if (!$daftar) {
            $msg = "⚠️ No. Daftar tidak ditemukan. Pastikan sudah terdaftar di halaman Daftar.";
            $msg_type = "danger";
        } else {
            // Keterangan: OK jika tanggal datang sesuai jadwal
            $keterangan = ($tgl_datang === $daftar['tanggal_datang']) ? 'OK' : 'tidak';

            // No. antrian: hanya jika keterangan=OK
            $no_antrian = null;
            if ($keterangan === 'OK') {
                if ($is_edit) {
                    // Pertahankan no_antrian lama jika sudah ada
                    $old_row = queryRow($conn, "SELECT no_antrian FROM tbl_daftar_ulang WHERE id=?", "i", $edit_id);
                    $no_antrian = $old_row['no_antrian'] ?? null;
                    if (!$no_antrian) {
                        $max_row = queryRow($conn, "SELECT COALESCE(MAX(no_antrian),0)+1 AS nxt FROM tbl_daftar_ulang");
                        $no_antrian = $max_row['nxt'];
                    }
                } else {
                    // Cek sudah ada pendaftaran ulang untuk no_daftar ini?
                    $cek_dup = queryRow($conn, "SELECT id FROM tbl_daftar_ulang WHERE no_daftar=?", "s", $no_daftar);
                    if ($cek_dup) {
                        $msg = "⚠️ No. Daftar <strong>" . htmlspecialchars($no_daftar) . "</strong> sudah pernah daftar ulang. Gunakan tombol Edit pada data yang ada.";
                        $msg_type = "danger";
                        goto render;
                    }
                    $max_row = queryRow($conn, "SELECT COALESCE(MAX(no_antrian),0)+1 AS nxt FROM tbl_daftar_ulang");
                    $no_antrian = $max_row['nxt'];
                }
            }

            $hari_harus = $daftar['hari_datang'];
            $tgl_harus  = $daftar['tanggal_datang'];

            if ($is_edit) {
                $stmt = $conn->prepare(
                    "UPDATE tbl_daftar_ulang SET no_daftar=?, nama_pemohon=?, keperluan=?,
                     hari_harus_datang=?, tgl_harus_datang=?, hari_datang=?, tgl_datang=?,
                     ktp=?, kk=?, ijazah_akta=?, keterangan=?, no_antrian=?
                     WHERE id=?"
                );
                $stmt->bind_param("sssssssiiisii",
                    $no_daftar, $daftar['nama_pemohon'], $keperluan,
                    $hari_harus, $tgl_harus, $hari_datang, $tgl_datang,
                    $ktp, $kk, $ijazah_akta, $keterangan, $no_antrian, $edit_id
                );
                if ($stmt->execute()) {
                    $msg = "✅ Data daftar ulang berhasil diperbarui.";
                    $msg_type = "success";
                    $edit_data = null;
                } else {
                    $msg = "❌ Gagal: " . htmlspecialchars($conn->error);
                    $msg_type = "danger";
                }
                $stmt->close();
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO tbl_daftar_ulang
                     (no_daftar, nama_pemohon, keperluan, hari_harus_datang, tgl_harus_datang,
                      hari_datang, tgl_datang, ktp, kk, ijazah_akta, keterangan, no_antrian)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param("sssssssiiisd",
                    $no_daftar, $daftar['nama_pemohon'], $keperluan,
                    $hari_harus, $tgl_harus, $hari_datang, $tgl_datang,
                    $ktp, $kk, $ijazah_akta, $keterangan, $no_antrian
                );
                if ($stmt->execute()) {
                    $antri_info = ($keterangan === 'OK') ? " No. Antrian: <strong>$no_antrian</strong>." : "";
                    $msg = "✅ Data tersimpan. Keterangan: <strong>$keterangan</strong>.$antri_info";
                    $msg_type = "success";
                } else {
                    $msg = "❌ Gagal: " . htmlspecialchars($conn->error);
                    $msg_type = "danger";
                }
                $stmt->close();
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

render:
// Ambil semua pendaftar (untuk dropdown)
$list_daftar = $conn->query("SELECT no_daftar, nama_pemohon, hari_datang, tanggal_datang FROM tbl_daftar ORDER BY no_daftar");

// Semua data daftar ulang
$rows = $conn->query("SELECT * FROM tbl_daftar_ulang ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Daftar Ulang – Pengajuan Paspor</title>
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
    <a href="daftar.php">📋 Daftar</a>
    <a href="daftar_ulang.php" class="active">🔁 Daftar Ulang</a>
    <a href="pengurusan.php">📄 Pengurusan</a>
  </div>
</nav>

<div class="container">

  <?php if ($msg): ?>
  <div class="alert alert-<?= $msg_type ?>">
    <span><?= $msg ?></span>
  </div>
  <?php endif; ?>

  <!-- Form -->
  <div class="card" style="margin-bottom:24px;">
    <div class="card-header">
      <div class="icon">🔁</div>
      <h2><?= $edit_data ? 'Edit Data Daftar Ulang' : 'Input Daftar Ulang' ?></h2>
    </div>
    <div class="card-body">

      <div class="info-box">
        <span>ℹ️</span>
        <div>
          <strong>Keterangan otomatis:</strong> <em>OK</em> jika tanggal datang sesuai jadwal yang ditentukan saat daftar.
          Pemohon dengan keterangan <strong>OK</strong> mendapat nomor antrian secara otomatis.
        </div>
      </div>

      <form method="POST">
        <?php if ($edit_data): ?>
          <input type="hidden" name="is_edit" value="1">
          <input type="hidden" name="edit_id" value="<?= (int)$edit_data['id'] ?>">
        <?php endif; ?>

        <div class="form-grid">
          <label>No. Daftar <span style="color:var(--danger)">*</span></label>
          <div>
            <select name="no_daftar" required id="no_daftar_select" onchange="loadJadwal(this.value)">
              <option value="">-- Pilih No. Daftar --</option>
              <?php
              while ($d = $list_daftar->fetch_assoc()):
                $sel = ($edit_data && $edit_data['no_daftar'] === $d['no_daftar']) ? 'selected' : '';
              ?>
              <option value="<?= htmlspecialchars($d['no_daftar']) ?>"
                      data-nama="<?= htmlspecialchars($d['nama_pemohon']) ?>"
                      data-hari="<?= htmlspecialchars($d['hari_datang']) ?>"
                      data-tgl="<?= htmlspecialchars($d['tanggal_datang']) ?>"
                      data-tglfmt="<?= date('d/m/Y', strtotime($d['tanggal_datang'])) ?>"
                      <?= $sel ?>>
                <?= htmlspecialchars($d['no_daftar']) ?> – <?= htmlspecialchars($d['nama_pemohon']) ?>
              </option>
              <?php endwhile; ?>
            </select>
            <div id="lookup-box" style="display:none;margin-top:8px;padding:10px 14px;background:var(--primary-light);border:1px solid #bfdbfe;border-radius:6px;font-size:13px;color:var(--primary-dark);"></div>
          </div>

          <label>Nama Pemohon</label>
          <div class="readonly-field" id="nama-field"><?= htmlspecialchars($edit_data['nama_pemohon'] ?? '—') ?></div>

          <label>Keperluan <span style="color:var(--danger)">*</span></label>
          <select name="keperluan" required>
            <option value="">-- Pilih Keperluan --</option>
            <?php
            foreach (['Paspor Baru','Perpanjangan Paspor','Paspor Darurat','Paspor Anak'] as $k):
              $sel = ($edit_data && $edit_data['keperluan'] === $k) ? 'selected' : '';
            ?>
            <option value="<?= htmlspecialchars($k) ?>" <?= $sel ?>><?= htmlspecialchars($k) ?></option>
            <?php endforeach; ?>
          </select>

          <label>Hari Harus Datang</label>
          <div class="readonly-field" id="hari-harus-field"><?= htmlspecialchars($edit_data['hari_harus_datang'] ?? '—') ?></div>

          <label>Tgl. Harus Datang</label>
          <div class="readonly-field" id="tgl-harus-field">
            <?= $edit_data ? date('d/m/Y', strtotime($edit_data['tgl_harus_datang'])) : '—' ?>
          </div>
          <input type="hidden" id="tgl-harus-hidden" value="<?= htmlspecialchars($edit_data['tgl_harus_datang'] ?? '') ?>">

          <label>Hari Datang <span style="color:var(--danger)">*</span></label>
          <select name="hari_datang" required>
            <?php
            foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h):
              $sel = ($edit_data && $edit_data['hari_datang'] === $h) ? 'selected' : '';
            ?>
            <option value="<?= $h ?>" <?= $sel ?>><?= $h ?></option>
            <?php endforeach; ?>
          </select>

          <label>Tanggal Datang <span style="color:var(--danger)">*</span></label>
          <input type="date" name="tgl_datang"
                 value="<?= htmlspecialchars($edit_data['tgl_datang'] ?? date('Y-m-d')) ?>"
                 required>

          <label>Berkas</label>
          <div class="checkbox-group">
            <label class="checkbox-item">
              <input type="checkbox" name="ktp" <?= ($edit_data && $edit_data['ktp']) ? 'checked' : '' ?>>
              🪪 KTP
            </label>
            <label class="checkbox-item">
              <input type="checkbox" name="kk" <?= ($edit_data && $edit_data['kk']) ? 'checked' : '' ?>>
              📑 KK
            </label>
            <label class="checkbox-item">
              <input type="checkbox" name="ijazah_akta" <?= ($edit_data && $edit_data['ijazah_akta']) ? 'checked' : '' ?>>
              📜 Ijazah / Akta
            </label>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <?= $edit_data ? '💾 Perbarui Data' : '➕ Simpan Daftar Ulang' ?>
          </button>
          <?php if ($edit_data): ?>
          <a href="daftar_ulang.php" class="btn btn-secondary">✖ Batal</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabel -->
  <div class="card">
    <div class="card-header">
      <div class="icon">📊</div>
      <h2>Data Pendaftar Ulang</h2>
    </div>
    <div class="card-body">
      <?php if ($rows->num_rows === 0): ?>
        <div class="empty-state">
          <div class="icon">📭</div>
          <p>Belum ada data daftar ulang.</p>
        </div>
      <?php else: ?>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>No. Daftar</th>
              <th>Nama Pemohon</th>
              <th>Keperluan</th>
              <th style="text-align:center">KTP</th>
              <th style="text-align:center">KK</th>
              <th style="text-align:center">Ijazah/Akta</th>
              <th>Keterangan</th>
              <th>No. Antrian</th>
              <th style="width:140px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($r = $rows->fetch_assoc()): ?>
            <tr>
              <td><span class="badge badge-blue"><?= htmlspecialchars($r['no_daftar']) ?></span></td>
              <td><strong><?= htmlspecialchars($r['nama_pemohon']) ?></strong></td>
              <td><?= htmlspecialchars($r['keperluan']) ?></td>
              <td style="text-align:center">
                <span style="font-size:16px;color:<?= $r['ktp'] ? 'var(--success)' : 'var(--danger)' ?>">
                  <?= $r['ktp'] ? '✔' : '✖' ?>
                </span>
              </td>
              <td style="text-align:center">
                <span style="font-size:16px;color:<?= $r['kk'] ? 'var(--success)' : 'var(--danger)' ?>">
                  <?= $r['kk'] ? '✔' : '✖' ?>
                </span>
              </td>
              <td style="text-align:center">
                <span style="font-size:16px;color:<?= $r['ijazah_akta'] ? 'var(--success)' : 'var(--danger)' ?>">
                  <?= $r['ijazah_akta'] ? '✔' : '✖' ?>
                </span>
              </td>
              <td>
                <span class="badge <?= $r['keterangan'] === 'OK' ? 'badge-success' : 'badge-danger' ?>">
                  <?= $r['keterangan'] === 'OK' ? '✅ OK' : '❌ Tidak' ?>
                </span>
              </td>
              <td>
                <?php if ($r['no_antrian']): ?>
                  <span class="badge badge-blue" style="font-size:13px;">🎫 #<?= (int)$r['no_antrian'] ?></span>
                <?php else: ?>
                  <span class="badge badge-gray">—</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="action-group">
                  <a href="daftar_ulang.php?edit=<?= (int)$r['id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                  <a href="daftar_ulang.php?hapus=<?= (int)$r['id'] ?>"
                     class="btn btn-danger btn-sm"
                     onclick="return confirm('Hapus data ini?')">🗑️ Hapus</a>
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

<script>
function loadJadwal(no) {
    const sel = document.getElementById('no_daftar_select');
    const opt = sel.querySelector('option[value="' + CSS.escape(no) + '"]');
    const box  = document.getElementById('lookup-box');
    const namaF= document.getElementById('nama-field');
    const hariF= document.getElementById('hari-harus-field');
    const tglF = document.getElementById('tgl-harus-field');
    const tglH = document.getElementById('tgl-harus-hidden');

    if (opt && no) {
        const nama   = opt.dataset.nama;
        const hari   = opt.dataset.hari;
        const tgl    = opt.dataset.tgl;
        const tglfmt = opt.dataset.tglfmt;
        namaF.textContent = nama;
        hariF.textContent = hari;
        tglF.textContent  = tglfmt;
        tglH.value        = tgl;
        box.innerHTML = '📅 Jadwal datang seharusnya: <strong>' + hari + ', ' + tglfmt + '</strong>';
        box.style.display = 'block';
    } else {
        namaF.textContent = '—';
        hariF.textContent = '—';
        tglF.textContent  = '—';
        tglH.value        = '';
        box.style.display = 'none';
    }
}

window.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('no_daftar_select');
    if (sel && sel.value) loadJadwal(sel.value);
});
</script>
</body>
</html>
