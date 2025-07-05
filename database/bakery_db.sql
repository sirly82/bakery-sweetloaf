-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 05, 2025 at 11:59 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bakery_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bakery_settings`
--

CREATE TABLE `bakery_settings` (
  `id` int NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `description` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `produk_id` int NOT NULL,
  `jumlah` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `keranjang`
--

INSERT INTO `keranjang` (`id`, `user_id`, `produk_id`, `jumlah`) VALUES
(300, 33, 1, 1),
(301, 33, 6, 1),
(308, 37, 9, 2),
(309, 37, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `order_id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_ref` varchar(20) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_type` enum('cash','qris') DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `payment_status` enum('unpaid','paid','cancelled','refunded') DEFAULT 'unpaid',
  `order_status` enum('pending','on_progress','completed','cancelled') DEFAULT 'pending',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`order_id`, `user_id`, `order_ref`, `total`, `created_at`, `payment_type`, `paid_at`, `payment_status`, `order_status`, `updated_at`) VALUES
(95, 33, 'ORD1751665977', 126000.00, '2025-07-04 14:52:57', 'cash', NULL, 'cancelled', 'cancelled', '2025-07-04 23:42:53'),
(96, 33, 'ORD1751671375', 89000.00, '2025-07-04 16:22:55', 'cash', NULL, 'paid', 'completed', '2025-07-04 23:42:32'),
(97, 33, 'ORD1751691375', 89000.00, '2025-07-04 21:56:15', 'cash', '2025-07-05 12:00:30', 'paid', 'on_progress', '2025-07-05 05:00:30'),
(98, 38, 'ORD6868B67624BC4', 252000.00, '2025-07-04 22:21:58', 'qris', '2025-07-05 12:22:20', 'paid', 'completed', '2025-07-05 05:22:59'),
(99, 38, 'ORD1751710623', 30000.00, '2025-07-05 03:17:03', 'cash', '2025-07-05 17:17:22', 'paid', 'completed', '2025-07-05 10:17:29');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan_items`
--

CREATE TABLE `pesanan_items` (
  `id` int NOT NULL,
  `pesanan_id` int NOT NULL,
  `produk_id` int NOT NULL,
  `jumlah` int NOT NULL,
  `harga` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesanan_items`
--

INSERT INTO `pesanan_items` (`id`, `pesanan_id`, `produk_id`, `jumlah`, `harga`) VALUES
(1, 95, 10, 1, 126000.00),
(2, 96, 9, 1, 89000.00),
(3, 97, 9, 1, 89000.00),
(4, 98, 10, 2, 126000.00),
(5, 99, 2, 1, 12000.00),
(6, 99, 19, 1, 18000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `harga` int NOT NULL,
  `stok` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `foto`, `nama`, `deskripsi`, `harga`, `stok`, `created_at`, `updated_at`) VALUES
