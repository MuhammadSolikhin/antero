<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper function to check active state
function isActive($path)
{
    $current = basename($_SERVER['PHP_SELF']);
    return ($current == $path) ? 'active' : '';
}

// Determine if we need to show layout (only if logged in)
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];

    // Fetch Profile Picture
    require_once __DIR__ . '/../config/database.php'; // Ensure DB
    $uid = $_SESSION['user_id'];
    $u_query = $conn->query("SELECT foto_profil FROM users WHERE id=$uid");
    $u_data = $u_query ? $u_query->fetch_assoc() : null;
    $prof_pic = ($u_data && $u_data['foto_profil']) ? BASE_URL . "assets/uploads/profiles/" . $u_data['foto_profil'] : "https://ui-avatars.com/api/?name=" . $_SESSION['username'] . "&background=random";
    ?>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="border-end bg-white" id="sidebar-wrapper">
            <div
                class="sidebar-heading border-bottom bg-white text-primary d-flex align-items-center justify-content-center position-relative px-3">
                <!-- Full Logo -->
                <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Logo" class="img-fluid" style="max-height: 60px;">

                <!-- Close Button for Mobile -->
                <i class="bi bi-x-lg d-md-none text-muted position-absolute end-0 me-3" id="sidebar-close"
                    style="cursor: pointer;"></i>
            </div>
            <div class="list-group list-group-flush mt-3">

                <?php if ($role == 'admin'): ?>


                    <small class="text-uppercase text-muted px-4 mb-2 fw-bold" style="font-size:0.75rem;">Menu Admin</small>
                    <a href="<?= BASE_URL ?>admin/dashboard.php"
                        class="list-group-item list-group-item-action <?php echo isActive('dashboard.php'); ?>">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>admin/profile.php"
                        class="list-group-item list-group-item-action <?php echo isActive('profile.php'); ?>">
                        <i class="bi bi-person-circle"></i> Profile Saya
                    </a>
                    <a href="<?= BASE_URL ?>admin/verify.php"
                        class="list-group-item list-group-item-action <?php echo isActive('verify.php'); ?>">
                        <i class="bi bi-patch-check-fill"></i> Verifikasi Prestasi
                    </a>
                    <a href="<?= BASE_URL ?>admin/verify_trainings.php"
                        class="list-group-item list-group-item-action <?php echo isActive('verify_trainings.php'); ?>">
                        <i class="bi bi-patch-check"></i> Verifikasi Diklat
                    </a>
                    <a href="<?= BASE_URL ?>admin/users.php"
                        class="list-group-item list-group-item-action <?php echo isActive('users.php'); ?>">
                        <i class="bi bi-person-gear"></i> Data User
                    </a>
                    <a href="<?= BASE_URL ?>admin/students.php"
                        class="list-group-item list-group-item-action <?php echo isActive('students.php'); ?>">
                        <i class="bi bi-people-fill"></i> Data Siswa
                    </a>
                    <a href="<?= BASE_URL ?>admin/achievements.php"
                        class="list-group-item list-group-item-action <?php echo isActive('achievements.php'); ?>">
                        <i class="bi bi-trophy-fill"></i> Data Prestasi
                    </a>

                    <small class="text-uppercase text-muted px-4 mt-3 mb-2 fw-bold" style="font-size:0.75rem;">Master
                        Data</small>
                    <a href="<?= BASE_URL ?>admin/master_dojang.php"
                        class="list-group-item list-group-item-action <?php echo isActive('master_dojang.php'); ?>">
                        <i class="bi bi-building"></i> Data Dojang
                    </a>
                    <a href="<?= BASE_URL ?>admin/master_coach.php"
                        class="list-group-item list-group-item-action <?php echo isActive('master_coach.php'); ?>">
                        <i class="bi bi-people-fill"></i> Data Pelatih
                    </a>

                    <small class="text-uppercase text-muted px-4 mt-3 mb-2 fw-bold" style="font-size:0.75rem;">Informasi</small>
                    <a href="<?= BASE_URL ?>admin/news.php"
                        class="list-group-item list-group-item-action <?php echo isActive('news.php'); ?>">
                        <i class="bi bi-newspaper"></i> Kelola Berita
                    </a>
                    <a href="<?= BASE_URL ?>admin/flyer.php"
                        class="list-group-item list-group-item-action <?php echo isActive('flyer.php'); ?>">
                        <i class="bi bi-megaphone"></i> Pengaturan Flyer
                    </a>
                    <a href="<?= BASE_URL ?>admin/club_settings.php"
                        class="list-group-item list-group-item-action <?php echo isActive('club_settings.php'); ?>">
                        <i class="bi bi-gear-fill"></i> Pengaturan Sosmed
                    </a>

                    <small class="text-uppercase text-muted px-4 mt-3 mb-2 fw-bold" style="font-size:0.75rem;">Laporan</small>
                    <a href="<?= BASE_URL ?>admin/reports.php"
                        class="list-group-item list-group-item-action <?php echo isActive('reports.php'); ?>">
                        <i class="bi bi-file-earmark-bar-graph"></i> Laporan
                    </a>

                <?php else: // Student ?>
                    <small class="text-uppercase text-muted px-4 mb-2 fw-bold" style="font-size:0.75rem;">Menu Siswa</small>
                    <a href="<?= BASE_URL ?>student/dashboard.php"
                        class="list-group-item list-group-item-action <?php echo isActive('dashboard.php'); ?>">
                        <i class="bi bi-house-door-fill"></i> Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>student/biodata.php"
                        class="list-group-item list-group-item-action <?php echo isActive('biodata.php'); ?>">
                        <i class="bi bi-person-badge-fill"></i> Biodata Saya
                    </a>
                    <a href="<?= BASE_URL ?>student/achievement.php"
                        class="list-group-item list-group-item-action <?php echo isActive('achievement.php'); ?>">
                        <i class="bi bi-award-fill"></i> Prestasi
                    </a>
                    <a href="<?= BASE_URL ?>student/trainings.php"
                        class="list-group-item list-group-item-action <?php echo isActive('trainings.php'); ?>">
                        <i class="bi bi-journal-bookmark-fill"></i> Pelatihan/Diklat
                    </a>
                    <a href="<?= BASE_URL ?>student/coaches.php"
                        class="list-group-item list-group-item-action <?php echo isActive('coaches.php'); ?>">
                        <i class="bi bi-person-video3"></i> Info Pelatih
                    </a>
                <?php endif; ?>

                <div class="mt-5 px-3">
                    <a href="<?= BASE_URL ?>auth/logout.php" class="btn btn-danger w-100 rounded-pill"
                        onclick="confirmLogout(event, this.href)"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light navbar-top border-bottom">
                <div class="container-fluid">
                    <i class="bi bi-list" id="menu-toggle"></i>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto mt-2 mt-lg-0 align-items-center">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#"
                                    id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="<?php echo $prof_pic; ?>" alt="Profile"
                                        class="rounded-circle object-fit-cover shadow-sm" width="40" height="40"
                                        style="border: 2px solid #fff;">
                                    <div class="d-none d-md-block text-start" style="line-height: 1.2;">
                                        <span class="d-block fw-bold text-dark"
                                            style="font-size: 0.9rem;"><?php echo $_SESSION['username'] ?? 'User'; ?></span>
                                        <small class="text-muted text-uppercase"
                                            style="font-size: 0.7rem;"><?php echo $role; ?></small>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0"
                                    aria-labelledby="navbarDropdown">
                                    <?php if ($role == 'admin'): ?>
                                        <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/profile.php"><i
                                                    class="bi bi-person me-2"></i> Profile Saya</a></li>
                                    <?php elseif ($role == 'siswa'): ?>
                                        <li><a class="dropdown-item" href="<?= BASE_URL ?>student/biodata.php"><i
                                                    class="bi bi-person-badge me-2"></i> Biodata</a></li>
                                    <?php endif; ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout.php"
                                            onclick="confirmLogout(event, this.href)"><i
                                                class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- Content will be injected here by the including file -->

            <!-- Script for Toggling -->
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var el = document.getElementById("wrapper");
                    var toggleButton = document.getElementById("menu-toggle");
                    var closeButton = document.getElementById("sidebar-close");

                    if (toggleButton) {
                        toggleButton.addEventListener('click', function (e) {
                            e.preventDefault();
                            el.classList.toggle("toggled");
                        });
                    }

                    if (closeButton) {
                        closeButton.addEventListener('click', function (e) {
                            e.preventDefault();
                            el.classList.remove("toggled");
                        });
                    }
                });

                function confirmLogout(event, url) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Yakin ingin keluar?',
                        text: "Anda akan mengakhiri sesi ini.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Logout',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = url;
                        }
                    });
                }
            </script>

        <?php } else { ?>
            <!-- Fallback if not logged in (Layout for guests if ever needed, but we force login) -->
            <nav class="navbar navbar-light bg-light">
                <div class="container">
                    <span class="navbar-brand mb-0 h1">Prestasi Bela Diri</span>
                    <a href="<?= BASE_URL ?>login.php" class="btn btn-primary">Login</a>
                </div>
            </nav>
        <?php } ?>