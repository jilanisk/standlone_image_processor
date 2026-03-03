<?php
ini_set('memory_limit', '512M');
set_time_limit(0);
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$uploadDir  = __DIR__ . '/uploads/';
$outputDir  = __DIR__ . '/uploadFolder/';
$zipDir     = __DIR__ . '/zips/';

foreach ([$uploadDir, $outputDir, $zipDir] as $d) {
    if (!is_dir($d)) mkdir($d, 0777, true);
}

$results = [];
$zipReady = false;
$zipName = '';

/* -------------------------------------------------
   ZIP DOWNLOAD HANDLER
--------------------------------------------------*/
if (isset($_GET['download']) && $_GET['download'] === 'zip') {

    $zipName = 'images_' . date('Ymd_His') . '.zip';
    $zipPath = $zipDir . $zipName;

    $zip = new ZipArchive();
    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($outputDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relative = substr($filePath, strlen($outputDir));
            $zip->addFile($filePath, $relative);
        }
    }

    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipName . '"');
    header('Content-Length: ' . filesize($zipPath));
    readfile($zipPath);

    unlink($zipPath);
    exit;
}

/* -------------------------------------------------
   EXCEL PROCESSING
--------------------------------------------------*/
if (!empty($_FILES['excel']['tmp_name'])) {

    $excelFile = $uploadDir . basename($_FILES['excel']['name']);
    move_uploaded_file($_FILES['excel']['tmp_name'], $excelFile);

    $sheet = IOFactory::load($excelFile)->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);

    foreach ($rows as $i => $row) {

        if ($i === 1) continue;

        $imagePath = trim($row['A'] ?? '');
        $skewId    = trim($row['B'] ?? '');

        if (!$imagePath || !$skewId) continue;

        $imagePath = str_replace('\\', '/', $imagePath);

        $isRemote = filter_var($imagePath, FILTER_VALIDATE_URL);

        if ($isRemote) {

            $imageData = @file_get_contents($imagePath);

            if ($imageData === false) {
                $results[] = [
                    'row' => $i,
                    'skew' => $skewId,
                    'file' => '-',
                    'status' => 'Remote Not Found'
                ];
                continue;
            }
        } else {

            if (!file_exists($imagePath)) {
                $results[] = [
                    'row' => $i,
                    'skew' => $skewId,
                    'file' => '-',
                    'status' => 'Missing'
                ];
                continue;
            }

            $imageData = file_get_contents($imagePath);
        }

        $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        $skuDir = $outputDir . $skewId . '/';

        if (!is_dir($skuDir)) mkdir($skuDir, 0777, true);

        $max = 0;
        foreach (glob($skuDir . $skewId . '_*.*') as $f) {
            if (preg_match('/_(\d+)\./', $f, $m)) {
                $max = max($max, (int)$m[1]);
            }
        }

        $seq = str_pad($max + 1, 2, '0', STR_PAD_LEFT);
        $dest = $skuDir . $skewId . '_' . $seq . '.' . $ext;

       file_put_contents($dest, $imageData);

        $results[] = [
            'row' => $i,
            'skew' => $skewId,
            'file' => basename($dest),
            'status' => 'Copied'
        ];
    }

    $zipReady = count($results) > 0;
}
?>
<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Excel Image Processor</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .spinner-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, .85);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        [data-bs-theme="dark"] .spinner-overlay {
            background: rgba(0, 0, 0, .7);
        }
    </style>
</head>

<body class="bg-body-tertiary">

    <!-- 🔄 Loading Spinner -->
    <div class="spinner-overlay" id="loader">
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <div>Processing Excel…</div>
        </div>
    </div>

    <div class="container py-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Excel Image Processor</h3>
                <div class="text-muted small">
                    Upload Excel → Organize images → Download ZIP
                </div>
            </div>

            <!-- 🌙 Dark Mode Toggle -->
            <button class="btn btn-outline-secondary btn-sm" id="themeToggle">
                🌙 Dark Mode
            </button>
        </div>

        <!-- Upload Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="post" enctype="multipart/form-data" onsubmit="showLoader()">
                    <div class="row g-2">
                        <div class="col-md-9">
                            <input type="file" name="excel" class="form-control" accept=".xlsx,.xls,.csv" required>
                        </div>
                        <div class="col-md-3 d-grid">
                            <button class="btn btn-primary">
                                Process Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($results)): ?>

            <?php
            $copied = count(array_filter($results, fn($r) => $r['status'] === 'Copied'));
            $missing = count(array_filter($results, fn($r) => $r['status'] === 'Missing'));
            ?>

            <!-- 📊 Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-success shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="text-success">Copied</h6>
                            <h2 class="mb-0"><?= $copied ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-danger shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="text-danger">Missing</h6>
                            <h2 class="mb-0"><?= $missing ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🔍 Search -->
            <div class="row g-3 mb-4">
                <div class="col-md-9">
                    <input type="text" id="search" class="form-control"
                        placeholder="Search by SKU, file, or status…">
                </div>
                <!-- ZIP Button -->
                <?php if (!empty($zipReady)): ?>
                    <div class="col-md-3">
                        <div class="d-flex justify-content-end">
                            <form method="get">
                                <button name="download" value="zip" class="btn btn-block btn-dark w-100">
                                    ⬇ Download ZIP
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- Table -->
            </div>

            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-striped mb-0" id="resultTable">
                        <thead class="table-light">
                            <tr>
                                <th>Row</th>
                                <th>Skew ID</th>
                                <th>File</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $r): ?>
                                <tr>
                                    <td><?= (int)$r['row'] ?></td>
                                    <td><?= htmlspecialchars($r['skew']) ?></td>
                                    <td><?= htmlspecialchars($r['file']) ?></td>
                                    <td>
                                        <?php if ($r['status'] === 'Copied'): ?>
                                            <span class="badge bg-success">Copied</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Missing</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>



        <?php endif; ?>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        /* 🔄 Loader */
        function showLoader() {
            document.getElementById('loader').style.display = 'flex';
        }

        /* 🔍 Table Search */
        document.getElementById('search')?.addEventListener('keyup', function() {
            let value = this.value.toLowerCase();
            document.querySelectorAll('#resultTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(value) ? '' : 'none';
            });
        });

        /* 🌙 Dark Mode */
        const toggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        function setTheme(mode) {
            html.setAttribute('data-bs-theme', mode);
            localStorage.setItem('theme', mode);
            toggle.textContent = mode === 'dark' ? '☀ Light Mode' : '🌙 Dark Mode';
        }

        toggle.addEventListener('click', () => {
            setTheme(html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
        });

        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) setTheme(savedTheme);
    </script>

</body>

</html>