<?php
// Config harus diletakkan di atas sebelum output apapun
include '../config.php';

// Cek jika admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die("Akses ditolak.");
}

// 1. Set Headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="laporan_penjualan_fresbak_'.date('Y-m-d').'.csv"');

// 2. Buka output stream
$output = fopen('php://output', 'w');

// 3. Tulis header CSV
fputcsv($output, [
    'Order ID', 
    'Nama Pelanggan', 
    'Alamat', 
    'Kontak', 
    'Tanggal Pesan', 
    'Metode Bayar', 
    'Status', 
    'Total (Rp)'
]);

// 4. Ambil data dari DB
$stmt = $db->query("SELECT id, customer_name, customer_address, customer_contact, order_date, payment_method, status, total_price 
                   FROM orders 
                   WHERE status = 'selesai' 
                   ORDER BY order_date DESC");

// 5. Tulis data baris per baris
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Format data jika perlu (misal, hapus koma dari alamat)
    $row['customer_address'] = str_replace(["\r", "\n"], ' ', $row['customer_address']);
    fputcsv($output, $row);
}

// 6. Tutup stream
fclose($output);
exit;
?>