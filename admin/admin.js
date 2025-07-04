// admin/admin.js

document.addEventListener('DOMContentLoaded', function() {
    // Fungsionalitas Toggle Sidebar
    const menuToggle = document.getElementById('menuToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    const adminMainContent = document.querySelector('.admin-main-content'); // Selector untuk area konten utama
    const wrapper = document.querySelector('.wrapper'); // Wrapper utama yang menampung semua

    // Pastikan elemen-elemen ada sebelum menambahkan event listener
    if (menuToggle && adminSidebar) {
        menuToggle.addEventListener('click', () => {
            adminSidebar.classList.toggle('active');
            // Toggle kelas pada main-content untuk menyesuaikan tata letak
            if (adminMainContent) {
                adminMainContent.classList.toggle('sidebar-active');
            }
            if (wrapper) {
                wrapper.classList.toggle('sidebar-active'); // Sesuaikan wrapper juga
            }
        });

        // Opsional: Logika untuk menutup sidebar ketika mengklik di luar sidebar
        // Jika Anda ingin ini aktif, hapus komentar pada bagian berikut:
        /*
        if (adminMainContent) {
            adminMainContent.addEventListener('click', (event) => {
                // Periksa apakah klik terjadi di luar sidebar dan sidebar sedang aktif
                if (!adminSidebar.contains(event.target) && adminSidebar.classList.contains('active') && !menuToggle.contains(event.target)) {
                    adminSidebar.classList.remove('active');
                    adminMainContent.classList.remove('sidebar-active');
                    if (wrapper) {
                        wrapper.classList.remove('sidebar-active');
                    }
                }
            });
        }
        */
    }

    // --- Fungsionalitas Kasir (POS) ---

    const productSearchInput = document.getElementById('productSearch');
    const productGrid = document.querySelector('.product-grid');
    const cartItemsContainer = document.getElementById('cartItems');
    const subtotalAmountSpan = document.getElementById('subtotalAmount');
    const grandTotalAmountSpan = document.getElementById('grandTotalAmount');
    const finalizeOrderBtn = document.getElementById('finalizeOrderBtn');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const customerNameInput = document.getElementById('customerName');
    const orderNotesTextarea = document.getElementById('orderNotes');

    let cart = {}; // Objek untuk menyimpan item di keranjang

    // Fungsi untuk menampilkan pesan alert (jika belum ada, tambahkan ini)
    function showAlert(message, type = 'info') {
        const alertContainer = document.querySelector('.admin-main-content'); // Atau buat div khusus di HTML
        if (!alertContainer) return;

        const alertBox = document.createElement('div');
        alertBox.className = `alert alert-${type}`;
        alertBox.textContent = message;

        // Hapus alert sebelumnya jika ada
        const existingAlert = alertContainer.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        alertContainer.prepend(alertBox); // Tambahkan di bagian atas konten utama

        setTimeout(() => {
            alertBox.remove();
        }, 4000); // Alert akan hilang setelah 4 detik
    }

    // Tampilkan pesan PHP awal jika ada
    if (typeof initialMessage !== 'undefined' && initialMessage.length > 0) {
        showAlert(initialMessage, initialMessageType);
    }


    // --- Fungsi Bantuan AJAX ---
    async function sendAjaxRequest(action, data) {
        const formData = new FormData();
        formData.append('action', action);
        for (const key in data) {
            formData.append(key, data[key]);
        }

        try {
            const response = await fetch('cashier.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // Memberi tahu server ini adalah permintaan AJAX
                },
                body: formData
            });
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Error during AJAX request:', error);
            showAlert('Terjadi kesalahan komunikasi dengan server.', 'danger');
            return { success: false, message: 'Kesalahan jaringan.' };
        }
    }

    // --- Fungsi Render Keranjang ---
    function renderCart() {
        cartItemsContainer.innerHTML = ''; // Bersihkan tampilan keranjang

        let subtotal = 0;
        let hasItems = false;

        for (const productId in cart) {
            if (cart.hasOwnProperty(productId)) {
                const item = cart[productId];
                const itemTotal = item.qty * item.harga;
                subtotal += itemTotal;
                hasItems = true;

                const cartItemDiv = document.createElement('div');
                cartItemDiv.className = 'cart-item';
                cartItemDiv.innerHTML = `
                    <div class="item-info">
                        <h4>${item.nama}</h4>
                        <p>Rp ${new Intl.NumberFormat('id-ID').format(item.harga)} x ${item.qty}</p>
                    </div>
                    <div class="item-controls">
                        <div class="qty-control">
                            <button class="btn-qty-minus" data-id="${item.id}">-</button>
                            <input type="number" class="item-qty-input" data-id="${item.id}" value="${item.qty}" min="1" max="${item.stok_tersedia}">
                            <button class="btn-qty-plus" data-id="${item.id}">+</button>
                        </div>
                        <span class="item-price">Rp ${new Intl.NumberFormat('id-ID').format(itemTotal)}</span>
                        <button class="btn-remove-item" data-id="${item.id}"><i class="fas fa-times"></i></button>
                    </div>
                `;
                cartItemsContainer.appendChild(cartItemDiv);
            }
        }

        // Tampilkan pesan keranjang kosong jika tidak ada item
        if (!hasItems) {
            cartItemsContainer.innerHTML = '<p class="empty-cart-message">Keranjang masih kosong.</p>';
            finalizeOrderBtn.disabled = true; // Nonaktifkan tombol pesan
            clearCartBtn.disabled = true;
        } else {
            finalizeOrderBtn.disabled = false;
            clearCartBtn.disabled = false;
        }

        // Update total
        subtotalAmountSpan.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(subtotal)}`;
        grandTotalAmountSpan.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(subtotal)}`;
    }

    // --- Inisialisasi Keranjang dari Sesi (saat halaman dimuat) ---
    async function initializeCart() {
        const response = await sendAjaxRequest('get_cart', {});
        if (response.success && response.cart) {
            cart = response.cart;
        } else {
            console.error("Gagal mengambil keranjang dari sesi:", response.message);
        }
        renderCart();
    }
    initializeCart(); // Panggil saat DOMContentLoaded


    // --- Event Listener: Tambah Produk ke Keranjang ---
    if (productGrid) {
        productGrid.addEventListener('click', async (event) => {
            const addButton = event.target.closest('.btn-add-to-cart');
            if (addButton && !addButton.disabled) {
                const productId = addButton.dataset.id;
                const productCard = addButton.closest('.product-card');
                const productName = productCard.dataset.nama;
                const productPrice = parseFloat(productCard.dataset.harga);
                const productStock = parseInt(productCard.dataset.stok);

                // Cek kuantitas saat ini di keranjang (lokal)
                const currentQtyInCart = cart[productId] ? cart[productId].qty : 0;

                if ((currentQtyInCart + 1) <= productStock) {
                    const response = await sendAjaxRequest('add_to_cart', {
                        product_id: productId,
                        qty: 1
                    });
                    if (response.success) {
                        cart[productId] = {
                            id: productId,
                            nama: productName,
                            harga: productPrice,
                            qty: currentQtyInCart + 1,
                            stok_tersedia: productStock // Simpan stok asli untuk validasi di JS
                        };
                        renderCart();
                        showAlert(response.message, 'success');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                } else {
                    showAlert('Stok ' + productName + ' tidak cukup!', 'danger');
                }
            }
        });
    }

    // --- Event Listener: Ubah Kuantitas atau Hapus Item dari Keranjang ---
    if (cartItemsContainer) {
        cartItemsContainer.addEventListener('click', async (event) => {
            const target = event.target;
            const productId = target.dataset.id;

            if (!productId || !cart[productId]) return;

            let newQty;
            const item = cart[productId];

            if (target.classList.contains('btn-qty-minus')) {
                newQty = item.qty - 1;
            } else if (target.classList.contains('btn-qty-plus')) {
                newQty = item.qty + 1;
            } else if (target.classList.contains('btn-remove-item')) {
                newQty = 0; // Setel ke 0 untuk menghapus
            } else {
                return; // Bukan tombol yang relevan
            }

            if (newQty > item.stok_tersedia) {
                showAlert('Kuantitas melebihi stok yang tersedia (' + item.stok_tersedia + ').', 'danger');
                return;
            }

            const response = await sendAjaxRequest('update_cart_qty', {
                product_id: productId,
                new_qty: newQty
            });

            if (response.success) {
                if (newQty <= 0) {
                    delete cart[productId];
                } else {
                    cart[productId].qty = newQty;
                }
                renderCart();
                showAlert(response.message, 'success');
            } else {
                showAlert(response.message, 'danger');
            }
        });

        // Tangani perubahan langsung pada input kuantitas
        cartItemsContainer.addEventListener('change', async (event) => {
            const inputElement = event.target;
            if (inputElement.classList.contains('item-qty-input')) {
                const productId = inputElement.dataset.id;
                let newQty = parseInt(inputElement.value);

                if (isNaN(newQty) || newQty < 0) {
                    newQty = 1; // Default ke 1 jika input tidak valid
                    inputElement.value = newQty;
                }

                if (!cart[productId]) return; // Jika entah kenapa produk tidak ada di keranjang lokal

                const item = cart[productId];
                if (newQty > item.stok_tersedia) {
                    showAlert('Kuantitas melebihi stok yang tersedia (' + item.stok_tersedia + ').', 'danger');
                    newQty = item.stok_tersedia; // Sesuaikan dengan stok maksimal
                    inputElement.value = newQty;
                }

                const response = await sendAjaxRequest('update_cart_qty', {
                    product_id: productId,
                    new_qty: newQty
                });

                if (response.success) {
                    if (newQty <= 0) {
                        delete cart[productId];
                    } else {
                        cart[productId].qty = newQty;
                    }
                    renderCart();
                    showAlert(response.message, 'success');
                } else {
                    showAlert(response.message, 'danger');
                }
            }
        });
    }

    // --- Event Listener: Selesaikan Pesanan ---
    if (finalizeOrderBtn) {
        finalizeOrderBtn.addEventListener('click', async () => {
            if (Object.keys(cart).length === 0) {
                showAlert('Keranjang kosong. Tambahkan produk terlebih dahulu.', 'danger');
                return;
            }

            const customerName = customerNameInput.value.trim() || 'Pembeli Langsung';
            const orderNotes = orderNotesTextarea.value.trim();
            const grandTotal = parseFloat(grandTotalAmountSpan.textContent.replace(/[^0-9,-]+/g, "").replace(",", ".")); // Parse "Rp 100.000" to 100000

            const response = await sendAjaxRequest('finalize_order', {
                customer_name: customerName,
                notes: orderNotes,
                grand_total: grandTotal
            });

            if (response.success) {
                showAlert(response.message, 'success');
                cart = {}; // Kosongkan keranjang di sisi client
                renderCart(); // Render ulang keranjang kosong
                customerNameInput.value = ''; // Bersihkan input
                orderNotesTextarea.value = '';
                // Opsional: refresh daftar produk untuk update stok jika diperlukan
                setTimeout(() => {
                    location.reload(); // Refresh setelah alert muncul
                }, 1000);
            } else {
                showAlert(response.message, 'danger');
            }
        });
    }

    // --- Event Listener: Bersihkan Keranjang ---
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', async () => {
            const confirmClear = confirm('Anda yakin ingin mengosongkan keranjang?');
            if (confirmClear) {
                const response = await sendAjaxRequest('update_cart_qty', {
                    product_id: 'all', // Penanda untuk membersihkan semua
                    new_qty: 0
                });

                if (response.success) {
                    cart = {}; // Kosongkan keranjang di sisi client
                    renderCart(); // Render ulang keranjang kosong
                    showAlert('Keranjang berhasil dikosongkan.', 'info');
                } else {
                    showAlert(response.message, 'danger');
                }
            }
        });
    }

    // --- Fungsionalitas Pencarian Produk ---
    if (productSearchInput) {
        productSearchInput.addEventListener('keyup', () => {
            const searchTerm = productSearchInput.value.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');

            productCards.forEach(card => {
                const productName = card.dataset.nama.toLowerCase();
                if (productName.includes(searchTerm)) {
                    card.style.display = 'flex'; // Tampilkan kartu
                } else {
                    card.style.display = 'none'; // Sembunyikan kartu
                }
            });
        });
    }

}); // Penutup DOMContentLoaded