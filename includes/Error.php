<?php
include_once 'includes/IJSONSerializable.php';

/**
 * Error class
 *
 * @author Ethem Kurt
 *        
 */
class Error implements IJSONSerializable
{

    /**
     * Error ID
     *
     * @var string
     */
    private $errorID = '';

    /**
     * Error
     *
     * @var string
     */
    private $error = '';

    /**
     * HTTP response code
     *
     * @var integer
     */
    private $httpResponseCode = 400;

    /**
     * Bad request
     *
     * @var integer
     */
    const BAD_REQUEST = 400;

    /**
     * Unauthorized
     *
     * @var integer
     */
    const UNAUTHORIZED = 401;

    /**
     * Forbidden
     *
     * @var integer
     */
    const FORBIDDEN = 403;

    /**
     * Internal server error
     *
     * @var integer
     */
    const INTERNAL_SERVER_ERROR = 500;

    /**
     * Constructor
     *
     * @param string $errorID
     *            Error ID
     * @param string $error
     *            Error
     * @param integer $httpResponseCode
     *            HTTP response code
     */
    function __construct($errorID, $error, $httpResponseCode)
    {
        if (is_string($errorID))
        {
            $this->errorID = $errorID;
        }
        if (is_string($error))
        {
            $this->error = $error;
        }
        if (is_int($httpResponseCode))
        {
            $this->httpResponseCode = $httpResponseCode;
        }
    }

    /**
     * Get error ID
     *
     * @return string Error ID
     */
    public function GetErrorID()
    {
        return $this->errorID;
    }

    /**
     * Get error
     *
     * @return string Error
     */
    public function GetError()
    {
        return $this->error;
    }

    /**
     * Get HTTP response code
     *
     * @return integer HTTP response code
     */
    public function GetHTTPResponseCode()
    {
        return $this->httpResponseCode;
    }

    /**
     *
     * {@inheritdoc}
     * @see IJSONSerializable::ToJSON()
     */
    public function ToJSON()
    {
        return json_encode(get_object_vars($this));
    }
}
?>