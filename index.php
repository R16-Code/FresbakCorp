<?php
include 'config.php';
try {
    // MODIFIKASI: Tambahkan kondisi WHERE is_active = 1
    $stmt = $db->query("SELECT id, name, price, image FROM products WHERE is_active = 1 ORDER BY id DESC LIMIT 6");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Untuk debugging, Anda bisa menampilkan error-nya:
    // echo "Database Error: " . $e->getMessage();
    $products = [];
}

$cart_map = []; // Array untuk menyimpan product_id => data keranjang
if (isset($_SESSION['user_id'])) {
    try {
        $stmt_cart = $db->prepare("SELECT id as cart_id, product_id, quantity FROM cart WHERE user_id = ?");
        $stmt_cart->execute([$_SESSION['user_id']]);
        while ($row = $stmt_cart->fetch(PDO::FETCH_ASSOC)) {
            $cart_map[$row['product_id']] = [
                'qty' => $row['quantity'],
                'cart_id' => $row['cart_id']
            ];
        }
    } catch (PDOException $e) {
        // Silent error atau log
    }
}

include 'header.php';

if (isset($_SESSION['message'])): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md flex justify-between items-center">
            <p><?= $_SESSION['message'] ?></p>
            <button onclick="this.parentElement.remove()" class="text-green-700 font-bold">&times;</button>
        </div>
    </div>
    <?php unset($_SESSION['message']); // Hapus pesan agar tidak muncul terus saat refresh ?>
<?php endif;
?>

<section class="bg-gray-800 rounded-xl shadow-lg mb-12">
    <div class="py-20 px-8 text-center">
        <h1 class="text-5xl font-extrabold text-white mb-4">
            Temukan Furnitur Impian Anda
        </h1>
        <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
            Koleksi terbaik untuk memperindah rumah Anda. Desain minimalis, kualitas premium.
        </p>
        <a href="products.php" class="inline-block bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-8 rounded-full shadow-lg transition duration-300 transform hover:scale-105">
            Jelajahi Sekarang
        </a>
    </div>
</section>

<section class="mb-12">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 border-b-2 border-green-500 pb-2">Produk Terbaru</h2>
    
    <?php if (empty($products)): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded" role="alert">
            <p class="font-bold">Informasi</p>
            <p>Belum ada produk aktif yang tersedia.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-8">
            <?php foreach ($products as $product): ?>
            
            <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:shadow-xl hover:-translate-y-1 transition duration-300 group">
                <a href="product.php?id=<?= $product['id'] ?>">
                    <div class="relative h-60 overflow-hidden">
                        <?php 
                        $image_path = "uploads/products/" . htmlspecialchars($product['image']);
                        $image_src = file_exists($image_path) && !is_dir($image_path) ? $image_path : 'assets/placeholder.jpg';
                        ?>
                        <img src="<?= $image_src ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                    </div>
                
                    <div class="p-5">
                        <h3 class="text-xl font-semibold text-gray-800 mb-1 truncate"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="text-lg font-bold text-green-600">Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
                    </div>
                </a>

                <div class="p-5 pt-0">
                    <?php 
                    // Cek apakah produk ini ada di keranjang user?
                    $in_cart = isset($cart_map[$product['id']]);
                    $qty_now = $in_cart ? $cart_map[$product['id']]['qty'] : 0;
                    $cart_id = $in_cart ? $cart_map[$product['id']]['cart_id'] : 0;
                    ?>

                    <?php if (!$in_cart): ?>
                        <form action="cart.php" method="POST" class="flex justify-end">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                            
                            <button type="submit" name="add_to_cart" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-6 rounded-lg transition duration-300 shadow-md text-sm flex items-center">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Beli
                            </button>
                        </form>

                    <?php else: ?>
                        <div class="flex justify-end items-center space-x-3">
                            
                            <form action="cart.php" method="POST">
                                <input type="hidden" name="cart_id" value="<?= $cart_id ?>">
                                <input type="hidden" name="update_quantity" value="1">
                                <input type="hidden" name="new_quantity" value="<?= $qty_now - 1 ?>">
                                <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                                
                                <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold w-8 h-8 rounded-full flex items-center justify-center transition">
                                    -
                                </button>
                            </form>

                            <span class="font-bold text-gray-800 text-lg w-6 text-center"><?= $qty_now ?></span>

                            <form action="cart.php" method="POST">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                                
                                <button type="submit" name="add_to_cart" class="bg-green-500 hover:bg-green-600 text-white font-bold w-8 h-8 rounded-full flex items-center justify-center transition shadow">
                                    +
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php endforeach; ?>
        </div>

        <div class="text-center mt-10">
            <a href="products.php" class="text-lg font-semibold text-green-600 hover:text-green-700 transition duration-150 border-b-2 border-green-300 hover:border-green-600">
                Lihat Semua Produk &rarr;
            </a>
        </div>
    <?php endif; ?>

</section>

<?php include 'footer.php'; ?>