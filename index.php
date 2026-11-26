<?php
session_start();

// Inisialisasi data awal mahasiswa jika session masih kosong
if (!isset($_SESSION['mahasiswa'])) {
    $_SESSION['mahasiswa'] = [
        [
            'nim' => '17241010',
            'nama' => 'Risma Siti Awaliyah',
            'email' => 'risma@ars.ac.id',
            'prodi' => 'Teknik Informatika',
            'semester' => '5',
            'jk' => 'Perempuan',
            'minat' => ['Data Science', 'Business Intelligence']
        ],
        [
            'nim' => '17241001',
            'nama' => 'Eprilda Nonifili Z',
            'email' => 'eprilda@ars.ac.id',
            'prodi' => 'Teknik Informatika',
            'semester' => '5',
            'jk' => 'Perempuan',
            'minat' => ['UI/UX Design', 'Web Development']
        ],
        [
            'nim' => '17241002',
            'nama' => 'Saif Lathif',
            'email' => 'saif@ars.ac.id',
            'prodi' => 'Teknik Informatika',
            'semester' => '5',
            'jk' => 'Laki-laki',
            'minat' => ['Data Science', 'Networking']
        ],
       [
            'nim' => '17241003',
            'nama' => 'Apsarini Syifa',
            'email' => 'syifa@ars.ac.id',
            'prodi' => 'Teknik Informatika',
            'semester' => '5',
            'jk' => 'Perempuan',
            'minat' => ['Web Development', 'Business Intelligence']
        ]
    ];
}

$notif = null;
$editData = null;
$editIndex = -1;

// Proses Reset Data Default
if (isset($_GET['reset'])) {
    unset($_SESSION['mahasiswa']);
    header("Location: index.php?msg=reset");
    exit;
}

// Proses Hapus Data (GET)
if (isset($_GET['hapus'])) {
    $idx = (int)$_GET['hapus'];
    if (isset($_SESSION['mahasiswa'][$idx])) {
        array_splice($_SESSION['mahasiswa'], $idx, 1);
        header("Location: index.php?msg=deleted");
        exit;
    }
}

// Ambil Data untuk Diedit
if (isset($_GET['edit'])) {
    $idx = (int)$_GET['edit'];
    if (isset($_SESSION['mahasiswa'][$idx])) {
        $editData = $_SESSION['mahasiswa'][$idx];
        $editIndex = $idx;
    }
}

// Proses Simpan / Update Data (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = htmlspecialchars(trim($_POST['nim'] ?? ''));
    $nama = htmlspecialchars(trim($_POST['nama'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $prodi = htmlspecialchars(trim($_POST['prodi'] ?? ''));
    $semester = htmlspecialchars(trim($_POST['semester'] ?? ''));
    $jk = htmlspecialchars(trim($_POST['jk'] ?? ''));
    $minat = isset($_POST['minat']) ? array_map('htmlspecialchars', $_POST['minat']) : [];
    $idx = (int)($_POST['edit_index'] ?? -1);

    if ($nim && $nama && $email && $prodi && $semester && $jk) {
        if (!ctype_digit($nim)) {
            $notif = ['type' => 'error', 'text' => 'NIM harus berupa angka saja!'];
        } else {
            $record = [
                'nim' => $nim,
                'nama' => $nama,
                'email' => $email,
                'prodi' => $prodi,
                'semester' => $semester,
                'jk' => $jk,
                'minat' => $minat
            ];

            if ($idx >= 0 && isset($_SESSION['mahasiswa'][$idx])) {
                $_SESSION['mahasiswa'][$idx] = $record;
                header("Location: index.php?msg=updated");
                exit;
            } else {
                $_SESSION['mahasiswa'][] = $record;
                header("Location: index.php?msg=added");
                exit;
            }
        }
    } else {
        $notif = ['type' => 'error', 'text' => 'Harap lengkapi semua kolom wajib bertanda bintang (*)!'];
    }
}

// Notifikasi dari parameter GET
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added':
            $notif = ['type' => 'success', 'text' => 'Data mahasiswa berhasil ditambahkan!'];
            break;
        case 'updated':
            $notif = ['type' => 'success', 'text' => 'Perubahan data mahasiswa berhasil disimpan!'];
            break;
        case 'deleted':
            $notif = ['type' => 'warning', 'text' => 'Data mahasiswa telah dihapus dari daftar.'];
            break;
        case 'reset':
            $notif = ['type' => 'info', 'text' => 'Daftar data dikembalikan ke contoh awal.'];
            break;
    }
}

