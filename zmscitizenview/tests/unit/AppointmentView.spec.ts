import {describe, expect, it} from "vitest";

describe('this', () => {
  it('is a test', () => {
    expect(1).toEqual(1);
  });
});

// import { shallowMount} from '@vue/test-utils';
// import { describe, it, expect, beforeEach } from 'vitest';
// import AppointmentView from './../../src/components/Appointment/AppointmentView.vue';
// import { createI18n } from "vue-i18n";
// import deDE from "./../../src/utils/de-DE.json";
// import enUS from "./../../src/utils/de-DE.json";
//
// const i18n = createI18n({
//   legacy: false,
//   locale: "de-DE",
//   messages: {
//     "de-DE": deDE,
//     "en-US": enUS,
//   },
// });
//
// describe('AppointmentView', () => {
//   let wrapper;
//   beforeEach(() => {
//     wrapper = shallowMount(AppointmentView, {
//       props: {
//         baseUrl: '',
//         t: i18n,
//       },
//     });
//   });
//
//   describe('Rendering', () => {
//     it('renders the component correctly', () => {
//       expect(wrapper.exists()).toBe(true);
//       expect(wrapper.findComponent({ name: 'muc-stepper' }).exists()).toBe(true);
//     });
//     it('displays the Service Finder when currentView is 0', async () => {
//       await wrapper.setData({ currentView: 0 });
//       expect(wrapper.findComponent({ name: 'service-finder' }).exists()).toBe(true);
//     });
//   });
//
// });
//
