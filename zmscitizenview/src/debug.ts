const fakeLogin = (
  email: string | undefined = undefined,
  firstName: string | undefined = undefined,
  lastName: string | undefined = undefined
) => {
  if (!email || !firstName || !lastName) {
    console.error(
      "Usage: fakeLogin('email@example.com', 'First Name', 'Last Name')"
    );
    return;
  }
  const data = btoa(
    JSON.stringify({
      lhmExtID: email,
      email: email,
      given_name: firstName,
      family_name: lastName,
    })
  );
  document.dispatchEvent(
    new CustomEvent("authorization-event", {
      detail: `.${data}.`,
    })
  );
};
window.fakeLogin = fakeLogin;

const fakeLogout = () => {
  document.dispatchEvent(
    new CustomEvent("authorization-event", {
      detail: undefined,
    })
  );
};
window.fakeLogout = fakeLogout;
