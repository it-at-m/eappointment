import fontCss from "@/assets/mde-preview-0.3.0/css/fonts.css?inline";

if (!document.querySelector("style[data-zms-mde-preview-fonts]")) {
  const style = document.createElement("style");
  style.setAttribute("data-zms-mde-preview-fonts", "0.3.0");
  style.textContent = fontCss;
  document.head.appendChild(style);
}
