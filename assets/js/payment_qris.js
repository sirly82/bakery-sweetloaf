document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("finishPaymentBtn");
    if (btn) {
        btn.addEventListener("click", function () {
            window.location.href = "payment_success.php";
        });
    }
});