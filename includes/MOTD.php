<?php
include_once 'includes/IJSONSerializable.php';

/**
 * Message of the day class
 *
 * @author Ethem Kurt
 *        
 */
class MOTD implements IJSONSerializable
{

    /**
     * Message of the day
     *
     * @var string
     */
    private $motd = '';

    /**
     * ID
     *
     * @var string
     */
    private $id = '';

    /**
     * Default constructor
     */
    function __construct()
    {
        // ...
    }

    /**
     * From request
     *
     * @param object $motd
     *            Message of the day object
     * @return MOTD MOTD object
     */
    public static function FromRequest($motd)
    {
        $ret = new self();
        if (is_object($motd))
        {
            if (isset($motd->motd) && isset($motd->id))
            {
                if (is_string($motd->motd) && is_string($motd->id))
                {
                    $ret->motd = $motd->motd;
                    $ret->id = $motd->id;
                }
            }
        }
        return $ret;
    }

    /**
     * New MOTD
     *
     * @param string $motd
     *            Message of the day
     * @param string $appSecret
     *            Application secret
     * @return MOTD MOTD object
     */
    public static function NewMOTD($motd, $appSecret)
    {
        $ret = new self();
        if (is_string($motd))
        {
            $ret->motd = $motd;
        }
        $ret->id = $ret->CreateHash($appSecret);
        return $ret;
    }

    /**
     * Create hash
     *
     * @param string $appSecret
     *            Application secret
     * @return string Hash
     */
    public function CreateHash($appSecret)
    {
        return strtolower(hash('sha512', $this->motd . (is_string($appSecret) ? ('|' . $appSecret) : '')));
    }

    /**
     * Get message of the day
     *
     * @return string Message of the day
     */
    public function GetMOTD()
    {
        return $this->motd;
    }
    
    /**
     * Validate
     *
     * @param string $appSecret
     *            Application secret
     * @return boolean "true" if valid, otherwise "false"
     */
    public function Validate($appSecret)
    {
        return ($this->id === $this->CreateHash($appSecret));
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