export type CaptchaVerifyResponse = {
  meta?: { success?: boolean };
  data?: { valid?: boolean };
  token?: string;
};

export function isCaptchaVerifySuccess(
  meta?: { success?: boolean },
  data?: { valid?: boolean }
): boolean {
  return meta?.success === true && data?.valid === true;
}

export const captchaVerifyFetch: typeof fetch = async (url, init) => {
  const response = await fetch(url, init);
  if (!response.headers.get("content-type")?.includes("json")) {
    return response;
  }

  const json = (await response.json()) as CaptchaVerifyResponse;
  const requestBody = init?.body
    ? (JSON.parse(String(init.body)) as { payload?: string })
    : {};
  const valid = isCaptchaVerifySuccess(json.meta, json.data);

  return Response.json(
    {
      ...json,
      verified: valid,
      payload: requestBody.payload,
    },
    { status: response.status }
  );
};
