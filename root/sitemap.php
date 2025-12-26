<?php
/**
 * DYNAMIC SITEMAP GENERATOR
 * SEO Friendly untuk Wedding Website
 */

header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

// Base URL
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
            "://" . $_SERVER['HTTP_HOST'] . '/';

// Main pages
$pages = [
    '' => ['priority' => '1.0', 'changefreq' => 'daily'],
    'home' => ['priority' => '1.0', 'changefreq' => 'daily'],
    'couple' => ['priority' => '0.9', 'changefreq' => 'weekly'],
    'events' => ['priority' => '0.9', 'changefreq' => 'weekly'],
    'gallery' => ['priority' => '0.8', 'changefreq' => 'weekly'],
    'rsvp' => ['priority' => '0.7', 'changefreq' => 'monthly'],
    'gifts' => ['priority' => '0.7', 'changefreq' => 'monthly'],
    'messages' => ['priority' => '0.6', 'changefreq' => 'monthly']
];

// Generate URLs
foreach ($pages as $page => $data) {
    $url = $base_url . ($page ? $page : '');
    $lastmod = date('Y-m-d'); // Today's date
    
    echo "<url>";
    echo "<loc>" . htmlspecialchars($url) . "</loc>";
    echo "<lastmod>" . $lastmod . "</lastmod>";
    echo "<changefreq>" . $data['changefreq'] . "</changefreq>";
    echo "<priority>" . $data['priority'] . "</priority>";
    echo "</url>";
}

// If you have gallery images, add them here
try {
    require_once 'config/database.php';
    $db = new Database();
    
    $images = $db->fetchAll("SELECT id, filename FROM gallery WHERE is_active = 1 ORDER BY created_at DESC LIMIT 50");
    
    foreach ($images as $image) {
        $image_url = $base_url . 'gallery/image/' . $image['id'];
        echo "<url>";
        echo "<loc>" . htmlspecialchars($image_url) . "</loc>";
        echo "<lastmod>" . date('Y-m-d') . "</lastmod>";
        echo "<changefreq>monthly</changefreq>";
        echo "<priority>0.5</priority>";
        echo "</url>";
    }
    
    $db->close();
} catch (Exception $e) {
    // Continue without gallery images
}

echo '</urlset>';
?>
