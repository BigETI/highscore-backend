<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
header('Content-Type: application/json');
include_once 'includes/Connector.php';

$response = null;

/**
 * Initialize connector
 *
 * @param string $appName
 *            Application name
 * @param object $response
 *            Response
 * @return boolean|Connector Connector if successful, otherwise "false"
 */
function InitConnector($appName, &$response)
{
    $ret = new Connector($appName);
    if ($ret->IsBanned())
    {
        $response = new Error('banned.app', "This application or network has been banned.", Error::FORBIDDEN);
        $ret = false;
    }
    else if ($ret->IsRateLimited())
    {
        $response = new Error('rate_limited.network', "This network has been rate limited.", Error::FORBIDDEN);
        $ret = false;
    }
    return $ret;
}

/**
 * Initialize connector with user
 *
 * @param string $appName
 *            Application name
 * @param string $userUUID
 *            User UUID
 * @param object $response
 *            Response
 * @return boolean|Connector Connector if successful, otherwise "false"
 */
function InitConnectorWithUser($appName, $userUUID, &$response)
{
    $ret = new Connector($appName);
    if ($ret->IsUserBanned($userUUID))
    {
        $response = new Error('banned.user', "This user, application or network has been banned.", Error::FORBIDDEN);
        $ret = false;
    }
    return $ret;
}

