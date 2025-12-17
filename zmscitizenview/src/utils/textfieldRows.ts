import { computed, ref } from "vue";

const pivotWidth = 500;
const smallScreenTextareaRows = 11;
const bigScreenTextareaRows = 6;

const windowWidth = ref(typeof window !== "undefined" ? window.innerWidth : 0);

export const updateWindowWidth = () => {
  if (typeof window !== "undefined") {
    windowWidth.value = window.innerWidth;
  }
};

export const textfieldRows = computed(() => {
  return windowWidth.value <= pivotWidth
    ? smallScreenTextareaRows
    : bigScreenTextareaRows;
});
