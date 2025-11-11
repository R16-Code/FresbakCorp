<?php
include 'config.php';

// Wajib login dan via POST
if (!isset($_SESSION['user_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error_message = '';

// Ambil data form
$customer_name = trim($_POST['customer_name']);
$customer_address = trim($_POST['customer_address']);
$customer_contact = trim($_POST['customer_contact']);
$payment_method = trim($_POST['payment_method']);

// Ambil harga total yang dikirim dari form checkout
$total_price_from_form = $_POST['calculated_total'] ?? 0;

// Mulai Transaksi Database
$db->beginTransaction();

try {
    // 1. Ambil Item Keranjang dari DB dan Kunci Baris
    $stmt_cart = $db->prepare("
        SELECT 
            c.product_id, 
            c.quantity AS qty, 
            p.price, 
            p.stock, 
            p.name 
        FROM cart c 
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ? 
        FOR UPDATE
    ");
    $stmt_cart->execute([$user_id]);
    $cart_products = $stmt_cart->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart_products)) {
        throw new Exception("Keranjang belanja Anda kosong. Silakan kembali.");
    }

    // 2. Hitung Ulang Total dan Cek Stok
    $total_price = $_POST['shipping_fee'] ?? 0; // Mulai dari biaya kirim
    $items_to_process = [];

    foreach ($cart_products as $product) {
        $qty = $product['qty'];

        if ($qty > $product['stock']) {
            throw new Exception("Stok untuk " . $product['name'] . " tidak mencukupi (sisa " . $product['stock'] . ").");
        }
        
        $item_subtotal = $product['price'] * $qty;
        $total_price += $item_subtotal;
        
        $items_to_process[] = [
            'product_id' => $product['product_id'],
            'price' => $product['price'],
            'qty' => $qty,
            'stock' => $product['stock']
        ];
    }
    
    // Cek Integritas Harga (Opsional, tapi bagus untuk keamanan)
    if (abs($total_price - $total_price_from_form) > 10) { // Toleransi 10 Rupiah
         throw new Exception("Integritas harga gagal. Total harga tidak sesuai.");
    }


    // 3. Insert ke tabel 'orders'
    $sql_order = "
        INSERT INTO orders (user_id, customer_name, customer_address, customer_contact, total_price, payment_method, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending')
    ";
    $stmt_order = $db->prepare($sql_order);
    $stmt_order->execute([
        $user_id, 
        $customer_name, 
        $customer_address, 
        $customer_contact, 
        $total_price, // Total harga yang sudah dihitung ulang di server
        $payment_method
    ]);
    $order_id = $db->lastInsertId();

    // 4. Insert ke 'order_items' dan update stok 'products'
    $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price_at_order) VALUES (?, ?, ?, ?)";
    $stmt_item = $db->prepare($sql_item);
    
    $sql_update_stock = "UPDATE products SET stock = ? WHERE id = ?";
    $stmt_update_stock = $db->prepare($sql_update_stock);

    foreach ($items_to_process as $item) {
        // Insert item
        $stmt_item->execute([$order_id, $item['product_id'], $item['qty'], $item['price']]);
        
        // Update stok
        $new_stock = $item['stock'] - $item['qty'];
        $stmt_update_stock->execute([$new_stock, $item['product_id']]);
    }

    // 5. Kosongkan keranjang dari DATABASE
    $stmt_clear_cart = $db->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt_clear_cart->execute([$user_id]);

    // 6. Commit transaksi
    $db->commit();

    // 7. Redirect ke halaman upload bukti
    header("Location: upload_proof.php?order_id=" . $order_id);
    exit;

} catch (Exception $e) {
    // 8. Rollback jika ada error
    $db->rollBack();
    $_SESSION['error'] = "Checkout gagal: " . $e->getMessage();
    header("Location: cart.php"); // Kembali ke keranjang jika gagal
    exit;
}
?>