<?php
include_once 'includes/Highscore.php';

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
     * @return Highscores Highscore
     */
    public static function NewHighscore($highscores, $baseRank, $appSecret)
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
        $ret->Init($appSecret);
        return $ret;
    }

    /**
     * Initialize
     *
     * @param string $appSecret
     *            Application secret
     */
    private function Init($appSecret)
    {
        $this->id = $this->CreateHash($appSecret);
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
        $str = '';
        foreach ($this->highscores as $highscore)
        {
            if ($highscore instanceof Highscore)
            {
                $str .= $highscore->GetName() . ';' . $highscore->GetScore() . ';' . $highscore->GetTries() . ';' . $highscore->GetLevel() . '|';
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
        $ret = get_object_vars($this);
        foreach ($this->highscores as $key => $highscore)
        {
            $ret['highscores'][$key] = $highscore->GetObjectVars();
        }
        return json_encode($ret);
    }
}
?>