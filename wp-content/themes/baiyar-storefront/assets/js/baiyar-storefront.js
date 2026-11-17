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
        ".baiyar-trust__item, .baiyar-shop-tile, .baiyar-story",
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

  // Shop-by-category tiles and the bestseller carousel: a plain scroll-snap track (see .baiyar-
  // carousel-track in style.css) - the prev/next buttons just scroll it, they don't reimplement
  // swipe/keyboard scrolling, which the browser already does for free on the track itself.
  function wireCarousels() {
    document.querySelectorAll("[data-carousel-prev], [data-carousel-next]").forEach(function (button) {
      var id = button.getAttribute("data-carousel-prev") || button.getAttribute("data-carousel-next");
      var track = document.querySelector('[data-carousel="' + id + '"]');
      if (!track) {
        return;
      }
      var direction = button.hasAttribute("data-carousel-prev") ? -1 : 1;
      button.addEventListener("click", function () {
        track.scrollBy({
          left: direction * track.clientWidth * 0.9,
          behavior: prefersReducedMotion ? "auto" : "smooth",
        });
      });
    });
  }

  // Variable products: WooCommerce keeps the <select> per attribute as the source of truth (so its own
  // availability/price logic keeps working); we draw colour swatches / size buttons on top of them and
  // forward clicks to the select. Sold-out combinations are struck through, read from the variations JSON.
  var SWATCH = {
    "Putih Tulang": "#ECE9E1", "Biru Langit": "#8FB4D9", "Hijau Sage": "#9DB59A", "Abu Muda": "#C9CACB",
    "Terakota": "#C47A5A", "Biru Indigo": "#4A5A85", "Putih": "#F7F7F5", "Kuning": "#E8C75A", "Biru Denim": "#5A6C99",
    "Pasir": "#CBB89A", "Zaitun": "#8A8A55", "Cokelat": "#7A5A44", "Biru Baja": "#6F8FAE", "Krem": "#E4D6BC"
  };

  function wireVariationSwatches() {
    var form = document.querySelector("form.variations_form");
    if (!form) {
      return;
    }
    var syncs = [];
    var variations = [];
    try {
      variations = JSON.parse(form.getAttribute("data-product_variations") || "[]");
    } catch (e) {}

    form.querySelectorAll("table.variations select").forEach(function (select) {
      var isColor = /warna/i.test(select.name);
      var group = document.createElement("div");
      group.className = "baiyar-options " + (isColor ? "baiyar-options--color" : "baiyar-options--size");
      select.parentNode.insertBefore(group, select);
      select.classList.add("baiyar-select-hidden");

      function sync() {
        group.querySelectorAll("button").forEach(function (b) {
          b.classList.toggle("is-active", b.getAttribute("data-value") === select.value);
        });
        if (isColor && select.value) {
          // WooCommerce only swaps the photo once colour AND size are chosen; show the colour's photo right away.
          var match = variations.find(function (v) { return v.attributes.attribute_warna === select.value && v.image && v.image.src; });
          var main = document.querySelector(".woocommerce-product-gallery__image img");
          if (match && main) {
            main.src = match.image.full_src || match.image.src;
            main.removeAttribute("srcset");
            main.removeAttribute("sizes");
          }
        }
        if (!isColor) {
          var colorSelect = form.querySelector("select[name='attribute_warna']");
          var color = colorSelect ? colorSelect.value : "";
          group.querySelectorAll("button").forEach(function (b) {
            var v = variations.find(function (x) {
              return x.attributes[select.name] === b.getAttribute("data-value") && (!color || x.attributes.attribute_warna === color);
            });
            b.classList.toggle("is-soldout", !!v && !v.is_in_stock);
          });
        }
        var label = select.closest("tr").querySelector("th label");
        if (label && isColor) {
          label.setAttribute("data-current", select.value);
        }
      }

      Array.prototype.forEach.call(select.options, function (opt) {
        if (!opt.value) {
          return;
        }
        var b = document.createElement("button");
        b.type = "button";
        b.setAttribute("data-value", opt.value);
        b.title = opt.value;
        if (isColor) {
          b.className = "baiyar-swatch";
          b.style.setProperty("--swatch", SWATCH[opt.value] || "#ccc");
          b.setAttribute("aria-label", opt.value);
        } else {
          b.className = "baiyar-size";
          b.textContent = opt.value;
        }
        b.addEventListener("click", function () {
          select.value = select.value === opt.value ? "" : opt.value;
          select.dispatchEvent(new Event("change", { bubbles: true }));
        });
        group.appendChild(b);
      });

      syncs.push(sync);
      select.addEventListener("change", function () { syncs.forEach(function (fn) { fn(); }); });
      sync();
    });

    if (window.jQuery) {
      window.jQuery(form).on("reset_data", function () {
        form.querySelectorAll(".baiyar-options button").forEach(function (b) { b.classList.remove("is-active"); });
      });
    }
  }

  // Single product page: submit the add-to-cart form in the background (no reload, no green notice bar),
  // then refresh WooCommerce's cart fragments and let the existing cart drawer open on "added_to_cart".
  function wireAjaxProductForm() {
    var form = document.querySelector("body.single-product form.cart");
    if (!form || !window.jQuery) {
      return;
    }
    form.addEventListener("submit", function (event) {
      var button = form.querySelector(".single_add_to_cart_button");
      if (!button || button.classList.contains("disabled") || button.classList.contains("wc-variation-selection-needed")) {
        return;
      }
      event.preventDefault();
      var data = new FormData(form);
      if (button.name) {
        data.append(button.name, button.value);
      }
      data.append("baiyar_ajax", "1");
      button.classList.add("loading");
      fetch(window.location.pathname + window.location.search, { method: "POST", body: data, credentials: "same-origin" })
        .then(function () {
          var $ = window.jQuery;
          $(document.body).one("wc_fragments_refreshed", function () {
            button.classList.remove("loading");
            $(document.body).trigger("added_to_cart");
          });
          $(document.body).trigger("wc_fragment_refresh");
        })
        .catch(function () {
          button.classList.remove("loading");
          form.submit();
        });
    });
  }

  // Cart drawer quantity stepper: delegated, because WooCommerce replaces the drawer's inner HTML on every
  // fragment refresh. Posts the new quantity, then asks WooCommerce to refresh the fragments.
  function wireDrawerStepper() {
    document.addEventListener("click", function (event) {
      var button = event.target.closest(".baiyar-stepper button");
      if (!button || !window.jQuery) {
        return;
      }
      var stepper = button.parentNode;
      var qty = Math.max(0, parseInt(stepper.getAttribute("data-qty"), 10) + parseInt(button.getAttribute("data-step"), 10));
      var body = new URLSearchParams({ action: "baiyar_cart_qty", key: stepper.getAttribute("data-key"), qty: String(qty) });
      stepper.classList.add("is-busy");
      fetch(window.baiyarAjaxUrl || "/wp-admin/admin-ajax.php", { method: "POST", body: body, credentials: "same-origin" }).then(function () {
        window.jQuery(document.body).trigger("wc_fragment_refresh");
      });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    markRevealTargets();
    observeReveal();
    wireAddToCartPulse();
    wireCartDrawer();
    wireCarousels();
    wireVariationSwatches();
    wireAjaxProductForm();
    wireDrawerStepper();
  });
})();
