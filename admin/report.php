<?php
include 'header_admin.php'; 

// Ambil pesanan yang sudah selesai
try {
    $stmt = $db->query("
        SELECT o.id, o.order_date, o.total_price, u.name AS customer_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.status = 'selesai' 
        ORDER BY o.order_date DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hitung total pendapatan
    $total_revenue = $db->query("SELECT SUM(total_price) FROM orders WHERE status = 'selesai'")->fetchColumn();

} catch (PDOException $e) {
    $error = "Terjadi kesalahan database: " . $e->getMessage();
    $orders = [];
    $total_revenue = 0;
}

?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Laporan Penjualan</h1>

<?php if (isset($error)): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow-md" role="alert">
        <p><?= $error ?></p>
    </div>
<?php endif; ?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center p-6 rounded-xl shadow-lg mb-8 bg-green-500 text-white">
    <h2 class="text-2xl font-semibold mb-2 md:mb-0">
        Total Pendapatan (Pesanan Selesai)
    </h2>
    <div class="text-4xl font-extrabold bg-white text-green-700 py-2 px-4 rounded-lg shadow-inner">
        Rp <?= number_format($total_revenue ?? 0, 0, ',', '.') ?>
    </div>
</div>

<div class="bg-white p-6 rounded-xl shadow-lg">
    
    <div class="flex justify-between items-center mb-4 border-b pb-4">
        <h2 class="text-xl font-bold text-gray-800">Daftar Transaksi Selesai</h2>
        <a href="export.php" class="flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 shadow-md">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Export Laporan (CSV)
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pelanggan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Selesai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada transaksi yang berstatus 'selesai'.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d M Y, H:i', strtotime($order['order_date'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer_admin.php'; ?>