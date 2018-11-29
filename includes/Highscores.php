<?php
include_once 'includes/Highscore.php';
include_once 'includes/HighscoreField.php';

/**
 * Highscores class
 *
 * @author Ethem Kurt
 *        
 */
class Highscores implements IJSONSerializable
{

    /**
     * Highscores
     *
     * @var Highscore[]
     */
    private $highscores = array();

    /**
     * Base rank
     *
     * @var integer
     */
    private $baseRank = 1;

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
     * @param object $highscores
     *            Highscore
     * @return Highscores Highscore
     */
    public static function FromRequest($highscores)
    {
        $ret = new self();
        if (is_object($highscores))
        {
            if (isset($highscores->highscores) && isset($highscores->baseRank) && isset($highscores->id))
            {
                if (is_array($highscores->highscores))
                {
                    foreach ($highscores->highscores as $highscore)
                    {
                        if (is_object($highscore))
                        {
                            $ret->highscores[] = new Highscore($highscore);
                        }
                    }
                }
                if (is_int($highscores->baseRank))
                {
                    $ret->baseRank = $highscores->baseRank;
                }
                if (is_string($highscores->id))
                {
                    $ret->id = $highscores->id;
                }
            }
        }
        return $ret;
    }

    /**
     * New highscore
     *
     * @param array $highscores
     *            Highscore entries
     * @param integer $baseRank
     *            Base rank
     * @param string $appSecret
     *            Application secret
     * @param array $hashOrder
     *            Hash order
     * @return Highscores Highscore
     */
    public static function NewHighscore($highscores, $baseRank, $appSecret, $hashOrder)
    {
        $ret = new self();
        if (is_array($highscores))
        {
            $ret->highscores = $highscores;
        }
        if (is_int($baseRank))
        {
            $ret->baseRank = $baseRank;
        }
        $ret->Init($appSecret, $hashOrder);
        return $ret;
    }

    /**
     * Initialize
     *
     * @param string $appSecret
     *            Application secret
     * @param array $hashOrder
     *            Hash order
     */
    private function Init($appSecret, $hashOrder)
    {
        $this->id = $this->CreateHash($appSecret, $hashOrder);
    }

    /**
     * Create hash
     *
     * @param string $appSecret
     *            Application secret
     * @param array $hashOrder
     *            Hash order
     * @return string Hash
     */
    public function CreateHash($appSecret, $hashOrder)
    {
        $str = '';
        foreach ($this->highscores as $highscore)
        {
            if ($highscore instanceof Highscore)
            {
                $first = true;
                if (is_array($hashOrder))
                {
                    foreach ($hashOrder as $field)
                    {
                        if (isset($highscore->$field))
                        {
                            if ($first)
                            {
                                $first = false;
                            }
                            else
                            {
                                $str .= ';';
                            }
                            $str .= $highscore->$field;
                        }
                    }
                }
                $str .= '|';
            }
        }
        $str .= $this->baseRank;
        if (is_string($appSecret))
        {
            $str .= '|' . $appSecret;
        }
        return strtolower(hash('sha512', $str));
    }

    /**
     * Get ID
     *
     * @return string ID
     */
    public function GetID()
    {
        return $this->id;
    }

    /**
     * Get highscores
     *
     * @return array Highscores
     */
    public function GetHighscores()
    {
        return $this->highscores;
    }

    /**
     * Get base rank
     *
     * @return integer Base rank
     */
    public function GetBaseRank()
    {
        return $this->baseRank;
    }

    /**
     * Validate
     *
     * @param string $appSecret
     *            Application secret
     * @param array $hashOrder
     *            Hash order
     * @return boolean "true" if valid, otherwise "false"
     */
    public function Validate($appSecret, $hashOrder)
    {
        return ($this->id === $this->CreateHash($appSecret, $hashOrder));
    }

    /**
     *
     * {@inheritdoc}
     * @see IJSONSerializable::ToJSON()
     */
    public function ToJSON()
    {
        $ret = get_object_vars($this);
        foreach ($this->highscores as $key => $highscore)
        {
            $ret['highscores'][$key] = $highscore->GetObjectVars();
        }
        return json_encode($ret);
    }
}
?>