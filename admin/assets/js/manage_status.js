console.log("manage_status.js loaded"); // Tambahkan ini

function updateStatus(id, field, value) {
  console.log("DEBUG UPDATE", { id, field, value });

  fetch('update_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${encodeURIComponent(id)}&${field}=${encodeURIComponent(value)}`
  })
  .then(async res => {
    const text = await res.text();
    console.log("Status:", res.status);
    console.log("Response Text:", text);
    if (!res.ok) throw new Error(text);
  })
  .catch(err => {
    console.error("ERROR:", err);
    alert("Gagal menyimpan: " + err.message);
  });
}

document.querySelectorAll('.status-pembayaran').forEach(select => {
  select.addEventListener('change', function () {
    updateStatus(this.dataset.id, 'payment_status', this.value);
  });
});

document.querySelectorAll('.status-pesanan').forEach(select => {
  select.addEventListener('change', function () {
    updateStatus(this.dataset.id, 'order_status', this.value);
  });
});
