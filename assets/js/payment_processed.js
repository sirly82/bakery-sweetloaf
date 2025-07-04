document.addEventListener("DOMContentLoaded", function () {
    const homeBtn = document.getElementById("goHomeBtn");
    if (homeBtn) {
        homeBtn.addEventListener("click", function () {
            window.location.href = "home.php";
        });
    }
});
