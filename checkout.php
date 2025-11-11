<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user = [];
$cart_items = [];
$subtotal = 0;
$shipping_fee = 25000;

try {
    $stmt_user = $db->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    // MENGAMBIL ITEM KERANJANG DARI TABEL 'cart' (bukan session)
    $stmt_cart = $db->prepare("
        SELECT c.quantity, p.name, p.price, (p.price * c.quantity) AS total_item_price
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt_cart->execute([$user_id]);
    $cart_items = $stmt_cart->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart_items)) {
        // Jika keranjang database kosong, redirect ke keranjang
        header("Location: cart.php");
        exit;
    }

    foreach ($cart_items as $item) {
        $subtotal += $item['total_item_price'];
    }
    
    $total_price = $subtotal + $shipping_fee;

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

include 'header.php';
?>

<h1 class="text-3xl font-bold text-gray-800 mb-8 border-b pb-2">Konfirmasi Pesanan (Checkout)</h1>

<form action="process_order.php" method="POST">
    <input type="hidden" name="calculated_total" value="<?= $total_price ?>">
    <input type="hidden" name="shipping_fee" value="<?= $shipping_fee ?>">
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

        <div class="lg:col-span-2 bg-white p-8 rounded-xl shadow-lg space-y-8">
            
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2 text-green-600">1. Detail Pengiriman</h2>
                
                <div class="mb-4">
                    <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
                    <input type="text" name="customer_name" id="customer_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition duration-150" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                
                <div class="mb-4">
                    <label for="customer_contact" class="block text-sm font-medium text-gray-700 mb-1">No. Telepon / WA</label>
                    <input type="text" name="customer_contact" id="customer_contact" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition duration-150" placeholder="Contoh: 0812xxxx" required>
                </div>

                <div class="mb-4">
                    <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                    <textarea name="customer_address" id="customer_address" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition duration-150" required placeholder="Jalan, Nomor Rumah, Kelurahan, Kecamatan, Kota"></textarea>
                </div>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2 text-green-600">2. Metode Pembayaran</h2>
                
                <div class="space-y-3 p-4 border border-gray-200 rounded-lg bg-gray-50">
                    
                    <?php 
                    $payment_options = [
                        'QRIS' => 'QRIS (Scan kode QR, semua e-wallet)',
                        'Bank Transfer - BCA' => 'Transfer Bank BCA',
                        'Bank Transfer - BRI' => 'Transfer Bank BRI',
                        'Bank Transfer - BNI' => 'Transfer Bank BNI',
                        'Bank Transfer - Mandiri' => 'Transfer Bank Mandiri',
                    ];
                    $i = 0;
                    foreach ($payment_options as $value => $label): 
                    ?>
                        <div class="flex items-center p-3 rounded-lg hover:bg-white transition duration-150 border-2 <?= $i == 0 ? 'border-green-500 bg-white' : 'border-transparent' ?>" onclick="document.getElementById('<?= strtolower(str_replace([' - ', ' '], ['-', '-'], $value)) ?>').checked = true;">
                            <input type="radio" name="payment_method" id="<?= strtolower(str_replace([' - ', ' '], ['-', '-'], $value)) ?>" value="<?= htmlspecialchars($value) ?>" class="mr-3 text-green-600 focus:ring-green-500" <?= $i == 0 ? 'checked' : '' ?>>
                            <label for="<?= strtolower(str_replace([' - ', ' '], ['-', '-'], $value)) ?>" class="text-gray-800 font-medium cursor-pointer flex-grow"><?= $label ?></label>
                        </div>
                    <?php $i++; endforeach; ?>

                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-xl shadow-xl sticky top-24 border-t-4 border-green-600">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">3. Ringkasan Pesanan</h2>

                <div class="space-y-3 mb-6 border-b pb-4">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)</span>
                            <span>Rp <?= number_format($item['total_item_price'], 0, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between text-md font-medium text-gray-700">
                        <span>Subtotal Barang</span>
                        <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between text-md font-medium text-gray-700">
                        <span>Biaya Pengiriman</span>
                        <span>Rp <?= number_format($shipping_fee, 0, ',', '.') ?></span>
                    </div>
                </div>

                <div class="flex justify-between border-t border-gray-300 pt-4 mt-4">
                    <span class="text-xl font-bold text-gray-800">TOTAL BAYAR</span>
                    <span class="text-xl font-extrabold text-green-600">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                </div>

                <button type="submit" class="w-full text-center mt-6 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg text-lg transition duration-300 shadow-lg transform hover:scale-[1.01]">
                    Konfirmasi & Bayar
                </button>
            </div>
        </div>
    </div>
</form>

<?php include 'footer.php'; ?>