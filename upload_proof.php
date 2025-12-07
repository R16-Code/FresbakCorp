<?php
include 'config.php';

// Harus login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';

if (!isset($_GET['order_id'])) {
    header("Location: my_orders.php");
    exit;
}

$order_id = $_GET['order_id'];

// Cek kepemilikan order
$stmt_check = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
$stmt_check->execute([$order_id, $user_id]);
$order = $stmt_check->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    // Jika order tidak ditemukan (mungkin sudah dibayar atau bukan milik user)
    $_SESSION['error'] = "Pesanan tidak ditemukan atau sudah diproses.";
    header("Location: my_orders.php");
    exit;
}

// Logika Upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["payment_proof"])) {
    $file = $_FILES["payment_proof"];
    
    // Validasi file (sama seperti sebelumnya)
    if ($file["error"] !== UPLOAD_ERR_OK) {
        $error = "Terjadi error saat upload.";
    } else {
        $target_dir = "uploads/proofs/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $allowed_ext = ["jpg", "jpeg", "png", "gif"];
        
        if (!in_array($file_extension, $allowed_ext)) {
            $error = "Hanya file JPG, JPEG, PNG, & GIF yang diperbolehkan.";
        } elseif ($file["size"] > 5000000) { // 5MB
            $error = "Ukuran file terlalu besar (maks 5MB).";
        } else {
            // Buat nama unik
            $new_filename = "proof_" . $order_id . "_" . uniqid() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                // Update database
                $sql_update = "UPDATE orders SET payment_proof = ?, status = 'diproses' WHERE id = ?";
                $stmt_update = $db->prepare($sql_update);
                $stmt_update->execute([$new_filename, $order_id]);
                
                header("Location: my_orders.php?upload_success=true");
                exit;
            } else {
                $error = "Gagal memindahkan file.";
            }
        }
    }
}

include 'header.php';
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-4">Konfirmasi Pembayaran</h1>
    <p class="mb-6">Total Tagihan: <span class="font-bold text-2xl text-red-600">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></span></p>

    <div class="bg-gray-100 p-6 rounded-lg mb-6">
        <h3 class="font-bold text-lg mb-3">Instruksi Pembayaran</h3>
        <p class="mb-4">Silakan selesaikan pembayaran menggunakan metode yang Anda pilih:</p>

        <?php 
        // Ambil metode pembayaran dari pesanan
        $payment_method = $order['payment_method'];

        if ($payment_method == 'QRIS') : 
        ?>
            <h4 class="font-semibold text-lg">Metode: QRIS</h4>
            <p class="text-sm text-gray-700 mb-3">Silakan scan kode QR di bawah ini menggunakan aplikasi e-wallet Anda (GoPay, OVO, Dana, ShopeePay, dll).</p>
            <div class="flex justify-center my-4">
                <img src="uploads/payment/qris_fresbak.jpg" alt="QRIS Fresbak Corp" class="w-64 h-64 border-4 border-white shadow-lg">
            </div>
            <p class="text-center text-sm text-gray-600">Pastikan nominal transfer sesuai dengan total tagihan.</p>

        <?php elseif (strpos($payment_method, 'Bank Transfer') !== false) : ?>
            <h4 class="font-semibold text-lg">Metode: <?= htmlspecialchars($payment_method) ?></h4>
            <p class="text-sm text-gray-700 mb-3">Silakan transfer ke rekening berikut:</p>
            
            <ul class="list-none space-y-4 mt-3">
                <?php if ($payment_method == 'Bank Transfer - BCA') : ?>
                    <li class="border p-4 rounded-lg bg-white">
                        <strong class="block text-lg">Bank BCA</strong>
                        Nomor Rekening: <strong class="text-xl tracking-wider">123-456-7890</strong><br>
                        Atas Nama: <strong class="text-lg">PT Fresbak Corp Indonesia</strong>
                    </li>
                <?php elseif ($payment_method == 'Bank Transfer - BRI') : ?>
                    <li class="border p-4 rounded-lg bg-white">
                        <strong class="block text-lg">Bank BRI</strong>
                        Nomor Rekening: <strong class="text-xl tracking-wider">0099-8877-6655-44</strong><br>
                        Atas Nama: <strong class="text-lg">PT Fresbak Corp Indonesia</strong>
                    </li>
                <?php elseif ($payment_method == 'Bank Transfer - BNI') : ?>
                    <li class="border p-4 rounded-lg bg-white">
                        <strong class="block text-lg">Bank BNI</strong>
                        Nomor Rekening: <strong class="text-xl tracking-wider">987-654-3210</strong><br>
                        Atas Nama: <strong class="text-lg">PT Fresbak Corp Indonesia</strong>
                    </li>
                
                <?php elseif ($payment_method == 'Bank Transfer - Mandiri') : ?>
                    <li class="border p-4 rounded-lg bg-white">
                        <strong class="block text-lg">Bank Mandiri</strong>
                        Nomor Rekening: <strong class="text-xl tracking-wider">111-222-3334</strong><br>
                        Atas Nama: <strong class="text-lg">PT Fresbak Corp Indonesia</strong>
                    </li>
                <?php endif; ?>
            </ul>
            <p class="mt-4 text-sm text-gray-600">Mohon transfer sesuai total tagihan (hingga 3 digit terakhir) agar pesanan cepat diproses.</p>

        <?php endif; ?>
    </div>
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <hr class="my-6">
    <h3 class="font-bold text-lg mb-3">Upload Bukti Transfer</h3>
    <form action="upload_proof.php?order_id=<?= $order_id ?>" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="payment_proof" class="block text-gray-700 font-bold mb-2">Pilih File (JPG, PNG, maks 5MB)</label>
            <input type="file" name="payment_proof" id="payment_proof" class="w-full px-3 py-2 border rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
        </div>
        <div class="text-center">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-lg">
                Kirim Bukti Pembayaran
            </button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>