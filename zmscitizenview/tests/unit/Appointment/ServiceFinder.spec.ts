import { mount } from "@vue/test-utils";
import { afterAll, beforeAll, describe, expect, it, vi } from "vitest";
import { nextTick, ref } from "vue";

// @ts-expect-error: Vue SFC import for test
import ServiceFinder from "@/components/Appointment/ServiceFinder.vue";

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

  const mockOffices = [
    {
      id: "1",
      name: "Rathaus Marienplatz",
      address: "Marienplatz 8, 80331 München",
    },
    {
      id: "2",
      name: "Bürgerbüro Pasing",
      address: "Landsberger Str. 486, 81241 München",
    },
  ];

  const mockRelations = [
    {
      officeId: "1",
      serviceId: "1",
      slots: 1,
      public: true,
      maxQuantity: 4
    },
    {
      officeId: "1",
      serviceId: "2",
      slots: 1,
      public: true,
      maxQuantity: 4
    },
    {
      officeId: "2",
      serviceId: "3",
      slots: 1,
      public: true,
      maxQuantity: 4
    }
  ];

  beforeAll(() => {
    vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
      status: 200,
      json: async () => ({
        services: mockServices,
        offices: mockOffices,
        relations: mockRelations
      }),
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

  const createWrapper = (service: any, additionalProps: any = {}) => {
    return mount(ServiceFinder, {
      props: {
        baseUrl: "https://www.muenchen.de",
        preselectedServiceId: undefined,
        preselectedOfficeId: undefined,
        exclusiveLocation: undefined,
        t: (key: string) => key,
        ...additionalProps,
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

      const gewerbeService = mockServices.find(s => s.name.includes("Gewerbe")) as ServiceImpl;
      expect(gewerbeService).toBeDefined();

      wrapper.vm.service = gewerbeService;
      await nextTick();

      expect(wrapper.vm.service).toStrictEqual(gewerbeService);
      expect(wrapper.text()).toContain(gewerbeService.name);
    });

    it("should show no results message when no matches found", async () => {
      const wrapper = createWrapper(null);
      await nextTick();

      const nonExistentService = {
        id: "non-existent",
        name: "NonExistentService",
        maxQuantity: 1
      };

      wrapper.vm.service = nonExistentService;
      await nextTick();

      expect(wrapper.vm.service).toStrictEqual(nonExistentService);
      expect(wrapper.vm.services.filter(s => s.name.includes("NonExistentService"))).toHaveLength(0);
    });

    it("should select service when clicked", async () => {
      const wrapper = createWrapper(null);
      await nextTick();

      const selectedService = mockServices[0];
      wrapper.vm.service = selectedService;
      await nextTick();

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

  describe("Invalid Jump-in Link Detection", () => {
    it("should emit invalidJumpinLink when preselected service is not found", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: mockServices,
          relations: [],
          offices: []
        }),
      }));

      const wrapper = mount(ServiceFinder, {
        props: {
          baseUrl: "https://www.muenchen.de",
          preselectedServiceId: "999999999999",
          preselectedOfficeId: undefined,
          exclusiveLocation: undefined,
          t: (key: string) => key,
        },
        global: {
          provide: {
            selectedServiceProvider: {
              selectedService: ref(null),
              updateSelectedService: () => {},
            },
          },
          stubs: {
            "muc-select": true,
            "muc-counter": true,
            "muc-button": true,
            "altcha-captcha": true,
          },
        },
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 0));

      expect(wrapper.emitted("invalidJumpinLink")).toBeTruthy();
    });

    it("should emit invalidJumpinLink when API returns empty services for preselected ID", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: [],
          relations: [],
          offices: []
        }),
      }));

      const wrapper = mount(ServiceFinder, {
        props: {
          baseUrl: "https://www.muenchen.de",
          preselectedServiceId: "000000000000",
          preselectedOfficeId: "000000000000",
          exclusiveLocation: undefined,
          t: (key: string) => key,
        },
        global: {
          provide: {
            selectedServiceProvider: {
              selectedService: ref(null),
              updateSelectedService: () => {},
            },
          },
          stubs: {
            "muc-select": true,
            "muc-counter": true,
            "muc-button": true,
            "altcha-captcha": true,
          },
        },
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 0));

      expect(wrapper.emitted("invalidJumpinLink")).toBeTruthy();
    });

    it("should not emit invalidJumpinLink when preselected service is found", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: mockServices,
          relations: [],
          offices: []
        }),
      }));

      const wrapper = mount(ServiceFinder, {
        props: {
          baseUrl: "https://www.muenchen.de",
          preselectedServiceId: "1",
          preselectedOfficeId: undefined,
          exclusiveLocation: undefined,
          t: (key: string) => key,
        },
        global: {
          provide: {
            selectedServiceProvider: {
              selectedService: ref(null),
              updateSelectedService: () => {},
            },
          },
          stubs: {
            "muc-select": true,
            "muc-counter": true,
            "muc-button": true,
            "altcha-captcha": true,
          },
        },
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 0));

      expect(wrapper.emitted("invalidJumpinLink")).toBeFalsy();
    });

    it("should not emit invalidJumpinLink when no preselected service ID is provided", async () => {
      const wrapper = createWrapper(null);
      await nextTick();
      expect(wrapper.emitted("invalidJumpinLink")).toBeFalsy();
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

    it("should show 'showLessServices' after showing all services", async () => {
      const wrapper = createWrapper(makeService(6));
      await nextTick();
      await wrapper
        .find(".m-button-group--secondary")
        .find("button")
        .trigger("click");
      await nextTick();
      const button = wrapper.find(".m-button-group--secondary");
      expect(button.exists()).toBe(true);
      expect(button.text()).toBe("showLessServices");
    });

    it("should get back to first 3 services when 'showLessServices' is clicked", async () => {
      const wrapper = createWrapper(makeService(6));
      await nextTick();
      await wrapper
        .find(".m-button-group--secondary")
        .find("button")
        .trigger("click");
      await nextTick();
      expect(wrapper.find(".m-button-group--secondary").text()).toBe("showLessServices");
      await wrapper
        .find(".m-button-group--secondary")
        .find("button")
        .trigger("click");
      await nextTick();
      const subServiceItems = wrapper.findAll(".subservice-stub");
      expect(subServiceItems.length).toBe(3);
      const button = wrapper.find(".m-button-group--secondary");
      expect(button.exists()).toBe(true);
      expect(button.text()).toBe("showAllServices");
    });
  });

  describe("Invalid Office ID (Location) Detection", () => {
    it("should emit invalidJumpinLink when preselected office ID is not found", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: mockServices,
          offices: mockOffices,
          relations: mockRelations
        })
      }));

      const wrapper = createWrapper(null, {
        preselectedServiceId: "1",
        preselectedOfficeId: "999999"
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeTruthy();
      expect(wrapper.emitted("invalidJumpinLink")).toHaveLength(2);
    });

    it("should emit invalidJumpinLink when both service and office IDs are invalid", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: mockServices,
          offices: mockOffices,
          relations: mockRelations
        })
      }));

      const wrapper = createWrapper(null, {
        preselectedServiceId: "000000000000",
        preselectedOfficeId: "000000000000"
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeTruthy();
      expect(wrapper.emitted("invalidJumpinLink")).toHaveLength(3);
    });

    it("should emit invalidJumpinLink when API returns empty offices array with preselected office", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: mockServices,
          offices: [],
          relations: []
        })
      }));

      const wrapper = createWrapper(null, {
        preselectedServiceId: "1",
        preselectedOfficeId: "1"
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeTruthy();
      expect(wrapper.emitted("invalidJumpinLink")).toHaveLength(2);
    });

    it("should NOT emit invalidJumpinLink when both service and office IDs are valid and have a relation", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: mockServices,
          offices: mockOffices,
          relations: mockRelations
        })
      }));

      const wrapper = createWrapper(null, {
        preselectedServiceId: "1",
        preselectedOfficeId: "1"
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeFalsy();
    });

    it("should NOT emit invalidJumpinLink when no preselected IDs are provided", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: mockServices,
          offices: mockOffices,
          relations: mockRelations
        })
      }));

      const wrapper = createWrapper(null, {
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeFalsy();
    });

    it("should emit invalidJumpinLink when office ID is invalid but service ID is valid", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: mockServices,
          offices: mockOffices,
          relations: mockRelations
        })
      }));

      const wrapper = createWrapper(null, {
        preselectedServiceId: "1",
        preselectedOfficeId: "nonexistent-office-id"
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeTruthy();
      expect(wrapper.emitted("invalidJumpinLink")).toHaveLength(2);
    });

    it("should emit invalidJumpinLink when valid service and office IDs don't work together (no relation)", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: [],
          offices: [],
          relations: []
        })
      }));

      const wrapper = createWrapper(null, {
        preselectedServiceId: "1",
        preselectedOfficeId: "2"
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeTruthy();
      expect(wrapper.emitted("invalidJumpinLink")).toHaveLength(3);
    });

    it("should emit invalidJumpinLink when service is not available at any office", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: [],
          offices: mockOffices,
          relations: []
        })
      }));

      const wrapper = createWrapper(null, {
        preselectedServiceId: "4",
        preselectedOfficeId: "1"
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeTruthy();
      expect(wrapper.emitted("invalidJumpinLink")).toHaveLength(2);
    });
  });

  describe("Valid Partial Jump-in Links", () => {
    it("should NOT emit invalidJumpinLink when only valid serviceId is provided", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: mockServices,
          offices: mockOffices,
          relations: mockRelations
        })
      }));

      const wrapper = createWrapper(null, {
        preselectedServiceId: "1",
        preselectedOfficeId: undefined
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeFalsy();
    });

    it("should NOT emit invalidJumpinLink when only valid officeId is provided", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: mockServices,
          offices: mockOffices,
          relations: mockRelations
        })
      }));

      const wrapper = createWrapper(null, {
        preselectedServiceId: undefined,
        preselectedOfficeId: "1"
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeFalsy();
    });

    it("should emit invalidJumpinLink when only invalid serviceId is provided", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: mockServices,
          offices: mockOffices,
          relations: mockRelations
        })
      }));

      const wrapper = createWrapper(null, {
        preselectedServiceId: "999999",
        preselectedOfficeId: undefined
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeTruthy();
      expect(wrapper.emitted("invalidJumpinLink")).toHaveLength(1);
    });

    it("should emit invalidJumpinLink when only invalid officeId is provided", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: mockServices,
          offices: mockOffices,
          relations: mockRelations
        })
      }));

      const wrapper = createWrapper(null, {
        preselectedServiceId: undefined,
        preselectedOfficeId: "999999"
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeTruthy();
      expect(wrapper.emitted("invalidJumpinLink")).toHaveLength(1);
    });

    it("should NOT emit invalidJumpinLink when API returns filtered results for valid serviceId only", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: [mockServices[0]],
          offices: [mockOffices[0]],
          relations: [mockRelations[0]]
        })
      }));

      const wrapper = createWrapper(null, {
        preselectedServiceId: "1",
        preselectedOfficeId: undefined
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeFalsy();
    });

    it("should NOT emit invalidJumpinLink when API returns filtered results for valid officeId only", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: [mockServices[0], mockServices[1]],
          offices: [mockOffices[0]],
          relations: [mockRelations[0], mockRelations[1]]
        })
      }));

      const wrapper = createWrapper(null, {
        preselectedServiceId: undefined,
        preselectedOfficeId: "1"
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeFalsy();
    });

    it("should emit invalidJumpinLink when both serviceId and officeId are null strings", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          services: mockServices,
          offices: mockOffices,
          relations: mockRelations
        })
      }));

      const wrapper = createWrapper(null, {
        preselectedServiceId: "null",
        preselectedOfficeId: "null"
      });

      await nextTick();
      await new Promise(resolve => setTimeout(resolve, 100));

      expect(wrapper.emitted("invalidJumpinLink")).toBeTruthy();
      expect(wrapper.emitted("invalidJumpinLink")).toHaveLength(3);
    });
  });

  describe("Variants", () => {
    const makeBaseAndVariants = () => {
      const base = {
        id: "10",
        name: "Basis Service",
        parentId: null,
        variantId: null,
        maxQuantity: 5,
        combinable: null,
      };
      const variant2 = {
        id: "12",
        name: "Basis Service (Variante 2)",
        parentId: "10",
        variantId: 2,
        maxQuantity: 5,
        combinable: null,
      };
      return { base, variant2 };
    };

    it("should include synthetic variant 1 (id 10) and real variant 2 (id 12) in sorted order", async () => {
      const { base, variant2 } = makeBaseAndVariants();
      const wrapper = createWrapper(base);
      wrapper.vm.services = [variant2, base];
      wrapper.vm.service = base;
      await nextTick();

      const vs = wrapper.vm.variantServices;
      expect(Array.isArray(vs)).toBeTruthy();
      expect(vs.map((v: any) => v.variantId)).toEqual([1, 2]);
      expect(vs[0].id).toBe("10");
      expect(vs[1].id).toBe("12");
    });

    it("should switch service correctly when changing selectedVariant (2 -> 1)", async () => {
      const { base, variant2 } = makeBaseAndVariants();
      const wrapper = createWrapper(base);
      wrapper.vm.services = [base, variant2];
      wrapper.vm.service = base;
      await nextTick();

      wrapper.vm.selectedVariant = "2";
      await nextTick();
      expect(wrapper.vm.service.id).toBe("12");
      expect(wrapper.vm.service.name).toContain("Variante 2");

      wrapper.vm.selectedVariant = "1";
      await nextTick();
      expect(wrapper.vm.service.id).toBe("10");
    });

    it("should show subservices only for variant 1 (base) and hide them for variant 2", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({ services: [], offices: [], relations: [] }),
      }));

      const base = {
        id: "10",
        name: "Basis Service",
        parentId: null,
        variantId: null,
        maxQuantity: 5,
        combinable: { a: { "2": ["1"] } },
      };
      const variant2 = {
        id: "12",
        name: "Basis Service (Variante 2)",
        parentId: "10",
        variantId: 2,
        maxQuantity: 5,
        combinable: null,
      };
      const sub = { id: "2", name: "Sub Service", maxQuantity: 5, combinable: null };

      const wrapper = createWrapper(base);
      wrapper.vm.services = [base, variant2, sub];
      wrapper.vm.service = base;
      await nextTick();

      expect(wrapper.vm.showSubservices).toBeFalsy();

      wrapper.vm.selectedVariant = "1";
      await nextTick();
      expect(wrapper.vm.showSubservices).toBeTruthy();

      wrapper.vm.selectedVariant = "2";
      await nextTick();
      expect(wrapper.vm.showSubservices).toBeFalsy();
    });
  });
});
