<?php
include_once 'includes/IJSONSerializable.php';

/**
 * Highscore class
 *
 * @author Ethem Kurt
 *        
 */
class Highscore implements IJSONSerializable
{

    /**
     * Score
     *
     * @var integer
     */
    private $score = 0;

    /**
     * Tries
     *
     * @var integer
     */
    private $tries = 0;

    /**
     * Level
     *
     * @var integer
     */
    private $level = 0;

    /**
     * Name
     *
     * @var string
     */
    private $name = '';

    /**
     * Constructor
     *
     * @param object $highscore
     *            Highscore
     */
    function __construct($highscore)
    {
        if (isset($highscore->score) && isset($highscore->tries) && isset($highscore->level) && isset($highscore->name))
        {
            if (is_numeric($highscore->score) && is_numeric($highscore->tries) && is_numeric($highscore->level) && is_string($highscore->name))
            {
                $this->score = intval($highscore->score);
                $this->tries = intval($highscore->tries);
                $this->level = intval($highscore->level);
                $this->name = $highscore->name;
            }
        }
    }

    /**
     * Get score
     *
     * @return integer Score
     */
    public function GetScore()
    {
        return $this->score;
    }

    /**
     * Get tries
     *
     * @return integer Tries
     */
    public function GetTries()
    {
        return $this->tries;
    }

    /**
     * Get level
     *
     * @return integer Level
     */
    public function GetLevel()
    {
        return $this->level;
    }

    /**
     * Get name
     *
     * @return string Name
     */
    public function GetName()
    {
        return $this->name;
    }

    /**
     * Get object variables
     *
     * @return array Object variables
     */
    public function GetObjectVars()
    {
        return get_object_vars($this);
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