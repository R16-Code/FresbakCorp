<?php
include 'config.php';
// Tidak perlu login untuk melihat halaman About
include 'header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <header class="text-center mb-12">
        <h1 class="text-4xl lg:text-5xl font-extrabold text-gray-900 mb-4">
            Mengenal <span class="text-green-600">FRESBAK</span> Corp
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Menciptakan ruang hidup yang fungsional, indah, dan berkelanjutan.
        </p>
    </header>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-16">
        
        <div class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-green-500">
            <h2 class="text-2xl font-bold text-gray-800 mb-3 flex items-center">
                <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                Visi Kami
            </h2>
            <p class="text-gray-600">
                Menjadi penyedia furnitur terdepan di Indonesia, dikenal karena desain minimalis yang **berkualitas tinggi**, dan komitmen terhadap **keberlanjutan** lingkungan. Kami percaya bahwa setiap rumah layak mendapatkan sentuhan elegan tanpa mengorbankan fungsi.
            </p>
        </div>
        
        <div class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-blue-500">
            <h2 class="text-2xl font-bold text-gray-800 mb-3 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m4 4v10m8-10v10"></path></svg>
                Misi Kami
            </h2>
            <ul class="list-disc list-inside text-gray-600 space-y-2">
                <li>Menghadirkan koleksi furnitur yang **inovatif** dan sesuai dengan tren gaya hidup modern.</li>
                <li>Memastikan pengalaman belanja online yang **mudah, aman, dan transparan** bagi setiap pelanggan.</li>
                <li>Memelihara **kualitas pengerjaan** dan menggunakan bahan baku yang bertanggung jawab.</li>
            </ul>
        </div>
    </div>

    <div class="text-center mb-16">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 border-b-2 border-green-500 pb-2 inline-block">Nilai Inti Kami</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="p-6 bg-gray-50 rounded-xl shadow-md transition duration-300 hover:shadow-xl">
                <div class="text-green-600 mb-3"><svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Desain Fungsional</h3>
                <p class="text-sm text-gray-600">Kami fokus pada furnitur yang tidak hanya indah secara visual tetapi juga maksimal dalam fungsi dan efisiensi ruang.</p>
            </div>
            
            <div class="p-6 bg-gray-50 rounded-xl shadow-md transition duration-300 hover:shadow-xl">
                <div class="text-green-600 mb-3"><svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Kualitas & Daya Tahan</h3>
                <p class="text-sm text-gray-600">Setiap produk dibuat dengan standar tertinggi, menjamin daya tahan jangka panjang untuk investasi rumah Anda.</p>
            </div>
            
            <div class="p-6 bg-gray-50 rounded-xl shadow-md transition duration-300 hover:shadow-xl">
                <div class="text-green-600 mb-3"><svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Transparansi Harga</h3>
                <p class="text-sm text-gray-600">Kami berkomitmen untuk menawarkan harga yang jujur dan adil tanpa biaya tersembunyi.
                </p>
            </div>
            
        </div>
    </div>
    
    <div class="bg-green-700 text-white p-10 rounded-xl text-center shadow-2xl">
        <h2 class="text-3xl font-bold mb-3">Siap Menata Ulang Ruangan Anda?</h2>
        <p class="text-lg mb-6 opacity-90">Jelajahi koleksi furnitur minimalis kami sekarang dan temukan potongan yang sempurna.</p>
        <a href="products.php" class="inline-block bg-white text-green-700 hover:bg-gray-100 font-bold py-3 px-8 rounded-full text-lg shadow-xl transition duration-300 transform hover:scale-105">
            Lihat Produk Kami
        </a>
    </div>

</div>

<?php include 'footer.php'; ?>