try
{
    switch ($_SERVER['REQUEST_METHOD'])
    {
        case 'GET':
            if (isset($_GET['appName']) && isset($_GET['mode']))
            {
                if (is_string($_GET['appName']) && is_string($_GET['mode']))
                {
                    switch ($_GET['mode'])
                    {
                        case 'online':
                            $entries = 100;
                            if (isset($_GET['entries']))
                            {
                                if (is_numeric($_GET['entries']))
                                {
                                    $entries = intval($_GET['entries']);
                                    if ($entries < 1)
                                    {
                                        $response = new Error('invalid.entries', 'Entries value "' . $entries . '" is invalid.', Error::BAD_REQUEST);
                                    }
                                }
                            }
                            if (is_null($response))
                            {
                                if (isset($_GET['userUUID']))
                                {
                                    if (is_string($_GET['userUUID']))
                                    {
                                        $user_uuid = $_GET['userUUID'];
                                        $connector = InitConnectorWithUser($_GET['appName'], $user_uuid, $response);
                                        if ($connector instanceof Connector)
                                        {
                                            $response = $connector->GetOnlineHighscore($user_uuid, $entries);
                                        }
                                    }
                                    else
                                    {
                                        $response = new Error('invalid.user_uuid_type', "Invalid data type for user UUID.", Error::BAD_REQUEST);
                                    }
                                }
                                else
                                {
                                    $base_rank = 1;
                                    if (isset($_GET['baseRank']))
                                    {
                                        if (is_numeric($_GET['baseRank']))
                                        {
                                            $base_rank = intval($_GET['baseRank']);
                                            if ($base_rank < 1)
                                            {
                                                $response = new Error('invalid.base_rank', 'Base rank value "' . $base_rank . '" is invalid.', Error::BAD_REQUEST);
                                            }
                                        }
                                    }
                                    $connector = InitConnector($_GET['appName'], $response);
                                    if ($connector instanceof Connector)
                                    {
                                        $response = $connector->GetHighscore($base_rank, $entries);
                                    }
                                }
                            }
                            break;
                        case 'local':
                            $entries = 100;
                            if (isset($_GET['entries']))
                            {
                                if (is_numeric($_GET['entries']))
                                {
                                    $entries = intval($_GET['entries']);
                                    if ($entries < 1)
                                    {
                                        $response = new Error('invalid.entries', 'Entries value "' . $entries . '" is invalid.', Error::BAD_REQUEST);
                                    }
                                }
                            }
                            if (is_null($response))
                            {
                                if (isset($_GET['userUUID']))
                                {
                                    if (is_string($_GET['userUUID']))
                                    {
                                        $user_uuid = $_GET['userUUID'];
                                        $connector = InitConnectorWithUser($_GET['appName'], $user_uuid, $response);
                                        if ($connector instanceof Connector)
                                        {
                                            $response = $connector->GetLocalHighscore($user_uuid, $entries);
                                        }
                                    }
                                    else
                                    {
                                        $response = new Error('invalid.user_uuid_type', "Invalid data type for user UUID.", Error::BAD_REQUEST);
                                    }
                                }
                                else
                                {
                                    $response = new Error('missing.mode_user_uuid', 'User UUID is missing for mode "local".', Error::BAD_REQUEST);
                                }
                            }
                            break;
                        default:
                            $response = new Error('invalid.mode', 'Mode "' . $_GET['mode'] . '" is not supported.', Error::BAD_REQUEST);
                            break;
                    }
                }
                else
                {
                    $response = new Error('invalid.get_params_type', 'Invalid get parameters data type.', Error::BAD_REQUEST);
                }
            }
            else
            {
                $response = new Error('invalid.get_params', 'Invalid get parameters.', Error::BAD_REQUEST);
            }
            break;
        case 'POST':
            $json = file_get_contents('php://input');
            if ($json === false)
            {
                $response = new Error('invalid.request_body', 'Invalid request body.', Error::BAD_REQUEST);
            }
            else
            {
                $json = rawurldecode($json);
                $request = json_decode($json);
                if (is_null($request))
                {
                    $response = new Error('invalid.request_body_syntax', 'Invalid request body syntax. JSON was expected.', Error::BAD_REQUEST);
                }
                else
                {
                    if (isset($request->appName) && isset($request->userUUID) && isset($request->highscores))
                    {
                        if (is_string($request->appName) && is_string($request->userUUID) && is_object($request->highscores))
                        {
                            if (isset($request->highscores->highscores) && isset($request->highscores->baseRank) && isset($request->highscores->id))
                            {
                                if (is_array($request->highscores->highscores) && is_int($request->highscores->baseRank) && is_string($request->highscores->id))
                                {
                                    $success = true;
                                    foreach ($request->highscores->highscores as $highscore)
                                    {
                                        if (is_object($highscore))
                                        {
                                            if (isset($highscore->score) && isset($highscore->tries) && isset($highscore->level) && isset($highscore->name))
                                            {
                                                if ((! is_numeric($highscore->score)) || (! is_int($highscore->tries)) || (! is_int($highscore->level)) || (! is_string($highscore->name)))
                                                {
                                                    $success = false;
                                                    break;
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $success = false;
                                            break;
                                        }
                                    }
                                    if ($success)
                                    {
                                        $connector = InitConnectorWithUser($request->appName, $request->userUUID, $response);
                                        if ($connector instanceof Connector)
                                        {
                                            $response = $connector->PostHighscore($request->userUUID, Highscores::FromRequest($request->highscores));
                                        }
                                    }
                                    else
                                    {
                                        $response = new Error('invalid.request_body_data', 'Invalid request body data.', Error::BAD_REQUEST);
                                    }
                                }
                                else
                                {
                                    $response = new Error('invalid.request_body_data', 'Invalid request body data.', Error::BAD_REQUEST);
                                }
                            }
                            else
                            {
                                $response = new Error('invalid.request_body_data', 'Invalid request body data.', Error::BAD_REQUEST);
                            }
                        }
                        else
                        {
                            $response = new Error('invalid.request_body_data', 'Invalid request body data.', Error::BAD_REQUEST);
                        }
                    }
                    else
                    {
                        $response = new Error('invalid.request_body_data', 'Invalid request body data.', Error::BAD_REQUEST);
                    }
                }
            }
            break;
        default:
            $response = new Error('invalid.request_method', 'Invalid request method.', Error::BAD_REQUEST);
            break;
    }
}
catch (Exception $e)
{
    $response = new Error('exception', $e->__toString(), Error::INTERNAL_SERVER_ERROR);
}

if (is_null($response))
{
    $response = new Error('empty.response', 'Empty response.', Error::INTERNAL_SERVER_ERROR);
}

if ($response instanceof Error)
{
    http_response_code($response->GetHTTPResponseCode());
}

echo (($response instanceof IJSONSerializable) ? $response->ToJSON() : json_encode(get_object_vars($response)));
?>