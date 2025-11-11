<?php
include '../config.php';

// Periksa sesi admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FRESBAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f9;
        }
        /* Style untuk sidebar aktif */
        .sidebar-link.active {
            background-color: #10b981; /* green-500 */
            color: white;
            font-weight: 600;
        }
        .sidebar-link.active svg {
            color: white;
        }
    </style>
</head>
<body>

<div class="flex h-screen bg-gray-50">
    <aside class="w-64 bg-gray-800 text-white flex flex-col fixed h-full shadow-2xl z-40">
        <div class="flex items-center justify-center h-16 bg-gray-900 border-b border-gray-700">
            <a href="index.php" class="text-xl font-bold tracking-wider uppercase">
                <span class="text-green-500">ADMIN</span>PANEL
            </a>
        </div>
        
        <nav class="flex-grow p-4 space-y-2">
            
            <a href="index.php" class="sidebar-link flex items-center p-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>

            <a href="orders.php" class="sidebar-link flex items-center p-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                Manajemen Pesanan
            </a>
            
            <a href="report.php" class="sidebar-link flex items-center p-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0h6"></path></svg>
                Laporan Penjualan
            </a>

            <a href="products.php" class="sidebar-link flex items-center p-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                Produk
            </a>

            <a href="users.php" class="sidebar-link flex items-center p-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Pengguna
            </a>
            
        </nav>
        
        <div class="p-4 border-t border-gray-700">
            <a href="../logout.php" class="flex items-center p-3 rounded-lg text-red-400 hover:bg-gray-700 transition duration-200">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3v-4a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Keluar
            </a>
        </div>
    </aside>

    <div class="flex flex-col flex-1 overflow-x-hidden overflow-y-auto lg:ml-64">
        
        <header class="flex items-center justify-between p-4 bg-white shadow-md lg:hidden sticky top-0 z-30">
            <div class="text-xl font-bold text-gray-800">Admin FRESBAK</div>
        </header>

        <main class="p-6">
            <script>
                // JavaScript untuk menandai link sidebar yang aktif
                document.addEventListener('DOMContentLoaded', function() {
                    const currentPath = window.location.pathname.split('/').pop();
                    const links = document.querySelectorAll('.sidebar-link');
                    links.forEach(link => {
                        const href = link.getAttribute('href');
                        if (href === currentPath) {
                            link.classList.add('active');
                        }
                    });
                });
            </script>