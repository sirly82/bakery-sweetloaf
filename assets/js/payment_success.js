document.addEventListener("DOMContentLoaded", function () {
  const btn = document.getElementById("goHomeBtn");
  if (btn) {
    btn.addEventListener("click", function () {
      window.location.href = "home.php";
    });
  }
});
