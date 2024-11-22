export class CustomerData {
  firstName: string;

  lastName: string;

  mailAddress: string;

  telephoneNumber?: string;

  remarks?: string;

  constructor(
    firstName: string,
    lastName: string,
    mailAddress: string,
    telephoneNumber: string | undefined,
    remarks: string | undefined
  ) {
    this.firstName = firstName;
    this.lastName = lastName;
    this.mailAddress = mailAddress;
    this.telephoneNumber = telephoneNumber;
    this.remarks = remarks;
  }
}
