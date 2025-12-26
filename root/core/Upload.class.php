<?php
/**
 * UPLOAD CLASS
 * Handle file uploads untuk gambar, musik, dan dokumen
 */

class Upload {
    private $db;
    private $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $allowedAudioTypes = ['mp3', 'ogg', 'm4a', 'wav'];
    private $allowedDocumentTypes = ['pdf', 'doc', 'docx'];
    private $maxImageSize = 5242880; // 5MB
    private $maxAudioSize = 10485760; // 10MB
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Upload image
     */
    public function uploadImage($file, $options = []) {
        // Default options
        $defaults = [
            'folder' => 'uploads/images/',
            'rename' => true,
            'resize' => false,
            'max_width' => 1920,
            'max_height' => 1080,
            'thumbnail' => false,
            'thumbnail_width' => 300,
            'thumbnail_height' => 300,
            'watermark' => false,
            'watermark_text' => SITE_NAME
        ];
        
        $options = array_merge($defaults, $options);
        
        // Validate file
        $validation = $this->validateFile($file, 'image');
        if (!$validation['success']) {
            return $validation;
        }
        
        // Create folder if not exists
        $uploadPath = ASSETS_PATH . $options['folder'];
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Generate filename
        $filename = $this->generateFilename($file['name'], $options['rename']);
        $filepath = $uploadPath . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'message' => 'Gagal mengupload file'];
        }
        
        // Process image if needed
        if ($options['resize']) {
            $this->resizeImage($filepath, $options['max_width'], $options['max_height']);
        }
        
        // Create thumbnail if needed
        $thumbnail = null;
        if ($options['thumbnail']) {
            $thumbnail = $this->createThumbnail(
                $filepath, 
                $options['thumbnail_width'], 
                $options['thumbnail_height']
            );
        }
        
        // Add watermark if needed
        if ($options['watermark']) {
            $this->addWatermark($filepath, $options['watermark_text']);
        }
        
        // Save to database if requested
        $fileData = [
            'filename' => $filename,
            'original_name' => $file['name'],
            'filepath' => $options['folder'] . $filename,
            'filesize' => $file['size'],
            'filetype' => $file['type'],
            'thumbnail' => $thumbnail,
            'uploaded_at' => date('Y-m-d H:i:s')
        ];
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'url' => BASE_URL . 'assets/' . $options['folder'] . $filename,
            'data' => $fileData
        ];
    }
    
    /**
     * Upload music file
     */
    public function uploadMusic($file, $metadata = []) {
        // Validate file
        $validation = $this->validateFile($file, 'audio');
        if (!$validation['success']) {
            return $validation;
        }
        
        // Create music folder
        $uploadPath = MUSIC_PATH;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Generate filename
        $filename = $this->generateMusicFilename($file['name'], $metadata);
        $filepath = $uploadPath . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'message' => 'Gagal mengupload file musik'];
        }
        
        // Get audio duration
        $duration = $this->getAudioDuration($filepath);
        
        // Save to database
        $musicId = $this->db->insert('wedding_songs', [
            'title' => $metadata['title'] ?? pathinfo($file['name'], PATHINFO_FILENAME),
            'artist' => $metadata['artist'] ?? 'Unknown',
            'filename' => $filename,
            'duration' => $duration,
            'filesize' => $file['size'],
            'filetype' => $file['type'],
            'is_active' => 1,
            'uploaded_at' => date('Y-m-d H:i:s')
        ]);
        
        return [
            'success' => true,
            'id' => $musicId,
            'filename' => $filename,
            'filepath' => $filepath,
            'url' => BASE_URL . 'assets/music/' . $filename,
            'duration' => $duration,
            'metadata' => $metadata
        ];
    }
    
    /**
     * Upload multiple files
     */
    public function uploadMultiple($files, $type = 'image', $options = []) {
        $results = [];
        
        foreach ($files['name'] as $key => $name) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $name,
                    'type' => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key],
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                ];
                
                if ($type === 'image') {
                    $result = $this->uploadImage($file, $options);
                } elseif ($type === 'audio') {
                    $result = $this->uploadMusic($file, $options);
                } else {
                    $result = ['success' => false, 'message' => 'Tipe file tidak didukung'];
                }
                
                $results[] = $result;
            }
        }
        
        return $results;
    }
    
    /**
     * Validate file
     */
    private function validateFile($file, $type = 'image') {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi batas server)',
                UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi batas form)',
                UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
                UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP'
            ];
            
            $message = $errorMessages[$file['error']] ?? 'Error upload tidak diketahui';
            return ['success' => false, 'message' => $message];
        }
        
        // Check file size
        $maxSize = ($type === 'image') ? $this->maxImageSize : $this->maxAudioSize;
        if ($file['size'] > $maxSize) {
            $maxSizeMB = round($maxSize / 1048576, 1);
            return ['success' => false, 'message' => "File terlalu besar. Maksimal {$maxSizeMB}MB"];
        }
        
        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($type === 'image' && !in_array($extension, $this->allowedImageTypes)) {
            $allowed = implode(', ', $this->allowedImageTypes);
            return ['success' => false, 'message' => "Tipe file tidak diizinkan. Hanya: {$allowed}"];
        }
        
        if ($type === 'audio' && !in_array($extension, $this->allowedAudioTypes)) {
            $allowed = implode(', ', $this->allowedAudioTypes);
            return ['success' => false, 'message' => "Tipe file tidak diizinkan. Hanya: {$allowed}"];
        }
        
        // Additional security checks for images
        if ($type === 'image') {
            $imageInfo = @getimagesize($file['tmp_name']);
            if (!$imageInfo) {
                return ['success' => false, 'message' => 'File bukan gambar yang valid'];
            }
            
            // Check MIME type
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($imageInfo['mime'], $allowedMimes)) {
                return ['success' => false, 'message' => 'Tipe MIME tidak diizinkan'];
            }
        }
        
        return ['success' => true];
    }
    
    /**
     * Generate filename
     */
    private function generateFilename($originalName, $rename = true) {
        if (!$rename) {
            // Sanitize original name
            $name = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $originalName);
            return time() . '_' . $name;
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        
        return "{$timestamp}_{$random}.{$extension}";
    }
    
    /**
     * Generate music filename
     */
    private function generateMusicFilename($originalName, $metadata) {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        if (isset($metadata['title']) && isset($metadata['artist'])) {
            $title = preg_replace('/[^a-zA-Z0-9]/', '_', $metadata['title']);
            $artist = preg_replace('/[^a-zA-Z0-9]/', '_', $metadata['artist']);
            $filename = "{$artist}_{$title}.{$extension}";
        } else {
            $filename = $this->generateFilename($originalName, true);
        }
        
        return strtolower($filename);
    }
    
    /**
     * Resize image
     */
    private function resizeImage($filepath, $maxWidth, $maxHeight) {
        list($width, $height, $type) = getimagesize($filepath);
        
        // Check if resizing is needed
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return true;
        }
        
        // Calculate new dimensions
        $ratio = $width / $height;
        
        if ($maxWidth / $maxHeight > $ratio) {
            $newWidth = $maxHeight * $ratio;
            $newHeight = $maxHeight;
        } else {
            $newWidth = $maxWidth;
            $newHeight = $maxWidth / $ratio;
        }
        
        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Load original image based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $originalImage = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $originalImage = imagecreatefrompng($filepath);
                // Preserve transparency
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
                imagefill($newImage, 0, 0, $transparent);
                break;
            case IMAGETYPE_GIF:
                $originalImage = imagecreatefromgif($filepath);
                // Preserve transparency
                $transparentIndex = imagecolortransparent($originalImage);
                if ($transparentIndex >= 0) {
                    $transparentColor = imagecolorsforindex($originalImage, $transparentIndex);
                    $transparentIndex = imagecolorallocate($newImage, 
                        $transparentColor['red'], 
                        $transparentColor['green'], 
                        $transparentColor['blue']);
                    imagefill($newImage, 0, 0, $transparentIndex);
                    imagecolortransparent($newImage, $transparentIndex);
                }
                break;
            case IMAGETYPE_WEBP:
                $originalImage = imagecreatefromwebp($filepath);
                break;
            default:
                return false;
        }
        
        // Resize image
        imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, 
            $newWidth, $newHeight, $width, $height);
        
        // Save resized image
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $filepath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($newImage, $filepath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $filepath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($newImage, $filepath, 85);
                break;
        }
        
        // Clean up
        imagedestroy($originalImage);
        imagedestroy($newImage);
        
        return true;
    }
    
    /**
     * Create thumbnail
     */
    private function createThumbnail($sourcePath, $thumbWidth, $thumbHeight) {
        list($width, $height, $type) = getimagesize($sourcePath);
        
        // Calculate thumbnail dimensions
        $sourceRatio = $width / $height;
        $thumbRatio = $thumbWidth / $thumbHeight;
        
        if ($thumbRatio > $sourceRatio) {
            $newWidth = $thumbWidth;
            $newHeight = $thumbWidth / $sourceRatio;
        } else {
            $newHeight = $thumbHeight;
            $newWidth = $thumbHeight * $sourceRatio;
        }
        
        // Create thumbnail image
        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // Load source image
        switch ($type) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                imagealphablending($thumbImage, false);
                imagesavealpha($thumbImage, true);
                $transparent = imagecolorallocatealpha($thumbImage, 0, 0, 0, 127);
                imagefill($thumbImage, 0, 0, $transparent);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                $transparentIndex = imagecolortransparent($sourceImage);
                if ($transparentIndex >= 0) {
                    $transparentColor = imagecolorsforindex($sourceImage, $transparentIndex);
                    $transparentIndex = imagecolorallocate($thumbImage, 
                        $transparentColor['red'], 
                        $transparentColor['green'], 
                        $transparentColor['blue']);
                    imagefill($thumbImage, 0, 0, $transparentIndex);
                    imagecolortransparent($thumbImage, $transparentIndex);
                }
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return null;
        }
        
        // Calculate crop position
        $srcX = ($newWidth - $thumbWidth) / 2;
        $srcY = ($newHeight - $thumbHeight) / 2;
        
        // Create thumbnail
        imagecopyresampled($thumbImage, $sourceImage, 0, 0, $srcX, $srcY, 
            $thumbWidth, $thumbHeight, $newWidth, $newHeight);
        
        // Generate thumbnail filename
        $pathinfo = pathinfo($sourcePath);
        $thumbFilename = $pathinfo['filename'] . '_thumb.' . $pathinfo['extension'];
        $thumbPath = $pathinfo['dirname'] . '/' . $thumbFilename;
        
        // Save thumbnail
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumbImage, $thumbPath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbImage, $thumbPath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumbImage, $thumbPath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($thumbImage, $thumbPath, 85);
                break;
        }
        
        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($thumbImage);
        
        return $thumbFilename;
    }
    
    /**
     * Add watermark to image
     */
    private function addWatermark($imagePath, $text) {
        list($width, $height, $type) = getimagesize($imagePath);
        
        // Load image
        switch ($type) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($imagePath);
                break;
            case IMAGETYPE_WEBP:
                $image = imagecreatefromwebp($imagePath);
                break;
            default:
                return false;
        }
        
        // Set watermark properties
        $fontSize = 20;
        $fontFile = ASSETS_PATH . 'fonts/arial.ttf'; // Pastikan font tersedia
        $textColor = imagecolorallocatealpha($image, 255, 255, 255, 60);
        $shadowColor = imagecolorallocatealpha($image, 0, 0, 0, 60);
        
        // Calculate text position (bottom right)
        $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth = $bbox[2] - $bbox[0];
        $textHeight = $bbox[7] - $bbox[1];
        
        $x = $width - $textWidth - 20;
        $y = $height - 20;
        
        // Add shadow
        imagettftext($image, $fontSize, 0, $x + 2, $y + 2, $shadowColor, $fontFile, $text);
        
        // Add text
        imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontFile, $text);
        
        // Save watermarked image
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($image, $imagePath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($image, $imagePath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($image, $imagePath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($image, $imagePath, 85);
                break;
        }
        
        imagedestroy($image);
        return true;
    }
    
    /**
     * Get audio duration
     */
    private function getAudioDuration($filepath) {
        // Try using getID3 library if available
        if (function_exists('getid3_analyze')) {
            $getID3 = new getID3;
            $fileInfo = $getID3->analyze($filepath);
            
            if (isset($fileInfo['playtime_seconds'])) {
                return (int) $fileInfo['playtime_seconds'];
            }
        }
        
        // Fallback: use file extension to estimate
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        
        // Default durations based on file type
        $defaultDurations = [
            'mp3' => 180, // 3 minutes
            'ogg' => 180,
            'm4a' => 180,
            'wav' => 180
        ];
        
        return $defaultDurations[$extension] ?? 180;
    }
    
    /**
     * Delete file
     */
    public function deleteFile($filename, $folder = '') {
        $filepath = ASSETS_PATH . $folder . $filename;
        
        if (file_exists($filepath)) {
            if (unlink($filepath)) {
                // Also delete thumbnail if exists
                $pathinfo = pathinfo($filepath);
                $thumbPath = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '_thumb.' . $pathinfo['extension'];
                
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
                
                return ['success' => true, 'message' => 'File berhasil dihapus'];
            }
        }
        
        return ['success' => false, 'message' => 'File tidak ditemukan'];
    }
    
    /**
     * Get file info
     */
    public function getFileInfo($filename, $folder = '') {
        $filepath = ASSETS_PATH . $folder . $filename;
        
        if (!file_exists($filepath)) {
            return null;
        }
        
        $pathinfo = pathinfo($filepath);
        $filesize = filesize($filepath);
        
        return [
            'filename' => $filename,
            'basename' => $pathinfo['basename'],
            'extension' => $pathinfo['extension'],
            'filesize' => $this->formatFilesize($filesize),
            'filesize_bytes' => $filesize,
            'filetype' => mime_content_type($filepath),
            'modified' => date('Y-m-d H:i:s', filemtime($filepath)),
            'url' => BASE_URL . 'assets/' . $folder . $filename
        ];
    }
    
    /**
     * Format filesize
     */
    private function formatFilesize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
?>
