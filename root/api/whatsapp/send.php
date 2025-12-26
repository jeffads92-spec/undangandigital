<?php
/**
 * WHATSAPP API GRATIS
 * Menggunakan click-to-chat tanpa API berbayar
 */

require_once '../../config/autoload.php';

class WhatsAppFree {
    public static function generateLink($phone, $message = '') {
        // Format: 081234567890 -> 6281234567890
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if(substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        $encodedMessage = urlencode($message);
        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }
    
    public static function sendInvitation($guest) {
        $message = "Halo {$guest['name']},\n\n"
                 . "Anda diundang ke pernikahan:\n"
                 . "{$guest['event_name']}\n"
                 . "Tanggal: {$guest['event_date']}\n"
                 . "Lokasi: {$guest['location']}\n\n"
                 . "Konfirmasi kehadiran: {$guest['rsvp_link']}\n\n"
                 . "Terima kasih!";
        
        return self::generateLink($guest['phone'], $message);
    }
}

// API Endpoint
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    
    switch($action) {
        case 'generate_link':
            $link = WhatsAppFree::generateLink(
                $data['phone'],
                $data['message'] ?? ''
            );
            echo json_encode(['success' => true, 'link' => $link]);
            break;
            
        case 'send_invitation':
            $link = WhatsAppFree::sendInvitation($data['guest']);
            echo json_encode(['success' => true, 'link' => $link]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
    }
}
?>
