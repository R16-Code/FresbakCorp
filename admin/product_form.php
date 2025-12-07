<?php
// Pastikan semua logika PHP diletakkan di SINI, sebelum output HTML/include header.
require_once '../config.php';

$is_edit = false;
$product = [
    'id' => null,
    'name' => '',
    'category' => '', 
    'description' => '',
    'price' => '',
    'stock' => '',
    'image' => ''
];
$message = '';
$error = '';

// [MODIFIKASI] Daftar Kategori Baru Berdasarkan Jenis Benda
$categories = ['Meja', 'Kursi', 'Lemari', 'Sofa', 'Tempat Tidur', 'Rak', 'Lampu', 'Dekorasi', 'Lainnya'];

// 1. Logika Ambil Data (untuk Edit)
if (isset($_GET['id']) && !isset($_POST['product_id'])) {
    $is_edit = true;
    $product_id = $_GET['id'];
    try {
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $fetched_product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fetched_product) {
            $product = $fetched_product;
        } else {
            $error = "Produk tidak ditemukan.";
            $is_edit = false;
        }
    } catch (PDOException $e) {
        $error = "Gagal mengambil data produk: " . $e->getMessage();
    }
}

// 2. Logika POST (Simpan/Update Data)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'] ?? null;
    $name = trim($_POST['name']);
    $category = $_POST['category']; // Ambil input kategori
    $description = trim($_POST['description']);
    
    $price_str = str_replace('.', '', $_POST['price']);
    $price_str = str_replace(',', '.', $price_str);
    $price = (int)floatval($price_str); 
    
    $stock = (int)$_POST['stock'];
    $current_image = $_POST['current_image'] ?? '';
    
    $is_edit = !empty($product_id);
    
    // Validasi Dasar
    if (empty($name) || $price <= 0) {
        $error = "Nama produk dan harga harus diisi dengan benar.";
    } else {
        $upload_dir = "../uploads/products/";
        $image_name = $current_image;

        // Logika Upload Gambar
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['image']['type'], $allowed_types)) {
                
                if ($is_edit && $current_image && file_exists($upload_dir . $current_image)) {
                    unlink($upload_dir . $current_image);
                }
                
                $image_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image_name = time() . '_' . uniqid() . '.' . $image_extension;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name)) {
                    $error = "Gagal mengupload file gambar.";
                }
            } else {
                $error = "Tipe file tidak didukung. Gunakan JPG, PNG, atau GIF.";
            }
        }
        
        // Simpan ke Database
        if (!$error) {
            try {
                if ($is_edit && $product_id) {
                    // Update Produk (+Category)
                    $stmt = $db->prepare("UPDATE products SET name = ?, category = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?");
                    $stmt->execute([$name, $category, $description, $price, $stock, $image_name, $product_id]);
                    $_SESSION['message'] = "Produk **" . htmlspecialchars($name) . "** berhasil diperbarui.";
                } else {
                    // Tambah Produk Baru (+Category)
                    $stmt = $db->prepare("INSERT INTO products (name, category, description, price, stock, image, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
                    $stmt->execute([$name, $category, $description, $price, $stock, $image_name]);
                    $_SESSION['message'] = "Produk baru **" . htmlspecialchars($name) . "** berhasil ditambahkan.";
                }
                
                header("Location: products.php?status=" . ($is_edit ? 'updated' : 'added'));
                exit;
            } catch (PDOException $e) {
                if ($image_name != $current_image && file_exists($upload_dir . $image_name)) {
                    unlink($upload_dir . $image_name);
                }
                $error = "Gagal menyimpan data ke database: " . $e->getMessage();
            }
        }
    }
    
    $product = array_merge($product, ['name' => $name, 'category' => $category, 'description' => $description, 'price' => $price, 'stock' => $stock, 'image' => $image_name]);
}


// --- START HTML OUTPUT ---
include 'header_admin.php';

$title = $is_edit ? 'Edit Produk: ' . htmlspecialchars($product['name']) : 'Tambah Produk Baru';
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6"><?= $title ?></h1>

<?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow-md" role="alert">
        <p><?= $error ?></p>
    </div>
<?php endif; ?>

<div class="bg-white p-8 rounded-xl shadow-lg">
    <form action="product_form.php<?= $is_edit ? '?id=' . $product['id'] : '' ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <?php if ($is_edit): ?>
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="hidden" name="current_image" value="<?= $product['image'] ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Produk</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($product['name']) ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition duration-150" placeholder="Contoh: Meja Makan Jati">
            </div>
            
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select name="category" id="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition duration-150">
                    <option value="Lainnya">Pilih Kategori...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($product['category'] == $cat) ? 'selected' : '' ?>>
                            <?= $cat ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea name="description" id="description" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition duration-150" placeholder="Jelaskan detail produk..."><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
                <input type="number" name="price" id="price" value="<?= htmlspecialchars($product['price']) ?>" required min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition duration-150" placeholder="Cth: 500000">
            </div>
            
            <div>
                <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stok</label>
                <input type="number" name="stock" id="stock" value="<?= htmlspecialchars($product['stock']) ?>" required min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition duration-150" placeholder="Jumlah stok">
            </div>
        </div>

        <div>
            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Gambar Produk</label>
            <input type="file" name="image" id="image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
            
            <?php if ($product['image']): 
                $image_path = "../uploads/products/" . htmlspecialchars($product['image']);
                $image_src = file_exists($image_path) && !is_dir($image_path) ? $image_path : '../assets/placeholder.jpg';
            ?>
                <p class="mt-3 text-sm text-gray-500">Gambar saat ini:</p>
                <img src="<?= $image_src ?>" alt="Gambar Produk" class="mt-2 w-32 h-32 object-cover rounded-lg shadow-md border border-gray-200">
            <?php endif; ?>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-between">
            <a href="products.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-150">Batal</a>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg text-lg transition duration-300 shadow-md">
                <?= $is_edit ? 'Simpan Perubahan' : 'Tambah Produk' ?>
            </button>
        </div>

    </form>
</div>

<?php include 'footer_admin.php'; ?>