document.addEventListener("DOMContentLoaded", function () {
    const btnQRIS = document.getElementById("finishPaymentBtn");
    const btnCash = document.getElementById("changeMethodBtn");

    // Tombol QRIS → proses AJAX
    if (btnQRIS) {
        btnQRIS.addEventListener("click", function (e) {
            e.preventDefault();

            fetch('proses_pembayaran_qris.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ finish: true })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect || 'payment_processed.php';
                } else {
                    console.error(data.message);
                    alert('Gagal memproses pembayaran. Silakan coba lagi.');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Terjadi kesalahan jaringan.');
            });
        });
    }

    // Tombol Cash → langsung redirect saja
    if (btnCash) {
        btnCash.addEventListener("click", function () {
            window.location.href = "pembayaran.php?ganti_ke=cash";
        });
    }
});
