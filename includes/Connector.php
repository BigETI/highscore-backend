<?php
include_once 'includes/Error.php';
include_once 'includes/Highscores.php';
include_once 'includes/MOTD.php';
include_once 'includes/Network.php';
include_once 'includes/UserUUID.php';
include_once 'includes/UUID.php';

/**
 * Connector class
 *
 * @author Ethem Kurt
 *        
 */
class Connector
{

    /**
     * MySQLi
     *
     * @var mysqli object
     */
    private $mysqli = null;

    /**
     * Tables
     *
     * @var array
     */
    private $tables = array();

    /**
     * Maximal infraction points
     *
     * @var integer
     */
    private $maxInfractionPoints = 10;

    /**
     * Rate limit
     *
     * @var integer
     */
    private $rateLimit = 10;

    /**
     * Rate limit time
     *
     * @var integer
     */
    private $rateLimitTime = 2;

    /**
     * Maximal highscore size
     *
     * @var integer
     */
    private $maxHighscoreSize = 100;

    /**
     * Application UUID
     *
     * @var string
     */
    private $appUUID = '';

    /**
     * Application name
     *
     * @var string
     */
    private $appName = '';

    /**
     * Global app secret
     * The default value is temporary and will be discarded when config.json is parsed or loaded from the database
     *
     * @var string
     */
    private $appSecret = 'PEvneHhhrlLZgAuiBoRcRJTYrBh26VIYkNwsJknjUMB92r8gVbJ8PBEOK38GMmlg';

    /**
     * Application privileges
     *
     * @var array
     */
    private $appPrivileges = array();

    /**
     * Bad words
     *
     * @var string[]
     */
    private $badWords = null;

    /**
     * Bad words replace with
     *
     * @var string[]
     */
    private $badWordsReplaceWith = null;

