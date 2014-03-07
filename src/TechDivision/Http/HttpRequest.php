<?php
/**
 * \TechDivision\Http\HttpRequest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
 */

namespace TechDivision\Http;

/**
 * Class HttpRequest
 *
 * @category  Library
 * @package   TechDivision_Http
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_Http
 */
class HttpRequest implements HttpRequestInterface
{
    /**
     * Hold's all headers got from http connection
     *
     * @var
     */
    protected $headers = array();

    /**
     * Hold's the http request method
     *
     * @var string
     */
    protected $method;

    /**
     * Hold's the protocol version
     *
     * @var string
     */
    protected $version;

    /**
     * Holds the uniform resource identifier
     *
     * @var string
     */
    protected $uri;

    /**
     * Hold's the file descriptor resource to body stream
     *
     * @var resource
     */
    protected $bodyStream;

    /**
     * Hold's the document root directory
     *
     * @var string
     */
    protected $documentRoot;

    /**
     * Hold's the request parameters
     *
     * @var array
     */
    protected $params;

    /**
     * Initialises the request object to default properties
     *
     * @return void
     */
    public function init()
    {
        // if body stream exists close it
        if (is_resource($this->bodyStream)) {
            fclose($this->bodyStream);
        }
        // init body stream
        $this->bodyStream = fopen('php://memory', 'w+');

        // init default response properties
        $this->headers = array();
        $this->params = array();
        $this->uri = null;
        $this->method = null;
        $this->version = null;
        $this->queryString = null;
        $this->scriptName = null;
        $this->pathInfo = null;
        $this->realPath = null;
    }

    /**
     * Add's a header information got from connection
     *
     * @param string $name  The header name
     * @param string $value The headers value
     *
     * @return void
     */
    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Check's if header exists by given name
     *
     * @param string $name The header name to check
     *
     * @return boolean
     */
    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }

    /**
     * Return's header by given name
     *
     * @param string $name The header name to get
     *
     * @return string|null
     */
    public function getHeader($name)
    {
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }
    }

    /**
     * Return's all headers as array
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Resets all headers by given array
     *
     * @param array $headers The headers array
     *
     * @return void
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * Set's the uri
     *
     * @param string $uri The uri
     *
     * @return void
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Set's the method
     *
     * @param string $method The http method
     *
     * @return void
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Get's request method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Return's requested uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set's document root
     *
     * @param string $documentRoot The document root
     *
     * @return void
     * @deprecated
     */
    public function setDocumentRoot($documentRoot)
    {
        $this->documentRoot = $documentRoot;
    }

    /**
     * Return's the document root
     *
     * @return string
     * @deprecated
     */
    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }

    /**
     * Set's query string
     *
     * @param string $queryString The requests query string
     *
     * @return void
     */
    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;
    }

    /**
     * Return's query string
     *
     * @return string The query string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Reset's the stream resource pointing to body content
     *
     * @param resource $bodyStream The body content stream resource
     *
     * @return void
     */
    public function setBodyStream($bodyStream)
    {
        // check if old body stream is still open
        if (is_resource($this->bodyStream)) {
            // close it before
            fclose($this->bodyStream);
            // free it
            unset($this->bodyStream);
        }
        $this->bodyStream = $bodyStream;
    }

    /**
     * Return's the stream resource pointing to body content
     *
     * @return resource The body content stream resource
     */
    public function getBodyStream()
    {
        return $this->bodyStream;
    }

    /**
     * Return's the body content stored in body stream
     *
     * @return string
     */
    public function getBodyContent()
    {
        // set bodystream resource ref to var
        $bodyStream = $this->getBodyStream();
        // rewind pointer
        rewind($bodyStream);
        // returns whole body content
        return fread($bodyStream, $this->getHeader(HttpProtocol::HEADER_CONTENT_LENGTH));
    }

    /**
     * Copies a source stream to body stream
     *
     * @param resource $sourceStream The file pointer to source stream
     * @param int      $maxlength    The max length to read from source stream
     * @param int      $offset       The offset from source stream to read
     *
     * @return int the total count of bytes copied.
     */
    public function copyBodyStream($sourceStream, $maxlength = null, $offset = null)
    {
        return stream_copy_to_stream($sourceStream, $this->getBodyStream(), $maxlength, $offset);
    }

    /**
     * Append's body stream with content
     *
     * @param string $content The content to append
     *
     * @return int
     */
    public function appendBodyStream($content)
    {
        return fwrite($this->getBodyStream(), $content);
    }

    /**
     * Set's the http request version
     *
     * @param string $version The http request version
     *
     * @return void
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Set's a parameter given in query string
     *
     * @param string $param The param key
     * @param string $value The param value
     *
     * @return void
     */
    public function setParam($param, $value)
    {
        $this->params[$param] = $value;
    }

    /**
     * Return's a param value by given key
     *
     * @param string $param The param key
     *
     * @return string|null The param value
     */
    public function getParam($param)
    {
        if (isset($this->params[$param])) {
            return $this->params[$param];
        }
    }

    /**
     * Return's the array of all params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set's the array of all params
     *
     * @param array $params The params array to set
     *
     * @return array
     */
    public function setParams($params)
    {
        $this->params = $params;
    }
}
