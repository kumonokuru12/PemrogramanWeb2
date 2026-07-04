-- ============================================
-- DATABASE: Pengajuan Paspor - Kantor Imigrasi
-- Jalankan script ini di phpMyAdmin:
--   1. Klik tab "Import"
--   2. Pilih file ini → klik Go
-- ============================================

CREATE DATABASE IF NOT EXISTS db_paspor
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_paspor;

-- ── Tabel Daftar (Pendaftaran) ──────────────
CREATE TABLE IF NOT EXISTS tbl_daftar (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    no_daftar      VARCHAR(20)  NOT NULL UNIQUE,
    nama_pemohon   VARCHAR(100) NOT NULL,
    tanggal_daftar DATE         NOT NULL,
    hari_datang    VARCHAR(20)  NOT NULL,
    tanggal_datang DATE         NOT NULL,
    jam_datang     TIME         NOT NULL,
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel Daftar Ulang ──────────────────────
CREATE TABLE IF NOT EXISTS tbl_daftar_ulang (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    no_daftar         VARCHAR(20)  NOT NULL UNIQUE,
    nama_pemohon      VARCHAR(100) NOT NULL,
    keperluan         VARCHAR(100) NOT NULL,
    hari_harus_datang VARCHAR(20),
    tgl_harus_datang  DATE,
    hari_datang       VARCHAR(20)  NOT NULL,
    tgl_datang        DATE         NOT NULL,
    ktp               TINYINT(1)  DEFAULT 0,
    kk                TINYINT(1)  DEFAULT 0,
    ijazah_akta       TINYINT(1)  DEFAULT 0,
    keterangan        ENUM('OK','tidak') NOT NULL DEFAULT 'tidak',
    no_antrian        INT UNIQUE NULL,
    created_at        TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_du_daftar
        FOREIGN KEY (no_daftar) REFERENCES tbl_daftar(no_daftar)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel Pengurusan ────────────────────────
CREATE TABLE IF NOT EXISTS tbl_pengurusan (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    no_antrian    INT          NOT NULL UNIQUE,
    no_daftar     VARCHAR(20)  NOT NULL,
    nama_pemohon  VARCHAR(100) NOT NULL,
    berkas        ENUM('lengkap','tidak lengkap') NOT NULL,
    status        ENUM('diterima','ditolak') NOT NULL,
    keterangan    VARCHAR(100) NOT NULL,
    pembayaran    DECIMAL(15,0) DEFAULT 0,
    status_bayar  ENUM('belum','sudah') NOT NULL DEFAULT 'belum',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_p_du
        FOREIGN KEY (no_antrian) REFERENCES tbl_daftar_ulang(no_antrian)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
