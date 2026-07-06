import express from "express";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const port = Number(process.env.PORT || 8025);
const apiBaseUrl = (
  process.env.ZMS_API_URL || "http://web/terminvereinbarung/api/2"
).replace(/\/$/, "");
const apiUser = process.env.ZMS_MAIL_VIEWER_API_USER || "superuser";
const apiPassword = process.env.ZMS_MAIL_VIEWER_API_PASSWORD || "vorschau";
const viewerUser = process.env.ZMS_MAIL_VIEWER_USER || "superuser";
const viewerPassword = process.env.ZMS_MAIL_VIEWER_PASSWORD || "vorschau";
const apiAuthorization =
  "Basic " + Buffer.from(`${apiUser}:${apiPassword}`).toString("base64");

const app = express();

app.use((req, res, next) => {
  const header = req.headers.authorization || "";
  if (header.startsWith("Basic ")) {
    const [user, password] = Buffer.from(header.slice(6), "base64")
      .toString("utf8")
      .split(":");
    if (user === viewerUser && password === viewerPassword) {
      return next();
    }
  }
  res.set("WWW-Authenticate", 'Basic realm="ZMS local mail viewer"');
  return res.status(401).send("Authentication required");
});

async function proxyApi(relativePath, query = "") {
  const url = `${apiBaseUrl}${relativePath}${query}`;
  const response = await fetch(url, {
    headers: {
      Accept: "application/json",
      Authorization: apiAuthorization,
    },
  });
  const body = await response.text();
  return { status: response.status, body };
}

app.get("/api/mails", async (req, res) => {
  try {
    const query = new URLSearchParams({
      resolveReferences: "1",
      limit: String(req.query.limit || "100"),
    });
    if (req.query.ids) {
      query.set("ids", String(req.query.ids));
    }
    const result = await proxyApi("/mails/", `?${query}`);
    res.status(result.status).type("application/json").send(result.body);
  } catch (error) {
    res.status(502).json({
      error: true,
      message: `Failed to reach ZMS API at ${apiBaseUrl}: ${error.message}`,
    });
  }
});

app.get("/api/mails/:id", async (req, res) => {
  try {
    const result = await proxyApi(`/mails/${req.params.id}/`);
    res.status(result.status).type("application/json").send(result.body);
  } catch (error) {
    res.status(502).json({
      error: true,
      message: `Failed to reach ZMS API at ${apiBaseUrl}: ${error.message}`,
    });
  }
});

app.use(express.static(path.join(__dirname, "public")));

app.listen(port, "0.0.0.0", () => {
  console.log(`ZMS mail viewer listening on http://0.0.0.0:${port}`);
  console.log(`Proxying ${apiBaseUrl}/mails/ as ${apiUser}`);
});
