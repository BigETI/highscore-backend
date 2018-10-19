<?php
include_once 'includes/IJSONSerializable.php';

/**
 * User UUID class
 *
 * @author Ethem Kurt
 *        
 */
class UserUUID implements IJSONSerializable
{

    /**
     * User UUID
     *
     * @var string
     */
    private $userUUID = '';

    /**
     * Constructor
     *
     * @param string $userUUID
     *            User UUID
     */
    function __construct($userUUID)
    {
        if (is_string($userUUID))
        {
            $this->userUUID = $userUUID;
        }
    }

    /**
     * Get user UUID
     *
     * @return string User UUID
     */
    public function GetUserUUID()
    {
        return $this->userUUID;
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