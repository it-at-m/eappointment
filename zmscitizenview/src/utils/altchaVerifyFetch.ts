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

export function extractPayload(
  body: BodyInit | null | undefined
): string | undefined {
  if (typeof body !== "string") return undefined;
  try {
    return (JSON.parse(body) as { payload?: string }).payload;
  } catch {
    return undefined;
  }
}

export const captchaVerifyFetch: typeof fetch = async (url, init) => {
  const response = await fetch(url, init);
  if (!response.headers.get("content-type")?.includes("json")) {
    return response;
  }

  const json = (await response.json()) as CaptchaVerifyResponse;
  const payload = extractPayload(init?.body);
  const valid = isCaptchaVerifySuccess(json.meta, json.data);

  return Response.json(
    {
      ...json,
      verified: valid,
      payload,
    },
    { status: response.status }
  );
};
