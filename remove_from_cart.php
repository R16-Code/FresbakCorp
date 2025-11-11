<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_id = intval($_GET['cart_id'] ?? 0);

if ($cart_id > 0) {
    try {
        // Verifikasi bahwa item keranjang milik user yang login
        $stmt_verify = $db->prepare("
            SELECT c.id FROM cart c 
            WHERE c.id = ? AND c.user_id = ?
        ");
        $stmt_verify->execute([$cart_id, $user_id]);
        
        if ($stmt_verify->rowCount() > 0) {
            // Hapus item dari keranjang
            $stmt_delete = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt_delete->execute([$cart_id, $user_id]);
            $_SESSION['message'] = "Produk berhasil dihapus dari keranjang.";
        } else {
            $_SESSION['message'] = "Item tidak ditemukan atau tidak valid.";
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
    }
}

header("Location: cart.php");
exit;
?>
