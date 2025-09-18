import { createApp } from "vue";
import CalldisplayApp from "./CalldisplayApp.vue";
import "./styles/main.scss";
import MucPatternLabVue from "@muenchen/muc-patternlab-vue";
import customIconsSprite from "@muenchen/muc-patternlab-vue/assets/icons/custom-icons.svg?raw";
import mucIconsSprite from "@muenchen/muc-patternlab-vue/assets/icons/muc-icons.svg?raw";
import i18n from "./i18n";

// Create the app
const app = createApp(CalldisplayApp);
app.use(i18n);
app.use(MucPatternLabVue);

// Inject icon sprites into the DOM
const iconContainer = document.createElement("div");
iconContainer.style.display = "none";
iconContainer.innerHTML = mucIconsSprite + customIconsSprite;
document.body.appendChild(iconContainer);

app.mount("#app");
