<?php
require_once 'db.php';

$msg = '';
$msg_type = '';

// ── PROSES: Generate pengurusan dari daftar_ulang keterangan=OK ──
if (isset($_GET['proses'])) {
    $id_du = (int)$_GET['proses'];
    $du = queryRow($conn, "SELECT * FROM tbl_daftar_ulang WHERE id=? AND keterangan='OK'", "i", $id_du);

    if ($du && $du['no_antrian']) {
        $cek = queryRow($conn, "SELECT id FROM tbl_pengurusan WHERE no_antrian=?", "i", $du['no_antrian']);
        if ($cek) {
            header("Location: pengurusan.php?msg=sudah_ada");
            exit;
        }

        $berkas     = ($du['ktp'] && $du['kk'] && $du['ijazah_akta']) ? 'lengkap' : 'tidak lengkap';
        $status     = ($berkas === 'lengkap') ? 'diterima' : 'ditolak';
        $keterangan = ($status === 'diterima') ? 'OK' : 'Berkas tidak lengkap';
        $pembayaran = ($status === 'diterima') ? 355000 : 0;

        $stmt = $conn->prepare(
            "INSERT INTO tbl_pengurusan (no_antrian, no_daftar, nama_pemohon, berkas, status, keterangan, pembayaran, status_bayar)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'belum')"
        );
        $stmt->bind_param("isssssi",
            $du['no_antrian'], $du['no_daftar'], $du['nama_pemohon'],
            $berkas, $status, $keterangan, $pembayaran
        );
        header("Location: pengurusan.php?msg=" . ($stmt->execute() ? "proses_ok" : "error"));
        $stmt->close();
    } else {
        header("Location: pengurusan.php?msg=invalid");
    }
    exit;
}

// ── KONFIRMASI SUDAH BAYAR ──
if (isset($_GET['bayar'])) {
    $id = (int)$_GET['bayar'];
    execStmt($conn, "UPDATE tbl_pengurusan SET status_bayar='sudah' WHERE id=? AND status='diterima'", "i", $id);
    header("Location: pengurusan.php?msg=bayar_ok");
    exit;
}

// ── BATALKAN BAYAR (kembali ke belum) ──
if (isset($_GET['batal_bayar'])) {
    $id = (int)$_GET['batal_bayar'];
    execStmt($conn, "UPDATE tbl_pengurusan SET status_bayar='belum' WHERE id=?", "i", $id);
    header("Location: pengurusan.php?msg=batal_ok");
    exit;
}

// ── HAPUS ──
if (isset($_GET['hapus'])) {
    execStmt($conn, "DELETE FROM tbl_pengurusan WHERE id=?", "i", (int)$_GET['hapus']);
    header("Location: pengurusan.php?msg=hapus_ok");
    exit;
}

// Pesan
$msgs = [
    'proses_ok'  => ['✅ Data berhasil diproses ke pengurusan.', 'success'],
    'bayar_ok'   => ['💰 Pembayaran berhasil dikonfirmasi. Total pendapatan diperbarui.', 'success'],
    'batal_ok'   => ['↩️ Status pembayaran dikembalikan ke belum dibayar.', 'success'],
    'hapus_ok'   => ['🗑️ Data berhasil dihapus.', 'success'],
    'sudah_ada'  => ['⚠️ No. antrian ini sudah ada di data pengurusan.', 'danger'],
    'invalid'    => ['❌ Data tidak valid atau keterangan bukan OK.', 'danger'],
    'error'      => ['❌ Terjadi kesalahan saat menyimpan data.', 'danger'],
];
if (isset($_GET['msg']) && isset($msgs[$_GET['msg']])) {
    [$msg, $msg_type] = $msgs[$_GET['msg']];
}

// Antrian siap proses
$antrian_siap = $conn->query(
    "SELECT du.* FROM tbl_daftar_ulang du
     LEFT JOIN tbl_pengurusan p ON p.no_antrian = du.no_antrian
     WHERE du.keterangan='OK' AND p.id IS NULL
     ORDER BY du.no_antrian ASC"
);

