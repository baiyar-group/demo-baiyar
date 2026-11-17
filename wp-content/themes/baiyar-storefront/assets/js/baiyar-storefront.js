(function () {
  "use strict";

  var prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  document.documentElement.classList.remove("no-js");

  // Scroll-reveal: same fade-up language as apps/landing's Reveal component (opacity 0->1, y 16px->0),
  // skipped entirely for prefers-reduced-motion rather than just shortened.
  function markRevealTargets() {
    var targets = document.querySelectorAll(
      "ul.products li.product, .baiyar-bestseller-grid li.product, .wc-block-grid__product, " +
        ".woocommerce-Tabs-panel, .cart_item, .wc-block-cart-item, " +
        ".baiyar-trust__item, .baiyar-category-tile, .baiyar-story",
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

  // The cart drawer: Storefront already renders the mini-cart markup (#site-header-cart's
  // .widget_shopping_cart), just hidden off-screen and shown on :hover. style.css turns that same
  // markup into a fixed slide-out panel; this only drives when it's open, independent of WooCommerce's
  // AJAX fragment refresh (which replaces the mini-cart's *inner* HTML on every add/remove, so the
  // overlay/close button live outside that subtree instead of inside it).
  function wireCartDrawer() {
    var headerCart = document.getElementById("site-header-cart");
    if (!headerCart) {
      return;
    }

    var overlay = document.createElement("div");
    overlay.className = "baiyar-cart-overlay";
    var closeButton = document.createElement("button");
    closeButton.type = "button";
    closeButton.className = "baiyar-cart-close";
    closeButton.setAttribute("aria-label", "Tutup keranjang");
    closeButton.innerHTML = "&times;";
    document.body.appendChild(overlay);
    document.body.appendChild(closeButton);

    function open() {
      headerCart.classList.add("baiyar-cart-open");
      document.body.classList.add("baiyar-cart-drawer-open");
    }
    function close() {
      headerCart.classList.remove("baiyar-cart-open");
      document.body.classList.remove("baiyar-cart-drawer-open");
    }

    headerCart.addEventListener("click", function (event) {
      var link = event.target.closest(".cart-contents");
      if (!link) {
        return;
      }
      event.preventDefault();
      headerCart.classList.contains("baiyar-cart-open") ? close() : open();
    });
    overlay.addEventListener("click", close);
    closeButton.addEventListener("click", close);
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") {
        close();
      }
    });

    if (window.jQuery) {
      window.jQuery(document.body).on("added_to_cart", open);
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    markRevealTargets();
    observeReveal();
    wireAddToCartPulse();
    wireCartDrawer();
  });
})();
