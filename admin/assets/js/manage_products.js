document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("productModal");
    const closeButton = document.querySelector(".close-button");
    const addProductBtn = document.getElementById("addProductBtn");
    const productForm = document.getElementById("productForm");

    // Buka modal kosong saat klik "Tambah Produk"
    addProductBtn.addEventListener("click", function () {
        productForm.reset();
        productForm.product_id.value = "";
        document.getElementById("fotoPreview").innerHTML = "";
        modal.style.display = "block";
    });

    // Tutup modal
    closeButton.addEventListener("click", function () {
        modal.style.display = "none";
    });

    // Tutup modal jika klik di luar konten
    window.addEventListener("click", function (e) {
        if (e.target == modal) {
            modal.style.display = "none";
        }
    });

    // Isi form ketika tombol edit diklik
    document.querySelectorAll(".btn-edit").forEach(function (button) {
        button.addEventListener("click", function () {
            const id = this.dataset.id;
            const nama = this.dataset.nama;
            const deskripsi = this.dataset.deskripsi;
            const harga = this.dataset.harga;
            const stok = this.dataset.stok;
            const foto = this.dataset.foto;
            // const basePath = window.location.origin + "/blom_fix/bakery-sweetloaf/admin/assets/uploads/products/";
            // const basePath = "../../assets/uploads/products/";

            productForm.product_id.value = id;
            productForm.nama.value = nama;
            productForm.deskripsi.value = deskripsi;
            productForm.harga.value = harga;
            productForm.stok.value = stok;
            productForm.current_foto.value = foto;


            const basePath = "assets/uploads/products/";

            if (foto && foto !== "NULL") {
                document.getElementById("fotoPreview").innerHTML = `
                    <p>Foto saat ini:</p>
                    <img src="${basePath + encodeURIComponent(foto)}" alt="Preview" style="max-width:150px; max-height:150px;">
                `;
            } else {
                document.getElementById("fotoPreview").innerHTML = "<em>Tidak ada foto.</em>";
            }

            modal.style.display = "block";
        });
    });
});