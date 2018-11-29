<?php

/**
 * Highscore field class
 * @author BigETI
 *
 */
class HighscoreField
{

    /**
     * Field
     *
     * @var string
     */
    private $field = '';

    /**
     * Sort criteria
     *
     * @var integer
     */
    private $sortCriteria = 0;

    /**
     * Is numeric
     *
     * @var boolean
     */
    private $isNumeric = false;

    /**
     * Constructor
     *
     * @param string $field
     *            Field
     * @param integer $sortCriteria
     *            Sort criteria
     * @param boolean $isNumeric
     *            Is numeric
     */
    function __construct($field, $sortCriteria, $isNumeric)
    {
        if (is_string($field))
        {
            $this->field = $field;
        }
        if (is_int($sortCriteria))
        {
            $this->sortCriteria = $sortCriteria;
        }
        if (is_bool($isNumeric))
        {
            $this->isNumeric = $isNumeric;
        }
    }

    /**
     * Get field
     *
     * @return string Field
     */
    public function GetField()
    {
        return $this->field;
    }

    /**
     * Get sort criteria
     *
     * @return integer Sort criteria
     */
    public function GetSortCriteria()
    {
        return $this->sortCriteria;
    }

    /**
     * Is numeric
     *
     * @return boolean "true" if numeric, otherwise "false"
     */
    public function IsNumeric()
    {
        return $this->isNumeric;
    }
}
?>