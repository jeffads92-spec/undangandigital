<?php
/**
 * QRIS GENERATOR GRATIS
 * Menggunakan QR code static dengan nomor rekening
 */

require_once '../../config/autoload.php';

class QRISFree {
    public static function generate($amount, $customer = '') {
        // Generate transaction ID
        $transactionId = 'QRIS-' . time() . '-' . rand(1000, 9999);
        
        // Get bank info from settings
        $db = new Database();
        $bank = [
            'name' => 'BCA',
            'account' => '1234567890',
            'holder' => 'NAMA PEMILIK REK'
        ];
        
        // Generate QRIS data (simplified)
        $qrisData = [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'customer' => $customer,
            'bank_name' => $bank['name'],
            'bank_account' => $bank['account'],
            'account_name' => $bank['holder'],
            'qr_content' => "bank:{$bank['name']};acc:{$bank['account']};amount:{$amount}",
            'qr_image' => self::generateQRImage("bank:{$bank['name']};acc:{$bank['account']};amount:{$amount}"),
            'instructions' => self::getInstructions()
        ];
        
        // Save to database
        $db->insert('payments', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'customer_name' => $customer,
            'method' => 'qris',
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $qrisData;
    }
    
    private static function generateQRImage($data) {
        // Use free QR code generator
        $size = '300x300';
        $encoded = urlencode($data);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}&data={$encoded}";
    }
    
    private static function getInstructions() {
        return [
            '1. Buka aplikasi bank/e-wallet Anda',
            '2. Pilih menu "Scan QRIS" atau "Bayar dengan QR"',
            '3. Arahkan kamera ke kode QR di atas',
            '4. Konfirmasi jumlah pembayaran',
            '5. Selesaikan transaksi'
        ];
    }
}

// API Endpoint
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    
    $data = json_decode(file_get_contents('php://input'), true);
    $amount = floatval($data['amount'] ?? 50000);
    $customer = $data['customer'] ?? '';
    
    $result = QRISFree::generate($amount, $
