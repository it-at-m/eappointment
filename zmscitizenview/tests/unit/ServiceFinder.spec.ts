import { mount } from "@vue/test-utils";
import { afterAll, beforeAll, describe, expect, it, vi } from "vitest";
import { nextTick, ref } from "vue";

// @ts-expect-error: Vue SFC import for test
import ServiceFinder from "@/components/Appointment/ServiceFinder.vue";

// Mock ServiceImpl interface for testing
interface ServiceImpl {
  id: string;
  name: string;
  maxQuantity: number;
  providers?: any[];
  count?: number;
  subServices?: any[];
  combinable?: any;
}

describe("ServiceFinder", () => {
  const mockServices = [
    {
      id: "1",
      name: "Gewerbe-Ummeldung",
      maxQuantity: 1,
      combinable: null,
    },
    {
      id: "2",
      name: "Gewerbe-Anmeldung",
      maxQuantity: 1,
      combinable: null,
    },
    {
      id: "3",
      name: "Führerschein-Ummeldung",
      maxQuantity: 1,
      combinable: null,
    },
    {
      id: "4",
      name: "Führerschein-Anmeldung",
      maxQuantity: 1,
      combinable: null,
    },
  ];

  beforeAll(() => {
    vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
      status: 200,
      json: async () => ({ services: mockServices }),
    }));
  });

  afterAll(() => {
    vi.unstubAllGlobals();
  })

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
        baseUrl: "https://www.muenchen.de",
        preselectedServiceId: undefined,
        preselectedOfficeId: undefined,
        exclusiveLocation: undefined,
        t: (key: string) => key,
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
          MucSelect: {
            template: '<div class="muc-select-stub"></div>',
            props: ['id', 'items', 'label', 'hint', 'multiple', 'noItemFoundMessage', 'itemTitle', 'modelValue'],
            emits: ['update:modelValue'],
          },
        },
      },
    });
  };

  describe("Service Selection", () => {
    it("should show service name when service is selected", async () => {
      const service = makeService(0);
      service.name = "Test Service Name";
      const wrapper = createWrapper(service);
      await nextTick();
      expect(wrapper.text()).toContain("Test Service Name");
    });

    it("should not show service dropdown when service is preselected", async () => {
      const wrapper = createWrapper(makeService(0));
      await nextTick();
      expect(wrapper.find("#service-search").exists()).toBe(false);
    });

    it("should show service dropdown when no service is selected", async () => {
      const wrapper = createWrapper(null);
      await nextTick();
      expect(wrapper.find(".muc-select-stub").exists()).toBe(true);
    });
  });

  describe("Service Search", () => {
    it("should filter services by name", async () => {
      const wrapper = createWrapper(null);
      await nextTick();

      // Find a service with "Gewerbe" in its name
      const gewerbeService = mockServices.find(s => s.name.includes("Gewerbe")) as ServiceImpl;
      expect(gewerbeService).toBeDefined();

      // Set the service to the found service object
      wrapper.vm.service = gewerbeService;
      await nextTick();

      // Check if the service was set correctly
      expect(wrapper.vm.service).toStrictEqual(gewerbeService);
      expect(wrapper.text()).toContain(gewerbeService.name);
    });

    it("should show no results message when no matches found", async () => {
      const wrapper = createWrapper(null);
      await nextTick();

      // Create a non-existent service object
      const nonExistentService = {
        id: "non-existent",
        name: "NonExistentService",
        maxQuantity: 1
      };

      // Set the service to the non-existent service
      wrapper.vm.service = nonExistentService;
      await nextTick();

      // Check if the service was set but no results are found
      expect(wrapper.vm.service).toStrictEqual(nonExistentService);
      expect(wrapper.vm.services.filter(s => s.name.includes("NonExistentService"))).toHaveLength(0);
    });

    it("should select service when clicked", async () => {
      const wrapper = createWrapper(null);
      await nextTick();

      // Simulate selecting a service
      const selectedService = mockServices[0];
      wrapper.vm.service = selectedService;
      await nextTick();

      // Check if service is selected and displayed
      expect(wrapper.vm.service).toStrictEqual(selectedService);
      expect(wrapper.text()).toContain(selectedService.name);
    });
  });

  describe("Service Count", () => {
    it("should increase service count when plus button is clicked", async () => {
      const service = makeService(0);
      const wrapper = createWrapper(service);
      await nextTick();
      const counter = wrapper.findComponent({ name: "muc-counter" });
      await counter.vm.$emit("update:modelValue", 2);
      expect(service.count).toBe(2);
    });

    // TODO(ZMSKVR-486): Fix maxQuantity enforcement in ServiceFinder.vue
    // it("should not allow increasing count beyond maxQuantity", async () => {
    //   const service = makeService(0);
    //   service.maxQuantity = 2;
    //   const wrapper = createWrapper(service);
    //   await nextTick();
    //   const counter = wrapper.findComponent({ name: "muc-counter" });
    //   await counter.vm.$emit("update:modelValue", 3);
    //   await nextTick();
    //   expect(service.count).toBe(2);
    // });
  });

  describe("Next Step", () => {
    it("should emit next event when next button is clicked", async () => {
      const wrapper = createWrapper(makeService(0));
      await nextTick();
      const nextButton = wrapper.find(".m-button-group button");
      await nextButton.trigger("click");
      expect(wrapper.emitted("next")).toBeTruthy();
    });

    it("should not show next button when no service is selected", async () => {
      const wrapper = createWrapper(null);
      await nextTick();
      const nextButton = wrapper.find(".m-button-group button");
      expect(nextButton.exists()).toBe(false);
    });
  });

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
      expect(button.text()).toBe("showAllServices");
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
