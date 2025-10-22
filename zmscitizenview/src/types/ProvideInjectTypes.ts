import { Ref } from "vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { CustomerData } from "@/types/CustomerData";
import { OfficeImpl } from "@/types/OfficeImpl";
import { ServiceImpl } from "@/types/ServiceImpl";

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
  appointment: Ref<AppointmentDTO | undefined>;
}
