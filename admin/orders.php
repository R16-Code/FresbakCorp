<?php
// LOGIKA PHP HARUS SEBELUM include header (sebelum output HTML)
include '../config.php';

// Periksa sesi admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit;
}

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Logika Filter Status
$status_filter = $_GET['status'] ?? 'all';

// Logika Update Status - HARUS SEBELUM HTML OUTPUT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    try {
        $stmt_update = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt_update->execute([$new_status, $order_id]);
        $_SESSION['message'] = "Status pesanan #$order_id berhasil diubah menjadi '" . ucfirst($new_status) . "'.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Gagal memperbarui status: " . $e->getMessage();
    }
    header("Location: orders.php?status=" . $status_filter);
    exit;
}

// Ambil data pesanan
$sql_params = [];
$sql_where = "1=1";
if ($status_filter !== 'all') {
    $sql_where = "o.status = ?";
    $sql_params[] = $status_filter;
}

$sql = "
    SELECT o.id, o.order_date, u.name AS customer_name, o.total_price, o.status, o.payment_method, o.customer_address 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE $sql_where
    ORDER BY o.order_date DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($sql_params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function untuk warna status
function getStatusClass($status) {
    return [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'diproses' => 'bg-blue-100 text-blue-800',
        'dikirim' => 'bg-indigo-100 text-indigo-800',
        'selesai' => 'bg-green-100 text-green-800',
        'dibatalkan' => 'bg-red-100 text-red-800',
    ][$status] ?? 'bg-gray-100 text-gray-800';
}

// SEKARANG INCLUDE HTML HEADER (SETELAH SEMUA LOGIKA & REDIRECT)
include 'header_admin.php';
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Manajemen Pesanan</h1>

<?php if ($message): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-md" role="alert">
        <p><?= $message ?></p>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-xl shadow-lg">
    
    <div class="mb-4 flex space-x-3 border-b pb-3">
        <?php 
        $statuses = ['all', 'pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
        foreach ($statuses as $status):
        ?>
            <a href="?status=<?= $status ?>" class="px-4 py-2 text-sm font-semibold rounded-full transition duration-150 
                <?= $status_filter == $status ? 'bg-green-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <?= $status == 'all' ? 'Semua' : ucfirst($status) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada pesanan dengan status '<?= $status_filter == 'all' ? 'Apapun' : ucfirst($status_filter) ?>'.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?= $order['id'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= getStatusClass($order['status']) ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <details class="relative">
                                    <summary class="text-blue-600 hover:text-blue-800 cursor-pointer">
                                        Detail & Update
                                    </summary>
                                    <div class="absolute right-0 mt-2 w-72 bg-gray-50 border border-gray-200 rounded-lg shadow-xl p-4 z-10">
                                        <h4 class="font-bold mb-2">Order #<?= $order['id'] ?></h4>
                                        <p class="text-xs text-gray-600 mb-3">Metode: <?= $order['payment_method'] ?></p>
                                        <p class="text-xs text-gray-600 mb-3">Alamat: <?= htmlspecialchars($order['customer_address']) ?></p>

                                        <form action="orders.php?status=<?= $status_filter ?>" method="POST" class="space-y-2">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <label for="status_<?= $order['id'] ?>" class="block text-xs font-medium text-gray-700">Ubah Status:</label>
                                            <select name="new_status" id="status_<?= $order['id'] ?>" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                                                <option value="diproses" <?= $order['status'] == 'diproses' ? 'selected' : '' ?>>Diproses (Verifikasi)</option>
                                                <option value="dikirim" <?= $order['status'] == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                                <option value="selesai" <?= $order['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                                <option value="dibatalkan" <?= $order['status'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                            </select>
                                            <button type="submit" name="update_status" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 rounded-lg transition duration-150">
                                                Update
                                            </button>
                                        </form>
                                        
                                        </div>
                                </details>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer_admin.php'; ?>