Shipping plugins for WooCommerce are often buggy, unintuitive, inaccurate, or limited in significant ways. WooCommerce Versatile Shipping was made to combine the functionality of dynamic carrier rate generators with fixed rate and bulk rate options.

Versatile Shipping solves a number of pain points including:

- Solves: Inaccurate carrier shipping rates
- Solves: Can't combine carrier rates with fixed custom rates or other rate types.
- Solves: Buggy inconsistent performance due to bugged code or API calls.

![Screenshot_1280](https://github.com/mdoulos/woocommerce-versatile-shipping/assets/25509977/e163b632-16ab-48b0-8351-c484fe5307ef)
![Screenshot_1277](https://github.com/mdoulos/woocommerce-versatile-shipping/assets/25509977/f7c1da80-6c3e-4617-a650-dd4644ed5d44)

Features:

- Calculates accurate carrier rates. Testing found that some carrier rates are even more accurate than official carrier plugins.
- Supports all product sizes, from t-shirts to equipment shipped on a dedicated trailer.
- Can specify custom rates, quantity based rates, free shipping for certain products, etc.
- Each product can be set to Free Shipping for that product only or the whole cart (if that product is in the cart).
- Each product can be set to disable shipping altogether.
- A custom rate for each product can be specified per shipping zone.
- Free shipping can be set if a certain quantity of the product is in the cart.
- A custom rate can apply for every 1 product, combined for all instances of the product, or set to x amount of the product.
- A maximum rate can be applied if the rate is quantity based.

Setting up Versatile Shipping requires inputting the values of your carrier's rate plan and some understanding of how the carrier rates are calculated. This is because the rates are calculated locally with the plugin rather than making an API call to the carrier.


![Screenshot_1278](https://github.com/mdoulos/woocommerce-versatile-shipping/assets/25509977/4933a68f-470c-464f-9cf4-6c272bded882)
![Screenshot_1279](https://github.com/mdoulos/woocommerce-versatile-shipping/assets/25509977/b76049cf-340e-4990-a0e4-67e78467ce7b)
