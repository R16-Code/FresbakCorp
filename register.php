<?php
include 'config.php'; 

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $message = "Semua field wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid.";
    } else {
        // Hashing password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'customer'; // Default role

        try {
            // Cek apakah email sudah terdaftar
            $stmt_check = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check->execute([$email]);
            if ($stmt_check->rowCount() > 0) {
                $message = "Email sudah terdaftar. Silakan login.";
            } else {
                // Gunakan kolom 'password' dan 'role' (Bukan password_hash/is_admin)
                $stmt_insert = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt_insert->execute([$name, $email, $password_hash, $role]);
                
                $_SESSION['message'] = "Registrasi berhasil. Silakan login.";
                header("Location: login.php");
                exit;
            }
        } catch (PDOException $e) {
            $message = "Terjadi kesalahan database: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - FRESBAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap'); body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-2xl">
        <h2 class="text-3xl font-bold text-center text-green-700 mb-6">Daftar Akun</h2>
        
        <?php if ($message): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg" role="alert">
                <p><?= $message ?></p>
            </div>
        <?php endif; ?>
        
        <form action="register.php" method="POST" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="name" id="name" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-lg transition duration-200">
                Daftar
            </button>
        </form>
        
        <p class="mt-6 text-center text-sm text-gray-600">
            Sudah punya akun? 
            <a href="login.php" class="font-semibold text-green-600 hover:text-green-800">Masuk di sini</a>
        </p>
    </div>
</body>
</html>