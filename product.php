<?php
include 'config.php';

$product = null;
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    try {
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle error
    }
}

if (!$product) {
    header("Location: products.php");
    exit;
}

$image_path = "uploads/products/" . htmlspecialchars($product['image']);
$image_src = file_exists($image_path) && !is_dir($image_path) ? $image_path : 'assets/placeholder.jpg';

include 'header.php';
?>

<div class="max-w-6xl mx-auto bg-white p-8 rounded-xl shadow-lg">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

        <div class="lg:sticky lg:top-24">
            <div class="bg-gray-100 p-4 rounded-xl shadow-inner">
                <img src="<?= $image_src ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-auto object-contain rounded-lg">
            </div>
            <a href="products.php" class="inline-block mt-4 text-sm text-gray-500 hover:text-green-600 transition duration-150">&larr; Kembali ke Daftar Produk</a>
        </div>

        <div class="py-4">
            <h1 class="text-4xl font-bold text-gray-900 mb-3"><?= htmlspecialchars($product['name']) ?></h1>
            
            <p class="text-gray-500 text-sm mb-6">ID Produk: #<?= $product['id'] ?></p>

            <div class="flex items-baseline mb-6 space-x-4">
                <span class="text-4xl font-extrabold text-green-600">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                <span class="text-base text-gray-500 font-semibold">
                    Stok: 
                    <?php if ($product['stock'] > 10): ?>
                        <span class="text-green-500">Tersedia Banyak</span>
                    <?php elseif ($product['stock'] > 0): ?>
                        <span class="text-yellow-500">Tersisa <?= $product['stock'] ?></span>
                    <?php else: ?>
                        <span class="text-red-500">Habis</span>
                    <?php endif; ?>
                </span>
            </div>

            <h2 class="text-2xl font-semibold text-gray-800 mb-3 border-b pb-2">Deskripsi</h2>
            <div class="text-gray-700 leading-relaxed mb-8">
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>

            <form action="cart.php" method="POST" class="space-y-4">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                <div class="flex items-center space-x-4">
                    <label for="quantity" class="text-lg font-medium text-gray-700">Jumlah:</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-center focus:ring-green-500 focus:border-green-500" <?= $product['stock'] == 0 ? 'disabled' : '' ?>>
                </div>

                <button type="submit" name="add_to_cart" 
                    class="w-full lg:w-3/4 flex items-center justify-center bg-green-600 hover:bg-green-700 text-white text-xl font-bold py-3 rounded-xl shadow-lg transition duration-300 transform hover:scale-[1.01]"
                    <?= $product['stock'] == 0 ? 'disabled' : '' ?>>
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <?= $product['stock'] == 0 ? 'Stok Habis' : 'Tambahkan ke Keranjang' ?>
                </button>
            </form>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>