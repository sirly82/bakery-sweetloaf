function selectPaymentMethod(method) {
    const radio = document.getElementById(method + '-radio');
    if (radio) {
        document.getElementById('loading').style.display = 'block';
        radio.checked = true;
        document.getElementById('paymentForm').submit();
    }
}

// Animasi saat memilih metode
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', function() {
            document.querySelectorAll('.payment-method').forEach(m => {
                m.classList.remove('selected');
            });
            this.classList.add('selected');
        });
    });
});