(1, 'croissant.jpeg', 'Croissant', 'Croissant adalah roti pastry asal Prancis yang terkenal dengan bentuk setengah bulan dan tekstur berlapis-lapis, renyah di luar dan lembut di dalam. Terbuat dari adonan ragi yang dilapisi mentega, croissant melalui proses pelipatan dan pemanggangan untuk menciptakan lapisan tipis yang ringan. Biasanya disajikan sebagai sarapan atau camilan, croissant dapat dinikmati polos atau dengan berbagai isian seperti selai, keju, atau cokelat. Keistimewaan croissant terletak pada teknik pembuatannya yang menghasilkan rasa yang gurih dan tekstur yang sangat menggugah selera.', 15000, 100, '2024-12-29 16:41:06', '2025-07-04 18:02:26'),
(2, 'mini tart.jpeg', 'Strawberry Mini Tart', 'Hidangan penutup kecil yang terdiri dari kulit kue tart renyah yang diisi dengan krim lembut, seperti custard atau cream cheese, dan dihiasi dengan potongan stroberi segar di atasnya. Rasanya yang manis dan sedikit asam dari stroberi segar berpadu sempurna dengan krim yang kaya, menciptakan keseimbangan rasa yang menyegarkan. Tart mini ini sering dijadikan camilan elegan untuk berbagai acara spesial, seperti pesta ulang tahun atau perayaan, berkat penyajiannya yang cantik dan menggugah selera.', 12000, 100, '2024-12-29 16:42:01', '2025-07-04 18:02:37'),
(3, 'waffle.jpeg', 'Waffle', 'Sejenis kue yang terbuat dari adonan tepung, telur, susu, dan mentega, yang dipanggang dalam cetakan khusus untuk menghasilkan tekstur renyah di luar dan lembut di dalam. Waffle memiliki pola grid atau jaring-jaring yang khas di permukaannya, yang memberikan karakteristik unik pada penampilannya. Biasanya, waffle disajikan dengan berbagai topping seperti sirup maple, buah-buahan segar, es krim, atau krim kocok, menjadikannya hidangan yang populer sebagai sarapan, camilan, atau makanan penutup. Variasi waffle dapat ditemukan di banyak negara, dengan perbedaan dalam adonan dan cara penyajiannya, menjadikannya makanan yang fleksibel dan disukai banyak orang di seluruh dunia.', 11500, 13, '2024-12-29 16:43:32', '2024-12-29 16:43:32'),
(4, 'strawberry_cheesecake.jpeg', 'Strawberry Cheesecake', 'Hidangan penutup yang memadukan kelembutan krim keju dengan rasa manis dan sedikit asam dari buah stroberi segar. Lapisan dasar yang terbuat dari biskuit graham yang dihancurkan memberikan tekstur renyah, sementara lapisan krim keju yang lembut dan kaya rasa melengkapi rasa keseluruhan. Untuk sentuhan akhir, stroberi segar yang dipotong diletakkan di atas cheesecake, memberikan warna cerah dan rasa yang segar. Keunikan dari Strawberry Cheesecake terletak pada keseimbangan sempurna antara keju yang creamy, rasa buah yang segar, dan dasar biskuit yang renyah, menjadikannya favorit banyak orang sebagai pencuci mulut yang memanjakan lidah.', 95000, 6, '2024-12-29 16:44:26', '2025-06-25 16:25:22'),
(5, 'white_blueberry_cupcakes.jpeg', 'White Blueberry Cupcake', 'White Blueberry Cupcake adalah sebuah cupcakes lembut dengan rasa manis dan sedikit asam, yang menggabungkan kelembutan kue vanila putih dengan topping blueberry segar. Setiap gigitan memberikan sensasi rasa buah blueberry yang juicy, berpadu dengan tekstur ringan dari adonan cupcakes yang kaya. Cupcake ini dihiasi dengan krim keju atau buttercream putih yang halus, menambah rasa gurih yang melengkapi kelezatannya. Cocok disajikan sebagai camilan manis di berbagai acara, White Blueberry Cupcake menawarkan perpaduan sempurna antara rasa klasik dan buah yang menyegarkan.', 13000, 18, '2024-12-29 16:45:10', '2025-06-25 16:25:58'),
(6, 'red velvet caake.jpeg', 'Red Velvet Cake', 'Red Velvet Cake adalah kue yang terkenal dengan warna merah cerah dan tekstur lembut yang memikat. Kue ini memiliki rasa yang kaya dengan sentuhan cokelat ringan dan aroma vanilla yang khas, sering kali dipadukan dengan lapisan krim keju atau buttercream yang lembut dan creamy. Keistimewaannya terletak pada kombinasi rasa manis dan sedikit asam yang berasal dari penggunaan buttermilk dan cuka, memberikan kelembutan dan kelembapan pada kue. Red Velvet Cake sering kali disajikan pada acara-acara spesial, seperti ulang tahun dan perayaan, karena penampilannya yang memukau dan rasanya yang lezat.', 87000, 10, '2024-12-29 16:45:55', '2024-12-29 16:45:55'),
(7, 'bagel.jpeg', 'Bagel', 'Bagel adalah sejenis roti bulat dengan lubang di tengahnya yang memiliki tekstur kenyal dan sedikit padat. Biasanya, adonan bagel direbus terlebih dahulu sebelum dipanggang, memberikan permukaan yang sedikit mengkilap dan keras. Bagel sering disajikan dengan berbagai topping, seperti krim keju, selai, atau daging asap, dan bisa diisi dengan bahan lain seperti telur, salmon, atau sayuran. Bagel berasal dari Eropa Timur, khususnya dari tradisi Yahudi, dan kini telah menjadi makanan populer di banyak negara, sering disantap sebagai sarapan atau camilan.', 9500, 13, '2024-12-29 16:46:41', '2024-12-31 06:20:38'),
(9, 'black forest cake.jpeg', 'Black Forest Cake', 'Black Forest Cake adalah kue lapis yang kaya dan lezat, terkenal dengan kombinasi rasa manis dan segar. Kue ini terbuat dari lapisan kue cokelat yang lembut dan basah, diisi dengan krim kocok yang ringan dan cerah, serta dilapisi dengan ceri hitam manis di bagian atasnya. Ceri yang digunakan seringkali merupakan ceri maraschino atau ceri yang diawetkan, memberikan kontras rasa yang menyegarkan. Kue ini juga biasanya ditaburi dengan serpihan cokelat dan hiasan krim di sekelilingnya, menciptakan tampilan yang cantik dan menggugah selera. Black Forest Cake menjadi pilihan populer di berbagai perayaan karena cita rasanya yang kaya dan tekstur yang lembut.', 89000, 12, '2024-12-29 16:49:15', '2025-06-25 16:26:19'),
(10, 'cake custom.jpeg', 'Baby Shower Custom Cake', 'Kue yang dirancang dengan indah untuk merayakan kedatangan bayi yang akan datang. Biasanya dihias dengan warna-warna pastel seperti merah muda, biru, coklat, atau kuning. Kue ini bisa dihias dengan detail lucu seperti sepatu bayi, mainan gantung, popok, atau binatang bayi. Kue ini dapat memiliki beberapa lapisan, dengan masing-masing lapisan menampilkan desain atau tema yang berbeda, sesuai dengan jenis kelamin bayi atau tema keseluruhan baby shower. Kue custom ini juga bisa dipersonalisasi dengan nama bayi, tanggal perkiraan lahir, atau pesan khusus, memberikan kenangan manis bagi tamu yang hadir dalam acara tersebut.', 126000, 9, '2024-12-31 06:46:35', '2024-12-31 07:37:25'),
(19, 'macaron.jpeg', 'Macaroni', 'Makaron adalah kudapan manis asal Prancis yang terkenal dengan tekstur renyah di luar dan lembut di dalam. Dibuat dari putih telur, gula halus, dan almond bubuk, makaron kami hadir dalam berbagai rasa dan warna menarik seperti stroberi, cokelat, lemon, dan matcha. Setiap gigitannya memberikan perpaduan rasa manis yang halus dengan isian krim atau ganache yang lezat. Cocok untuk hadiah spesial atau teman minum teh di sore hari.', 18000, 8, '2025-07-05 10:13:24', '2025-07-05 11:21:05');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `comment` text COLLATE utf8mb4_general_ci NOT NULL,
  `rating` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
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
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `role` enum('admin','user') COLLATE utf8mb4_general_ci DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `email`, `phone`, `address`, `role`, `created_at`) VALUES
(1, 'Bellarissa Revicha', 'bellarrvc', '$2y$10$RVYpNNbNlEdJR0RIUcZlL.blO3m9WvJ20HP8m57gtp5lMDU7Zn4hi', 'bellarissa@gmail.com', '085927771534', 'Jogja', 'admin', '2024-12-29 16:38:54'),
(5, 'Deandra', 'deandra', '$2y$10$sotqtfo4FPqPK2J3HuNqe.6a6BMPrE1mP4cFL7C7QaK8ebBSKo.vC', 'deandra@gmail.com', '085927771534', 'Jogja', 'user', '2025-01-10 16:25:59'),
(24, 'anayra', 'anayra', '$2y$10$R8kMxsi9Rpct/ClntQwPruhyNuj.bJwoVLWqT//Smar2vn5sOcn0a', 'anayra@gmail.com', '083250739742', 'Yogya', 'admin', '2025-04-17 01:39:17'),
(25, 'FIKRIAN', 'cappuccin', '$2y$10$6sNGhBV8Vv2yYqRFpeBpI.7pPa1Z/.d.5aumBEnsAncv6v55kFpEy', 'kafein@gmail.com', '', '', 'user', '2025-06-11 15:03:49'),
(30, 'LOKIIII', 'LOKIIII', '$2y$10$6Gx8zWtkKHi/t7jaZDYKDO5IxijxR8YhpCAlifXe5u2zOkiRHXXMm', 'LOKIIII@gmail.com', '081392779785', 'adadeh', 'user', '2025-06-11 15:15:13'),
(31, 'Fikri', 'FIKRI', '$2y$10$KamKoVprVOzZJdGgMaoXU.dSwtGHLXvg3Nw4IenQ/y.xD7sXwzbx6', 'FIKRI@gmail.com', '081392779785', 'ADA', 'admin', '2025-06-12 01:24:24'),
(33, 'Sirly Ziadatul Mustafidah', 'sirly', '$2y$10$O8AXVZ0ios1mzTVM7OeUmuTqNQkbgdzoLH3UFWa7Gv.JPW6h5SMQO', 'sirlycoba@gmail.com', '082231768604', 'Jawa Barat', 'user', '2025-07-01 13:57:01'),
(37, 'Admin', 'adminsirly', '$2y$10$BDKqderKbT2Pzn/Wb2KLgOxfQe1pDvoJG9B39eIAAqzvfUV767TwS', 'admin@gmail.com', '0988888899', 'Jayapura', 'admin', '2025-07-04 20:36:52'),
(38, 'Zaqi', 'akbar', '$2y$10$e15aha13eqrJKXe44.aaAOw04GlUxJBKyY2uqK3NC0/FkjB8JYtz.', 'zaqia@gmail.com', '085747748383', 'Pacitan', 'user', '2025-07-05 05:13:07');

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
-- Indexes for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pesanan_items`
--
ALTER TABLE `pesanan_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bakery_settings`
--
ALTER TABLE `bakery_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=312;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `order_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `pesanan_items`
--
ALTER TABLE `pesanan_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `keranjang_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pesanan_items`
--
ALTER TABLE `pesanan_items`
  ADD CONSTRAINT `pesanan_items_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`order_id`),
  ADD CONSTRAINT `pesanan_items_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