// Data pengurusan
$pengurusan = $conn->query("SELECT * FROM tbl_pengurusan ORDER BY no_antrian ASC");

// Statistik — total hanya yang SUDAH BAYAR
$total_pendapatan  = queryRow($conn, "SELECT COALESCE(SUM(pembayaran),0) AS total FROM tbl_pengurusan WHERE status='diterima' AND status_bayar='sudah'")['total'];
$total_diterima    = queryRow($conn, "SELECT COUNT(*) AS c FROM tbl_pengurusan WHERE status='diterima'")['c'];
$total_ditolak     = queryRow($conn, "SELECT COUNT(*) AS c FROM tbl_pengurusan WHERE status='ditolak'")['c'];
$total_sudah_bayar = queryRow($conn, "SELECT COUNT(*) AS c FROM tbl_pengurusan WHERE status='diterima' AND status_bayar='sudah'")['c'];
$total_belum_bayar = queryRow($conn, "SELECT COUNT(*) AS c FROM tbl_pengurusan WHERE status='diterima' AND status_bayar='belum'")['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pengurusan – Pengajuan Paspor</title>
<link rel="stylesheet" href="style.css">
<style>
.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.summary-card {
    background: white; border-radius: 10px;
    padding: 16px 18px; box-shadow: var(--shadow);
    border: 1px solid var(--gray-200); text-align: center;
}
.summary-card .num { font-size: 26px; font-weight: 700; }
.summary-card .lbl { font-size: 12px; color: var(--gray-500); margin-top: 4px; font-weight: 500; }
.summary-card.green  .num { color: var(--success); }
.summary-card.red    .num { color: var(--danger); }
.summary-card.blue   .num { color: var(--primary); }
.summary-card.orange .num { color: var(--warning); }

.antrian-item {
    display: flex; align-items: center; gap: 16px;
    padding: 14px 18px; border: 1px solid var(--gray-200);
    border-radius: 8px; margin-bottom: 10px; background: white;
    transition: box-shadow .18s;
}
.antrian-item:hover { box-shadow: var(--shadow); }
.antrian-no {
    width: 48px; height: 48px; background: var(--primary); color: white;
    border-radius: 12px; display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 700; flex-shrink: 0;
}
.antrian-info { flex: 1; }
.antrian-info strong { font-size: 14px; }
.antrian-info small  { font-size: 12px; color: var(--gray-500); }

/* ── Status bayar badge ── */
.badge-sudah { background: #dcfce7; color: #16a34a; }
.badge-belum { background: #fef3c7; color: #d97706; }

/* ── Tombol bayar ── */
.btn-konfirmasi-bayar {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 13px; border-radius: 6px; border: none;
    background: #16a34a; color: white;
    font-size: 12px; font-weight: 600; font-family: inherit;
    cursor: pointer; text-decoration: none; transition: background .18s;
}
.btn-konfirmasi-bayar:hover { background: #15803d; }

.btn-batal-bayar {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 13px; border-radius: 6px;
    background: #fef3c7; color: #92400e;
    border: 1px solid #fde68a;
    font-size: 12px; font-weight: 600; font-family: inherit;
    cursor: pointer; text-decoration: none; transition: background .18s;
}
.btn-batal-bayar:hover { background: #fde68a; }

/* Warna baris tabel */
tr.row-lunas   { background: #f0fdf4; }
tr.row-pending { background: #fffbeb; }
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
  Programmer: <strong>Mohammad Nur Arief</strong>
</div>

<nav class="site-nav">
  <div class="nav-inner">
    <a href="index.php">🏠 Beranda</a>
    <a href="daftar.php">📋 Daftar</a>
    <a href="daftar_ulang.php">🔁 Daftar Ulang</a>
    <a href="pengurusan.php" class="active">📄 Pengurusan</a>
  </div>
</nav>

<div class="container">

  <?php if ($msg): ?>
  <div class="alert alert-<?= $msg_type ?>">
    <span><?= $msg ?></span>
  </div>
  <?php endif; ?>

  <!-- Summary -->
  <div class="summary-cards">
    <div class="summary-card green">
      <div class="num"><?= (int)$total_diterima ?></div>
      <div class="lbl">✅ Diterima</div>
    </div>
    <div class="summary-card red">
      <div class="num"><?= (int)$total_ditolak ?></div>
      <div class="lbl">❌ Ditolak</div>
    </div>
    <div class="summary-card green">
      <div class="num"><?= (int)$total_sudah_bayar ?></div>
      <div class="lbl">💰 Sudah Bayar</div>
    </div>
    <div class="summary-card orange">
      <div class="num"><?= (int)$total_belum_bayar ?></div>
      <div class="lbl">⏳ Belum Bayar</div>
    </div>
    <div class="summary-card blue">
      <div class="num"><?= $antrian_siap->num_rows ?></div>
      <div class="lbl">🕐 Menunggu Proses</div>
    </div>
  </div>

  <!-- Antrian Siap Proses -->
  <?php if ($antrian_siap->num_rows > 0): ?>
  <div class="card" style="margin-bottom:24px;">
    <div class="card-header">
      <div class="icon">⏳</div>
      <h2>Antrian Siap Diproses</h2>
    </div>
    <div class="card-body">
      <div class="info-box">
        <span>📌</span>
        <div>
          Pemohon berikut telah daftar ulang dengan keterangan <strong>OK</strong> dan belum diproses.
          Klik <strong>Proses Sekarang</strong> untuk menentukan kelengkapan berkas dan status penerimaan.
        </div>
      </div>

      <?php while ($a = $antrian_siap->fetch_assoc()): ?>
      <div class="antrian-item">
        <div class="antrian-no"><?= (int)$a['no_antrian'] ?></div>
        <div class="antrian-info">
          <strong><?= htmlspecialchars($a['nama_pemohon']) ?></strong>
          <br>
          <small>
            No. Daftar: <?= htmlspecialchars($a['no_daftar']) ?> &nbsp;|&nbsp;
            <?= htmlspecialchars($a['keperluan']) ?> &nbsp;|&nbsp;
            KTP: <?= $a['ktp'] ? '<span style="color:var(--success)">✔</span>' : '<span style="color:var(--danger)">✖</span>' ?>
            KK: <?= $a['kk'] ? '<span style="color:var(--success)">✔</span>' : '<span style="color:var(--danger)">✖</span>' ?>
            Ijazah: <?= $a['ijazah_akta'] ? '<span style="color:var(--success)">✔</span>' : '<span style="color:var(--danger)">✖</span>' ?>
          </small>
        </div>
        <a href="pengurusan.php?proses=<?= (int)$a['id'] ?>"
           class="btn btn-success"
           onclick="return confirm('Proses berkas <?= htmlspecialchars(addslashes($a['nama_pemohon'])) ?>?')">
           ▶ Proses Sekarang
        </a>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Data Pengurusan -->
  <div class="card">
    <div class="card-header">
      <div class="icon">📄</div>
      <h2>Data Pengurusan Paspor</h2>
    </div>
    <div class="card-body">

      <?php if ($pengurusan->num_rows === 0): ?>
        <div class="empty-state">
          <div class="icon">📭</div>
          <p>Belum ada data pengurusan. Proses antrian dari daftar ulang terlebih dahulu.</p>
        </div>
      <?php else: ?>

      <div class="info-box" style="margin-bottom:16px;">
        <span>💡</span>
        <div>
          Klik <strong>✔ Konfirmasi Bayar</strong> jika pemohon sudah membayar.
          Pembayaran yang <strong>belum dikonfirmasi tidak masuk ke total pendapatan di bawah</strong>.
        </div>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>No. Antrian</th>
              <th>No. Daftar</th>
              <th>Nama Pemohon</th>
              <th>Berkas</th>
              <th>Status</th>
              <th>Keterangan</th>
              <th>Pembayaran</th>
              <th>Status Bayar</th>
              <th style="width:70px;">Hapus</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($r = $pengurusan->fetch_assoc()):
              $diterima    = $r['status'] === 'diterima';
              $sudah_bayar = $r['status_bayar'] === 'sudah';
              $row_class   = $sudah_bayar ? 'row-lunas' : ($diterima ? 'row-pending' : '');
            ?>
            <tr class="<?= $row_class ?>">
              <td><span class="badge badge-blue" style="font-size:13px;">🎫 #<?= (int)$r['no_antrian'] ?></span></td>
              <td><?= htmlspecialchars($r['no_daftar']) ?></td>
              <td><strong><?= htmlspecialchars($r['nama_pemohon']) ?></strong></td>
              <td>
                <span class="badge <?= $r['berkas'] === 'lengkap' ? 'badge-success' : 'badge-danger' ?>">
                  <?= $r['berkas'] === 'lengkap' ? '📁 Lengkap' : '📂 Tidak Lengkap' ?>
                </span>
              </td>
              <td>
                <span class="badge <?= $diterima ? 'badge-success' : 'badge-danger' ?>">
                  <?= $diterima ? '✅ Diterima' : '❌ Ditolak' ?>
                </span>
              </td>
              <td><?= htmlspecialchars($r['keterangan']) ?></td>
              <td>
                <?php if ($r['pembayaran'] > 0): ?>
                  <strong style="color:<?= $sudah_bayar ? 'var(--success)' : 'var(--warning)' ?>;">
                    Rp <?= number_format((int)$r['pembayaran'], 0, ',', '.') ?>
                  </strong>
                <?php else: ?>
                  <span style="color:var(--gray-400);">—</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!$diterima): ?>
                  <span class="badge badge-gray" style="font-size:11px;">Tidak perlu</span>

                <?php elseif ($sudah_bayar): ?>
                  <div style="display:flex;flex-direction:column;gap:5px;align-items:flex-start;">
                    <span class="badge badge-sudah">💰 Sudah Dibayar</span>
                    <a href="pengurusan.php?batal_bayar=<?= (int)$r['id'] ?>"
                       class="btn-batal-bayar"
                       onclick="return confirm('Batalkan konfirmasi pembayaran ini?')">
                       ↩ Batalkan
                    </a>
                  </div>

                <?php else: ?>
                  <div style="display:flex;flex-direction:column;gap:5px;align-items:flex-start;">
                    <span class="badge badge-belum">⏳ Belum Dibayar</span>
                    <a href="pengurusan.php?bayar=<?= (int)$r['id'] ?>"
                       class="btn-konfirmasi-bayar"
                       onclick="return confirm('Konfirmasi pembayaran Rp 355.000 untuk <?= htmlspecialchars(addslashes($r['nama_pemohon'])) ?>?')">
                       ✔ Konfirmasi Bayar
                    </a>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <a href="pengurusan.php?hapus=<?= (int)$r['id'] ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus data pengurusan ini?')">🗑️</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <!-- Total Pendapatan — hanya yang sudah bayar -->
      <div style="margin-top:20px;">
        <p style="font-size:12px;color:var(--gray-500);margin-bottom:8px;">
          * Total di bawah hanya menghitung pembayaran yang sudah dikonfirmasi
        </p>
        <div class="pendapatan-box">
          <div>
            <div class="label">💰 Total Pendapatan</div>
            <div class="amount">Rp <?= number_format((int)$total_pendapatan, 0, ',', '.') ?></div>
          </div>
          <div style="width:1px;height:48px;background:rgba(255,255,255,.25);"></div>
          <div>
            <div class="label">Sudah Lunas</div>
            <div style="font-size:18px;font-weight:700;">
              <?= (int)$total_sudah_bayar ?> orang × Rp 355.000
            </div>
          </div>
          <?php if ($total_belum_bayar > 0): ?>
          <div style="width:1px;height:48px;background:rgba(255,255,255,.25);"></div>
          <div>
            <div class="label" style="opacity:.75;">⏳ Belum Dikonfirmasi</div>
            <div style="font-size:14px;font-weight:600;opacity:.85;">
              <?= (int)$total_belum_bayar ?> orang
              (Rp <?= number_format($total_belum_bayar * 355000, 0, ',', '.') ?> pending)
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <?php endif; ?>
    </div>
  </div>

</div>
</body>
</html>
