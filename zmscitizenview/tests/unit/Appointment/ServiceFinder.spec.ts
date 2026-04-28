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
  parentId?: string | null;
  variantId?: number | null;
  showOnStartPage?: boolean;
}

describe("ServiceFinder", () => {
  const mockServices = [
    {
      id: "1",
      name: "Gewerbe-Ummeldung",
      maxQuantity: 1,
      combinable: null,
      showOnStartPage: true,
    },
    {
      id: "2",
      name: "Gewerbe-Anmeldung",
      maxQuantity: 1,
      combinable: null,
      showOnStartPage: true,
    },
    {
      id: "3",
      name: "Führerschein-Ummeldung",
      maxQuantity: 1,
      combinable: null,
      showOnStartPage: true,
    },
    {
      id: "4",
      name: "Führerschein-Anmeldung",
      maxQuantity: 1,
      combinable: null,
      showOnStartPage: false,
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
  });

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
        globalState: {
          baseUrl: "https://www.muenchen.de",
        },
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
          serviceLinkProvider: {
            serviceLinkId: ref<string | null>(null),
            updateServiceLinkId: () => {},
          },
        },
        stubs: {
          SubserviceListItem: {
            template: '<li class="subservice-stub"></li>',
          },
          MucSelect: {
            name: "MucSelect",
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

    it("should only show services with showOnStartPage === true in the selection", async () => {
      const wrapper = createWrapper(null);

      await new Promise((r) => setTimeout(r, 0));
      await nextTick();

      const select = wrapper.findComponent({ name: "MucSelect" });
      expect(select.exists()).toBe(true);

      const items: any[] = select.props("items") as any[] || [];

      const visibleServices = items.filter((s: any) => s.showOnStartPage === true);
      expect(visibleServices).toHaveLength(3);

      const visibleIds = visibleServices.map((s: any) => s.id);
      expect(visibleIds).toEqual(expect.arrayContaining(["1", "2", "3"]));
      expect(visibleIds).not.toContain("4");
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
      const buttons = wrapper.findAll(".m-button-group button");
      const nextButton = buttons.find(b => b.text().includes("next"));
      await nextButton.trigger("click");
      expect(wrapper.emitted("next")).toBeTruthy();
    });

    it("should not show next button when no service is selected", async () => {
      const wrapper = createWrapper(null);
      await nextTick();
      const buttons = wrapper.findAll(".m-button-group button");
      const nextButton = buttons.find(b => b.text().includes("next"));
      expect(nextButton).toBeUndefined();
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
          globalState: {
            baseUrl: "https://www.muenchen.de",
          },
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
            serviceLinkProvider: {
              serviceLinkId: ref<string | null>(null),
              updateServiceLinkId: () => {},
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
          globalState: {
            baseUrl: "https://www.muenchen.de",
          },
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
            serviceLinkProvider: {
              serviceLinkId: ref<string | null>(null),
              updateServiceLinkId: () => {},
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
          globalState: {
            baseUrl: "https://www.muenchen.de",
          },
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
            serviceLinkProvider: {
              serviceLinkId: ref<string | null>(null),
              updateServiceLinkId: () => {},
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
    const VARIANT_ID_PRESENCE = 1;
    const VARIANT_ID_TELEPHONE = 2;
    const VARIANT_ID_VIDEO = 3;
    const VARIANT_ID_LARGE_CUSTOMER = 4;
    const VARIANT_ID_SMALL_CUSTOMER = 5;

    const makeServiceVariant = ({
      id,
      name,
      parentId = null,
      variantId = null,
      showOnStartPage = false,
      combinable = null,
    }: {
      id: string;
      name: string;
      parentId?: string | null;
      variantId?: number | null;
      showOnStartPage?: boolean;
      combinable?: any;
    }) => ({
      id,
      name,
      parentId,
      variantId,
      maxQuantity: 5,
      combinable,
      showOnStartPage,
    });

    const makeBaseAndTelephoneVariant = () => {
      const baseService = makeServiceVariant({
        id: "10",
        name: "Basis Service",
        showOnStartPage: true,
      });

      const telephoneVariant = makeServiceVariant({
        id: "12",
        name: "Basis Service Telefon",
        parentId: "10",
        variantId: VARIANT_ID_TELEPHONE,
      });

      return { baseService, telephoneVariant };
    };

    it("should include implicit presence variant when a telephone variant exists", async () => {
      const { baseService, telephoneVariant } = makeBaseAndTelephoneVariant();
      const wrapper = createWrapper(baseService);

      wrapper.vm.services = [telephoneVariant, baseService];
      wrapper.vm.service = baseService;

      await nextTick();

      const variantServices = wrapper.vm.variantServices as any[];

      expect(Array.isArray(variantServices)).toBeTruthy();
      expect(variantServices.map((variant) => variant.variantId)).toEqual([
        VARIANT_ID_PRESENCE,
        VARIANT_ID_TELEPHONE,
      ]);
      expect(variantServices[0].id).toBe("10");
      expect(variantServices[1].id).toBe("12");
    });

    it("should include implicit presence variant when a video variant exists", async () => {
      const baseService = makeServiceVariant({
        id: "10",
        name: "Basis Service",
        showOnStartPage: true,
      });

      const videoVariant = makeServiceVariant({
        id: "13",
        name: "Basis Service Video",
        parentId: "10",
        variantId: VARIANT_ID_VIDEO,
      });

      const wrapper = createWrapper(baseService);

      wrapper.vm.services = [videoVariant, baseService];
      wrapper.vm.service = baseService;

      await nextTick();

      const variantServices = wrapper.vm.variantServices as any[];

      expect(variantServices.map((variant) => variant.variantId)).toEqual([
        VARIANT_ID_PRESENCE,
        VARIANT_ID_VIDEO,
      ]);
      expect(variantServices[0].id).toBe("10");
      expect(variantServices[1].id).toBe("13");
    });

    it("should not include implicit presence variant when only small and large customer variants exist", async () => {
      const baseService = makeServiceVariant({
        id: "20",
        name: "Planeinsicht Grundstücksentwässerung",
        showOnStartPage: true,
      });

      const smallCustomerVariant = makeServiceVariant({
        id: "21",
        name: "Planeinsicht Grundstücksentwässerung – Kleinkunden",
        parentId: "20",
        variantId: VARIANT_ID_SMALL_CUSTOMER,
      });

      const largeCustomerVariant = makeServiceVariant({
        id: "22",
        name: "Planeinsicht Grundstücksentwässerung – Großkunden",
        parentId: "20",
        variantId: VARIANT_ID_LARGE_CUSTOMER,
      });

      const wrapper = createWrapper(baseService);

      wrapper.vm.services = [
        baseService,
        smallCustomerVariant,
        largeCustomerVariant,
      ];
      wrapper.vm.service = baseService;

      await nextTick();

      const variantServices = wrapper.vm.variantServices as any[];

      expect(variantServices.map((variant) => variant.variantId)).toEqual([
        VARIANT_ID_LARGE_CUSTOMER,
        VARIANT_ID_SMALL_CUSTOMER,
      ]);
      expect(
        variantServices.some(
          (variant) => variant.variantId === VARIANT_ID_PRESENCE
        )
      ).toBe(false);
      expect(variantServices.map((variant) => variant.id)).not.toContain("20");
    });

    it("should not include implicit presence variant when only a small customer variant exists", async () => {
      const baseService = makeServiceVariant({
        id: "20",
        name: "Planeinsicht Grundstücksentwässerung",
        showOnStartPage: true,
      });

      const smallCustomerVariant = makeServiceVariant({
        id: "21",
        name: "Planeinsicht Grundstücksentwässerung – Kleinkunden",
        parentId: "20",
        variantId: VARIANT_ID_SMALL_CUSTOMER,
      });

      const wrapper = createWrapper(baseService);

      wrapper.vm.services = [baseService, smallCustomerVariant];
      wrapper.vm.service = baseService;

      await nextTick();

      const variantServices = wrapper.vm.variantServices as any[];

      expect(variantServices.map((variant) => variant.variantId)).toEqual([
        VARIANT_ID_SMALL_CUSTOMER,
      ]);
      expect(
        variantServices.some(
          (variant) => variant.variantId === VARIANT_ID_PRESENCE
        )
      ).toBe(false);
    });

    it("should not include implicit presence variant when only a large customer variant exists", async () => {
      const baseService = makeServiceVariant({
        id: "20",
        name: "Planeinsicht Grundstücksentwässerung",
        showOnStartPage: true,
      });

      const largeCustomerVariant = makeServiceVariant({
        id: "22",
        name: "Planeinsicht Grundstücksentwässerung – Großkunden",
        parentId: "20",
        variantId: VARIANT_ID_LARGE_CUSTOMER,
      });

      const wrapper = createWrapper(baseService);

      wrapper.vm.services = [baseService, largeCustomerVariant];
      wrapper.vm.service = baseService;

      await nextTick();

      const variantServices = wrapper.vm.variantServices as any[];

      expect(variantServices.map((variant) => variant.variantId)).toEqual([
        VARIANT_ID_LARGE_CUSTOMER,
      ]);
      expect(
        variantServices.some(
          (variant) => variant.variantId === VARIANT_ID_PRESENCE
        )
      ).toBe(false);
    });

    it("should switch service correctly when changing selectedVariant from telephone to presence", async () => {
      const { baseService, telephoneVariant } = makeBaseAndTelephoneVariant();
      const wrapper = createWrapper(baseService);

      wrapper.vm.services = [baseService, telephoneVariant];
      wrapper.vm.service = baseService;

      await nextTick();

      wrapper.vm.selectedVariant = String(VARIANT_ID_TELEPHONE);
      await nextTick();

      expect(wrapper.vm.service.id).toBe("12");
      expect(wrapper.vm.service.name).toContain("Telefon");

      wrapper.vm.selectedVariant = String(VARIANT_ID_PRESENCE);
      await nextTick();

      expect(wrapper.vm.service.id).toBe("10");
    });

    it("should show subservices only for presence variant and hide them for telephone variant", async () => {
      vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({ services: [], offices: [], relations: [] }),
      }));

      const baseService = makeServiceVariant({
        id: "10",
        name: "Basis Service",
        showOnStartPage: true,
        combinable: { a: { "2": ["1"] } },
      });

      const telephoneVariant = makeServiceVariant({
        id: "12",
        name: "Basis Service Telefon",
        parentId: "10",
        variantId: VARIANT_ID_TELEPHONE,
      });

      const subService = makeServiceVariant({
        id: "2",
        name: "Sub Service",
      });

      const wrapper = createWrapper(baseService);

      wrapper.vm.services = [baseService, telephoneVariant, subService];
      wrapper.vm.service = baseService;

      await nextTick();

      expect(wrapper.vm.showSubservices).toBeFalsy();

      wrapper.vm.selectedVariant = String(VARIANT_ID_PRESENCE);
      await nextTick();

      expect(wrapper.vm.showSubservices).toBeTruthy();

      wrapper.vm.selectedVariant = String(VARIANT_ID_TELEPHONE);
      await nextTick();

      expect(wrapper.vm.showSubservices).toBeFalsy();
    });
  });
});
