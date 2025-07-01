// Fungsi untuk menambahkan produk ke keranjang
function setupAddToCartButtons() {
    document.querySelectorAll('.btn-add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if(data.success) {
                    // Update counter keranjang (jika ada)
                    updateCartCounter(data.cart_count || 0);
                    alert('Produk berhasil ditambahkan ke keranjang!');
                } else {
                    alert('Gagal menambahkan produk: ' + (data.message || 'Error tidak diketahui'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan: ' + error.message);
            });
        });
    });
}

// Fungsi untuk update counter keranjang (opsional)
function updateCartCounter(count) {
    const counterElement = document.querySelector('.cart-counter');
    if (counterElement) {
        counterElement.textContent = count;
    }
}

// Jalankan saat DOM siap
document.addEventListener('DOMContentLoaded', function() {
    setupAddToCartButtons();
});