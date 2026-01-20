package ataf.core.clients;

import ataf.core.assertions.CustomAssertions;
import ataf.core.helpers.AuthenticationHelper;
import ataf.core.logging.ScenarioLogManager;
import com.github.markusbernhardt.proxy.selector.pac.JavaxPacScriptParser;
import com.github.markusbernhardt.proxy.selector.pac.ProxyEvaluationException;
import com.github.markusbernhardt.proxy.selector.pac.UrlPacScriptSource;
import org.apache.hc.client5.http.auth.AuthScope;
import org.apache.hc.client5.http.auth.CredentialsProvider;
import org.apache.hc.client5.http.auth.UsernamePasswordCredentials;
import org.apache.hc.client5.http.classic.methods.HttpGet;
import org.apache.hc.client5.http.classic.methods.HttpPost;
import org.apache.hc.client5.http.classic.methods.HttpPut;
import org.apache.hc.client5.http.classic.methods.HttpUriRequest;
import org.apache.hc.client5.http.config.ConnectionConfig;
import org.apache.hc.client5.http.config.RequestConfig;
import org.apache.hc.client5.http.cookie.BasicCookieStore;
import org.apache.hc.client5.http.cookie.StandardCookieSpec;
import org.apache.hc.client5.http.entity.mime.MultipartEntityBuilder;
import org.apache.hc.client5.http.impl.auth.BasicCredentialsProvider;
import org.apache.hc.client5.http.impl.classic.CloseableHttpClient;
import org.apache.hc.client5.http.impl.classic.CloseableHttpResponse;
import org.apache.hc.client5.http.impl.classic.HttpClientBuilder;
import org.apache.hc.client5.http.impl.classic.HttpClients;
import org.apache.hc.client5.http.impl.io.PoolingHttpClientConnectionManager;
import org.apache.hc.client5.http.impl.io.PoolingHttpClientConnectionManagerBuilder;
import org.apache.hc.client5.http.protocol.HttpClientContext;
import org.apache.hc.client5.http.ssl.SSLConnectionSocketFactoryBuilder;
import org.apache.hc.core5.http.ContentType;
import org.apache.hc.core5.http.Header;
import org.apache.hc.core5.http.HttpEntity;
import org.apache.hc.core5.http.HttpHost;
import org.apache.hc.core5.http.ParseException;
import org.apache.hc.core5.http.io.SocketConfig;
import org.apache.hc.core5.http.io.entity.EntityUtils;
import org.apache.hc.core5.http.io.entity.FileEntity;
import org.apache.hc.core5.http.io.entity.StringEntity;
import org.apache.hc.core5.http.ssl.TLS;
import org.apache.hc.core5.pool.PoolConcurrencyPolicy;
import org.apache.hc.core5.pool.PoolReusePolicy;
import org.apache.hc.core5.ssl.SSLContexts;
import org.apache.hc.core5.util.Timeout;
import org.apache.logging.log4j.Level;
import org.apache.logging.log4j.core.config.Configurator;
import org.identityconnectors.common.Base64;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.net.MalformedURLException;
import java.net.SocketTimeoutException;
import java.net.URL;
import java.nio.charset.StandardCharsets;
import java.nio.file.Path;
import java.time.LocalDateTime;
import java.time.ZoneId;
import java.util.List;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * A wrapper class for making HTTP requests using Apache HttpClient with various authentication
 * methods and proxy support.
 * <p>
 * This class supports GET, POST, and PUT HTTP methods, with optional basic authentication and
 * bearer token authorization. It also handles proxy configuration
 * via PAC files or direct proxy settings.
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class HttpClient implements AutoCloseable {
    private static final long TIMEOUT = 120000L;

    /**
     * Constant for the proxy auto script which is set to
     * <a href="http://flaucherint.muenchen.de/proxy.pac">http://flaucherint.muenchen.de/proxy.pac</a>
     */
    public static final String PROXY_PAC_URL = "http://flaucherint.muenchen.de/proxy.pac";

    private final HttpClientContext HTTP_CLIENT_CONTEXT = HttpClientContext.create();
    private final CloseableHttpClient HTTP_CLIENT;
    private String lastRequestProtocolInformation;
    private int lastRequestStatusCode;
    private String lastRequestStatusReasonPhrase;
    private String lastRequestContentType;
    private Header[] lastRequestHeaders;

    /**
     * Initializes the connection manager with SSL and socket configurations.
     *
     * @return a configured PoolingHttpClientConnectionManager instance.
     */
    private PoolingHttpClientConnectionManager getConnectionManager() {
        return PoolingHttpClientConnectionManagerBuilder.create()
                .setSSLSocketFactory(SSLConnectionSocketFactoryBuilder.create()
                        .setSslContext(SSLContexts.createSystemDefault())
                        .setTlsVersions(TLS.V_1_3)
                        .build())
                .setDefaultSocketConfig(SocketConfig.custom()
                        .setSoTimeout(Timeout.ofMilliseconds(TIMEOUT))
                        .build())
                .setPoolConcurrencyPolicy(PoolConcurrencyPolicy.STRICT)
                .setConnPoolPolicy(PoolReusePolicy.LIFO)
                .setDefaultConnectionConfig(ConnectionConfig.custom()
                        .setSocketTimeout(Timeout.ofMilliseconds(TIMEOUT))
                        .setConnectTimeout(Timeout.ofMilliseconds(TIMEOUT))
                        .setTimeToLive(Timeout.ofMilliseconds(TIMEOUT * 3L))
                        .build())
                .build();
    }

    /**
     * Default constructor that creates an HttpClient without any proxy settings.
     */
    public HttpClient() {
        Configurator.setLevel("org.apache.http.client.protocol.ResponseProcessCookies", Level.ERROR);
        HTTP_CLIENT = HttpClients
                .custom()
                .setConnectionManager(getConnectionManager())
                .setDefaultRequestConfig(RequestConfig.custom()
                        .setCookieSpec(StandardCookieSpec.STRICT)
                        .build())
                .setDefaultCookieStore(new BasicCookieStore())
                .build();
    }

    /**
     * Constructor that creates an HttpClient with proxy settings determined by a PAC URL and target
     * URL.
     *
     * @param pacUrl the URL of the PAC file.
     * @param targetUrl the URL of the target server.
     */
    public HttpClient(String pacUrl, String targetUrl) {
        HttpClientBuilder httpClientBuilder = HttpClients.custom();
        HttpHost proxy = getProxyHostByTargetUrl(pacUrl, targetUrl);
        if (proxy != null) {
            CredentialsProvider proxyCredentialsProvider = getProxyCredentialsProvider(proxy.getHostName(), proxy.getPort());
            CustomAssertions.assertNotNull(proxyCredentialsProvider, "Credentials provider could not be created!");
            httpClientBuilder.setProxy(proxy).setDefaultCredentialsProvider(proxyCredentialsProvider);
        }
        Configurator.setLevel("org.apache.http.client.protocol.ResponseProcessCookies", Level.ERROR);
        HTTP_CLIENT = httpClientBuilder
                .setConnectionManager(getConnectionManager())
                .setDefaultRequestConfig(RequestConfig.custom()
                        .setCookieSpec(StandardCookieSpec.STRICT)
                        .build())
                .setDefaultCookieStore(new BasicCookieStore())
                .build();
    }

    /**
     * Constructor that creates an HttpClient with a specified proxy hostname and port.
     *
     * @param proxyHostname the hostname of the proxy server.
     * @param proxyPort the port of the proxy server.
     */
    public HttpClient(String proxyHostname, int proxyPort) {
        HttpClientBuilder httpClientBuilder = HttpClients.custom();
        HttpHost proxy = new HttpHost(proxyHostname, proxyPort);
        CredentialsProvider proxyCredentialsProvider = getProxyCredentialsProvider(proxy.getHostName(), proxy.getPort());
        CustomAssertions.assertNotNull(proxyCredentialsProvider, "Credentials provider could not be created!");
        httpClientBuilder.setProxy(proxy).setDefaultCredentialsProvider(proxyCredentialsProvider);
        Configurator.setLevel("org.apache.http.client.protocol.ResponseProcessCookies", Level.ERROR);
        HTTP_CLIENT = httpClientBuilder
                .setConnectionManager(getConnectionManager())
                .setDefaultRequestConfig(RequestConfig.custom()
                        .setCookieSpec(StandardCookieSpec.STRICT)
                        .build())
                .setDefaultCookieStore(new BasicCookieStore())
                .build();
    }

    /**
     * Returns the protocol information of the last request made.
     *
     * @return a string containing the protocol information.
     */
    public String getLastRequestProtocolInformation() {
        return lastRequestProtocolInformation;
    }

    /**
     * Returns the status code of the last request made.
     *
     * @return an integer representing the status code.
     */
    public int getLastRequestStatusCode() {
        return lastRequestStatusCode;
    }

    /**
     * Returns the reason phrase of the last request's status.
     *
     * @return a string containing the reason phrase.
     */
    public String getLastRequestStatusReasonPhrase() {
        return lastRequestStatusReasonPhrase;
    }

    /**
     * Returns the content type of the last request's response.
     *
     * @return a string representing the content type.
     */
    public String getLastRequestContentType() {
        return lastRequestContentType;
    }

    /**
     * Returns the headers from the last request made.
     *
     * @return an array of Header objects.
     */
    public Header[] getLastRequestHeaders() {
        return lastRequestHeaders;
    }

    /**
     * Retrieves the proxy host based on the PAC URL and target URL.
     *
     * @param pacUrl the PAC file URL.
     * @param targetUrl the target URL for which to determine the proxy.
     * @return a configured HttpHost for the proxy or null if no proxy is needed.
     */
    private HttpHost getProxyHostByTargetUrl(String pacUrl, String targetUrl) {
        try {
            JavaxPacScriptParser javaxPacScriptParser = new JavaxPacScriptParser(new UrlPacScriptSource(pacUrl));
            Pattern pattern = Pattern.compile("PROXY ([\\d\\w.-]+):([\\d]+)");
            URL url = new URL(targetUrl);
            String javaScriptResult = javaxPacScriptParser.evaluate(url.toString(), url.getHost());
            ScenarioLogManager.getLogger().info("JavaScript result for URL \"{}\" with host \"{}\": {}", url, url.getHost(), javaScriptResult);
            Matcher matcher = pattern.matcher(javaScriptResult);
            if (matcher.find()) {
                ScenarioLogManager.getLogger().info("{} : {}", matcher.group(1), Integer.parseInt(matcher.group(2)));
                return new HttpHost(matcher.group(1), Integer.parseInt(matcher.group(2)));
            }
        } catch (ProxyEvaluationException | MalformedURLException e) {
            ScenarioLogManager.getLogger().error(e.getMessage(), e);
        }
        // DIRECT
        return null;
    }

    /**
     * Creates a {@link BasicCredentialsProvider} configured with proxy credentials obtained from the
     * {@link AuthenticationHelper}.
     *
     * @param proxyUrl The URL of the proxy server.
     * @param proxyPort The port of the proxy server.
     * @return A {@link BasicCredentialsProvider} with the proxy credentials.
     * @throws AssertionError If the credentials have not been set.
     * @throws IllegalStateException If username or password were null or blank.
     */
    private BasicCredentialsProvider getProxyCredentialsProvider(String proxyUrl, int proxyPort) {
        CustomAssertions.assertFalse(AuthenticationHelper.credentialsHaveNotBeenSet(), "Cannot set proxy due to missing credentials!");
        final StringBuilder clearUserName = new StringBuilder();
        final StringBuilder clearUserPassword = new StringBuilder();
        AuthenticationHelper.getUserName().access(clearUserName::append);
        if (clearUserName.isEmpty() || clearUserName.toString().isBlank()) {
            throw new IllegalStateException("Username must be supplied and must not be blank!");
        }
        AuthenticationHelper.getUserPassword().access(clearUserPassword::append);
        if (clearUserPassword.isEmpty() || clearUserPassword.toString().isBlank()) {
            throw new IllegalStateException("Password must be supplied and must not be blank!");
        }
        BasicCredentialsProvider credentialsProvider = new BasicCredentialsProvider();
        credentialsProvider.setCredentials(new AuthScope(proxyUrl, proxyPort),
                new UsernamePasswordCredentials(clearUserName.toString(), clearUserPassword.toString().toCharArray()));
        clearUserName.delete(0, clearUserName.length() - 1);
        clearUserName.setLength(0);
        clearUserPassword.delete(0, clearUserPassword.length() - 1);
        clearUserPassword.setLength(0);
        return credentialsProvider;
    }

    /**
     * Adds a "Basic Auth" header to the provided {@link HttpUriRequest} using credentials obtained from
     * the {@link AuthenticationHelper}.
     *
     * @param httpUriRequest The HTTP request to which the header will be added.
     * @throws AssertionError If the credentials have not been set.
     * @throws IllegalStateException If username or password were null or blank.
     */
    private void addBasicAuthHeader(HttpUriRequest httpUriRequest) {
        CustomAssertions.assertFalse(AuthenticationHelper.credentialsHaveNotBeenSet(), "Cannot add \"Basic Auth\" header due to missing credentials!");
        final StringBuilder clearUserName = new StringBuilder();
        final StringBuilder clearUserPassword = new StringBuilder();
        AuthenticationHelper.getUserName().access(clearUserName::append);
        if (clearUserName.isEmpty() || clearUserName.toString().isBlank()) {
            throw new IllegalStateException("Username must be supplied and must not be blank!");
        }
        AuthenticationHelper.getUserPassword().access(clearUserPassword::append);
        if (clearUserPassword.isEmpty() || clearUserPassword.toString().isBlank()) {
            throw new IllegalStateException("Password must be supplied and must not be blank!");
        }
        httpUriRequest.addHeader("Authorization",
                "Basic " + Base64.encode(clearUserName.toString().concat(":").concat(clearUserPassword.toString()).getBytes(StandardCharsets.UTF_8)));
        clearUserName.delete(0, clearUserName.length() - 1);
        clearUserName.setLength(0);
        clearUserPassword.delete(0, clearUserPassword.length() - 1);
        clearUserPassword.setLength(0);
    }

    /**
     * Adds a "Bearer" token authorization header to the provided {@link HttpUriRequest} using the token
     * obtained from the {@link AuthenticationHelper}.
     *
     * @param httpUriRequest The HTTP request to which the header will be added.
     * @throws AssertionError If the authorization token has not been set.
     * @throws IllegalStateException If authorization token was null or blank.
     */
    private void addAuthorizationHeader(HttpUriRequest httpUriRequest) {
        CustomAssertions.assertFalse(AuthenticationHelper.authorizationTokenHasNotBeenSet(),
                "Cannot add \"Authorization\" header due to missing authorization token!");
        final StringBuilder clearAuthorizationToken = new StringBuilder();
        AuthenticationHelper.getAuthorizationToken().access(clearAuthorizationToken::append);
        if (clearAuthorizationToken.isEmpty() || clearAuthorizationToken.toString().isBlank()) {
            throw new IllegalStateException("Jira authorization token must be supplied and must not be blank!");
        }
        httpUriRequest.addHeader("Authorization", "Bearer " + clearAuthorizationToken);
        clearAuthorizationToken.delete(0, clearAuthorizationToken.length() - 1);
        clearAuthorizationToken.setLength(0);
    }

    /**
     * Creates an HTTP GET request for the specified URL, applying the specified authentication method.
     *
     * @param targetURL The target URL for the GET request.
     * @param authenticationMethod The authentication method to use (BasicAuth, AccessToken, or None).
     * @return A configured {@link HttpGet} request.
     * @throws IllegalArgumentException If the authentication method is not supported.
     */
    private HttpGet createGetRequest(String targetURL, AuthenticationMethod authenticationMethod) {
        HttpGet httpRequest = new HttpGet(targetURL);
        httpRequest.addHeader("Content-Type", "application/json");
        switch (authenticationMethod) {
            case BasicAuth:
                addBasicAuthHeader(httpRequest);
                break;
            case AccessToken:
                addAuthorizationHeader(httpRequest);
                break;
            case None:
                break;
            default:
                throw new IllegalArgumentException("Authentication method \"" + authenticationMethod + "\" is not supported by implementation!");
        }
        return httpRequest;
    }

    /**
     * Creates an HTTP GET request for the specified URL with additional headers, applying the specified
     * authentication method.
     *
     * @param targetURL The target URL for the GET request.
     * @param headerList A list of additional headers to include in the request.
     * @param authenticationMethod The authentication method to use (BasicAuth, AccessToken, or None).
     * @return A configured {@link HttpGet} request.
     * @throws IllegalArgumentException If the authentication method is not supported.
     */
    private HttpGet createGetRequest(String targetURL, List<Header> headerList, AuthenticationMethod authenticationMethod) {
        HttpGet httpRequest = new HttpGet(targetURL);
        headerList.forEach(httpRequest::addHeader);
        switch (authenticationMethod) {
            case BasicAuth:
                addBasicAuthHeader(httpRequest);
                break;
            case AccessToken:
                addAuthorizationHeader(httpRequest);
                break;
            case None:
                break;
            default:
                throw new IllegalArgumentException("Authentication method \"" + authenticationMethod + "\" is not supported by implementation!");
        }
        return httpRequest;
    }

    /**
     * Creates an HTTP POST request for the specified URL, applying the specified authentication method.
     *
     * @param targetURL The target URL for the POST request.
     * @param authenticationMethod The authentication method to use (BasicAuth, AccessToken, or None).
     * @return A configured {@link HttpPost} request.
     * @throws IllegalArgumentException If the authentication method is not supported.
     */
    private HttpPost createPostRequest(String targetURL, AuthenticationMethod authenticationMethod) {
        HttpPost httpRequest = new HttpPost(targetURL);
        switch (authenticationMethod) {
            case BasicAuth:
                addBasicAuthHeader(httpRequest);
                break;
            case AccessToken:
                addAuthorizationHeader(httpRequest);
                break;
            case None:
                break;
            default:
                throw new IllegalArgumentException("Authentication method \"" + authenticationMethod + "\" is not supported by implementation!");
        }
        return httpRequest;
    }

    /**
     * Creates an HTTP PUT request for the specified URL, applying the specified authentication method.
     *
     * @param targetURL The target URL for the PUT request.
     * @param authenticationMethod The authentication method to use (BasicAuth, AccessToken, or None).
     * @return A configured {@link HttpPut} request.
     * @throws IllegalArgumentException If the authentication method is not supported.
     */
    private HttpPut createPutRequest(String targetURL, AuthenticationMethod authenticationMethod) {
        HttpPut httpRequest = new HttpPut(targetURL);
        httpRequest.addHeader("Content-Type", "application/json");
        switch (authenticationMethod) {
            case BasicAuth:
                addBasicAuthHeader(httpRequest);
                break;
            case AccessToken:
                addAuthorizationHeader(httpRequest);
                break;
            case None:
                break;
            default:
                throw new IllegalArgumentException("Authentication method \"" + authenticationMethod + "\" is not supported by implementation!");
        }
        return httpRequest;
    }

    /**
     * Executes the given HTTP request and processes the response.
     *
     * @param httpUriRequest The HTTP request to execute.
     * @return The {@link CloseableHttpResponse} from the request.
     * @throws IOException If an I/O error occurs while executing the request.
     */
    private CloseableHttpResponse executeHttpRequest(HttpUriRequest httpUriRequest) throws IOException {
        CloseableHttpResponse httpResponse = (CloseableHttpResponse) HTTP_CLIENT.executeOpen(null, httpUriRequest, HTTP_CLIENT_CONTEXT);
        lastRequestStatusCode = httpResponse.getCode();
        lastRequestStatusReasonPhrase = httpResponse.getReasonPhrase();
        lastRequestProtocolInformation = httpResponse.getVersion().getProtocol() + "/" + httpResponse.getVersion().getMajor() + "." + httpResponse.getVersion()
                .getMinor();
        lastRequestHeaders = httpResponse.getHeaders();
        if (this instanceof JiraClient) {
            JiraClient.updateRateLimitHeaders(lastRequestHeaders);
        } else if (this instanceof ConfluenceClient) {
            ConfluenceClient.updateRateLimitHeaders(lastRequestHeaders);
        }
        return httpResponse;
    }

    /**
     * Extracts the body content from the provided HTTP response.
     *
     * @param httpResponse The HTTP response to extract the body from.
     * @return The body content as a string.
     * @throws IOException If an I/O error occurs while reading the response.
     * @throws ParseException If a parsing error occurs.
     */
    private String getHttpResponseBody(CloseableHttpResponse httpResponse) throws IOException, ParseException {
        String result = "";
        try {
            HttpEntity httpEntity = httpResponse.getEntity();
            lastRequestContentType = httpEntity.getContentType();
            result = EntityUtils.toString(httpEntity);
            EntityUtils.consume(httpEntity);
        } catch (NullPointerException ignore) {
        }
        return result;
    }

    /**
     * Executes an HTTP GET request for the specified URL using the specified authentication method.
     *
     * @param targetURL The target URL for the GET request.
     * @param authenticationMethod The authentication method to use (BasicAuth, AccessToken, or None).
     * @return The response body as a string.
     */
    public String executeHttpGetRequest(String targetURL, AuthenticationMethod authenticationMethod) {
        String result = "";
        CloseableHttpResponse httpResponse = null;
        try {
            HttpGet httpRequest = createGetRequest(targetURL, authenticationMethod);
            httpResponse = executeHttpRequest(httpRequest);
            result = getHttpResponseBody(httpResponse);
            httpResponse.close();
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error("{} -> Target URL: {}", e.getMessage(), targetURL, e);
            if (result.isBlank()) {
                result = e.getMessage();
            }
        } finally {
            if (httpResponse != null) {
                try {
                    httpResponse.close();
                } catch (IOException e) {
                    ScenarioLogManager.getLogger().warn(e.getMessage(), e);
                }
            }
        }
        return result;
    }

    /**
     * Executes an HTTP GET request for the specified URL with additional headers using the specified
     * authentication method.
     *
     * @param targetURL The target URL for the GET request.
     * @param headerList A list of additional headers to include in the request.
     * @param authenticationMethod The authentication method to use (BasicAuth, AccessToken, or None).
     * @return The response body as a string.
     */
    public String executeHttpGetRequest(String targetURL, List<Header> headerList, AuthenticationMethod authenticationMethod) {
        String result = "";
        CloseableHttpResponse httpResponse = null;
        try {
            HttpGet httpRequest = createGetRequest(targetURL, headerList, authenticationMethod);
            httpResponse = executeHttpRequest(httpRequest);
            result = getHttpResponseBody(httpResponse);
            httpResponse.close();
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error("{} -> Target URL: {}", e.getMessage(), targetURL, e);
            if (result.isBlank()) {
                result = e.getMessage();
            }
        } finally {
            if (httpResponse != null) {
                try {
                    httpResponse.close();
                } catch (IOException e) {
                    ScenarioLogManager.getLogger().warn(e.getMessage(), e);
                }
            }
        }
        return result;
    }

    /**
     * Executes an HTTP GET request for the specified URL, saves the response content to a file, and
     * returns the saved file.
     *
     * @param targetURL The target URL for the GET request.
     * @param savePath The path to save the response content.
     * @param authenticationMethod The authentication method to use (BasicAuth, AccessToken, or None).
     * @return The saved file containing the response content.
     */
    public File executeHttpGetRequestForFile(String targetURL, Path savePath, AuthenticationMethod authenticationMethod) {
        File resultFile = new File(savePath.toString());
        CloseableHttpResponse httpResponse = null;
        try {
            HttpGet httpRequest = createGetRequest(targetURL, authenticationMethod);
            httpResponse = executeHttpRequest(httpRequest);
            HttpEntity httpEntity = httpResponse.getEntity();
            lastRequestContentType = httpEntity.getContentType();
            try (FileOutputStream outputStream = new FileOutputStream(resultFile)) {
                httpEntity.writeTo(outputStream);
                EntityUtils.consume(httpEntity);
            } catch (IOException e) {
                ScenarioLogManager.getLogger().error("Could not write to file: \"{}\"", savePath, e);
            }
            httpResponse.close();
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error("{}: {}", e.getMessage(), targetURL, e);
        } finally {
            if (httpResponse != null) {
                try {
                    httpResponse.close();
                } catch (IOException e) {
                    ScenarioLogManager.getLogger().warn(e.getMessage(), e);
                }
            }
        }
        return resultFile;
    }

    /**
     * Executes an HTTP POST request for the specified URL with the provided payload and content type,
     * using the specified authentication method.
     *
     * @param targetURL The target URL for the POST request.
     * @param payload The payload to send in the request body.
     * @param contentType The content type of the payload.
     * @param authenticationMethod The authentication method to use (BasicAuth, AccessToken, or None).
     * @return The response body as a string.
     */
    public String executeHttpPostRequest(String targetURL, String payload, ContentType contentType, AuthenticationMethod authenticationMethod) {
        String result = "";
        CloseableHttpResponse httpResponse = null;
        try {
            HttpPost httpRequest = createPostRequest(targetURL, authenticationMethod);
            httpRequest.addHeader("Content-Type", contentType.getMimeType());
            httpRequest.setEntity(new StringEntity(payload, contentType.withCharset(StandardCharsets.UTF_8)));
            httpResponse = executeHttpRequest(httpRequest);
            result = getHttpResponseBody(httpResponse);
            httpResponse.close();
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error("{} -> Target URL: {}", e.getMessage(), targetURL, e);
            if (result.isBlank()) {
                result = e.getMessage();
            }
        } finally {
            if (httpResponse != null) {
                try {
                    httpResponse.close();
                } catch (IOException e) {
                    ScenarioLogManager.getLogger().warn(e.getMessage(), e);
                }
            }
        }
        return result;
    }

    /**
     * Executes an HTTP POST request for the specified URL with files to upload, using the specified
     * authentication method.
     *
     * @param targetURL The target URL for the POST request.
     * @param infoFilePath The file path of the info file to upload.
     * @param resultFilePath The file path of the result file to upload.
     * @param authenticationMethod The authentication method to use (BasicAuth, AccessToken, or None).
     * @return The response body as a string.
     */
    public String executeHttpPostRequest(String targetURL, String infoFilePath, String resultFilePath, AuthenticationMethod authenticationMethod) {
        String result = "";
        boolean readTimeOutExceptionCaught;
        LocalDateTime timeout = LocalDateTime.now(ZoneId.of("Europe/Berlin")).plusSeconds(240L);
        do {
            readTimeOutExceptionCaught = false;
            CloseableHttpResponse httpResponse = null;
            try {
                HttpPost httpRequest = createPostRequest(targetURL, authenticationMethod);
                httpRequest.addHeader("X-Atlassian-Token", "no-check");
                HttpEntity entity = MultipartEntityBuilder.create()
                        .addBinaryBody("info", new File(infoFilePath), ContentType.APPLICATION_JSON.withCharset(StandardCharsets.UTF_8),
                                "xrayresultimport.json")
                        .addBinaryBody("result", new File(resultFilePath), ContentType.APPLICATION_JSON.withCharset(StandardCharsets.UTF_8), "data.json")
                        .build();
                httpRequest.setEntity(entity);
                httpResponse = executeHttpRequest(httpRequest);
                result = getHttpResponseBody(httpResponse);
                httpResponse.close();
            } catch (Exception e) {
                if (e instanceof SocketTimeoutException) {
                    readTimeOutExceptionCaught = true;
                }
                ScenarioLogManager.getLogger().error("{} -> Target URL: {}", e.getMessage(), targetURL, e);
                if (result.isBlank()) {
                    result = e.getMessage();
                }
            } finally {
                if (httpResponse != null) {
                    try {
                        httpResponse.close();
                    } catch (IOException e) {
                        ScenarioLogManager.getLogger().warn(e.getMessage(), e);
                    }
                }
            }
        } while (readTimeOutExceptionCaught && LocalDateTime.now(ZoneId.of("Europe/Berlin")).isBefore(timeout));
        return result;
    }

    /**
     * Executes an HTTP PUT request for the specified URL with the provided binary file, using the
     * specified authentication method.
     *
     * @param targetURL The target URL for the PUT request.
     * @param binaryFilePath The path to the binary file to upload.
     * @param authenticationMethod The authentication method to use (BasicAuth, AccessToken, or None).
     * @return The response body as a string.
     */
    public String executeHttpPutRequest(String targetURL, Path binaryFilePath, AuthenticationMethod authenticationMethod) {
        String result = "";
        boolean readTimeOutExceptionCaught;
        LocalDateTime timeout = LocalDateTime.now(ZoneId.of("Europe/Berlin")).plusSeconds(240L);
        do {
            readTimeOutExceptionCaught = false;
            CloseableHttpResponse httpResponse = null;
            try {
                HttpPut httpRequest = createPutRequest(targetURL, authenticationMethod);
                httpRequest.setEntity(new FileEntity(binaryFilePath.toFile(), ContentType.DEFAULT_BINARY));
                httpResponse = executeHttpRequest(httpRequest);
                result = getHttpResponseBody(httpResponse);
                httpResponse.close();
            } catch (Exception e) {
                if (e instanceof SocketTimeoutException) {
                    readTimeOutExceptionCaught = true;
                }
                ScenarioLogManager.getLogger().error("{} -> Target URL: {}", e.getMessage(), targetURL, e);
                if (result.isBlank()) {
                    result = e.getMessage();
                }
            } finally {
                if (httpResponse != null) {
                    try {
                        httpResponse.close();
                    } catch (IOException e) {
                        ScenarioLogManager.getLogger().warn(e.getMessage(), e);
                    }
                }
            }
        } while (readTimeOutExceptionCaught && LocalDateTime.now(ZoneId.of("Europe/Berlin")).isBefore(timeout));
        return result;
    }

    /**
     * Executes an HTTP PUT request for the specified URL with the provided payload, using the specified
     * authentication method.
     *
     * @param targetURL The target URL for the PUT request.
     * @param payload The payload to send in the request body.
     * @param authenticationMethod The authentication method to use (BasicAuth, AccessToken, or None).
     * @return The response body as a string.
     */
    public String executeHttpPutRequest(String targetURL, String payload, AuthenticationMethod authenticationMethod) {
        String result = "";
        CloseableHttpResponse httpResponse = null;
        try {
            HttpPut httpRequest = createPutRequest(targetURL, authenticationMethod);
            httpRequest.setEntity(new StringEntity(payload, ContentType.APPLICATION_JSON.withCharset(StandardCharsets.UTF_8)));
            httpResponse = executeHttpRequest(httpRequest);
            result = getHttpResponseBody(httpResponse);
            httpResponse.close();
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error("{} -> Target URL: {}", e.getMessage(), targetURL, e);
            if (result.isBlank()) {
                result = e.getMessage();
            }
        } finally {
            if (httpResponse != null) {
                try {
                    httpResponse.close();
                } catch (IOException e) {
                    ScenarioLogManager.getLogger().warn(e.getMessage(), e);
                }
            }
        }
        return result;
    }

    /**
     * Closes the HTTP client, releasing any resources it holds.
     */
    public void closeHttpClient() {
        try {
            HTTP_CLIENT.close();
        } catch (IOException e) {
            ScenarioLogManager.getLogger().error(e.getMessage(), e);
        }
    }

    /**
     * Closes the HTTP client. This method is part of the {@link AutoCloseable} interface.
     */
    @Override
    public void close() {
        closeHttpClient();
    }

    /**
     * Enum representing different authentication methods that can be used in HTTP requests.
     */
    public enum AuthenticationMethod {
        /**
         * Indicates basic authentication should be used.
         */
        BasicAuth,
        /**
         * Represents an authorization token to be used.
         */
        AccessToken,
        /**
         * Stands for no authentication method is required for the request.
         */
        None
    }

}
