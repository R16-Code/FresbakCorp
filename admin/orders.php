<?php
// LOGIKA PHP HARUS SEBELUM include header
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

// Logika Update Status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    try {
        $stmt_update = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt_update->execute([$new_status, $order_id]);
        $_SESSION['message'] = "Status pesanan berhasil diperbarui menjadi '" . ucfirst($new_status) . "'.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Gagal memperbarui status: " . $e->getMessage();
    }
    header("Location: orders.php?status=" . $status_filter);
    exit;
}

// Ambil data pesanan (Tanpa menampilkan ID di tabel nanti)
$sql_params = [];
$sql_where = "1=1";
if ($status_filter !== 'all') {
    $sql_where = "o.status = ?";
    $sql_params[] = $status_filter;
}

$sql = "
    SELECT o.id, o.order_date, u.name AS customer_name, o.total_price, o.status, 
           o.payment_method, o.customer_address, o.payment_proof
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE $sql_where
    ORDER BY o.order_date DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($sql_params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusClass($status) {
    return [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'diproses' => 'bg-blue-100 text-blue-800',
        'dikirim' => 'bg-indigo-100 text-indigo-800',
        'selesai' => 'bg-green-100 text-green-800',
        'dibatalkan' => 'bg-red-100 text-red-800',
    ][$status] ?? 'bg-gray-100 text-gray-800';
}

include 'header_admin.php';
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Manajemen Pesanan</h1>

<?php if ($message): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-md flex justify-between items-center">
        <p><?= $message ?></p>
        <button onclick="this.parentElement.remove()" class="text-green-700 font-bold">&times;</button>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-xl shadow-lg">
    
    <div class="mb-4 flex space-x-3 border-b pb-3 overflow-x-auto">
        <?php 
        $statuses = ['all', 'pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
        foreach ($statuses as $status):
        ?>
            <a href="?status=<?= $status ?>" class="px-4 py-2 text-sm font-semibold rounded-full transition duration-150 whitespace-nowrap
                <?= $status_filter == $status ? 'bg-green-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <?= $status == 'all' ? 'Semua' : ucfirst($status) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
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
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                            Tidak ada pesanan dengan status '<?= $status_filter == 'all' ? 'Apapun' : ucfirst($status_filter) ?>'.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('d M Y', strtotime($order['order_date'])) ?>
                                <br><span class="text-xs text-gray-400"><?= date('H:i', strtotime($order['order_date'])) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= getStatusClass($order['status']) ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="openModal('modal-<?= $order['id'] ?>')" class="text-blue-600 hover:text-blue-900 font-medium flex items-center bg-blue-50 px-3 py-1 rounded hover:bg-blue-100 transition">
                                    Detail & Update
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>

                                <div id="modal-<?= $order['id'] ?>" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                        
                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal('modal-<?= $order['id'] ?>')"></div>

                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                            
                                            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                    Detail Pesanan
                                                </h3>
                                                <button type="button" onclick="closeModal('modal-<?= $order['id'] ?>')" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                                    <span class="sr-only">Close</span>
                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                                                <div class="space-y-4">
                                                    
                                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                                        <div>
                                                            <p class="text-gray-500">Tanggal:</p>
                                                            <p class="font-medium text-gray-900"><?= date('d F Y, H:i', strtotime($order['order_date'])) ?></p>
                                                        </div>
                                                        <div>
                                                            <p class="text-gray-500">Metode Bayar:</p>
                                                            <p class="font-medium text-gray-900"><?= htmlspecialchars($order['payment_method']) ?></p>
                                                        </div>
                                                    </div>

                                                    <div class="bg-gray-50 p-3 rounded text-sm">
                                                        <p class="text-gray-500 mb-1">Alamat Pengiriman:</p>
                                                        <p class="text-gray-800 font-medium"><?= nl2br(htmlspecialchars($order['customer_address'])) ?></p>
                                                    </div>

                                                    <div>
                                                        <p class="text-sm font-medium text-gray-700 mb-2">Bukti Pembayaran:</p>
                                                        <?php if (!empty($order['payment_proof'])): ?>
                                                            <?php $proof_path = "../uploads/proofs/" . htmlspecialchars($order['payment_proof']); ?>
                                                            <a href="<?= $proof_path ?>" target="_blank" class="block relative group border rounded-lg overflow-hidden">
                                                                <img src="<?= $proof_path ?>" alt="Bukti Transfer" class="w-full h-48 object-cover hover:opacity-90 transition">
                                                                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 opacity-0 group-hover:opacity-100 transition">
                                                                    <span class="text-white text-xs font-bold bg-black bg-opacity-60 px-2 py-1 rounded">Klik untuk Memperbesar</span>
                                                                </div>
                                                            </a>
                                                        <?php else: ?>
                                                            <div class="text-center py-4 bg-gray-50 border border-dashed border-gray-300 rounded-lg text-gray-400 text-sm">
                                                                Belum ada bukti yang diupload.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="pt-4 border-t mt-4">
                                                        <form action="orders.php?status=<?= $status_filter ?>" method="POST">
                                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                            <label for="status_<?= $order['id'] ?>" class="block text-sm font-medium text-gray-700 mb-2">Update Status Pesanan:</label>
                                                            <div class="flex space-x-2">
                                                                <select name="new_status" id="status_<?= $order['id'] ?>" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm p-2 border">
                                                                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                                                                    <option value="diproses" <?= $order['status'] == 'diproses' ? 'selected' : '' ?>>Diproses (Verifikasi)</option>
                                                                    <option value="dikirim" <?= $order['status'] == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                                                    <option value="selesai" <?= $order['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                                                    <option value="dibatalkan" <?= $order['status'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                                                </select>
                                                                <button type="submit" name="update_status" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-150">
                                                                    Simpan
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>

                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }
</script>

<?php include 'footer_admin.php'; ?>