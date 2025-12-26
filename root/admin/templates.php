<?php
/**
 * TEMPLATE MANAGEMENT
 * Kelola 3 template premium untuk website
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

// Get all templates
$templates = $db->fetchAll("SELECT * FROM templates ORDER BY name");
$activeTemplate = $db->fetch("SELECT * FROM templates WHERE is_active = 1 LIMIT 1");

// Handle template activation
if (isset($_GET['activate'])) {
    $templateId = intval($_GET['activate']);
    
    // Deactivate all templates first
    $db->update('templates', ['is_active' => 0], '1=1');
    
    // Activate selected template
    $result = $db->update('templates', 
        ['is_active' => 1, 'activated_at' => date('Y-m-d H:i:s')],
        'id = ?', [$templateId]
    );
    
    if ($result) {
        $success = 'Template berhasil diaktifkan!';
        $auth->logActivity($_SESSION['user_id'], 'activate_template', "Activated template ID: {$templateId}");
        
        // Refresh template data
        $templates = $db->fetchAll("SELECT * FROM templates ORDER BY name");
        $activeTemplate = $db->fetch("SELECT * FROM templates WHERE is_active = 1 LIMIT 1");
    } else {
        $error = 'Gagal mengaktifkan template';
    }
}

// Handle template preview
if (isset($_GET['preview'])) {
    $templateId = intval($_GET['preview']);
    $template = $db->fetch("SELECT * FROM templates WHERE id = ?", [$templateId]);
    
    if ($template) {
        // Set preview session
        $_SESSION['preview_template'] = $template['folder_name'];
        header('Location: ../?preview=1');
        exit;
    }
}

// Handle template customization
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customize'])) {
    $templateId = intval($_POST['template_id']);
    $customCss = $_POST['custom_css'] ?? '';
    
    // Save custom CSS
    $customFile = "../templates/{$templates[$templateId-1]['folder_name']}/assets/css/custom.css";
    
    if (file_put_contents($customFile, $customCss)) {
        $success = 'Custom CSS berhasil disimpan!';
        $auth->logActivity($_SESSION['user_id'], 'customize_template', "Customized template ID: {$templateId}");
    } else {
        $error = 'Gagal menyimpan custom CSS';
    }
}

// Get template directory contents
function getTemplateInfo($folderName) {
    $templatePath = "../templates/{$folderName}/";
    
    if (!is_dir($templatePath)) {
        return null;
    }
    
    $info = [
        'name' => $folderName,
        'path' => $templatePath,
        'files' => [],
        'has_custom_css' => false,
        'custom_css_content' => ''
    ];
    
    // Check if custom.css exists
    $customCssFile = $templatePath . 'assets/css/custom.css';
    if (file_exists($customCssFile)) {
        $info['has_custom_css'] = true;
        $info['custom_css_content'] = file_get_contents($customCssFile);
    }
    
    // Count files
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($templatePath));
    $info['file_count'] = iterator_count($iterator);
    
    // Get main files
    $mainFiles = ['index.php', 'config.php', 'assets/css/style.css', 'assets/js/main.js'];
    foreach ($mainFiles as $file) {
        if (file_exists($templatePath . $file)) {
            $info['files'][] = $file;
        }
    }
    
    return $info;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Template - Admin Panel</title>
    
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .template-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 2px solid transparent;
        }
        
        .template-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .template-card.active {
            border-color: #8B4513;
            box-shadow: 0 5px 20px rgba(139, 69, 19, 0.2);
        }
        
        .template-preview {
            height: 200px;
            background: #f9f5f0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .template-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .template-preview .no-preview {
            color: #999;
            font-size: 0.9em;
        }
        
        .template-info {
            padding: 20px;
        }
        
        .template-name {
            font-size: 1.2em;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .template-status {
            font-size: 0.8em;
            padding: 3px 8px;
            border-radius: 12px;
            background: #28a745;
            color: white;
        }
        
        .template-desc {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .template-meta {
            display: flex;
            gap: 15px;
            font-size: 0.85em;
            color: #888;
            margin-bottom: 20px;
        }
        
        .template-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-template {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .btn-activate { background: #28a745; color: white; }
        .btn-activate:hover { background: #218838; }
        
        .btn-preview { background: #17a2b8; color: white; }
        .btn-preview:hover { background: #138496; }
        
        .btn-customize { background: #ffc107; color: #333; }
        .btn-customize:hover { background: #e0a800; }
        
        .btn-files { background: #6c757d; color: white; }
        .btn-files:hover { background: #5a6268; }
        
        .customization-panel {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .css-editor {
            margin: 20px 0;
        }
        
        .css-header {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            border: 1px solid #dee2e6;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .css-title {
            font-weight: 600;
            color: #495057;
        }
        
        .css-actions {
            display: flex;
            gap: 10px;
        }
        
        .css-content {
            font-family: 'Courier New', monospace;
            width: 100%;
            height: 300px;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 0 0 8px 8px;
            resize: vertical;
            font-size: 14px;
            line-height: 1.5;
            tab-size: 4;
        }
        
        .css-content:focus {
            outline: none;
            border-color: #8B4513;
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }
        
        .css-help {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.9em;
            color: #666;
        }
        
        .css-help h4 {
            color: #495057;
            margin-bottom: 10px;
        }
        
        .css-example {
            background: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        
        .preview-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .color-picker {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .color-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .color-box {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: 2px solid #ddd;
            cursor: pointer;
        }
        
        .color-input {
            width: 100px;
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
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
                <h1><i class="fas fa-palette"></i> Kelola Template</h1>
                <p>Pilih dan kustomisasi template untuk website pernikahan</p>
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
            
            <?php if (isset($_SESSION['preview_template'])): ?>
            <div class="preview-notice">
                <i class="fas fa-eye"></i>
                <div>
                    <strong>Anda sedang dalam mode preview!</strong>
                    <p>Template <?= htmlspecialchars($_SESSION['preview_template']) ?> sedang ditampilkan.</p>
                    <a href="?clear_preview=1" class="btn-secondary" style="margin-top: 10px; display: inline-block;">
                        <i class="fas fa-times"></i> Keluar dari Preview
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Tabs -->
            <div class="tab-nav">
                <button class="tab-link active" data-tab="templates">
                    <i class="fas fa-th-large"></i> Daftar Template
                </button>
                <button class="tab-link" data-tab="customize">
                    <i class="fas fa-sliders-h"></i> Kustomisasi
                </button>
                <button class="tab-link" data-tab="colors">
                    <i class="fas fa-fill-drip"></i> Warna & Font
                </button>
            </div>
            
            <!-- Tab Content: Templates -->
            <div class="tab-content active" id="templates">
                <h2>Template Premium Tersedia</h2>
                <p>Pilih template yang ingin digunakan untuk website pernikahan:</p>
                
                <div class="template-grid">
                    <?php foreach ($templates as $template): 
                        $templateInfo = getTemplateInfo($template['folder_name']);
                    ?>
                    <div class="template-card <?= $template['is_active'] ? 'active' : '' ?>">
                        <div class="template-preview">
                            <?php if ($template['thumbnail'] && file_exists("../" . $template['thumbnail'])): ?>
                            <img src="../<?= htmlspecialchars($template['thumbnail']) ?>" 
                                 alt="<?= htmlspecialchars($template['name']) ?>">
                            <?php else: ?>
                            <div class="no-preview">
                                <i class="fas fa-image" style="font-size: 3em; opacity: 0.3; margin-bottom: 10px;"></i>
                                <p>No preview available</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="template-info">
                            <div class="template-name">
                                <?= htmlspecialchars($template['name']) ?>
                                <?php if ($template['is_active']): ?>
                                <span class="template-status">Aktif</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="template-desc">
                                <?= htmlspecialchars($template['description'] ?? 'Template pernikahan premium dengan desain elegan.') ?>
                            </div>
                            
                            <div class="template-meta">
                                <span><i class="fas fa-folder"></i> <?= $template['folder_name'] ?></span>
                                <span><i class="fas fa-code"></i> <?= $templateInfo['file_count'] ?? '?' ?> files</span>
                                <?php if ($template['activated_at']): ?>
                                <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($template['activated_at'])) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="template-actions">
                                <?php if (!$template['is_active']): ?>
                                <a href="?activate=<?= $template['id'] ?>" 
                                   class="btn-template btn-activate"
                                   onclick="return confirm('Aktifkan template <?= htmlspecialchars($template['name']) ?>?')">
                                    <i class="fas fa-check-circle"></i> Aktifkan
                                </a>
                                <?php endif; ?>
                                
                                <a href="?preview=<?= $template['id'] ?>" 
                                   class="btn-template btn-preview"
                                   target="_blank">
                                    <i class="fas fa-eye"></i> Preview
                                </a>
                                
                                <button class="btn-template btn-customize" 
                                        onclick="openCustomization(<?= $template['id'] ?>)">
                                    <i class="fas fa-sliders-h"></i> Kustomisasi
                                </button>
                                
                                <button class="btn-template btn-files" 
                                        onclick="viewFiles('<?= $template['folder_name'] ?>')">
                                    <i class="fas fa-file-code"></i> Files
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 30px;">
                    <h3><i class="fas fa-info-circle"></i> Informasi Template</h3>
                    <p><strong>Template Aktif:</strong> <?= $activeTemplate ? htmlspecialchars($activeTemplate['name']) : 'Tidak ada' ?></p>
                    <p><strong>Total Template:</strong> <?= count($templates) ?> template premium</p>
                    <p><strong>Catatan:</strong> Hanya satu template yang bisa aktif dalam satu waktu.</p>
                </div>
            </div>
            
            <!-- Tab Content: Customization -->
            <div class="tab-content" id="customize">
                <div class="customization-panel">
                    <h2><i class="fas fa-sliders-h"></i> Kustomisasi Template</h2>
                    <p>Tambahkan CSS kustom untuk memodifikasi tampilan template:</p>
                    
                    <?php if ($activeTemplate): 
                        $activeTemplateInfo = getTemplateInfo($activeTemplate['folder_name']);
                    ?>
                    <div style="background: #e9ecef; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        <p><strong>Template Aktif:</strong> <?= htmlspecialchars($activeTemplate['name']) ?></p>
                        <p><strong>Folder:</strong> templates/<?= $activeTemplate['folder_name'] ?>/</p>
                    </div>
                    
                    <form method="POST" id="customizeForm">
                        <input type="hidden" name="template_id" value="<?= $activeTemplate['id'] ?>">
                        <input type="hidden" name="customize" value="1">
                        
                        <div class="css-editor">
                            <div class="css-header">
                                <div class="css-title">
                                    <i class="fas fa-code"></i> CSS Kustom
                                    <small>(templates/<?= $activeTemplate['folder_name'] ?>/assets/css/custom.css)</small>
                                </div>
                                <div class="css-actions">
                                    <button type="button" class="btn-secondary" onclick="resetCustomCSS()">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                    <button type="button" class="btn-secondary" onclick="loadDefaultCSS()">
                                        <i class="fas fa-code"></i> Contoh CSS
                                    </button>
                                </div>
                            </div>
                            
                            <textarea name="custom_css" 
                                      class="css-content" 
                                      id="customCss" 
                                      placeholder="/* Tambahkan CSS kustom di sini */
