<?php
include 'config.php';

// Menangkap Input Pencarian & Filter
$search_keyword = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

// [MODIFIKASI] Daftar Kategori Baru (Harus sama dengan di Admin)
$categories = ['Meja', 'Kursi', 'Lemari', 'Sofa', 'Tempat Tidur', 'Rak', 'Lampu', 'Dekorasi', 'Lainnya'];

// 1. Ambil Produk dengan Filter
try {
    $sql = "SELECT id, name, price, image, description, stock FROM products WHERE is_active = 1";
    $params = [];

    // Jika ada pencarian
    if (!empty($search_keyword)) {
        $sql .= " AND name LIKE ?";
        $params[] = "%" . $search_keyword . "%";
    }

    // Jika ada filter kategori
    if (!empty($category_filter)) {
        $sql .= " AND category = ?";
        $params[] = $category_filter;
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Jika error (misal kolom category belum dibuat), tampilkan array kosong
    $products = [];
}

// 2. Ambil Data Keranjang User (Untuk Logika Tombol Dinamis)
$cart_map = []; 
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
        // Silent error
    }
}

include 'header.php';
?>

<?php if (isset($_SESSION['message'])): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md flex justify-between items-center">
            <p><?= $_SESSION['message'] ?></p>
            <button onclick="this.parentElement.remove()" class="text-green-700 font-bold hover:text-green-900">&times;</button>
        </div>
    </div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex flex-col md:flex-row justify-between items-center mb-8 border-b pb-4 gap-4">
        <h1 class="text-3xl font-bold text-gray-800">Semua Koleksi Kami</h1>
        
        <form action="products.php" method="GET" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <select name="category" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat ?>" <?= $category_filter == $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
            
            <div class="relative">
                <input type="text" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($search_keyword) ?>" 
                       class="border border-gray-300 rounded-lg px-4 py-2 pl-10 focus:ring-green-500 focus:border-green-500 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition">
                Cari
            </button>
        </form>
    </div>

    <?php if (empty($products)): ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-8 rounded-lg shadow-sm text-center">
            <svg class="w-16 h-16 mx-auto text-yellow-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h3 class="text-xl font-bold mb-2">Produk Tidak Ditemukan</h3>
            <p>Maaf, kami tidak menemukan produk yang sesuai dengan pencarian "<strong><?= htmlspecialchars($search_keyword . ' ' . $category_filter) ?></strong>".</p>
            <a href="products.php" class="inline-block mt-4 text-green-600 hover:underline font-medium">Lihat Semua Produk &rarr;</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php foreach ($products as $product): ?>
            
            <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:shadow-xl hover:-translate-y-1 transition duration-300 group flex flex-col justify-between">
                <div>
                    <a href="product.php?id=<?= $product['id'] ?>">
                        <div class="relative h-48 overflow-hidden">
                            <?php 
                            $image_path = "uploads/products/" . htmlspecialchars($product['image']);
                            $image_src = file_exists($image_path) && !is_dir($image_path) ? $image_path : 'assets/placeholder.jpg';
                            ?>
                            <img src="<?= $image_src ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                        </div>
                    
                        <div class="p-4">
                            <h3 class="text-xl font-semibold text-gray-800 mb-1 truncate"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="text-lg font-bold text-green-600 mb-3">Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
                            <p class="text-sm text-gray-500 line-clamp-2"><?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...</p>
                        </div>
                    </a>
                </div>

                <div class="p-4 pt-0 mt-auto flex justify-between items-center">
                    <a href="product.php?id=<?= $product['id'] ?>" class="text-sm text-green-600 font-medium hover:underline">Lihat Detail</a>
                    
                    <?php 
                    $in_cart = isset($cart_map[$product['id']]);
                    $qty_now = $in_cart ? $cart_map[$product['id']]['qty'] : 0;
                    $cart_id = $in_cart ? $cart_map[$product['id']]['cart_id'] : 0;
                    ?>

                    <?php if (!$in_cart): ?>
                        
                        <form action="cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                            
                            <button type="submit" name="add_to_cart" 
                                class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 shadow-md text-sm flex items-center"
                                <?= $product['stock'] == 0 ? 'disabled style="opacity: 0.6;"' : '' ?>>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </button>
                        </form>

                    <?php else: ?>
                        
                        <div class="flex items-center space-x-3">
                            <form action="cart.php" method="POST">
                                <input type="hidden" name="cart_id" value="<?= $cart_id ?>">
                                <input type="hidden" name="update_quantity" value="1">
                                <input type="hidden" name="new_quantity" value="<?= $qty_now - 1 ?>">
                                <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                                <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold w-8 h-8 rounded-full flex items-center justify-center transition">
                                    -
                                </button>
                            </form>

                            <span class="text-lg font-bold text-gray-800 w-6 text-center"><?= $qty_now ?></span>

                            <form action="cart.php" method="POST">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="add_to_cart" value="1"> 
                                <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold w-8 h-8 rounded-full flex items-center justify-center transition shadow-md"
                                <?= $qty_now >= $product['stock'] ? 'disabled style="opacity: 0.5;"' : '' ?>>
                                    +
                                </button>
                            </form>
                        </div>

                    <?php endif; ?>

                </div>
            </div>

            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php include 'footer.php'; ?>