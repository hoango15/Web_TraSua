document.addEventListener("DOMContentLoaded", () => {
  const yearElement = document.querySelector(".current-year");
  if (yearElement) {
    yearElement.textContent = new Date().getFullYear();
  }
});

function addToCart(productId, quantity = 1) {
  fetch("add_to_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "product_id=" + productId + "&quantity=" + quantity,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const cartCount = document.getElementById("cart-count");
        if (cartCount) {
          cartCount.textContent = data.cart_count;
        }
        alert("Product added to cart!");
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}