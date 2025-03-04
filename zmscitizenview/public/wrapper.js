customElements.define('zms-appointment',
  class extends HTMLElement {
    constructor() {
      super();
      this.attachShadow({ mode: 'open' });
      const i18nHost = document.createElement("i18n-host");
      const wrapped = document.createElement("zms-appointment-wrapped");
      for (let attr of this.attributes) {
        wrapped.setAttribute(attr.name, attr.value);
      }
      i18nHost.appendChild(wrapped);
      this.shadowRoot.appendChild(i18nHost);
    }
  }
);
