-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 04, 2025 at 04:02 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bakery`
--

-- --------------------------------------------------------

--
-- Table structure for table `bakery_settings`
--

CREATE TABLE `bakery_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bakery_settings`
--

INSERT INTO `bakery_settings` (`id`, `setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'bakery_name', 'SweetLoaf Bakery', 'Nama bakery', '2025-07-04 12:56:04', '2025-07-04 12:56:04'),
(2, 'bakery_address', 'Jl. Contoh No. 123, Kota Anda, 12345', 'Alamat lengkap bakery', '2025-07-04 12:56:04', '2025-07-04 12:56:04'),
(3, 'bakery_phone', '+62 812 3456 7890', 'Nomor telepon kontak bakery', '2025-07-04 12:56:04', '2025-07-04 12:56:04'),
(4, 'bakery_email', 'info@sweetloaf.com', 'Email kontak bakery', '2025-07-04 12:56:04', '2025-07-04 12:56:04'),
(5, 'operating_hours', 'Senin - Sabtu, 08:00 - 20:00', 'Jam operasional bakery', '2025-07-04 12:56:04', '2025-07-04 12:56:04'),
(6, 'delivery_fee', '15000', 'Biaya pengiriman default', '2025-07-04 12:56:04', '2025-07-04 12:56:04'),
(7, 'min_order_for_delivery', '50000', 'Minimal order untuk pengiriman', '2025-07-04 12:56:04', '2025-07-04 12:56:04');

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `nama_pengguna` varchar(255) NOT NULL,
  `pesanan` text NOT NULL,
  `total_item` int(11) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('Belum Diproses','Sedang Diproses','Selesai') DEFAULT 'Belum Diproses',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `nama_pengguna`, `pesanan`, `total_item`, `total_harga`, `catatan`, `status`, `created_at`, `updated_at`) VALUES
