<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = $_GET['id'];
$product = null;

try {
    // Ambil produk HANYA JIKA AKTIF (is_active = 1)
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Anda bisa menambahkan logging error di sini
    $product = null;
}

include 'header.php';
?>

<?php if (!$product): ?>
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-6 rounded-lg shadow-md text-center">
        <h1 class="text-2xl font-bold mb-2">Produk Tidak Ditemukan</h1>
        <p>Maaf, produk yang Anda cari tidak tersedia atau telah dihapus.</p>
        <a href="products.php" class="mt-4 inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
            &larr; Kembali ke Daftar Produk
        </a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 bg-white p-8 rounded-xl shadow-lg">
        
        <div class="relative overflow-hidden rounded-lg">
            <?php 
            $image_path = "uploads/products/" . htmlspecialchars($product['image']);
            $image_src = file_exists($image_path) && !is_dir($image_path) ? $image_path : 'assets/placeholder.jpg';
            ?>
            <img src="<?= $image_src ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover">
        </div>

        <div>
            <h1 class="text-4xl font-extrabold text-gray-800 mb-2"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="text-3xl font-bold text-green-600 mb-6">Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
            
            <div class="prose max-w-none text-gray-600 mb-6">
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <p class="text-sm font-medium text-gray-700">
                    Stok Tersedia: 
                    <span class="font-bold text-lg <?= $product['stock'] > 0 ? 'text-green-700' : 'text-red-700' ?>">
                        <?= $product['stock'] > 0 ? $product['stock'] . ' unit' : 'Habis' ?>
                    </span>
                </p>
            </div>

            <form action="cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <div class="flex items-center space-x-4">
                    <div class="flex items-center border rounded-lg">
                        <label for="quantity" class="pl-3 text-sm font-medium text-gray-600">Qty:</label>
                        <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>" 
                               class="w-16 text-center border-0 focus:ring-0"
                               <?= $product['stock'] == 0 ? 'disabled' : '' ?>>
                    </div>

                    <button type="submit" name="add_to_cart" 
                            class="flex-grow bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-lg transition duration-300 shadow-md flex items-center justify-center"
                            <?= $product['stock'] == 0 ? 'disabled style="opacity: 0.6; cursor: not-allowed;"' : '' ?>>
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <?= $product['stock'] > 0 ? 'Tambah ke Keranjang' : 'Stok Habis' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>