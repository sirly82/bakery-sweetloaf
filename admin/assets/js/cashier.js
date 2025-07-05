$('#finalizeOrderBtn').on('click', function () {
    const customerName = $('#customerName').val().trim();
    const notes = $('#orderNotes').val().trim();
    const grandTotalText = $('#grandTotalAmount').text().replace(/[Rp\\.\\s]/g, '');
    const grandTotal = parseFloat(grandTotalText) || 0;

    console.log("Total akhir:", grandTotal);

    if (grandTotal <= 0) {
        alert('Keranjang kosong atau total tidak valid.');
        return;
    }

    if (!confirm('Yakin ingin menyelesaikan pesanan ini?')) return;

    $('#finalizeOrderBtn').prop('disabled', true); // Hindari klik ganda

    $.post('cashier.php', {
        action: 'finalize_order',
        customer_name: customerName,
        notes: notes,
        grand_total: grandTotal
    }, function (res) {
        console.log("Response dari server:", res);

        if (res.success) {
            alert(res.message + '\nNomor Pesanan: ' + (res.order_ref || ''));
            window.location.href = res.redirect;
        } else {
            alert('Gagal: ' + res.message);
        }
    }, 'json').fail(function (xhr, status, error) {
        console.error('AJAX Error:', status, error);
        console.error('Server response:', xhr.responseText);
        alert('Terjadi kesalahan saat mengirim data ke server.\n' + xhr.responseText);
        $('#finalizeOrderBtn').prop('disabled', false); // Aktifkan ulang
    });
});
