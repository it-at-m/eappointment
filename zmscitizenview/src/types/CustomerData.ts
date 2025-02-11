export class CustomerData {
  firstName: string;

  lastName: string;

  mailAddress: string;

  telephoneNumber?: string;

  customTextfield?: string;

  constructor(
    firstName: string,
    lastName: string,
    mailAddress: string,
    telephoneNumber: string | undefined,
    customTextfield: string | undefined
  ) {
    this.firstName = firstName;
    this.lastName = lastName;
    this.mailAddress = mailAddress;
    this.telephoneNumber = telephoneNumber;
    this.customTextfield = customTextfield;
  }
}
