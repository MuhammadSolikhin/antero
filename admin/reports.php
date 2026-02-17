<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="glass-card p-4">
                <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                    <i class="bi bi-file-earmark-text fs-3 text-primary me-2"></i>
                    <h3 class="fw-bold mb-0">Halaman Laporan</h3>
                </div>

                <form action="export_process.php" method="POST" target="_blank">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih Data Laporan</label>
                        <select name="data_type" class="form-select" required>
                            <option value="">-- Pilih Jenis Data --</option>
                            <option value="students">Data Siswa</option>
                            <option value="achievements">Data Prestasi</option>
                            <option value="coaches">Data Pelatih</option>
                            <option value="dojangs">Data Dojang</option>
                        </select>
                        <div class="form-text">Pilih jenis data yang ingin Anda unduh atau cetak.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih Format Laporan</label>
                        <div class="d-flex gap-3">
                            <div class="form-check custom-radio-card">
                                <input class="form-check-input" type="radio" name="format" id="formatExcel"
                                    value="excel" checked>
                                <label class="form-check-label" for="formatExcel">
                                    <i class="bi bi-file-earmark-excel text-success fs-4 d-block mb-1"></i>
                                    Excel (.xls)
                                </label>
                            </div>
                            <div class="form-check custom-radio-card">
                                <input class="form-check-input" type="radio" name="format" id="formatPdf" value="pdf">
                                <label class="form-check-label" for="formatPdf">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-4 d-block mb-1"></i>
                                    PDF / Print
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill py-2">
                            <i class="bi bi-download me-1"></i> Proses Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-radio-card {
        flex: 1;
        position: relative;
    }

    .custom-radio-card .form-check-input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 100%;
        width: 100%;
        z-index: 2;
    }

    .custom-radio-card .form-check-label {
        display: block;
        background: rgba(255, 255, 255, 0.5);
        border: 2px solid transparent;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .custom-radio-card .form-check-input:checked+.form-check-label {
        background: #fff;
        border-color: var(--bs-primary);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
</style>

<?php require_once '../includes/footer.php'; ?>