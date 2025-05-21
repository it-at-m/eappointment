import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import { nextTick, ref } from "vue";

// @ts-expect-error: Vue SFC import for test
import ServiceFinder from "@/components/Appointment/ServiceFinder.vue";

describe("ServiceFinder", () => {
  const mockT = (key: string) => key;
  const mockProvider = {
    id: "1",
    name: "Test Provider",
    slotTimeInMinutes: 30,
    slots: 1,
  };

  const makeService = (subCount: number) => {
    return {
      id: "1",
      name: "Test Service",
      maxQuantity: 10,
      providers: [mockProvider],
      count: 1,
      subServices: Array(subCount)
        .fill(null)
        .map((_, index) => ({
          id: `sub-${index}`,
          name: `Sub Service ${index}`,
          maxQuantity: 5,
          providers: [mockProvider],
          count: 0,
        })),
    };
  };

  const createWrapper = (service: any) => {
    return mount(ServiceFinder, {
      props: {
        baseUrl: "http://test.com",
        preselectedServiceId: undefined,
        preselectedOfficeId: undefined,
        exclusiveLocation: undefined,
        t: mockT,
      },
      global: {
        provide: {
          selectedServiceProvider: {
            selectedService: ref(service),
            updateSelectedService: () => {},
          },
        },
        stubs: {
          SubserviceListItem: {
            template: '<li class="subservice-stub"></li>',
          },
        },
      },
    });
  };

  describe("Show All Services Button", () => {
    it("should not show button when there are 5 or fewer services", async () => {
      const wrapper = createWrapper(makeService(5));
      await nextTick();
      expect(wrapper.find(".m-button-group--secondary").exists()).toBe(false);
    });

    it("should show button when there are more than 5 services", async () => {
      const wrapper = createWrapper(makeService(6));
      await nextTick();
      const button = wrapper.find(".m-button-group--secondary");
      expect(button.exists()).toBe(true);
      expect(button.text()).toBe("Alle Leistungen anzeigen");
    });

    it("should initially show only first 3 services when there are more than 5", async () => {
      const wrapper = createWrapper(makeService(6));
      await nextTick();
      const subServiceItems = wrapper.findAll(".subservice-stub");
      expect(subServiceItems.length).toBe(3);
    });

    it("should show all services when button is clicked", async () => {
      const wrapper = createWrapper(makeService(6));
      await nextTick();
      await wrapper
        .find(".m-button-group--secondary")
        .find("button")
        .trigger("click");
      await nextTick();
      const subServiceItems = wrapper.findAll(".subservice-stub");
      expect(subServiceItems.length).toBe(6);
    });

    it("should hide button after showing all services", async () => {
      const wrapper = createWrapper(makeService(6));
      await nextTick();
      await wrapper
        .find(".m-button-group--secondary")
        .find("button")
        .trigger("click");
      await nextTick();
      expect(wrapper.find(".m-button-group--secondary").exists()).toBe(false);
    });
  });
});
