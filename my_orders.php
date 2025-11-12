<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

try {
    $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

include 'header.php';
?>

<h1 class="text-3xl font-bold text-gray-800 mb-8 border-b pb-2">Riwayat Pesanan Saya</h1>

<?php if ($message): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-md" role="alert">
        <p><?= $message ?></p>
    </div>
<?php endif; ?>

<?php if (isset($_GET['upload_success'])): ?>
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 rounded-lg shadow-md" role="alert">
        <p class="font-bold">Bukti Pembayaran Terkirim!</p>
        <p>Pesanan Anda telah kami terima dan sedang menunggu verifikasi dari admin.</p>
    </div>
<?php endif; ?>

<div class="space-y-6">
    <?php if (empty($orders)): ?>
        <div class="bg-white p-6 rounded-xl shadow-md text-center">
            <p class="text-lg text-gray-600">Anda belum memiliki riwayat pesanan.</p>
            <a href="products.php" class="text-green-600 font-semibold hover:underline mt-2 inline-block">Mulai Belanja &rarr;</a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): 
            
            $status_class = [
                'pending' => 'bg-yellow-100 text-yellow-800',
                'diproses' => 'bg-blue-100 text-blue-800',
                'dikirim' => 'bg-indigo-100 text-indigo-800',
                'selesai' => 'bg-green-100 text-green-800',
                'dibatalkan' => 'bg-red-100 text-red-800',
            ];

            $status_label = [
                'pending' => 'Menunggu Pembayaran',
                'diproses' => 'Menunggu Verifikasi Admin',
                'dikirim' => 'Sedang Dikirim',
                'selesai' => 'Selesai',
                'dibatalkan' => 'Dibatalkan',
            ];

            $current_status = strtolower($order['status']);
        ?>
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
                <div class="flex justify-between items-start mb-3 border-b pb-3">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Tanggal Pesan: <?= date('d M Y, H:i', strtotime($order['order_date'])) ?></span>
                    </div>
                    
                    <span class="px-3 py-1 text-sm font-semibold rounded-full <?= $status_class[$current_status] ?? 'bg-gray-100 text-gray-800' ?>">
                        <?= $status_label[$current_status] ?? ucfirst($order['status']) ?>
                    </span>
                </div>

                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-md font-medium text-gray-700">Total Pembayaran:</p>
                        <p class="text-2xl font-extrabold text-green-600">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></p>
                        <p class="text-sm text-gray-500 mt-1">Metode: <?= htmlspecialchars($order['payment_method']) ?></p>
                    </div>

                    <div class="text-right space-y-2">
                        <?php if ($current_status == 'pending'): ?>
                            <a href="upload_proof.php?order_id=<?= $order['id'] ?>" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg text-sm transition duration-300 shadow-md">
                                Upload Bukti Bayar
                            </a>
                        <?php elseif ($current_status == 'dikirim'): ?>
                            <a href="process_order.php?action=confirm_received&order_id=<?= $order['id'] ?>" class="text-green-600 hover:text-green-700 text-sm font-medium block">
                                Konfirmasi Diterima
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>