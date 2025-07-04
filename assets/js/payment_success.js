document.addEventListener("DOMContentLoaded", function () {
  // Tombol Home
  const homeBtn = document.getElementById("goHomeBtn");
  if (homeBtn) {
    homeBtn.addEventListener("click", function () {
      window.location.href = "home.php";
    });
  }

  // Tombol Cetak Struk
  const cetakBtn = document.getElementById("goCetakBtn");
  if (cetakBtn) {
    cetakBtn.addEventListener("click", function () {
      // Kirim data keranjang ke server sebelum redirect
      fetch('prepare_for_print.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ action: 'prepare_print' })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          window.location.href = "cetak-struk.php";
        } else {
          alert('Gagal mempersiapkan struk: ' + (data.message || 'Keranjang kosong'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mempersiapkan struk');
      });
    });
  }
});