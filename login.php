<?php
include 'config.php';

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['is_admin']) {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Ambil password HASH, role, dan kolom penting lainnya.
        // Gunakan 'password' AS password_hash agar kode di bawahnya tidak perlu diubah
        $stmt = $db->prepare("SELECT id, name, email, password AS password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login Berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            
            // Tentukan hak akses admin berdasarkan kolom 'role'
            $_SESSION['is_admin'] = ($user['role'] === 'admin');

            if ($_SESSION['is_admin']) {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $message = "Email atau password salah.";
        }
    } catch (PDOException $e) {
        $message = "Terjadi kesalahan database: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - FRESBAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap'); body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-2xl">
        <h2 class="text-3xl font-bold text-center text-green-700 mb-6">Masuk ke Akun</h2>
        
        <?php if ($message): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg" role="alert">
                <p><?= $message ?></p>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-lg transition duration-200">
                Masuk
            </button>
        </form>
        
        <p class="mt-6 text-center text-sm text-gray-600">
            Belum punya akun? 
            <a href="register.php" class="font-semibold text-green-600 hover:text-green-800">Daftar di sini</a>
        </p>
        
        <div class="mt-6">
            <a href="index.php" class="block w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2.5 rounded-lg transition duration-200 text-center">
                Masuk sebagai Tamu
            </a>
        </div>
    </div>
</body>
</html>