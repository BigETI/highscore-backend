<?php
include_once 'includes/IJSONSerializable.php';
include_once 'includes/HighscoreField.php';

/**
 * Highscore class
 *
 * @author Ethem Kurt
 *        
 */
class Highscore implements IJSONSerializable
{

    /**
     * Constructor
     *
     * @param object $highscore
     *            Highscore
     * @param array $highscoreFields
     *            Highscore fields
     */
    function __construct($highscore, $highscoreFields)
    {
        if (is_object($highscore) && is_array($highscoreFields))
        {
            foreach ($highscoreFields as $highscore_field)
            {
                if ($highscore_field instanceof HighscoreField)
                {
                    $field = $highscore_field->GetField();
                    if (isset($highscore->$field))
                    {
                        if ($highscore_field->IsNumeric())
                        {
                            if (is_numeric($highscore->$field))
                            {
                                $this->$field = intval($highscore->$field);
                            }
                        }
                        else if (is_string($highscore->$field))
                        {
                            $this->$field = $highscore->$field;
                        }
                    }
                }
            }
        }
    }

    /**
     * Equals
     *
     * @param Highscore $highscore
     *            Highscore
     * @param array $highscoreFields
     *            Highscore fields
     * @return boolean "true" if equals, otherwise "false"
     */
    public function Equals($highscore, $highscoreFields)
    {
        $ret = true;
        if (is_array($highscoreFields))
        {
            foreach ($highscoreFields as $highscore_field)
            {
                if ($highscore_field instanceof HighscoreField)
                {
                    $field = $highscore_field->GetField();
                    if (isset($this->$field) && isset($highscore->$field))
                    {
                        if ($this->$field !== $highscore->$field)
                        {
                            $ret = false;
                            break;
                        }
                    }
                }
            }
        }
        return $ret;
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