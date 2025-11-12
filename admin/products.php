<?php
require_once '../config.php';

// Logika Nonaktifkan/Arsip (Soft Delete)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Lakukan Soft Delete: UPDATE is_active = 0
    $stmt_deactivate = $db->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
    $stmt_deactivate->execute([$id]);
    
    header("Location: products.php?status=deactivated");
    exit;
}

// Logika Aktifkan Kembali
if (isset($_GET['activate'])) {
    $id = $_GET['activate'];
    
    // UPDATE is_active = 1 (Aktif)
    $stmt_activate = $db->prepare("UPDATE products SET is_active = 1 WHERE id = ?");
    $stmt_activate->execute([$id]);
    
    header("Location: products.php?status=activated");
    exit;
}

// Logika HAPUS PERMANEN (Hard Delete) - HANYA untuk produk yang sudah Nonaktif
if (isset($_GET['hard_delete'])) {
    $id = $_GET['hard_delete'];
    
    try {
        // Hapus gambar lama (diperlukan karena baris akan hilang)
        $stmt_img = $db->prepare("SELECT image FROM products WHERE id = ? AND is_active = 0");
        $stmt_img->execute([$id]);
        $old_image = $stmt_img->fetchColumn();
        
        // Coba Hapus data utama
        $stmt_delete = $db->prepare("DELETE FROM products WHERE id = ? AND is_active = 0");
        $stmt_delete->execute([$id]);

        if ($stmt_delete->rowCount() > 0) {
            // Jika berhasil dihapus dari DB, hapus file gambar
            if ($old_image && file_exists("../uploads/products/" . $old_image)) {
                unlink("../uploads/products/" . $old_image);
            }
            header("Location: products.php?status=deleted");
            exit;
        } else {
            // Jika produk tidak ditemukan atau masih aktif
            header("Location: products.php?status=error_delete");
            exit;
        }

    } catch (PDOException $e) {
        // Error 1451 (Foreign Key) akan tertangkap di sini
        if ($e->getCode() == '23000') {
             header("Location: products.php?status=fk_error");
             exit;
        }
        // Jika error lain, tetap tampilkan
        // $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Ambil semua produk (Admin biasanya perlu melihat semua, aktif maupun nonaktif)
$stmt = $db->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header_admin.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Manajemen Produk</h1>
    <a href="product_form.php?action=add" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
        + Tambah Produk
    </a>
</div>

<?php if (isset($_GET['status'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
        <?php
        if ($_GET['status'] == 'added') echo "Produk baru berhasil ditambahkan.";
        if ($_GET['status'] == 'updated') echo "Produk berhasil diperbarui.";
        if ($_GET['status'] == 'deactivated') echo "Produk berhasil dinonaktifkan.";
        if ($_GET['status'] == 'activated') echo "Produk berhasil diaktifkan kembali.";
        if ($_GET['status'] == 'deleted') echo "Produk berhasil dihapus secara permanen."; // Pesan baru
        if ($_GET['status'] == 'fk_error') echo "Gagal menghapus: Produk ini terkait dengan data pesanan dan tidak dapat dihapus secara permanen."; // Pesan error FK
        ?>
    </div>
<?php endif; ?>

<div class="bg-white p-8 rounded-lg shadow-md overflow-x-auto">
    <table class="w-full min-w-full table-auto">
        <thead>
            <tr class="border-b">
                <th class="text-left p-4">Gambar</th>
                <th class="text-left p-4">Nama Produk</th>
                <th class="text-left p-4">Harga</th>
                <th class="text-left p-4">Stok</th>
                <th class="text-left p-4">Status</th> 
                <th class="text-left p-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr class="border-b <?= $product['is_active'] ? '' : 'bg-gray-50 opacity-75' ?>"> 
                    <td class="p-4">
                        <img src="../uploads/products/<?= htmlspecialchars($product['image'] ? $product['image'] : 'default.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-16 h-16 object-cover rounded">
                    </td>
                    <td class="p-4 font-medium"><?= htmlspecialchars($product['name']) ?></td>
                    <td class="p-4">Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                    <td class="p-4"><?= $product['stock'] ?></td>
                    <td class="p-4">
                        <span class="font-bold <?= $product['is_active'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $product['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <td class="p-4 whitespace-nowrap">
                        <a href="product_form.php?action=edit&id=<?= $product['id'] ?>" class="text-blue-600 hover:text-blue-800 mr-3 font-medium">Edit</a>
                        
                        <?php if ($product['is_active']): ?>
                            <a href="products.php?delete=<?= $product['id'] ?>" class="text-yellow-600 hover:text-yellow-800 mr-3" onclick="return confirm('Yakin ingin menonaktifkan produk ini?')">Nonaktifkan</a>
                            <span class="text-gray-400 cursor-not-allowed" title="Nonaktifkan produk terlebih dahulu untuk menghapus permanen.">Hapus Permanen</span>
                        <?php else: ?>
                            <a href="products.php?activate=<?= $product['id'] ?>" class="text-green-600 hover:text-green-800 mr-3" onclick="return confirm('Yakin ingin mengaktifkan produk ini kembali?')">Aktifkan</a>
                            <a href="products.php?hard_delete=<?= $product['id'] ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('PERINGATAN! Anda akan MENGHAPUS PERMANEN produk ini. Tindakan ini tidak dapat dibatalkan. Lanjutkan?')">Hapus Permanen</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer_admin.php'; ?>