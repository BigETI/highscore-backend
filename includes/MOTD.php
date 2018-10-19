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
     * Constructor
     *
     * @param string $motd
     *            Message of the day
     */
    function __construct($motd)
    {
        if (is_string($motd))
        {
            $this->motd = $motd;
        }
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