/* Contoh: */
body {
    font-family: 'Arial', sans-serif;
}

.header {
    background: linear-gradient(to right, #8B4513, #D2691E);
}

.btn-primary {
    background-color: #8B4513;
    border-color: #8B4513;
}"><?= htmlspecialchars($activeTemplateInfo['custom_css_content'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Simpan CSS Kustom
                        </button>
                        
                        <a href="?preview=<?= $activeTemplate['id'] ?>" 
                           target="_blank" 
                           class="btn-secondary">
                            <i class="fas fa-eye"></i> Preview Perubahan
                        </a>
                    </form>
                    
                    <div class="css-help">
                        <h4><i class="fas fa-question-circle"></i> Bantuan CSS Kustom</h4>
                        <p>Gunakan CSS kustom untuk:</p>
                        <ul>
                            <li>Mengubah warna, font, atau ukuran</li>
                            <li>Menyesuaikan layout halaman</li>
                            <li>Menambahkan efek khusus (animasi, shadow, dll)</li>
                            <li>Memperbaiki tampilan di perangkat tertentu</li>
                        </ul>
                        
                        <div class="css-example">
                            /* Contoh perubahan warna utama */<br>
                            :root {<br>
                            &nbsp;&nbsp;--primary-color: #8B4513;<br>
                            &nbsp;&nbsp;--secondary-color: #DAA520;<br>
                            }<br><br>
                            
                            /* Contoh perubahan font */<br>
                            body {<br>
                            &nbsp;&nbsp;font-family: 'Poppins', 'Arial', sans-serif;<br>
                            }<br><br>
                            
                            /* Contoh tombol kustom */<br>
                            .btn-custom {<br>
                            &nbsp;&nbsp;background: linear-gradient(45deg, #8B4513, #D2691E);<br>
                            &nbsp;&nbsp;border: none;<br>
                            &nbsp;&nbsp;border-radius: 25px;<br>
                            &nbsp;&nbsp;padding: 12px 30px;<br>
                            }
                        </div>
                    </div>
                    <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-palette" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                        <h3>Tidak ada template aktif</h3>
                        <p>Aktifkan template terlebih dahulu untuk melakukan kustomisasi.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Tab Content: Colors & Fonts -->
            <div class="tab-content" id="colors">
                <div class="customization-panel">
                    <h2><i class="fas fa-fill-drip"></i> Warna & Font</h2>
                    <p>Atur skema warna dan font untuk template aktif:</p>
                    
                    <?php if ($activeTemplate): ?>
                    <form method="POST" id="colorsForm">
                        <input type="hidden" name="template_id" value="<?= $activeTemplate['id'] ?>">
                        <input type="hidden" name="update_colors" value="1">
                        
                        <!-- Color Scheme -->
                        <h3 style="margin-top: 30px;"><i class="fas fa-palette"></i> Skema Warna</h3>
                        <p>Pilih warna untuk template:</p>
                        
                        <div class="color-picker">
                            <div class="color-item">
                                <div class="color-box" style="background: #8B4513;" 
                                     onclick="selectColor('primary', '#8B4513')"></div>
                                <input type="text" 
                                       name="color_primary" 
                                       class="color-input" 
                                       value="#8B4513" 
                                       placeholder="#8B4513">
                                <span>Warna Utama</span>
                            </div>
                            
                            <div class="color-item">
                                <div class="color-box" style="background: #DAA520;" 
                                     onclick="selectColor('secondary', '#DAA520')"></div>
                                <input type="text" 
                                       name="color_secondary" 
                                       class="color-input" 
                                       value="#DAA520" 
                                       placeholder="#DAA520">
                                <span>Warna Sekunder</span>
                            </div>
                            
                            <div class="color-item">
                                <div class="color-box" style="background: #333333;" 
                                     onclick="selectColor('text', '#333333')"></div>
                                <input type="text" 
                                       name="color_text" 
                                       class="color-input" 
                                       value="#333333" 
                                       placeholder="#333333">
                                <span>Warna Teks</span>
                            </div>
                            
                            <div class="color-item">
                                <div class="color-box" style="background: #f9f5f0;" 
                                     onclick="selectColor('background', '#f9f5f0')"></div>
                                <input type="text" 
                                       name="color_background" 
                                       class="color-input" 
                                       value="#f9f5f0" 
                                       placeholder="#f9f5f0">
                                <span>Warna Latar</span>
                            </div>
                            
                            <div class="color-item">
                                <div class="color-box" style="background: #ffffff;" 
                                     onclick="selectColor('card', '#ffffff')"></div>
                                <input type="text" 
                                       name="color_card" 
                                       class="color-input" 
                                       value="#ffffff" 
                                       placeholder="#ffffff">
                                <span>Warna Kartu</span>
                            </div>
                        </div>
                        
                        <!-- Font Selection -->
                        <h3 style="margin-top: 40px;"><i class="fas fa-font"></i> Tipografi</h3>
                        <p>Pilih font untuk website:</p>
                        
                        <div style="margin: 20px 0;">
                            <div style="margin-bottom: 15px;">
                                <label>Font Utama:</label>
                                <select name="font_primary" class="form-control" style="width: 300px;">
                                    <option value="'Poppins', sans-serif">Poppins (Modern)</option>
                                    <option value="'Playfair Display', serif">Playfair Display (Elegant)</option>
                                    <option value="'Montserrat', sans-serif">Montserrat (Clean)</option>
                                    <option value="'Great Vibes', cursive">Great Vibes (Script)</option>
                                    <option value="'Arial', sans-serif">Arial (Standard)</option>
                                </select>
                                <small>Digunakan untuk judul dan teks utama</small>
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <label>Font Sekunder:</label>
                                <select name="font_secondary" class="form-control" style="width: 300px;">
                                    <option value="'Open Sans', sans-serif">Open Sans (Readable)</option>
                                    <option value="'Roboto', sans-serif">Roboto (Modern)</option>
                                    <option value="'Lato', sans-serif">Lato (Friendly)</option>
                                    <option value="'Raleway', sans-serif">Raleway (Elegant)</option>
                                    <option value="'Arial', sans-serif">Arial (Standard)</option>
                                </select>
                                <small>Digunakan untuk teks body dan paragraf</small>
                            </div>
                            
                            <div>
                                <label>Ukuran Font Dasar:</label>
                                <input type="range" 
                                       name="font_size" 
                                       min="12" 
                                       max="18" 
                                       value="16" 
                                       style="width: 300px;">
                                <span id="fontSizeValue">16px</span>
                                <small>Ukuran font dasar untuk seluruh website</small>
                            </div>
                        </div>
                        
                        <!-- Preview -->
                        <h3 style="margin-top: 40px;"><i class="fas fa-eye"></i> Preview</h3>
                        <p>Lihat preview perubahan sebelum menyimpan:</p>
                        
                        <div style="background: var(--preview-bg, #f9f5f0); 
                                    padding: 20px; 
                                    border-radius: 8px; 
                                    margin: 20px 0;
                                    border: 1px solid #ddd;">
                            <div style="color: var(--preview-text, #333333); 
                                        font-family: var(--preview-font, 'Poppins', sans-serif);">
                                <h2 style="color: var(--preview-primary, #8B4513); 
                                           margin-bottom: 10px;">
                                    Judul Contoh
                                </h2>
                                <p style="margin-bottom: 15px;">
                                    Ini adalah contoh teks paragraf dengan font yang dipilih.
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                                </p>
                                <button style="background: var(--preview-primary, #8B4513); 
                                              color: white; 
                                              border: none; 
                                              padding: 10px 20px; 
                                              border-radius: 5px;
                                              cursor: pointer;">
                                    Tombol Contoh
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-save"></i> Simpan Pengaturan Warna & Font
                        </button>
                        
                        <button type="button" class="btn-secondary" onclick="updatePreview()">
                            <i class="fas fa-sync"></i> Update Preview
                        </button>
                    </form>
                    <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-palette" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                        <h3>Tidak ada template aktif</h3>
                        <p>Aktifkan template terlebih dahulu untuk mengatur warna dan font.</p>
                    </div>
                    <?php endif; ?>
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
            document.querySelectorAll('.tab-link').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            this.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
            
            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
        });
    });
    
    // Open Customization
    function openCustomization(templateId) {
        // Switch to customize tab
        document.querySelector('.tab-link[data-tab="customize"]').click();
        
        // In a real implementation, you would load the specific template's CSS here
        // For now, just show a message
        showNotification('Memuat kustomisasi template...', 'info');
    }
    
    // View Files
    function viewFiles(folderName) {
        window.open(`template-files.php?folder=${encodeURIComponent(folderName)}`, '_blank');
    }
    
    // Reset Custom CSS
    function resetCustomCSS() {
        if (confirm('Reset CSS kustom ke keadaan awal?')) {
            document.getElementById('customCss').value = '';
            showNotification('CSS telah direset', 'success');
        }
    }
    
    // Load Default CSS Example
    function loadDefaultCSS() {
        const defaultCSS = `/* CSS Kustom untuk Template Pernikahan */

/* Warna Kustom */
:root {
    --primary-color: #8B4513;
    --secondary-color: #DAA520;
    --accent-color: #4B0082;
}

/* Font Kustom */
body {
    font-family: 'Poppins', sans-serif;
    line-height: 1.6;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
}

/* Header Kustom */
.header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 60px 0;
}

/* Tombol Kustom */
.btn-primary {
    background: var(--primary-color);
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Animasi Kustom */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.5s ease-out;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .header {
        padding: 40px 0;
    }
    
    h1 {
        font-size: 2em;
    }
}`;
        
        document.getElementById('customCss').value = defaultCSS;
        showNotification('Contoh CSS dimuat', 'success');
    }
    
    // Color Picker
    function selectColor(type, color) {
        const input = document.querySelector(`input[name="color_${type}"]`);
        const colorBox = event.target.closest('.color-item').querySelector('.color-box');
        
        // Open color picker
        const colorPicker = document.createElement('input');
        colorPicker.type = 'color';
        colorPicker.value = color;
        colorPicker.style.position = 'absolute';
        colorPicker.style.opacity = '0';
        colorPicker.style.width = '30px';
        colorPicker.style.height = '30px';
        colorPicker.style.cursor = 'pointer';
        
        colorBox.parentNode.appendChild(colorPicker);
        colorPicker.click();
        
        colorPicker.addEventListener('change', function() {
            const newColor = this.value;
            input.value = newColor;
            colorBox.style.background = newColor;
            this.remove();
            updatePreview();
        });
        
        colorPicker.addEventListener('blur', function() {
            this.remove();
        });
    }
    
    // Font Size Range
    const fontSizeInput = document.querySelector('input[name="font_size"]');
    const fontSizeValue = document.getElementById('fontSizeValue');
    
    if (fontSizeInput) {
        fontSizeInput.addEventListener('input', function() {
            fontSizeValue.textContent = this.value + 'px';
            updatePreview();
        });
    }
    
    // Update Preview
    function updatePreview() {
        const preview = document.querySelector('[style*="--preview-bg"]');
        if (!preview) return;
        
        // Get color values
        const primaryColor = document.querySelector('input[name="color_primary"]').value;
        const secondaryColor = document.querySelector('input[name="color_secondary"]').value;
        const textColor = document.querySelector('input[name="color_text"]').value;
        const bgColor = document.querySelector('input[name="color_background"]').value;
        const cardColor = document.querySelector('input[name="color_card"]').value;
        
        // Get font values
        const primaryFont = document.querySelector('select[name="font_primary"]').value;
        const fontSize = document.querySelector('input[name="font_size"]').value + 'px';
        
        // Update preview styles
        preview.style.setProperty('--preview-primary', primaryColor);
        preview.style.setProperty('--preview-secondary', secondaryColor);
        preview.style.setProperty('--preview-text', textColor);
        preview.style.setProperty('--preview-bg', bgColor);
        preview.style.setProperty('--preview-card', cardColor);
        preview.style.setProperty('--preview-font', primaryFont);
        preview.style.fontSize = fontSize;
    }
    
    // Show notification
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: ${type === 'info' ? '#17a2b8' : type === 'success' ? '#28a745' : '#dc3545'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;
        notification.innerHTML = `
            <i class="fas fa-${type === 'info' ? 'info-circle' : type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    // Initialize color picker for all color inputs
    document.querySelectorAll('.color-input').forEach(input => {
        input.addEventListener('input', function() {
            const colorBox = this.previousElementSibling;
            if (colorBox && colorBox.classList.contains('color-box')) {
                colorBox.style.background = this.value;
            }
            updatePreview();
        });
    });
    
    // Initialize font select change
    document.querySelectorAll('select[name^="font_"]').forEach(select => {
        select.addEventListener('change', updatePreview);
    });
    
    // Initialize preview on load
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(updatePreview, 100);
        
        // Check for tab in URL
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab && document.querySelector(`.tab-link[data-tab="${tab}"]`)) {
            document.querySelector(`.tab-link[data-tab="${tab}"]`).click();
        }
    });
    </script>
</body>
</html>