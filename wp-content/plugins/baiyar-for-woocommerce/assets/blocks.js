(function () {
  const settings = window.wc.wcSettings.getSetting("baiyar_data", {});
  const label = settings.title || "Baiyar";
  const el = window.wp.element.createElement;

  const Content = () => el("div", {}, settings.description || "");

  window.wc.wcBlocksRegistry.registerPaymentMethod({
    name: "baiyar",
    label: label,
    content: el(Content),
    edit: el(Content),
    canMakePayment: () => true,
    ariaLabel: label,
    supports: { features: settings.supports || ["products"] },
  });
})();
