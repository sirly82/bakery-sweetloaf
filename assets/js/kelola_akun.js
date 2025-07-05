document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");

    form.addEventListener("submit", (e) => {
        const name = form.name.value.trim();
        const email = form.email.value.trim();
        const phone = form.phone.value.trim();
        const address = form.address.value.trim();

        if (!name || !email || !phone || !address) {
            alert("Semua kolom wajib diisi kecuali password.");
            e.preventDefault();
            return;
        }

        const emailPattern = /^[^@]+@[^@]+\.[a-zA-Z]{2,}$/;
        if (!emailPattern.test(email)) {
            alert("Format email tidak valid.");
            e.preventDefault();
            return;
        }

        const phonePattern = /^[0-9]{10,15}$/;
        if (!phonePattern.test(phone)) {
            alert("Nomor telepon harus angka 10–15 digit.");
            e.preventDefault();
        }
    });
});