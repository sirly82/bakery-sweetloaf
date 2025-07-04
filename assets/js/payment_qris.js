document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("finishPaymentBtn");
    if (btn) {
        btn.addEventListener("click", function () {
            fetch('proses_pembayaran_qris.php', {
                method: 'POST'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect || 'payment_processed.php';
                } else {
                    alert(data.message || 'Gagal memproses pembayaran');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Terjadi kesalahan saat menyimpan data');
            });
        });
    }
});
