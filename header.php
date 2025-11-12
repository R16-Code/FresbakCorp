<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fresbak - Toko Furniture Modern</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f7f7;
        }
    </style>
</head>
<body>

<header class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            
            <a href="index.php" class="text-2xl font-bold text-gray-800 tracking-wider">
                <span class="text-green-600">FRES</span>BAK
            </a>

            <nav class="hidden md:flex space-x-8">
                <a href="index.php" class="text-gray-600 hover:text-green-600 font-medium transition duration-150">Beranda</a>
                <a href="products.php" class="text-gray-600 hover:text-green-600 font-medium transition duration-150">Produk</a>
                <a href="about.php" class="text-gray-600 hover:text-green-600 font-medium transition duration-150">Tentang Kami</a>
            </nav>

            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="my_orders.php" class="text-gray-600 hover:text-green-600 p-2 rounded-full hover:bg-gray-100 transition duration-150">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c4.418 0 8 1.79 8 4v2H4v-2c0-2.21 3.582-4 8-4z"></path></svg>
                    </a>
                    <a href="cart.php" class="text-gray-600 hover:text-green-600 p-2 rounded-full hover:bg-gray-100 transition duration-150 relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </a>
                    <a href="logout.php" class="text-sm font-medium text-white bg-red-500 hover:bg-red-600 py-1.5 px-4 rounded-full transition duration-150 shadow-md">Keluar</a>
                <?php else: ?>
                    <a href="login.php" class="text-sm font-medium text-gray-600 hover:text-green-600 transition duration-150">Masuk</a>
                    <a href="register.php" class="text-sm font-medium text-white bg-green-500 hover:bg-green-600 py-1.5 px-4 rounded-full transition duration-150 shadow-md">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">