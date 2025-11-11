<?php
include 'header_admin.php'; 

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Logika Update Hak Akses Admin
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_admin_status'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['new_role']; // Menggunakan kolom 'role'
    
    try {
        $stmt_update = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt_update->execute([$new_role, $user_id]);
        
        // Memperbarui sesi admin jika admin mengubah status dirinya sendiri (penting!)
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['is_admin'] = ($new_role === 'admin');
        }
        
        $_SESSION['message'] = "Hak akses pengguna berhasil diperbarui menjadi '" . ucfirst($new_role) . "'.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Gagal memperbarui hak akses: " . $e->getMessage();
    }
    header("Location: users.php");
    exit;
}

// Ambil semua data pengguna
try {
    // Ambil kolom 'role' dan buat alias 'is_admin' untuk memudahkan tampilan
    $stmt = $db->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Gagal mengambil data pengguna: " . $e->getMessage();
}
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Manajemen Pengguna</h1>

<?php if ($message): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-md" role="alert">
        <p><?= $message ?></p>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow-md" role="alert">
        <p><?= $error ?></p>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Daftar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peran (Role)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada pengguna terdaftar.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $user['id'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($user['name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form action="users.php" method="POST" class="flex items-center space-x-2">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="update_admin_status" value="1">
                                    
                                    <select name="new_role" onchange="this.form.submit()" class="px-3 py-1 text-sm border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 rounded-md" 
                                        <?= $user['id'] == $_SESSION['user_id'] ? 'disabled title="Anda tidak bisa mengubah peran Anda sendiri."' : '' ?>>
                                        <option value="customer" <?= $user['role'] == 'customer' ? 'selected' : '' ?>>Pelanggan</option>
                                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Admin
                                        </span>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer_admin.php'; ?>