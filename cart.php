<?php
include 'config.php';
// Pastikan sesi sudah dimulai di config.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
$cart_items = [];
$subtotal = 0;

// PROSES MENAMBAHKAN PRODUK KE KERANJANG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);

    if ($product_id > 0 && $quantity > 0) {
        try {
            // Cek apakah produk sudah ada di keranjang
            $stmt_check = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt_check->execute([$user_id, $product_id]);
            $existing_item = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($existing_item) {
                // Update quantity jika produk sudah ada
                $new_quantity = $existing_item['quantity'] + $quantity;
                $stmt_update = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt_update->execute([$new_quantity, $existing_item['id']]);
                $message = "Produk berhasil diperbarui di keranjang.";
            } else {
                // Insert produk baru ke keranjang
                $stmt_insert = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt_insert->execute([$user_id, $product_id, $quantity]);
                $message = "Produk berhasil ditambahkan ke keranjang.";
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// PROSES UPDATE QUANTITY (via AJAX atau form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $cart_id = intval($_POST['cart_id'] ?? 0);
    $new_quantity = intval($_POST['new_quantity'] ?? 0);

    if ($cart_id > 0 && $new_quantity > 0) {
        try {
            // Verifikasi dan update quantity
            $stmt_verify = $db->prepare("SELECT id FROM cart WHERE id = ? AND user_id = ?");
            $stmt_verify->execute([$cart_id, $user_id]);
            
            if ($stmt_verify->rowCount() > 0) {
                $stmt_update = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt_update->execute([$new_quantity, $cart_id, $user_id]);
                $message = "Quantity berhasil diperbarui.";
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

try {
    // Ambil item keranjang dari tabel cart
    $stmt = $db->prepare("
        SELECT 
            c.id AS cart_id, 
            c.product_id, 
            c.quantity, 
            p.name, 
            p.price, 
            p.stock,
            p.image,
            (p.price * c.quantity) AS total_item_price
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hitung Subtotal
    foreach ($cart_items as $item) {
        $subtotal += $item['total_item_price'];
    }

} catch (PDOException $e) {
    $message = "Database Error: " . $e->getMessage();
}

// Logika update atau delete item keranjang (opsional, tambahkan jika perlu)
// ...

include 'header.php'; // Asumsi file header Anda ada
?>

<h1 class="text-3xl font-bold text-gray-800 mb-8 border-b pb-2">Keranjang Belanja</h1>

<?php if ($message): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg" role="alert">
        <p><?= htmlspecialchars($message) ?></p>
    </div>
<?php endif; ?>

<?php if (empty($cart_items)): ?>
    <div class="text-center p-10 bg-white rounded-xl shadow-lg">
        <p class="text-xl text-gray-600 mb-4">Keranjang Anda kosong. Yuk, mulai belanja!</p>
        <a href="index.php" class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200">
            Lihat Produk
        </a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-4">
            <?php foreach ($cart_items as $item): ?>
                <div class="flex items-center bg-white p-4 rounded-xl shadow-lg border-l-4 border-green-500">
                    <img src="uploads/products/<?= htmlspecialchars($item['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-16 h-16 object-cover rounded-md mr-4">
                    
                    <div class="flex-grow">
                        <h2 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($item['name']) ?></h2>
                        <p class="text-sm text-gray-500">Harga: Rp <?= number_format($item['price'], 0, ',', '.') ?> / item</p>
                    </div>
                    
                    <form method="POST" action="cart.php" class="flex items-center w-32 text-center">
                        <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                        <input type="hidden" name="update_quantity" value="1">
                        <p class="text-sm text-gray-600 w-full">Qty:</p>
                        <input type="number" name="new_quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" class="w-full text-center border rounded-md p-1 text-sm" onchange="this.form.submit()">
                    </form>

                    <div class="w-32 text-right font-bold text-green-600">
                        Rp <?= number_format($item['total_item_price'], 0, ',', '.') ?>
                    </div>
                    
                    <a href="remove_from_cart.php?cart_id=<?= $item['cart_id'] ?>" class="ml-4 text-red-500 hover:text-red-700 transition duration-150">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-xl shadow-xl sticky top-24">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Ringkasan Belanja</h2>
                
                <div class="flex justify-between text-lg font-semibold text-gray-700 mb-4">
                    <span>Subtotal</span>
                    <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                </div>
                
                <a href="checkout.php" class="w-full block text-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg text-lg transition duration-300 shadow-md">
                    Lanjut ke Checkout
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>