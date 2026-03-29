import { mount } from "@vue/test-utils";
import { describe, it, expect } from "vitest";
import AvailabilityInfoModal from "@/components/Appointment/AppointmentSelection/AvailabilityInfoModal.vue";

const createWrapper = (props = {}) =>
  mount(AvailabilityInfoModal, {
    props: {
      open: false,
      html: "",
      t: (key: string) => key,
      ...props,
    },
    global: {
      stubs: {
        MucModal: {
          props: ["open"],
          emits: ["close"],
          template: `
            <div v-if="open" class="muc-modal">
              <slot name="title"/>
              <slot name="body"/>
              <slot name="footer"/>
              <button class="close" @click="$emit('close')" />
            </div>
          `,
        },
      },
    },
  });

describe("AvailabilityInfoModal", () => {
  it("renders when open", () => {
    const wrapper = createWrapper({ open: true });

    expect(wrapper.find(".muc-modal").exists()).toBe(true);
  });

  it("does not render when closed", () => {
    const wrapper = createWrapper({ open: false });

    expect(wrapper.find(".muc-modal").exists()).toBe(false);
  });

  it("renders translated title", () => {
    const wrapper = createWrapper({ open: true });

    expect(wrapper.text()).toContain("newAppointmentsInfoLink");
  });

  it("renders provided HTML", () => {
    const html = "<p>Test <strong>content</strong></p>";
    const wrapper = createWrapper({ open: true, html });

    expect(wrapper.html()).toContain(html);
  });

  it("emits update:open false when modal emits close", async () => {
    const wrapper = createWrapper({ open: true });

    await wrapper.find(".close").trigger("click");

    expect(wrapper.emitted("update:open")).toEqual([[false]]);
  });

  it("handles nested HTML content", () => {
    const html = "<div><p><strong>Nested</strong> content</p></div>";
    const wrapper = createWrapper({ open: true, html });

    expect(wrapper.text()).toContain("Nested content");
  });

  it("handles long HTML content", () => {
    const html = `<p>${"A".repeat(1000)}</p>`;
    const wrapper = createWrapper({ open: true, html });

    expect(wrapper.text()).toContain("A".repeat(10));
  });
});
