<?php
/**
 * GUEST MANAGEMENT
 * Kelola data tamu undangan
 */

require_once '../config/autoload.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$error = '';
$success = '';

// Handle actions
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// Delete guest
if ($action === 'delete' && $id) {
    if ($db->delete('guests', 'id = ?', [$id])) {
        $success = 'Data tamu berhasil dihapus';
        $auth->logActivity($_SESSION['user_id'], 'delete_guest', "Deleted guest ID: {$id}");
    } else {
        $error = 'Gagal menghapus data tamu';
    }
}

// Import CSV
if (isset($_POST['import_csv'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $result = importCSV($_FILES['csv_file']['tmp_name']);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Silakan pilih file CSV';
    }
}

// Export CSV
if (isset($_GET['export'])) {
    exportCSV();
    exit;
}

// Get all guests with search
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$where = [];
$params = [];

if ($search) {
    $where[] = "(name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($status && in_array($status, ['hadir', 'tidak', 'pending'])) {
    $where[] = "attendance = ?";
    $params[] = $status;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$total = $db->fetchColumn(
    "SELECT COUNT(*) FROM guests {$whereClause}",
    $params
);

// Get guests
$guests = $db->fetchAll(
    "SELECT * FROM guests 
     {$whereClause} 
     ORDER BY created_at DESC 
     LIMIT ? OFFSET ?",
    array_merge($params, [$limit, $offset])
);

// Get statistics
$stats = $db->fetchAll(
    "SELECT attendance, COUNT(*) as count 
     FROM guests 
     GROUP BY attendance"
);

// Import CSV function
function importCSV($filepath) {
    global $db, $auth;
    
    $handle = fopen($filepath, 'r');
    if (!$handle) {
        return ['success' => false, 'message' => 'Gagal membuka file'];
    }
    
    $imported = 0;
    $skipped = 0;
    $errors = [];
    
    // Skip header row
    $header = fgetcsv($handle);
    
    $db->beginTransaction();
    
    try {
        while (($row = fgetcsv($handle)) !== FALSE) {
            if (count($row) < 2) {
                $skipped++;
                continue;
            }
            
            // Map CSV columns (adjust based on your CSV structure)
            $data = [
                'name' => trim($row[0] ?? ''),
                'phone' => trim($row[1] ?? ''),
                'email' => trim($row[2] ?? ''),
                'attendance' => trim($row[3] ?? 'pending'),
                'people' => intval($row[4] ?? 1),
                'message' => trim($row[5] ?? ''),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Validate required fields
            if (empty($data['name']) || empty($data['phone'])) {
                $skipped++;
                continue;
            }
            
            // Check if phone already exists
            $existing = $db->fetch(
                "SELECT id FROM guests WHERE phone = ?",
                [$data['phone']]
            );
            
            if ($existing) {
                // Update existing
                $db->update('guests', $data, 'id = ?', [$existing['id']]);
            } else {
                // Insert new
                $db->insert('guests', $data);
            }
            
            $imported++;
        }
        
        $db->commit();
        fclose($handle);
        
        $auth->logActivity($_SESSION['user_id'], 'import_guests', "Imported {$imported} guests from CSV");
        
        return [
            'success' => true,
            'message' => "Berhasil import {$imported} data tamu" . 
                        ($skipped > 0 ? ", {$skipped} data dilewati" : "")
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        fclose($handle);
        
        return [
            'success' => false,
            'message' => 'Import gagal: ' . $e->getMessage()
        ];
    }
}

// Export CSV function
function exportCSV() {
    global $db;
    
    $guests = $db->fetchAll(
        "SELECT * FROM guests ORDER BY name ASC"
    );
    
    // Set headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=tamu_undangan_' . date('Y-m-d') . '.csv');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write header
    fputcsv($output, [
        'Nama Lengkap',
        'Nomor Telepon/WA',
        'Email',
        'Status Kehadiran',
        'Jumlah Orang',
        'Ucapan/Doa',
        'Tanggal Daftar'
    ]);
    
    // Write data
    foreach ($guests as $guest) {
        fputcsv($output, [
            $guest['name'],
            $guest['phone'],
            $guest['email'] ?? '',
            $guest['attendance'] == 'hadir' ? 'Hadir' : 
               ($guest['attendance'] == 'tidak' ? 'Tidak Hadir' : 'Pending'),
            $guest['people'],
            $guest['message'] ?? '',
            date('d/m/Y H:i', strtotime($guest['created_at']))
        ]);
    }
    
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tamu - Admin Panel</title>
    
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 1.8em;
            font-weight: bold;
            color: #8B4513;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9em;
        }
        
        .search-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .form-inline {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .table th {
            background: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: 500;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }
        
        .page-link {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            color: #8B4513;
            text-decoration: none;
            border-radius: 4px;
        }
        
        .page-link:hover {
            background: #f8f9fa;
        }
        
        .page-link.active {
            background: #8B4513;
            color: white;
            border-color: #8B4513;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.85em;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary { background: #8B4513; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        
        .import-export {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #dee2e6;
        }
        
        .tab-link {
            padding: 10px 20px;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            color: #666;
            cursor: pointer;
            font-weight: 500;
        }
        
        .tab-link.active {
            color: #8B4513;
            border-bottom-color: #8B4513;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="content">
            <div class="page-header">
                <h1><i class="fas fa-users"></i> Kelola Data Tamu</h1>
                <p>Total <?= number_format($total) ?> tamu terdaftar</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="stats-cards">
                <?php 
                $statsMap = ['hadir' => 0, 'tidak' => 0, 'pending' => 0];
                foreach ($stats as $stat) {
                    $statsMap[$stat['attendance']] = $stat['count'];
                }
                ?>
                
                <div class="stat-card">
                    <div class="stat-number"><?= $total ?></div>
                    <div class="stat-label">Total Tamu</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?= $statsMap['hadir'] ?></div>
                    <div class="stat-label">Akan Hadir</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?= $statsMap['tidak'] ?></div>
                    <div class="stat-label">Tidak Hadir</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?= $statsMap['pending'] ?></div>
                    <div class="stat-label">Belum Konfirmasi</div>
                </div>
            </div>
            
            <!-- Search & Filter -->
            <div class="search-box">
                <form method="GET" class="form-inline">
                    <input type="text" 
                           name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Cari nama, telepon, atau email..."
                           class="form-control" 
                           style="flex: 1;">
                    
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="hadir" <?= $status == 'hadir' ? 'selected' : '' ?>>Hadir</option>
                        <option value="tidak" <?= $status == 'tidak' ? 'selected' : '' ?>>Tidak Hadir</option>
                        <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                    </select>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    
                    <a href="guests.php" class="btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </form>
            </div>
            
            <!-- Tabs -->
            <div class="tab-nav">
                <button class="tab-link active" data-tab="list">Daftar Tamu</button>
                <button class="tab-link" data-tab="import">Import CSV</button>
                <button class="tab-link" data-tab="export">Export Data</button>
            </div>
            
            <!-- Tab Content: List -->
            <div class="tab-content active" id="list">
                <?php if (empty($guests)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Tidak ada data tamu ditemukan.
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Kontak</th>
                                <th>Status</th>
                                <th>Jumlah</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($guests as $index => $guest): ?>
                            <tr>
                                <td><?= $offset + $index + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($guest['name']) ?></strong>
                                    <?php if ($guest['message']): ?>
                                    <br><small><?= substr(htmlspecialchars($guest['message']), 0, 50) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($guest['phone']) ?>
                                    <?php if ($guest['email']): ?>
                                    <br><small><?= htmlspecialchars($guest['email']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $guest['attendance'] == 'hadir' ? 'success' : 
                                        ($guest['attendance'] == 'tidak' ? 'danger' : 'warning')
                                    ?>">
                                        <?= $guest['attendance'] == 'hadir' ? 'Hadir' : 
                                           ($guest['attendance'] == 'tidak' ? 'Tidak' : 'Pending') ?>
                                    </span>
                                </td>
                                <td><?= $guest['people'] ?> orang</td>
                                <td><?= date('d/m/Y', strtotime($guest['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-sm btn-primary" 
                                                onclick="editGuest(<?= $guest['id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <button class="btn-sm btn-secondary" 
                                                onclick="viewDetails(<?= $guest['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button class="btn-sm btn-danger" 
                                                onclick="confirmDelete(<?= $guest['id'] ?>, '<?= htmlspecialchars($guest['name']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total > $limit): ?>
                <div class="pagination">
                    <?php
                    $totalPages = ceil($total / $limit);
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>" 
                       class="page-link">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>" 
                       class="page-link <?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>" 
                       class="page-link">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Tab Content: Import -->
            <div class="tab-content" id="import">
                <div class="import-export">
                    <h3><i class="fas fa-file-import"></i> Import Data dari CSV</h3>
                    <p>Upload file CSV dengan format berikut:</p>
                    
                    <div class="alert alert-info">
                        <strong>Format CSV:</strong><br>
                        Nama, Telepon, Email, Status (hadir/tidak/pending), Jumlah Orang, Pesan
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Pilih File CSV:</label>
                            <input type="file" name="csv_file" accept=".csv" required>
                            <small>File harus berformat .csv dengan maksimal 5MB</small>
                        </div>
                        
                        <button type="submit" name="import_csv" class="btn-primary">
                            <i class="fas fa-upload"></i> Import Data
                        </button>
                        
                        <a href="sample.csv" class="btn-secondary" download>
                            <i class="fas fa-download"></i> Download Template
                        </a>
                    </form>
                </div>
            </div>
            
            <!-- Tab Content: Export -->
            <div class="tab-content" id="export">
                <div class="import-export">
                    <h3><i class="fas fa-file-export"></i> Export Data ke CSV</h3>
                    <p>Download semua data tamu dalam format CSV untuk backup atau analisis.</p>
                    
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle"></i> 
                        Data akan diexport dalam format UTF-8 dengan semua kolom yang tersedia.
                    </div>
                    
                    <a href="?export=1" class="btn-primary">
                        <i class="fas fa-download"></i> Download CSV
                    </a>
                    
                    <button class="btn-secondary" onclick="printGuests()">
                        <i class="fas fa-print"></i> Cetak Daftar
                    </button>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Tab Navigation
    document.querySelectorAll('.tab-link').forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            document.querySelectorAll('.tab-link').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Edit Guest
    function editGuest(id) {
        window.location.href = 'guest-edit.php?id=' + id;
    }
    
    // View Details
    function viewDetails(id) {
        window.location.href = 'guest-view.php?id=' + id;
    }
    
    // Confirm Delete
    function confirmDelete(id, name) {
        if (confirm(`Apakah Anda yakin ingin menghapus tamu:\n"${name}"?`)) {
            window.location.href = 'guests.php?action=delete&id=' + id;
        }
    }
    
    // Print Guests
    function printGuests() {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>Daftar Tamu - ${new Date().toLocaleDateString()}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    h1 { color: #8B4513; border-bottom: 2px solid #8B4513; padding-bottom: 10px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th { background: #f8f9fa; padding: 10px; border: 1px solid #ddd; text-align: left; }
                    td { padding: 10px; border: 1px solid #ddd; }
                    .badge { padding: 3px 6px; border-radius: 3px; font-size: 0.85em; }
                    .badge-success { background: #d4edda; color: #155724; }
                    .badge-danger { background: #f8d7da; color: #721c24; }
                    .badge-warning { background: #fff3cd; color: #856404; }
                    @media print {
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <h1>Daftar Tamu Undangan</h1>
                <p>Tanggal cetak: ${new Date().toLocaleDateString('id-ID')}</p>
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Jumlah</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Array.from(document.querySelectorAll('.table tbody tr')).map(row => {
                            const cells = row.querySelectorAll('td');
                            return `
                            <tr>
                                <td>${cells[1]?.querySelector('strong')?.textContent || ''}</td>
                                <td>${cells[2]?.textContent?.split('\\n')[0] || ''}</td>
                                <td>${cells[2]?.querySelector('small')?.textContent || ''}</td>
                                <td>${cells[3]?.textContent || ''}</td>
                                <td>${cells[4]?.textContent || ''}</td>
                                <td>${cells[5]?.textContent || ''}</td>
                            </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
                <div class="no-print" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                    <button onclick="window.print()">Cetak</button>
                    <button onclick="window.close()">Tutup</button>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
    }
    
    // Auto refresh search results
    let searchTimeout;
    document.querySelector('input[name="search"]').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (this.value.length >= 3 || this.value.length === 0) {
                this.form.submit();
            }
        }, 500);
    });
    </script>
</body>
</html>