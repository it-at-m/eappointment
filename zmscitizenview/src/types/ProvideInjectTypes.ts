import { Ref } from "vue";

import { ServiceImpl } from "@/types/ServiceImpl";
import {OfficeImpl} from "@/types/OfficeImpl";
import {AppointmentImpl} from "@/types/AppointmentImpl";
import {CustomerData} from "@/types/CustomerData";

export interface SelectedServiceProvider {
  selectedService: Ref<ServiceImpl | undefined>;
  updateSelectedService: (newService: ServiceImpl) => void;
}

export interface SelectedTimeslotProvider {
  selectedProvider: Ref<OfficeImpl | undefined>;
  selectedTimeslot: Ref<number>;
}

export interface CustomerDataProvider {
  customerData: Ref<CustomerData>;
}

export interface SelectedAppointmentProvider {
  appointment: Ref<AppointmentImpl | undefined>;
}
