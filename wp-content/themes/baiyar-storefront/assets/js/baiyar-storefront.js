(function () {
  "use strict";

  var prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  document.documentElement.classList.remove("no-js");

  // Scroll-reveal: same fade-up language as apps/landing's Reveal component (opacity 0->1, y 16px->0),
  // skipped entirely for prefers-reduced-motion rather than just shortened.
  function markRevealTargets() {
    var targets = document.querySelectorAll(
      "ul.products li.product, .wc-block-grid__product, .woocommerce-Tabs-panel, .cart_item, .wc-block-cart-item",
    );
    targets.forEach(function (el, index) {
      if (prefersReducedMotion) {
        return;
      }
      el.classList.add("baiyar-reveal");
      el.style.transitionDelay = Math.min(index * 60, 300) + "ms";
    });
  }

  function observeReveal() {
    if (prefersReducedMotion || !("IntersectionObserver" in window)) {
      document.querySelectorAll(".baiyar-reveal").forEach(function (el) {
        el.classList.add("baiyar-revealed");
      });
      return;
    }
    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("baiyar-revealed");
            observer.unobserve(entry.target);
          }
        });
      },
      { rootMargin: "-60px" },
    );
    document.querySelectorAll(".baiyar-reveal").forEach(function (el) {
      observer.observe(el);
    });
  }

  // Add-to-cart success pulse on the button that was clicked, and a bump on the header cart icon
  // once WooCommerce's own AJAX fragment refresh confirms the cart changed.
  function wireAddToCartPulse() {
    document.body.addEventListener("click", function (event) {
      var button = event.target.closest(".add_to_cart_button, button[name='add-to-cart']");
      if (!button || prefersReducedMotion) {
        return;
      }
      button.classList.remove("baiyar-added-pulse");
      // restart animation even on repeated clicks
      void button.offsetWidth;
      button.classList.add("baiyar-added-pulse");
    });

    if (window.jQuery) {
      window.jQuery(document.body).on("added_to_cart", function () {
        if (prefersReducedMotion) {
          return;
        }
        var cartIcons = document.querySelectorAll(".site-header-cart, .cart-contents, .wc-block-mini-cart");
        cartIcons.forEach(function (el) {
          el.classList.remove("baiyar-cart-bump");
          void el.offsetWidth;
          el.classList.add("baiyar-cart-bump");
        });
      });
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    markRevealTargets();
    observeReveal();
    wireAddToCartPulse();
  });
})();
