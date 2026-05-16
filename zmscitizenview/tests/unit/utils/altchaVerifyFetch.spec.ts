import { describe, expect, it, vi } from "vitest";

import {
  captchaVerifyFetch,
  isCaptchaVerifySuccess,
} from "@/utils/altchaVerifyFetch";

describe("isCaptchaVerifySuccess", () => {
  it("returns true when meta.success and data.valid are true", () => {
    expect(
      isCaptchaVerifySuccess({ success: true }, { valid: true })
    ).toBe(true);
  });

  it("returns false otherwise", () => {
    expect(
      isCaptchaVerifySuccess({ success: false }, { valid: true })
    ).toBe(false);
    expect(isCaptchaVerifySuccess({ success: true }, undefined)).toBe(false);
  });
});

describe("captchaVerifyFetch", () => {
  it("adapts a successful zmscitizenapi response for ALTCHA v3", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValueOnce({
      headers: new Headers({ "content-type": "application/json" }),
      status: 200,
      json: async () => ({
        meta: { success: true },
        data: { valid: true },
        token: "jwt",
      }),
    } as Response);

    const response = await captchaVerifyFetch("https://example.com/verify", {
      method: "POST",
      body: JSON.stringify({ payload: "pow-base64" }),
    });
    const json = await response.json();

    expect(json.verified).toBe(true);
    expect(json.payload).toBe("pow-base64");
    expect(json.token).toBe("jwt");
  });
});
