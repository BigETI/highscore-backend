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
     * Constructor
     *
     * @param object $highscores
     *            Highscore
     */
    function __construct($highscores)
    {
        if (is_object($highscores))
        {
            if (is_array($highscores->highscores))
            {
                $this->highscores = $highscores->highscores;
            }
            if (is_int($highscores->baseRank))
            {
                $this->baseRank = $highscores->baseRank;
            }
            if (is_string($highscores->id))
            {
                $this->id = $highscores->id;
            }
        }
    }

    /**
     * Constructor
     *
     * @param array $highscores
     *            Highscores
     * @param integer $baseRank
     *            Base rank
     * @param string $appSecret
     *            Application secret
     */
    function __construct($highscores, $baseRank, $appSecret)
    {
        if (is_array($highscores))
        {
            $this->highscores = $highscores;
        }
        if (is_int($baseRank))
        {
            $this->baseRank = $baseRank;
        }
        $this->Init($appSecret);
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
    private function CreateHash($appSecret)
    {
        $str = '';
        foreach ($this->highscores as $highscore)
        {
            if ($highscore instanceof Highscore)
            {
                $str .= $highscore->GetName() . ';' . $highscore->GetScore() . ';' . $highscore->GetTries() . ';' . $highscore->GetLevel() . '|';
            }
        }
        $str .= $this->baseRank . '|' . $appSecret;
        return hash('sha512', $str);
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
     * @param string $hashKey
     *            Hash key
     * @return boolean "true" if valid, otherwise "false"
     */
    public function Validate($hashKey)
    {
        return ($this->id === CreateHash($hashKey));
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
        return json_encode();
    }
}
?>