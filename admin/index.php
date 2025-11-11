<?php
include 'header_admin.php'; 
// Kode PHP Dashboard (Ambil statistik, dll.)
// ... (Kode sama seperti yang terakhir diberikan untuk dashboard.php, hanya di-include dengan benar)

// Ambil 5 pesanan terbaru yang statusnya 'pending' atau 'diproses'
$stmt_pending = $db->query("
    SELECT id, total_price, order_date, status 
    FROM orders 
    WHERE status IN ('pending', 'diproses') 
    ORDER BY order_date ASC LIMIT 5
");
$pending_orders = $stmt_pending->fetchAll(PDO::FETCH_ASSOC);

// ... (lanjutkan sisa kode dashboard.php)

// Ambil statistik (di asumsikan sudah di atas)
$total_orders = $db->query("SELECT COUNT(*) AS total FROM orders WHERE status = 'selesai'")->fetchColumn();
$total_revenue = $db->query("SELECT SUM(total_price) AS total FROM orders WHERE status = 'selesai'")->fetchColumn() ?? 0;
$total_products = $db->query("SELECT COUNT(*) AS total FROM products")->fetchColumn();
$total_users = $db->query("SELECT COUNT(*) AS total FROM users")->fetchColumn() ?? 0;

?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard Admin</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
        <div class="flex flex-col items-start">
            <div class="p-3 rounded-full bg-green-100 text-green-500 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 6v2m0 6a9 9 0 110-18 9 9 0 010 18z"></path></svg>
            </div>
            <div class="w-full">
                <p class="text-sm font-medium text-gray-500 mb-1">Pendapatan Total</p>
                <p class="text-lg lg:text-xl font-bold text-gray-900 break-words">Rp <?= number_format($total_revenue, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Pesanan Selesai</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($total_orders, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-yellow-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Jumlah Produk</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($total_products, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-indigo-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-indigo-100 text-indigo-500 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c4.418 0 8 1.79 8 4v2H4v-2c0-2.21 3.582-4 8-4z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Pengguna</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($total_users, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg">
        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Pesanan yang menunggu verifikasi</h2>
        
        <?php 
        if (empty($pending_orders)):
        ?>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-gray-600">Semua pesanan sudah diverifikasi!</p>
            </div>
        <?php else: ?>
            <ul class="divide-y divide-gray-100">
                <?php foreach ($pending_orders as $order): ?>
                    <li class="py-3 flex justify-between items-center">
                        <div>
                            <p class="font-medium text-gray-700">#<?= $order['id'] ?> - Rp <?= number_format($order['total_price'], 0, ',', '.') ?></p>
                            <span class="text-sm text-gray-500"><?= date('d M Y H:i', strtotime($order['order_date'])) ?></span>
                        </div>
                        <a href="orders.php?id=<?= $order['id'] ?>" class="text-sm text-green-600 hover:text-green-700 font-medium py-1 px-3 rounded-full border border-green-200 hover:bg-green-50 transition duration-150">
                            Lihat & Proses
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <div class="mt-4 text-center">
            <a href="orders.php" class="text-sm font-semibold text-gray-600 hover:text-green-600">Lihat Semua Pesanan &rarr;</a>
        </div>
    </div>
    
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Aksi Cepat</h2>
            <div class="space-y-3">
                <a href="product_form.php" class="w-full flex items-center justify-center bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg font-semibold shadow-md transition duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Tambah Produk Baru
                </a>
                <a href="export.php" class="w-full flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-lg font-semibold shadow-md transition duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Laporan (CSV)
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer_admin.php'; ?>