    /**
     * Constructor
     *
     * @param string $appName
     *            App name
     */
    public function __construct($appName)
    {
        $config_json = file_get_contents('./includes/config.json');
        if ($config_json !== false)
        {
            $config = json_decode($config_json);
            if (is_object($config))
            {
                if (isset($config->database))
                {
                    if (is_object($config->database))
                    {
                        if (isset($config->database->auth))
                        {
                            if (is_object($config->database->auth))
                            {
                                if (isset($config->database->auth->host) && isset($config->database->auth->username) && isset($config->database->auth->passwd) && isset($config->database->auth->dbname) && isset($config->database->auth->port))
                                {
                                    if (is_string($config->database->auth->host) && is_string($config->database->auth->username) && is_string($config->database->auth->passwd) && is_string($config->database->auth->dbname) && is_int($config->database->auth->port))
                                    {
                                        $this->mysqli = new mysqli($config->database->auth->host, $config->database->auth->username, $config->database->auth->passwd, $config->database->auth->dbname, $config->database->auth->port);
                                        if ($this->mysqli->connect_error)
                                        {
                                            $this->mysqli = null;
                                        }
                                    }
                                }
                            }
                        }
                        if (isset($config->database->tables))
                        {
                            if (is_object($config->database->tables))
                            {
                                foreach ($config->database->tables as $alias => $table)
                                {
                                    if (is_string($alias) && is_string($table))
                                    {
                                        $this->tables[$alias] = $table;
                                    }
                                }
                            }
                        }
                    }
                    if (isset($config->limits))
                    {
                        if (is_object($config->limits))
                        {
                            if (isset($config->limits->maxInfractionPoints))
                            {
                                if (is_int($config->limits->maxInfractionPoints))
                                {
                                    $this->maxInfractionPoints = $config->limits->maxInfractionPoints;
                                }
                            }
                            if (isset($config->limits->rateLimit))
                            {
                                if (is_int($config->limits->rateLimit))
                                {
                                    $this->rateLimit = $config->limits->rateLimit;
                                }
                            }
                            if (isset($config->limits->rateLimitTime))
                            {
                                if (is_int($config->limits->rateLimitTime))
                                {
                                    $this->rateLimitTime = $config->limits->rateLimitTime;
                                }
                            }
                            if (isset($config->limits->maxHighscoreSize))
                            {
                                if (is_int($config->limits->maxHighscoreSize))
                                {
                                    $this->maxHighscoreSize = $config->limits->maxHighscoreSize;
                                }
                            }
                        }
                    }
                    if (isset($config->globalSecrets))
                    {
                        if (is_object($config->globalSecrets))
                        {
                            if (isset($config->globalSecrets->appSecret))
                            {
                                if (is_string($config->globalSecrets->appSecret))
                                {
                                    $this->appSecret = $config->globalSecrets->appSecret;
                                }
                            }
                        }
                    }
                    if (isset($config->globalAppPrivileges))
                    {
                        if (is_object($config->globalAppPrivileges))
                        {
                            foreach ($config->globalAppPrivileges as $key => $value)
                            {
                                if (is_string($key) && is_int($value))
                                {
                                    $this->appPrivileges[$key] = $value;
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->InitApp($appName);
    }

    /**
     * Initialize application
     *
     * @param string $appName
     *            Application name
     */
    private function InitApp($appName)
    {
        if (is_string($appName))
        {
            $this->appName = $appName;
            if ($this->mysqli instanceof mysqli)
            {
                $result = $this->mysqli->query('SELECT `uuid`, `name`, `secret` FROM `' . $this->mysqli->real_escape_string($this->ResolveTableName('apps')) . '` WHERE `name`=\'' . $this->mysqli->real_escape_string($appName) . '\' LIMIT 1;');
                if ($result instanceof mysqli_result)
                {
                    $app = $result->fetch_object();
                    if (is_object($app))
                    {
                        if (isset($app->uuid) && isset($app->name) && isset($app->secret))
                        {
                            if (is_string($app->uuid) && is_string($app->name) && is_string($app->secret))
                            {
                                $this->appUUID = $app->uuid;
                                $this->appName = $app->name;
                                $this->appSecret = $app->secret;
                                $inner_result = $this->mysqli->query('SELECT `privilege`, `value` FROM `' . $this->mysqli->real_escape_string($this->ResolveTableName('appPrivileges')) . '` WHERE `appUUID`=\'' . $this->mysqli->real_escape_string($this->appUUID) . '\';');
                                if ($inner_result instanceof mysqli_result)
                                {
                                    while (is_object($privilege = $inner_result->fetch_object()))
                                    {
                                        if (isset($privilege->privilege) && isset($privilege->value))
                                        {
                                            if (is_string($privilege->privilege) && is_numeric($privilege->value))
                                            {
                                                $this->appPrivileges[$privilege->privilege] = intval($privilege->value);
                                            }
                                        }
                                    }
                                    $inner_result->close();
                                    unset($inner_result);
                                }
                            }
                        }
                    }
                    $result->close();
                    unset($result);
                }
            }
        }
    }

    /**
     * Initialize user
     *
     * @param string $userUUID
     *            User UUID
     * @return string Correct user UUID
     */
    private function InitUser($userUUID)
    {
        $ret = '';
        if (is_string($userUUID))
        {
            $ret = $userUUID;
            if ($this->mysqli instanceof mysqli)
            {
                if (trim($userUUID) == '')
                {
                    $ret = $this->CreateNewUser();
                }
                else
                {
                    $result = $this->mysqli->query('SELECT `uuid` FROM `' . $this->mysqli->real_escape_string($this->ResolveTableName('users')) . '` WHERE `uuid`=\'' . $this->mysqli->real_escape_string($userUUID) . '\' LIMIT 1;');
                    if ($result instanceof mysqli_result)
                    {
                        if ($result->num_rows <= 0)
                        {
                            $ret = $this->CreateNewUser();
                        }
                        $result->close();
                        unset($result);
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * Initialize bad words
     */
    private function InitBadWords()
    {
        if (is_null($this->badWords))
        {
            $this->badWords = array();
            $this->badWordsReplaceWith = array();
            if ($this->mysqli instanceof mysqli)
            {
                $result = $this->mysqli->query('SELECT `badWord`, `replaceWith` FROM `' . $this->mysqli->real_escape_string($this->ResolveTableName('badWords')) . '`;');
                if ($result instanceof mysqli_result)
                {
                    while (is_object($obj = $result->fetch_object()))
                    {
                        if (isset($obj->badWord) && isset($obj->replaceWith))
                        {
                            if (is_string($obj->badWord) && is_string($obj->replaceWith))
                            {
                                $this->badWords[] = $obj->badWord;
                                $this->badWordsReplaceWith[] = $obj->replaceWith;
                            }
                        }
                    }
                    $result->close();
                    unset($result);
                }
            }
        }
    }

    /**
     * Resolve table name
     *
     * @param string $alias
     *            Alias table name
     * @return string Table name
     */
    private function ResolveTableName($alias)
    {
        $ret = $alias;
        if (isset($this->tables[$alias]))
        {
            $ret = $this->tables[$alias];
        }
        return $ret;
    }

    /**
     * Apply bad words filter
     *
     * @param string $str
     *            String reference
     */
    private function ApplyBadWordsFilter(&$str)
    {
        $this->InitBadWords();
        str_replace($this->badWords, $this->badWordsReplaceWith, $str);
    }

    /**
     * Create new user
     */
    private function CreateNewUser()
    {
        $ret = '';
        $result = false;
        do
        {
            $ret = UUID::Create();
            $result = $this->mysqli->query('INSERT INTO `' . $this->mysqli->real_escape_string($this->ResolveTableName('users')) . '` (`uuid`) VALUES (\'' . $this->mysqli->real_escape_string($ret) . '\');');
        }
        while ($result === false);
        $this->AddActivity('Create new user');
        return $ret;
    }

    /**
     * Has application privilege
     *
     * @param string $privilege
     *            Privilege
     * @param integer $minValue
     *            Minimum value
     * @return boolean "true" if application has privilege, otherwise "false"
     */
    private function HasAppPrivilege($privilege, $minValue)
    {
        $ret = false;
        if (is_string($privilege) && is_int($minValue))
        {
            if (isset($this->appPrivileges[$privilege]))
            {
                $ret = ($this->appPrivileges[$privilege] >= $minValue);
            }
        }
        return $ret;
    }

    /**
     * Add activity
     *
     * @param string $request
     *            Request
     * @return boolean "true" if successful, otherwise "false"
     */
    private function AddActivity($request)
    {
        $ret = false;
        if (($this->mysqli instanceof mysqli) && is_string($request))
        {
            $result = $this->mysqli->query('INSERT INTO `' . $this->mysqli->real_escape_string($this->ResolveTableName('activities')) . '` (`uuid`, `appName`, `ip`, `request`) VALUES (\'' . $this->mysqli->real_escape_string(UUID::Create()) . '\', \'' . $this->mysqli->real_escape_string($this->appName) . '\', \'' . $this->mysqli->real_escape_string(Network::GetClientIPAddress()) . '\', \'' . $this->mysqli->real_escape_string($request) . '\');');
            $ret = ($result !== false);
        }
        return $ret;
    }

    /**
     * Is banned
     *
     * @return boolean "true" if banned, otherwise "false"
     */
    public function IsBanned()
    {
        $ret = false;
        if ($this->mysqli instanceof mysqli)
        {
            $result = $this->mysqli->query('SELECT `uuid` FROM `' . $this->mysqli->real_escape_string($this->ResolveTableName('bans')) . '` WHERE `ip`=\'' . $this->mysqli->real_escape_string(Network::GetClientIPAddress()) . '\' LIMIT 1;');
            if ($result instanceof mysqli_result)
            {
                if ($result->num_rows > 0)
                {
                    $ret = true;
                }
                $result->close();
                unset($result);
            }
            if (! $ret)
            {
                $result = $this->mysqli->query('SELECT `uuid` FROM `' . $this->mysqli->real_escape_string($this->ResolveTableName('appBans')) . '` WHERE `name`=\'' . $this->mysqli->real_escape_string($this->appName) . '\' LIMIT 1;');
                if ($result instanceof mysqli_result)
                {
                    $ret = ($result->num_rows > 0);
                    $result->close();
                    unset($result);
                }
            }
        }
        return $ret;
    }

    /**
     * Is user banned
     *
     * @param string $userUUID
     *            User UUID
     * @return boolean "true" if banned, otherwise "false"
     */
    public function IsUserBanned($userUUID)
    {
        $ret = $this->IsBanned();
        if (($this->mysqli instanceof mysqli) && (! $ret) && is_string($userUUID))
        {
            $result = $this->mysqli->query('SELECT SUM(`points`) AS `totalPoints` FROM `' . $this->mysqli->real_escape_string($this->ResolveTableName('infractions')) . '` WHERE `userUUID`=\'' . $this->mysqli->real_escape_string($userUUID) . '\' GROUP BY `userUUID`;');
            if ($result instanceof mysqli_result)
            {
                $obj = $result->fetch_object();
                if (is_object($obj))
                {
                    if (isset($obj->totalPoints))
                    {
                        if (is_numeric($obj->totalPoints))
                        {
                            $total_points = intval($obj->totalPoints);
                            $ret = ($total_points >= $this->maxInfractionPoints);
                        }
                    }
                    unset($obj);
                }
                $result->close();
                unset($result);
            }
        }
        return $ret;
    }

    /**
     * Is rate limited
     *
     * @return boolean "true" if rate limited, otherwise "false"
     */
    public function IsRateLimited()
    {
        $ret = false;
        if ($this->mysqli instanceof mysqli)
        {
            $result = $this->mysqli->query('SELECT `uuid` FROM `' . $this->mysqli->real_escape_string($this->ResolveTableName('activities')) . '` WHERE `appName`=\'' . $this->mysqli->real_escape_string($this->appName) . '\' AND `ip`=\'' . $this->mysqli->real_escape_string(Network::GetClientIPAddress()) . '\' AND (`creationDateTime` + ' . $this->rateLimitTime . ') >= NOW() LIMIT ' . $this->rateLimit . ';');
            if ($result instanceof mysqli_result)
            {
                $ret = ($result->num_rows >= $this->rateLimit);
                $result->close();
                unset($result);
            }
        }
        return $ret;
    }

    /**
     * Get highscore
     *
     * @param integer $baseRank
     *            Base rank
     * @param integer $entries
     *            Entries
     * @return Error|Highscores Highscore if successful, otherwise error
     */
    public function GetHighscore($baseRank, $entries)
    {
        $ret = null;
        if (is_int($baseRank) && is_int($entries))
        {
            if ($this->HasAppPrivilege("highscores.read", 1))
            {
                if (($baseRank > 0) && ($entries > 0))
                {
                    if ($this->mysqli instanceof mysqli)
                    {
                        if ($entries > $this->maxHighscoreSize)
                        {
                            $entries = $this->maxHighscoreSize;
                        }
                        $result = $this->mysqli->query('SELECT `score`, `tries`, `level`, `name` FROM `' . $this->mysqli->real_escape_string($this->ResolveTableName('highscores')) . '` ORDER BY `score` DESC, `tries` ASC, `level` DESC, `name` ASC LIMIT ' . ($baseRank - 1) . ', ' . $entries . ';');
                        if ($result instanceof mysqli_result)
                        {
                            $highscores = array();
                            while (($highscore = $result->fetch_object()) != null)
                            {
                                if (isset($highscore->name))
                                {
                                    if (is_string($highscore->name))
                                    {
                                        $this->ApplyBadWordsFilter($highscore->name);
                                    }
                                }
                                $highscores[] = new Highscore($highscore);
                            }
                            $ret = Highscores::NewHighscore($highscores, 1, $this->appSecret);
                            $result->close();
                            unset($result);
                            $this->AddActivity('Get highscore');
                        }
                        else
                        {
                            $ret = new Error('database.failed_query', 'Failed database query.', Error::INTERNAL_SERVER_ERROR);
                        }
                    }
                    else
                    {
                        $ret = new Error('missing.database_connection', 'Missing database connection.', Error::INTERNAL_SERVER_ERROR);
                    }
                }
                else
                {
                    $ret = new Error('invalid.param_values', 'Invalid parameter values.', Error::BAD_REQUEST);
                }
            }
            else
            {
                $ret = new Error('unauthorized.highscore_read', 'Reading highscores is not authorized.', Error::UNAUTHORIZED);
            }
        }
        else
        {
            $ret = new Error('invalid.param_data_types', 'Invalid parameter data types.', Error::BAD_REQUEST);
        }
        if (is_null($ret))
        {
            $ret = new Error('empty.response', 'Empty response.', Error::INTERNAL_SERVER_ERROR);
        }
        return $ret;
    }

    /**
     * Get online highscore
     *
     * @param string $userUUID
     *            User UUID
     * @param integer $entries
     *            Entries
     * @return Error|Highscores Online highscore if successful, otherwise error
     */
    public function GetOnlineHighscore($userUUID, $entries)
    {
        $ret = null;
        if (is_string($userUUID) && is_int($entries))
        {
            if ($this->HasAppPrivilege("highscores.read", 1))
            {
                if ($entries > 0)
                {
                    if ($entries > $this->maxHighscoreSize)
                    {
                        $entries = $this->maxHighscoreSize;
                    }
                    if ($this->mysqli instanceof mysqli)
                    {
                        $base_rank = 1;
                        $highscores_table = $this->mysqli->real_escape_string($this->ResolveTableName('highscores'));
                        $result = $this->mysqli->query('SET @rowNumber=0;');
                        if ($result !== false)
                        {
                            unset($result);
                            $result = $this->mysqli->query('SELECT `rank` FROM (SELECT `t`.*, (@rowNumber := @rowNumber + 1) AS `rank` FROM `' . $highscores_table . '` AS `t`, (SELECT @rowNumber := 0) AS `r` ORDER BY `score` DESC, `tries` ASC, `level` DESC, `name` ASC) AS `a` WHERE `userUUID`=\'' . $this->mysqli->real_escape_string($userUUID) . '\' LIMIT 1;');
                            if ($result instanceof mysqli_result)
                            {
                                $obj = $result->fetch_object();
                                if (is_object($obj))
                                {
                                    if (isset($obj->rank))
                                    {
                                        if (is_numeric($obj->rank))
                                        {
                                            $base_rank = intval($obj->rank);
                                        }
                                    }
                                }
                                $result->close();
                                unset($result);
                            }
                        }
                        $result = $this->mysqli->query('SELECT `score`, `tries`, `level`, `name` FROM `' . $highscores_table . '` ORDER BY `score` DESC, `tries` ASC, `level` DESC, `name` ASC LIMIT ' . ($base_rank - 1) . ', ' . $entries . ';');
                        if ($result instanceof mysqli_result)
                        {
                            $highscores = array();
                            while (is_object($highscore = $result->fetch_object()))
                            {
                                if (isset($highscore->name))
                                {
                                    if (is_string($highscore->name))
                                    {
                                        $this->ApplyBadWordsFilter($highscore->name);
                                    }
                                }
                                $highscores[] = new Highscore($highscore);
                            }
                            $ret = Highscores::NewHighscore($highscores, $base_rank, $this->appSecret);
                            $result->close();
                            unset($result);
                            $this->AddActivity('Get online highscore');
                        }
                        else
                        {
                            $ret = new Error('database.failed_query', 'Failed database query.', Error::INTERNAL_SERVER_ERROR);
                        }
                    }
                    else
                    {
                        $ret = new Error('missing.database_connection', 'Missing database connection.', Error::INTERNAL_SERVER_ERROR);
                    }
                }
                else
                {
                    $ret = new Error('invalid.param_data', 'Invalid parameter data.', Error::BAD_REQUEST);
                }
            }
            else
            {
                $ret = new Error('unauthorized.highscore_read', 'Reading highscores is not authorized.', Error::UNAUTHORIZED);
            }
        }
        else
        {
            $ret = new Error('invalid.param_data_types', 'Invalid parameter data types.', Error::BAD_REQUEST);
        }
        if (is_null($ret))
        {
            $ret = new Error('empty.response', 'Empty response.', Error::INTERNAL_SERVER_ERROR);
        }
        return $ret;
    }

    /**
     * Get local highscore
     *
     * @param string $userUUID
     *            User UUID
     * @param integer $entries
     *            Entries
     * @return Error|Highscores Local highscore if successful, otherwise error
     */
    public function GetLocalHighscore($userUUID, $entries)
    {
        $ret = null;
        if (is_string($userUUID) && is_int($entries))
        {
            if ($this->HasAppPrivilege("highscores.read", 1))
            {
                if ($entries > 0)
                {
                    if ($entries > $this->maxHighscoreSize)
                    {
                        $entries = $this->maxHighscoreSize;
                    }
                    if ($this->mysqli instanceof mysqli)
                    {
                        $result = $this->mysqli->query('SELECT `score`, `tries`, `level`, `name` FROM `' . $this->mysqli->real_escape_string($this->ResolveTableName('highscores')) . '` WHERE `userUUID`=\'' . $this->mysqli->real_escape_string($userUUID) . '\' ORDER BY `score` DESC, `tries` ASC, `level` DESC, `name` ASC LIMIT ' . $entries . ';');
                        if ($result instanceof mysqli_result)
                        {
                            $highscores = array();
                            while (is_object($highscore = $result->fetch_object()))
                            {
                                if (isset($highscore->name))
                                {
                                    if (is_string($highscore->name))
                                    {
                                        $this->ApplyBadWordsFilter($highscore->name);
                                    }
                                }
                                $highscores[] = new Highscore($highscore);
                            }
                            $ret = Highscores::NewHighscore($highscores, 1, $this->appSecret);
                            $this->AddActivity('Get local highscore');
                        }
                        else
                        {
                            $ret = new Error('database.failed_query', 'Failed database query.', Error::INTERNAL_SERVER_ERROR);
                        }
                    }
                    else
                    {
                        $ret = new Error('missing.database_connection', 'Missing database connection.', Error::INTERNAL_SERVER_ERROR);
                    }
                }
                else
                {
                    $ret = new Error('invalid.param_data', 'Invalid parameter data.', Error::BAD_REQUEST);
                }
            }
            else
            {
                $ret = new Error('unauthorized.highscore_read', 'Reading highscores is not authorized.', Error::UNAUTHORIZED);
            }
        }
        else
        {
            $ret = new Error('invalid.param_data_types', 'Invalid parameter data types.', Error::BAD_REQUEST);
        }
        if (is_null($ret))
        {
            $ret = new Error('empty.response', 'Empty response.', Error::INTERNAL_SERVER_ERROR);
        }
        return $ret;
    }

    /**
     * Post highscore
     *
     * @param string $userUUID
     *            User UUID
     * @param Highscores $highscores
     *            Highscore
     * @return Error|UserUUID Correct user UUID if successful, otherwise error
     */
    public function PostHighscore($userUUID, $highscores)
    {
        $ret = false;
        if (is_string($userUUID) && ($highscores instanceof Highscores))
        {
            if ($this->HasAppPrivilege("highscores.write", 1))
            {
                if ($this->mysqli instanceof mysqli)
                {
                    if ($highscores->Validate($this->appSecret))
                    {
                        $ret = new UserUUID($this->InitUser($userUUID));
                        $highscores_table = $this->mysqli->real_escape_string($this->ResolveTableName('highscores'));
                        $result = $this->mysqli->query('SELECT `score`, `tries`, `level`, `name` FROM `' . $highscores_table . '` WHERE `userUUID`=\'' . $this->mysqli->real_escape_string($userUUID) . '\'');
                        if ($result instanceof mysqli_result)
                        {
                            $result_highscores = array();
                            $append_highscores = array();
                            while (is_object($highscore = $result->fetch_object()))
                            {
                                if (isset($highscore->score) && isset($highscore->tries) && isset($highscore->level) && isset($highscore->name))
                                {
                                    if (is_numeric($highscore->score) && is_numeric($highscore->tries) && is_numeric($highscore->level) && is_string($highscore->name))
                                    {
                                        $result_highscores[] = new Highscore($highscore);
                                    }
                                }
                            }
                            foreach ($highscores->GetHighscores() as $highscore)
                            {
                                if ($highscore instanceof Highscore)
                                {
                                    $append = true;
                                    foreach ($result_highscores as $h)
                                    {
                                        if (($highscore->GetScore() === $h->GetScore()) && ($highscore->GetTries() === $h->GetTries()) && ($highscore->GetLevel() === $h->GetLevel()) && ($highscore->GetName() === $h->GetName()))
                                        {
                                            $append = false;
                                            break;
                                        }
                                    }
                                    if ($append)
                                    {
                                        $append_highscores[] = $highscore;
                                    }
                                }
                            }
                            if (count($append_highscores) > 0)
                            {
                                $query = 'INSERT IGNORE INTO `' . $highscores_table . '` (`uuid`, `userUUID`, `score`, `tries`, `level`, `name`) VALUES ';
                                $first = true;
                                foreach ($append_highscores as $highscore)
                                {
                                    if ($first)
                                    {
                                        $first = false;
                                    }
                                    else
                                    {
                                        $query .= ', ';
                                    }
                                    $query .= '(\'' . $this->mysqli->real_escape_string(UUID::Create()) . '\', \'' . $this->mysqli->real_escape_string($ret->GetUserUUID()) . '\', ' . $highscore->GetScore() . ', ' . $highscore->GetTries() . ', ' . $highscore->GetLevel() . ', \'' . $this->mysqli->real_escape_string($highscore->GetName()) . '\')';
                                }
                                $query .= ';';
                                $result = $this->mysqli->query($query);
                                if ($result === false)
                                {
                                    $ret = new Error('database.failed_query', 'Failed database query.', Error::INTERNAL_SERVER_ERROR);
                                }
                                else
                                {
                                    $this->AddActivity('Post highscore');
                                }
                            }
                            else
                            {
                                $this->AddActivity('Post highscore');
                            }
                        }
                        else
                        {
                            $ret = new Error('database.failed_query', 'Failed database query.', Error::INTERNAL_SERVER_ERROR);
                        }
                    }
                    else
                    {
                        $ret = new Error('invalid.highscore_data', 'Highscore data validation has failed.', Error::BAD_REQUEST);
                    }
                }
                else
                {
                    $ret = new Error('missing.database_connection', 'Missing database connection.', Error::INTERNAL_SERVER_ERROR);
                }
            }
            else
            {
                $ret = new Error('unauthorized.highscore_write', 'Writing highscores is not authorized.', Error::UNAUTHORIZED);
            }
        }
        else
        {
            $ret = new Error('invalid.param_data_types', 'Invalid parameter data types.', Error::BAD_REQUEST);
        }
        if (is_null($ret))
        {
            $ret = new Error('empty.response', 'Empty response.', Error::INTERNAL_SERVER_ERROR);
        }
        return $ret;
    }

    /**
     * Get message of the day
     *
     * @return Error|MOTD Message of the day if successful, otherwise error
     */
    public function GetMOTD()
    {
        $ret = null;
        if ($this->HasAppPrivilege("motd.read", 1))
        {
            if ($this->mysqli instanceof mysqli)
            {
                $result = $this->mysqli->query('SELECT `motd` FROM `' . $this->mysqli->real_escape_string($this->ResolveTableName('motds')) . '` ORDER BY `creationDateTime` DESC LIMIT 1;');
                if ($result instanceof mysqli_result)
                {
                    $motd = $result->fetch_object();
                    if (is_object($motd))
                    {
                        if (isset($motd->motd))
                        {
                            if (is_string($motd->motd))
                            {
                                $ret = MOTD::NewMOTD($motd->motd);
                            }
                        }
                    }
                    if (is_null($ret))
                    {
                        $ret = MOTD::NewMOTD('');
                    }
                    $result->close();
                    unset($result);
                }
                else
                {
                    $ret = new Error('database.failed_query', 'Failed database query.', Error::INTERNAL_SERVER_ERROR);
                }
            }
            else
            {
                $ret = new Error('missing.database_connection', 'Missing database connection.', Error::INTERNAL_SERVER_ERROR);
            }
        }
        else
        {
            $ret = new Error('unauthorized.motd_read', 'Reading message of the day is not authorized.', Error::UNAUTHORIZED);
        }
        if (is_null($ret))
        {
            $ret = new Error('empty.response', 'Empty response.', Error::INTERNAL_SERVER_ERROR);
        }
        return $ret;
    }

    /**
     * Set message of the day
     *
     * @param MOTD $motd
     *            MOTD object
     * @return Error|MOTD Accepted message of the day if successful, otherwise error
     */
    public function SetMOTD($motd)
    {
        $ret = null;
        if (is_object($motd))
        {
            if ($this->HasAppPrivilege("motd.write", 1))
            {
                if ($this->mysqli instanceof mysqli)
                {
                    $result = $this->mysqli->query('INSERT INTO `' . $this->mysqli->real_escape_string($this->ResolveTableName('motds')) . '` (`uuid`, `motd`) VALUES (\'' . $this->mysqli->real_escape_string(UUID::Create()) . '\', \'' . $this->mysqli->real_escape_string($motd) . '\');');
                    if ($result === false)
                    {
                        $ret = new Error('database.failed_query', 'Failed database query.', Error::INTERNAL_SERVER_ERROR);
                    }
                    else
                    {
                        $ret = MOTD::NewMOTD($motd);
                    }
                }
                else
                {
                    $ret = new Error('missing.database_connection', 'Missing database connection.', Error::INTERNAL_SERVER_ERROR);
                }
            }
            else
            {
                $ret = new Error('unauthorized.motd_write', 'Writing message of the day is not authorized.', Error::UNAUTHORIZED);
            }
        }
        else
        {
            $ret = new Error('invalid.param_data_types', 'Invalid parameter data types.', Error::BAD_REQUEST);
        }
        if (is_null($ret))
        {
            $ret = new Error('empty.response', 'Empty response.', Error::INTERNAL_SERVER_ERROR);
        }
        return $ret;
    }
}
?>