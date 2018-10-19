<?php
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

try
{
    switch ($_SERVER['REQUEST_METHOD'])
    {
        case 'GET':
            if (isset($_GET['appName']))
            {
                if (is_string($_GET['appName']))
                {
                    $connector = InitConnector($_GET['appName'], $response);
                    if ($connector instanceof Connector)
                    {
                        $response = $connector->GetMOTD();
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
                $request = json_decode($json);
                if (is_null($request))
                {
                    $response = new Error('invalid.request_body_syntax', 'Invalid request body syntax. JSON was expected.', Error::BAD_REQUEST);
                }
                else
                {
                    if (isset($request->appName) && isset($request->motd))
                    {
                        if (is_string($request->appName) && is_string($request->motd))
                        {
                            $connector = InitConnector($request->appName, $response);
                            if ($connector instanceof Connector)
                            {
                                $response = $connector->SetMOTD($request->motd);
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
?>