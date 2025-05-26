/**
 * Type definition for combinable services structure
 * @type {{ [key: string]: { [serviceId: string]: number[] } }}
 */
export type Combinable = {
  [key: string]: {
    [serviceId: string]: number[];
  };
};
