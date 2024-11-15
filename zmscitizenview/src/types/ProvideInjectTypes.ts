import { Ref } from "vue";

import { ServiceImpl } from "@/types/ServiceImpl";

export interface SelectedServiceProvider {
  selectedService: Ref<ServiceImpl | undefined>;
  updateSelectedService: (newService: ServiceImpl) => void;
}

export interface SelectedTimeslotProvider {
  selectedTimeslot: Ref<number>;
}
