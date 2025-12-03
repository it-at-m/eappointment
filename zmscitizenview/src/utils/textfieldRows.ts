import { computed, ref } from "vue";

const windowWidth = ref(typeof window !== "undefined" ? window.innerWidth : 0);

export const updateWindowWidth = () => {
  if(typeof window !== "undefined") {
    windowWidth.value = window.innerWidth;
  }
};

export const textfieldRows = computed(() => {
  return windowWidth.value <= 500 ? 6 : 3;
});