(4, 'shofa21', 'Strawberry Cheesecake (1), Strawberry Mini Tart (2)', 3, 119000.00, 'Tambah box', 'Selesai', '2024-12-31 07:27:09', '2024-12-31 07:29:24'),
(5, 'deandra', 'Strawberry Mini Tart (2), Croissant (2)', 4, 54000.00, 'Tambah kartu ucapan \"Happy Wedding\"', 'Selesai', '2025-01-10 16:26:54', '2025-04-17 01:40:04');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `foto`, `nama`, `deskripsi`, `harga`, `stok`, `created_at`, `updated_at`) VALUES
(1, 'croissant.jpeg', 'Croissant', 'Croissant adalah roti pastry asal Prancis yang terkenal dengan bentuk setengah bulan dan tekstur berlapis-lapis, renyah di luar dan lembut di dalam. Terbuat dari adonan ragi yang dilapisi mentega, croissant melalui proses pelipatan dan pemanggangan untuk menciptakan lapisan tipis yang ringan. Biasanya disajikan sebagai sarapan atau camilan, croissant dapat dinikmati polos atau dengan berbagai isian seperti selai, keju, atau cokelat. Keistimewaan croissant terletak pada teknik pembuatannya yang menghasilkan rasa yang gurih dan tekstur yang sangat menggugah selera.', 15000, -2, '2024-12-29 16:41:06', '2025-04-17 01:40:04'),
(2, 'mini tart.jpeg', 'Strawberry Mini Tart', 'Hidangan penutup kecil yang terdiri dari kulit kue tart renyah yang diisi dengan krim lembut, seperti custard atau cream cheese, dan dihiasi dengan potongan stroberi segar di atasnya. Rasanya yang manis dan sedikit asam dari stroberi segar berpadu sempurna dengan krim yang kaya, menciptakan keseimbangan rasa yang menyegarkan. Tart mini ini sering dijadikan camilan elegan untuk berbagai acara spesial, seperti pesta ulang tahun atau perayaan, berkat penyajiannya yang cantik dan menggugah selera.', 12000, -3, '2024-12-29 16:42:01', '2025-04-17 01:40:04'),
(3, 'waffle.jpeg', 'Waffle', 'Sejenis kue yang terbuat dari adonan tepung, telur, susu, dan mentega, yang dipanggang dalam cetakan khusus untuk menghasilkan tekstur renyah di luar dan lembut di dalam. Waffle memiliki pola grid atau jaring-jaring yang khas di permukaannya, yang memberikan karakteristik unik pada penampilannya. Biasanya, waffle disajikan dengan berbagai topping seperti sirup maple, buah-buahan segar, es krim, atau krim kocok, menjadikannya hidangan yang populer sebagai sarapan, camilan, atau makanan penutup. Variasi waffle dapat ditemukan di banyak negara, dengan perbedaan dalam adonan dan cara penyajiannya, menjadikannya makanan yang fleksibel dan disukai banyak orang di seluruh dunia.', 11500, 13, '2024-12-29 16:43:32', '2024-12-29 16:43:32'),
(4, 'strawberry_cheesecake.jpeg', 'Strawberry Cheesecake', 'Hidangan penutup yang memadukan kelembutan krim keju dengan rasa manis dan sedikit asam dari buah stroberi segar. Lapisan dasar yang terbuat dari biskuit graham yang dihancurkan memberikan tekstur renyah, sementara lapisan krim keju yang lembut dan kaya rasa melengkapi rasa keseluruhan. Untuk sentuhan akhir, stroberi segar yang dipotong diletakkan di atas cheesecake, memberikan warna cerah dan rasa yang segar. Keunikan dari Strawberry Cheesecake terletak pada keseimbangan sempurna antara keju yang creamy, rasa buah yang segar, dan dasar biskuit yang renyah, menjadikannya favorit banyak orang sebagai pencuci mulut yang memanjakan lidah.', 95000, 6, '2024-12-29 16:44:26', '2025-06-25 16:25:22'),
(5, 'white_blueberry_cupcakes.jpeg', 'White Blueberry Cupcake', 'White Blueberry Cupcake adalah sebuah cupcakes lembut dengan rasa manis dan sedikit asam, yang menggabungkan kelembutan kue vanila putih dengan topping blueberry segar. Setiap gigitan memberikan sensasi rasa buah blueberry yang juicy, berpadu dengan tekstur ringan dari adonan cupcakes yang kaya. Cupcake ini dihiasi dengan krim keju atau buttercream putih yang halus, menambah rasa gurih yang melengkapi kelezatannya. Cocok disajikan sebagai camilan manis di berbagai acara, White Blueberry Cupcake menawarkan perpaduan sempurna antara rasa klasik dan buah yang menyegarkan.', 13000, 18, '2024-12-29 16:45:10', '2025-06-25 16:25:58'),
(6, 'red velvet caake.jpeg', 'Red Velvet Cake', 'Red Velvet Cake adalah kue yang terkenal dengan warna merah cerah dan tekstur lembut yang memikat. Kue ini memiliki rasa yang kaya dengan sentuhan cokelat ringan dan aroma vanilla yang khas, sering kali dipadukan dengan lapisan krim keju atau buttercream yang lembut dan creamy. Keistimewaannya terletak pada kombinasi rasa manis dan sedikit asam yang berasal dari penggunaan buttermilk dan cuka, memberikan kelembutan dan kelembapan pada kue. Red Velvet Cake sering kali disajikan pada acara-acara spesial, seperti ulang tahun dan perayaan, karena penampilannya yang memukau dan rasanya yang lezat.', 87000, 10, '2024-12-29 16:45:55', '2024-12-29 16:45:55'),
(7, 'bagel.jpeg', 'Bagel', 'Bagel adalah sejenis roti bulat dengan lubang di tengahnya yang memiliki tekstur kenyal dan sedikit padat. Biasanya, adonan bagel direbus terlebih dahulu sebelum dipanggang, memberikan permukaan yang sedikit mengkilap dan keras. Bagel sering disajikan dengan berbagai topping, seperti krim keju, selai, atau daging asap, dan bisa diisi dengan bahan lain seperti telur, salmon, atau sayuran. Bagel berasal dari Eropa Timur, khususnya dari tradisi Yahudi, dan kini telah menjadi makanan populer di banyak negara, sering disantap sebagai sarapan atau camilan.', 9500, 13, '2024-12-29 16:46:41', '2024-12-31 06:20:38'),
(9, 'black forest cake.jpeg', 'Black Forest Cake', 'Black Forest Cake adalah kue lapis yang kaya dan lezat, terkenal dengan kombinasi rasa manis dan segar. Kue ini terbuat dari lapisan kue cokelat yang lembut dan basah, diisi dengan krim kocok yang ringan dan cerah, serta dilapisi dengan ceri hitam manis di bagian atasnya. Ceri yang digunakan seringkali merupakan ceri maraschino atau ceri yang diawetkan, memberikan kontras rasa yang menyegarkan. Kue ini juga biasanya ditaburi dengan serpihan cokelat dan hiasan krim di sekelilingnya, menciptakan tampilan yang cantik dan menggugah selera. Black Forest Cake menjadi pilihan populer di berbagai perayaan karena cita rasanya yang kaya dan tekstur yang lembut.', 89000, 12, '2024-12-29 16:49:15', '2025-06-25 16:26:19'),
(10, 'cake custom.jpeg', 'Baby Shower Custom Cake', 'Kue yang dirancang dengan indah untuk merayakan kedatangan bayi yang akan datang. Biasanya dihias dengan warna-warna pastel seperti merah muda, biru, coklat, atau kuning. Kue ini bisa dihias dengan detail lucu seperti sepatu bayi, mainan gantung, popok, atau binatang bayi. Kue ini dapat memiliki beberapa lapisan, dengan masing-masing lapisan menampilkan desain atau tema yang berbeda, sesuai dengan jenis kelamin bayi atau tema keseluruhan baby shower. Kue custom ini juga bisa dipersonalisasi dengan nama bayi, tanggal perkiraan lahir, atau pesan khusus, memberikan kenangan manis bagi tamu yang hadir dalam acara tersebut.', 126000, 9, '2024-12-31 06:46:35', '2024-12-31 07:37:25'),
(12, 'strawberry_cake.jpeg', 'Strawberry Pancake', 'Hidangan pancake lembut yang dipadukan dengan rasa manis dan asam dari stroberi segar. Pancake yang terbuat dari adonan tepung, telur, susu, gula, dan baking powder ini biasanya disajikan dalam tumpukan, kemudian diberi potongan stroberi segar di atasnya. Untuk menambah rasa, sering kali ditambahkan sirup stroberi, krim kocok, atau taburan gula halus. Kombinasi tekstur pancake yang empuk dan kesegaran stroberi menjadikannya pilihan sempurna untuk sarapan atau hidangan penutup.', 25000, 15, '2025-01-10 20:46:09', '2025-06-25 16:26:46');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `rating` int(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `user_id`, `customer_name`, `comment`, `rating`, `created_at`) VALUES
(1, NULL, 'Andi P', 'Saya suka sekali croissant-nya, renyah di luar lembut di dalam. Waffle-nya juga enak.', 4, '2025-06-25 16:42:29'),
(2, NULL, 'Sarah W', 'Kue Red Velvet-nya sangat lezat dan moist! Pengiriman cepat, sangat direkomendasikan.', 5, '2025-06-25 16:42:29'),
(3, NULL, 'Dewi S', 'Strawberry Mini Tart-nya cantik dan rasanya pas, tidak terlalu manis.', 5, '2025-06-25 16:43:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `email`, `phone`, `address`, `role`, `created_at`) VALUES
(1, 'Admin Toko', 'admin_bakery', '$2y$10$zKhpFNOAuxSgaD62jEso..IOXz6imGTrUOeyjwxGJj5ahPefbopIS', 'admin@bakery.com', '081234567890', 'Jalan Raya Bakery No. 1, Jakarta', 'admin', '2025-07-04 12:10:16'),
(2, 'Pengguna Biasa', 'user_roti', '$2y$10$zKhpFNOAuxSgaD62jEso..IOXz6imGTrUOeyjwxGJj5ahPefbopIS', 'user@bakery.com', '085678901234', 'Jalan Kue Manis No. 5, Bandung', 'user', '2025-07-04 12:10:16'),
(3, 'ada', 'adalah', '$2y$10$zKhpFNOAuxSgaD62jEso..IOXz6imGTrUOeyjwxGJj5ahPefbopIS', 'apalah@gmail.com', '08111111111', '-', 'user', '2025-07-04 12:11:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bakery_settings`
--
ALTER TABLE `bakery_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bakery_settings`
--
ALTER TABLE `bakery_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
