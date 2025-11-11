<?php
include 'header_admin.php';

// Logika Hapus
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Hapus gambar lama jika ada
    $stmt_img = $db->prepare("SELECT image FROM products WHERE id = ?");
    $stmt_img->execute([$id]);
    $old_image = $stmt_img->fetchColumn();
    if ($old_image && file_exists("../uploads/products/" . $old_image)) {
        unlink("../uploads/products/" . $old_image);
    }
    
    // Hapus data
    $stmt_delete = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt_delete->execute([$id]);
    
    header("Location: products.php?status=deleted");
    exit;
}

// Ambil semua produk
$stmt = $db->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        if ($_GET['status'] == 'deleted') echo "Produk berhasil dihapus.";
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
                <th class="text-left p-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr class="border-b">
                    <td class="p-4">
                        <img src="../uploads/products/<?= htmlspecialchars($product['image'] ? $product['image'] : 'default.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-16 h-16 object-cover rounded">
                    </td>
                    <td class="p-4 font-medium"><?= htmlspecialchars($product['name']) ?></td>
                    <td class="p-4">Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                    <td class="p-4"><?= $product['stock'] ?></td>
                    <td class="p-4">
                        <a href="product_form.php?action=edit&id=<?= $product['id'] ?>" class="text-blue-500 hover:text-blue-700 mr-2">Edit</a>
                        <a href="products.php?delete=<?= $product['id'] ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer_admin.php'; ?>