// Filter Data melalui HTTP GET
$filterProdi = $_GET['filter_prodi'] ?? '';
$filterKeyword = trim($_GET['q'] ?? '');

$filteredList = [];
foreach ($_SESSION['mahasiswa'] as $index => $mhs) {
    $matchProdi = ($filterProdi === '' || $mhs['prodi'] === $filterProdi);
    $matchKeyword = ($filterKeyword === '' ||
        stripos($mhs['nama'], $filterKeyword) !== false ||
        stripos($mhs['nim'], $filterKeyword) !== false ||
        stripos($mhs['email'], $filterKeyword) !== false);

    if ($matchProdi && $matchKeyword) {
        $mhs['original_index'] = $index;
        $filteredList[] = $mhs;
    }
}

// Hitung Statistik Dinamis
$totalMhs = count($_SESSION['mahasiswa']);
$totalTI = count(array_filter($_SESSION['mahasiswa'], fn($m) => $m['prodi'] === 'Teknik Informatika'));
$totalSI = count(array_filter($_SESSION['mahasiswa'], fn($m) => $m['prodi'] === 'Sistem Informasi'));
$totalLain = $totalMhs - ($totalTI + $totalSI);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Data Mahasiswa - Sistem Informasi Akademik</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #fdf8fb;
            --bg-card: #ffffff;
            --pastel-pink: #fce7f3;
            --pastel-pink-dark: #db2777;
            --pastel-lavender: #ede9fe;
            --pastel-lavender-dark: #7c3aed;
            --pastel-mint: #dcfce7;
            --pastel-mint-dark: #15803d;
            --pastel-peach: #ffedd5;
            --pastel-peach-dark: #c2410c;
            --pastel-sky: #e0f2fe;
            --pastel-sky-dark: #0369a1;
            --text-main: #334155;
            --text-muted: #64748b;
            --border-soft: #f1e4ec;
            --shadow-soft: 0 10px 25px -5px rgba(219, 39, 119, 0.06), 0 8px 10px -6px rgba(124, 58, 237, 0.04);
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            padding-bottom: 24px;
        }

        /* Header & Navbar */
        .site-header {
            background: linear-gradient(135deg, #fbcfe8 0%, #ddd6fe 50%, #bae6fd 100%);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            position: sticky;
            top: 0;
            z-index: 100;
            width: 100%;
        }

        .navbar {
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.25rem;
            color: #4c1d95;
            min-width: 0;
        }

        .brand-badge {
            background: #ffffff;
            color: var(--pastel-lavender-dark);
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            text-decoration: none;
            color: #3b0764;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 6px 12px;
            border-radius: 10px;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.65);
        }

        .container {
            max-width: 1220px;
            margin: 28px auto;
            padding: 0 20px;
        }

        /* Toast Notification */
        .toast {
            padding: 14px 20px;
            border-radius: 14px;
            margin-bottom: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            font-size: 0.92rem;
            animation: slideDown 0.35s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .toast-success { background: var(--pastel-mint); color: var(--pastel-mint-dark); border: 1px solid #bbf7d0; }
        .toast-warning { background: var(--pastel-peach); color: var(--pastel-peach-dark); border: 1px solid #fed7aa; }
        .toast-error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .toast-info    { background: var(--pastel-sky); color: var(--pastel-sky-dark); border: 1px solid #bae6fd; }

        /* Stat Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--bg-card);
            padding: 20px;
            border-radius: 18px;
            border: 1px solid var(--border-soft);
            box-shadow: var(--shadow-soft);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .stat-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 1.85rem;
            font-weight: 700;
        }

        .stat-pink .stat-value { color: var(--pastel-pink-dark); }
        .stat-lavender .stat-value { color: var(--pastel-lavender-dark); }
        .stat-mint .stat-value { color: var(--pastel-mint-dark); }
        .stat-sky .stat-value { color: var(--pastel-sky-dark); }

        /* Main Layout */
        .main-grid {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 24px;
            align-items: start;
        }

        .card {
            background: var(--bg-card);
            border-radius: 20px;
            padding: 24px;
            border: 1px solid var(--border-soft);
            box-shadow: var(--shadow-soft);
            min-width: 0;
        }

        .card-header {
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px dashed var(--border-soft);
        }

        .card-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1e293b;
        }

        .card-subtitle {
            font-size: 0.84rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: #475569;
        }

        .required {
            color: #e11d48;
        }

        input[type="text"],
        input[type="email"],
        select {
            width: 100%;
            max-width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.9rem;
            color: var(--text-main);
            background-color: #fcfaff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #c084fc;
            box-shadow: 0 0 0 3px rgba(192, 132, 252, 0.18);
            background-color: #ffffff;
        }

        .radio-group, .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 4px;
        }

        .option-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.83rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .option-pill:hover {
            background: var(--pastel-lavender);
            border-color: #d8b4fe;
        }

        .option-pill input {
            accent-color: var(--pastel-lavender-dark);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 11px 18px;
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.15s, opacity 0.2s;
        }

        .btn:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #ec4899, #8b5cf6);
            color: #ffffff;
            width: 100%;
            margin-top: 6px;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            width: 100%;
            margin-top: 8px;
        }

        .btn-filter {
            background: var(--pastel-lavender);
            color: var(--pastel-lavender-dark);
        }

        .btn-reset {
            background: var(--pastel-peach);
            color: var(--pastel-peach-dark);
            font-size: 0.82rem;
            padding: 8px 14px;
        }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 18px;
            background: #faf7fc;
            padding: 14px;
            border-radius: 14px;
            border: 1px solid var(--border-soft);
        }

        .filter-bar input {
            flex: 1;
            min-width: 180px;
        }

        .filter-bar select {
            width: auto;
            min-width: 175px;
        }

        /* Table Styling */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }

        thead th {
            background: #f5f3ff;
            color: #4c1d95;
            font-weight: 700;
            text-align: left;
            padding: 13px 14px;
            border-bottom: 2px solid #ede9fe;
            white-space: nowrap;
        }

        tbody td {
            padding: 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        tbody tr:hover {
            background-color: #fdf9fc;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 600;
        }

        .badge-ti { background: var(--pastel-lavender); color: var(--pastel-lavender-dark); }
        .badge-si { background: var(--pastel-sky); color: var(--pastel-sky-dark); }
        .badge-other { background: var(--pastel-mint); color: var(--pastel-mint-dark); }

        .tag-minat {
            display: inline-block;
            background: var(--pastel-pink);
            color: var(--pastel-pink-dark);
            font-size: 0.74rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 6px;
            margin: 2px 3px 2px 0;
        }

        .action-btns {
            display: flex;
            gap: 6px;
        }

        .btn-sm {
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-edit {
            background: var(--pastel-sky);
            color: var(--pastel-sky-dark);
        }

        .btn-delete {
            background: #ffe4e6;
            color: #be123c;
        }

        .empty-state {
            text-align: center;
            padding: 36px 16px;
            color: var(--text-muted);
        }

        /* Footer */
        .site-footer {
            max-width: 1220px;
            margin: 28px auto 0;
            padding: 16px 20px;
            text-align: center;
            font-size: 0.82rem;
            color: var(--text-muted);
            border-top: 1px dashed var(--border-soft);
        }

        /* Penyesuaian khusus layar HP tanpa mengubah tampilan laptop */
        @media (max-width: 960px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .navbar {
                padding: 12px 16px;
                gap: 8px;
            }

            .brand {
                font-size: 1.05rem;
                flex-wrap: wrap;
                gap: 6px;
            }

            .nav-links {
                width: 100%;
                gap: 6px;
                justify-content: flex-start;
            }

            .nav-links a {
                font-size: 0.78rem;
                padding: 5px 9px;
            }

            .container {
                margin: 18px auto;
                padding: 0 12px;
            }

            .card {
                padding: 16px;
                border-radius: 16px;
            }

            .filter-bar {
                flex-direction: column;
                padding: 12px;
            }

            .filter-bar input,
            .filter-bar select,
            .filter-bar .btn-filter {
                width: 100%;
                min-width: 0;
            }

            table {
                font-size: 0.82rem;
            }

            thead th,
            tbody td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>

<header class="site-header">
    <nav class="navbar" aria-label="Navigasi Utama">
        <div class="brand">
            <span>Portal Data Mahasiswa</span>
            <span class="brand-badge">ARS University</span>
        </div>
        <div class="nav-links">
            <a href="#ringkasan">Ringkasan</a>
            <a href="#form-input">Input Data</a>
            <a href="#daftar-mahasiswa">Daftar Mahasiswa</a>
            <a href="?reset=1" class="btn-reset" onclick="return confirm('Kembalikan daftar data ke contoh awal?')">Reset Data</a>
        </div>
    </nav>
</header>

<main class="container">
    <?php if ($notif): ?>
        <div class="toast toast-<?= $notif['type'] ?>" id="toastAlert">
            <span><?= $notif['text'] ?></span>
            <button onclick="document.getElementById('toastAlert').style.display='none'" style="background:none;border:none;font-weight:bold;cursor:pointer;color:inherit;">✕</button>
        </div>
    <?php endif; ?>

    <!-- Ringkasan Statistik -->
    <section id="ringkasan" class="stats-grid" aria-label="Ringkasan Statistik">
        <article class="stat-card stat-pink">
            <span class="stat-label">Total Mahasiswa</span>
            <span class="stat-value"><?= $totalMhs ?></span>
        </article>
        <article class="stat-card stat-lavender">
            <span class="stat-label">Teknik Informatika</span>
            <span class="stat-value"><?= $totalTI ?></span>
        </article>
        <article class="stat-card stat-sky">
            <span class="stat-label">Sistem Informasi</span>
            <span class="stat-value"><?= $totalSI ?></span>
        </article>
        <article class="stat-card stat-mint">
            <span class="stat-label">Program Studi Lainnya</span>
            <span class="stat-value"><?= $totalLain ?></span>
        </article>
    </section>

    <!-- Grid Utama: Form Input & Tabel Daftar -->
    <div class="main-grid">
        <!-- Kolom Kiri: Form HTTP POST -->
        <section id="form-input" class="card" aria-label="Form Mahasiswa">
            <article>
                <div class="card-header">
                    <h2 class="card-title"><?= $editData ? 'Edit Data Mahasiswa' : 'Input Data Mahasiswa' ?></h2>
                    <p class="card-subtitle">Pengiriman data menggunakan metode HTTP POST</p>
                </div>

                <form method="POST" action="index.php">
                    <input type="hidden" name="edit_index" value="<?= $editIndex ?>">

                    <div class="form-group">
                        <label for="nim">NIM (Nomor Induk Mahasiswa) <span class="required">*</span></label>
                        <input type="text" id="nim" name="nim" pattern="[0-9]+" title="NIM harus berupa angka saja" placeholder="Contoh: 17241010" required value="<?= $editData['nim'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="nama">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" required value="<?= $editData['nama'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Kampus / Pribadi <span class="required">*</span></label>
                        <input type="email" id="email" name="email" placeholder="nama@ars.ac.id" required value="<?= $editData['email'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="prodi">Program Studi <span class="required">*</span></label>
                        <select id="prodi" name="prodi" required>
                            <option value="">-- Pilih Program Studi --</option>
                            <?php
                            $daftarProdi = ['Teknik Informatika', 'Sistem Informasi', 'Manajemen', 'Akuntansi', 'Ilmu Komunikasi'];
                            foreach ($daftarProdi as $p):
                                $selected = (($editData['prodi'] ?? '') === $p) ? 'selected' : '';
                            ?>
                                <option value="<?= $p ?>" <?= $selected ?>><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="semester">Semester <span class="required">*</span></label>
                        <select id="semester" name="semester" required>
                            <?php for ($s = 1; $s <= 8; $s++):
                                $selected = (($editData['semester'] ?? '5') == $s) ? 'selected' : '';
                            ?>
                                <option value="<?= $s ?>" <?= $selected ?>>Semester <?= $s ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Jenis Kelamin <span class="required">*</span></label>
                        <div class="radio-group">
                            <label class="option-pill">
                                <input type="radio" name="jk" value="Perempuan" required <?= (($editData['jk'] ?? '') === 'Perempuan') ? 'checked' : '' ?>> Perempuan
                            </label>
                            <label class="option-pill">
                                <input type="radio" name="jk" value="Laki-laki" required <?= (($editData['jk'] ?? '') === 'Laki-laki') ? 'checked' : '' ?>> Laki-laki
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Bidang Minat / Keahlian</label>
                        <div class="checkbox-group">
                            <?php
                            $daftarMinat = ['Web Development', 'Data Science', 'UI/UX Design', 'Business Intelligence', 'Networking'];
                            $selectedMinat = $editData['minat'] ?? [];
                            foreach ($daftarMinat as $m):
                                $checked = in_array($m, $selectedMinat) ? 'checked' : '';
                            ?>
                                <label class="option-pill">
                                    <input type="checkbox" name="minat[]" value="<?= $m ?>" <?= $checked ?>> <?= $m ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <?= $editData ? 'Simpan Perubahan' : 'Simpan Data Mahasiswa' ?>
                    </button>

                    <?php if ($editData): ?>
                        <a href="index.php" class="btn btn-secondary">Batal Edit</a>
                    <?php endif; ?>
                </form>
            </article>
        </section>

        <!-- Kolom Kanan: Pencarian HTTP GET & Tabel -->
        <section id="daftar-mahasiswa" class="card" aria-label="Daftar Data Mahasiswa">
            <article>
                <div class="card-header">
                    <h2 class="card-title">Daftar Data Mahasiswa</h2>
                    <p class="card-subtitle">Pencarian server-side via HTTP GET serta filter cepat langsung</p>
                </div>

                <!-- Form Filter HTTP GET -->
                <form method="GET" action="index.php#daftar-mahasiswa" class="filter-bar">
                    <input type="text" id="liveSearchInput" name="q" placeholder="Cari NIM, nama, atau email..." value="<?= htmlspecialchars($filterKeyword) ?>">
                    <select id="liveProdiFilter" name="filter_prodi">
                        <option value="">Semua Prodi</option>
                        <?php foreach ($daftarProdi as $p): ?>
                            <option value="<?= $p ?>" <?= ($filterProdi === $p) ? 'selected' : '' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-filter">Filter</button>
                    <?php if ($filterKeyword !== '' || $filterProdi !== ''): ?>
                        <a href="index.php#daftar-mahasiswa" class="btn btn-secondary" style="width:auto;margin-top:0;">Reset</a>
                    <?php endif; ?>
                </form>

                <div class="table-responsive">
                    <table id="mahasiswaTable">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Mahasiswa</th>
                                <th>Prodi / Smt</th>
                                <th>Bidang Minat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($filteredList)): ?>
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        Tidak ada data mahasiswa yang sesuai dengan pencarian.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($filteredList as $mhs):
                                    $badgeClass = 'badge-other';
                                    if ($mhs['prodi'] === 'Teknik Informatika') $badgeClass = 'badge-ti';
                                    elseif ($mhs['prodi'] === 'Sistem Informasi') $badgeClass = 'badge-si';
                                ?>
                                    <tr data-prodi="<?= $mhs['prodi'] ?>">
                                        <td><strong><?= $mhs['nim'] ?></strong></td>
                                        <td>
                                            <div style="font-weight:700;color:#1e293b;"><?= $mhs['nama'] ?></div>
                                            <div style="font-size:0.8rem;color:#64748b;"><?= $mhs['email'] ?> &bull; <?= $mhs['jk'] ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $badgeClass ?>"><?= $mhs['prodi'] ?></span>
                                            <div style="font-size:0.78rem;color:#64748b;margin-top:4px;">Semester <?= $mhs['semester'] ?></div>
                                        </td>
                                        <td>
                                            <?php if (!empty($mhs['minat'])): ?>
                                                <?php foreach ($mhs['minat'] as $tag): ?>
                                                    <span class="tag-minat"><?= $tag ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span style="color:#94a3b8;font-size:0.8rem;">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                                <a href="?edit=<?= $mhs['original_index'] ?>#form-input" class="btn-sm btn-edit">Edit</a>
                                                <a href="?hapus=<?= $mhs['original_index'] ?>" class="btn-sm btn-delete" onclick="return confirm('Yakin ingin menghapus data <?= $mhs['nama'] ?>?')">Hapus</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </div>
</main>

<footer class="site-footer">
    <p>Portal Data Mahasiswa &bull; Sistem Manajemen Data Interaktif &bull; ARS University</p>
</footer>

<script>
    // Filter instan sisi klien (real-time tanpa reload halaman)
    const searchInput = document.getElementById('liveSearchInput');
    const prodiSelect = document.getElementById('liveProdiFilter');
    const tableRows = document.querySelectorAll('#mahasiswaTable tbody tr[data-prodi]');

    function filterTableInstant() {
        const keyword = searchInput.value.toLowerCase();
        const selectedProdi = prodiSelect.value;

        tableRows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowProdi = row.getAttribute('data-prodi');
            const matchText = text.includes(keyword);
            const matchProdi = selectedProdi === '' || rowProdi === selectedProdi;
            row.style.display = (matchText && matchProdi) ? '' : 'none';
        });
    }

    if (searchInput && prodiSelect) {
        searchInput.addEventListener('input', filterTableInstant);
        prodiSelect.addEventListener('change', filterTableInstant);
    }

    // Hilangkan notifikasi otomatis setelah 4 detik
    const toast = document.getElementById('toastAlert');
    if (toast) {
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.5s ease';
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    }
</script>

</body>
</html>