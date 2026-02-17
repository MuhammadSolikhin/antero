<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Stats
$total_students = $conn->query("SELECT count(*) as total FROM students")->fetch_assoc()['total'];
$total_achievements = $conn->query("SELECT count(*) as total FROM achievements")->fetch_assoc()['total'];
$pending_verification = $conn->query("SELECT count(*) as total FROM achievements WHERE status = 'pending'")->fetch_assoc()['total'];
$total_dojangs = $conn->query("SELECT count(*) as total FROM dojangs")->fetch_assoc()['total'];

// Fetch Top Active Students
$top_active = $conn->query("
    SELECT s.nama_lengkap, s.tingkatan_sabuk, d.nama_dojang, u.login_count, u.last_login 
    FROM students s 
    JOIN users u ON s.user_id = u.id 
    JOIN dojangs d ON s.dojang_id = d.id 
    ORDER BY u.login_count DESC 
    LIMIT 5
");

// Fetch Dojang Distribution
$dojang_dist = $conn->query("
    SELECT d.nama_dojang, COUNT(s.id) as total_siswa 
    FROM dojangs d 
    LEFT JOIN students s ON d.id = s.dojang_id 
    GROUP BY d.id 
    ORDER BY total_siswa DESC
");

// Prepare Data for Charts
// 1. Dojang Distribution
$dojang_labels = [];
$dojang_data = [];
$dojang_dist_arr = []; // Store for potential list use or tooltip backup
while ($row = $dojang_dist->fetch_assoc()) {
    $dojang_labels[] = $row['nama_dojang'];
    $dojang_data[] = $row['total_siswa'];
    $dojang_dist_arr[] = $row;
}

// 2. Achievement Stats (Approved only)
$ach_stats = $conn->query("
    SELECT tingkat, COUNT(*) as total 
    FROM achievements 
    WHERE status = 'approved' 
    GROUP BY tingkat
");
$ach_labels = [];
$ach_data = [];
while ($row = $ach_stats->fetch_assoc()) {
    $ach_labels[] = $row['tingkat'];
    $ach_data[] = $row['total'];
}

// 3. Student Growth (Monthly for current year)
$current_year = date('Y');
$growth_query = $conn->query("
    SELECT MONTH(created_at) as bulan, COUNT(*) as total 
    FROM students 
    WHERE YEAR(created_at) = '$current_year' 
    GROUP BY MONTH(created_at) 
    ORDER BY bulan ASC
");
// Initialize 12 months with 0
$growth_data = array_fill(1, 12, 0);
while ($row = $growth_query->fetch_assoc()) {
    $growth_data[$row['bulan']] = $row['total'];
}
// Re-index to 0-based array for JS if needed, but array_values works fine
$growth_data_js = array_values($growth_data);
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="glass-card p-4">
                <h2 class="fw-bold m-0 text-primary">Admin Dashboard</h2>
                <p class="text-muted m-0">Ringkasan Sistem</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="glass-card p-4 text-center">
                <h1 class="display-4 fw-bold text-primary"><?php echo $total_students; ?></h1>
                <p class="text-muted">Total Siswa</p>
                <a href="students.php" class="btn btn-outline-primary btn-sm rounded-pill mt-2">Lihat Detail</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-4 text-center">
                <h1 class="display-4 fw-bold text-success"><?php echo $total_achievements; ?></h1>
                <p class="text-muted">Total Prestasi</p>
                <a href="verify.php" class="btn btn-outline-success btn-sm rounded-pill mt-2">Lihat Detail</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-4 text-center">
                <h1 class="display-4 fw-bold text-warning"><?php echo $pending_verification; ?></h1>
                <p class="text-muted">Perlu Verifikasi</p>
                <a href="verify.php" class="btn btn-warning btn-sm rounded-pill w-100 mt-2">Cek Sekarang</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-4 text-center">
                <h1 class="display-4 fw-bold text-info"><?php echo $total_dojangs; ?></h1>
                <p class="text-muted">Total Dojang</p>
                <a href="master_dojang.php" class="btn btn-outline-info btn-sm rounded-pill mt-2">Kelola</a>
            </div>
        </div>
    </div>

    <!-- Charts Row 1: Growth & Achievements -->
    <div class="row mt-4 g-4">
        <div class="col-md-8">
            <div class="glass-card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-graph-up-arrow text-primary"></i> Pertumbuhan Siswa
                    (<?= $current_year ?>)</h5>
                <canvas id="studentGrowthChart"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-trophy text-warning"></i> Tingkat Prestasi</h5>
                <canvas id="achievementChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row mt-4 g-4">
        <div class="col-md-8">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0"><i class="bi bi-bar-chart-fill text-primary"></i> Statistik Keaktifan Siswa
                    </h5>
                    <small class="text-muted">Top 5 Login Terbanyak</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Lengkap</th>
                                <th>Dojang</th>
                                <th>Sabuk</th>
                                <th class="text-center">Total Login</th>
                                <th>Login Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($top_active->num_rows > 0): ?>
                                <?php while ($row = $top_active->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $row['nama_lengkap']; ?></td>
                                        <td><small><?php echo $row['nama_dojang']; ?></small></td>
                                        <td><span
                                                class="badge bg-light text-dark border"><?php echo $row['tingkatan_sabuk']; ?></span>
                                        </td>
                                        <td class="text-center fw-bold text-primary"><?php echo $row['login_count']; ?></td>
                                        <td><small
                                                class="text-muted"><?php echo $row['last_login'] ? date('d/m/Y H:i', strtotime($row['last_login'])) : '-'; ?></small>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Belum ada data aktivitas login.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Dojang Stats Section -->
        <div class="col-md-4">
            <div class="glass-card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-success"></i> Distribusi Siswa</h5>
                <div style="position: relative; height: 300px;">
                    <canvas id="dojangChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // --- 1. Student Growth Chart (Line) ---
        const ctxGrowth = document.getElementById('studentGrowthChart').getContext('2d');
        new Chart(ctxGrowth, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Siswa Baru',
                    data: <?php echo json_encode($growth_data_js); ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0d6efd',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // --- 2. Achievement Level Chart (Pie) ---
        const ctxAch = document.getElementById('achievementChart').getContext('2d');
        const achLabels = <?php echo json_encode($ach_labels); ?>;
        const achData = <?php echo json_encode($ach_data); ?>;

        if (achLabels.length > 0) {
            new Chart(ctxAch, {
                type: 'pie',
                data: {
                    labels: achLabels,
                    datasets: [{
                        data: achData,
                        backgroundColor: [
                            '#ffc107', // Warning (Daerah maybe?)
                            '#0d6efd', // Primary
                            '#198754', // Success
                            '#0dcaf0', // Info
                            '#dc3545'  // Danger
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        } else {
            document.getElementById('achievementChart').parentElement.innerHTML += '<p class="text-center text-muted">Belum ada data prestasi.</p>';
        }

        // --- 3. Dojang Distribution Chart (Doughnut) ---
        const ctxDojang = document.getElementById('dojangChart').getContext('2d');
        const dojangLabels = <?php echo json_encode($dojang_labels); ?>;
        const dojangData = <?php echo json_encode($dojang_data); ?>;

        if (dojangLabels.length > 0) {
            new Chart(ctxDojang, {
                type: 'doughnut',
                data: {
                    labels: dojangLabels,
                    datasets: [{
                        data: dojangData,
                        backgroundColor: [
                            '#6610f2', '#0d6efd', '#0dcaf0', '#198754', '#ffc107', '#fd7e14', '#dc3545', '#d63384', '#6f42c1', '#20c997'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }
                    }
                }
            });
        } else {
            document.getElementById('dojangChart').parentElement.innerHTML += '<p class="text-center text-muted">Belum ada data siswa.</p>';
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>