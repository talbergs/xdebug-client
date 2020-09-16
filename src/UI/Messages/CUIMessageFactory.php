<?php declare(strict_types=1);

namespace Acme\UI\Messages;

use Acme\Exceptions\EUnknownUIMessage;


final class CUIMessageFactory
{
    public static function fromString(string $str): IUIMessage
    {
        $sep = strpos($str, ' ');

        if ($sep !== false) {
            $method = substr($str, 0, $sep);
            $params = json_decode(substr($str, $sep + 1), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new EUnknownUIMessage(json_last_error_msg());
            }
        } else {
            $method = $str;
            $params = [];
        }

        return self::fromParams($method, $params);
    }

    public static function fromParams(string $method, array $params): IUIMessage
    {
        switch ($method) {
        case 'app:start_session':
            return new CUIStartXDSession($params);

        case 'xdebug:source':
            return new CUISourceMessage($params);

        case 'xdebug:breakpoint_list':
            return new CUIBreakpointListMessage($params);

        case 'xdebug:breakpoint_set':
            return new CUIBreakpointSetMessage($params);

        case 'xdebug:run':
            return new CUIRunMessage($params);

        case 'xdebug:status':
            return new CUIStatusMessage($params);

        case 'xdebug:stack_get':
            return new CUIStackGetMessage($params);;

        case 'exit:session':
            return new CUIExitSessionMessage($params);

        case 'app:state':
            return new CUIAppStateMessage($params);

        case 'app:list_sessions':
            return new CUIListSessions($params);

        case 'app:list_connections':
            return new CUIListConnections($params);

        case 'app:add_listener':
            return new CUIAddListener($params);

        case 'app:add_session':
            return new CUIAddSession($params);

        case 'app:files':
            return new CUIFilesMessage($params);

        default:
            throw new EUnknownUIMessage($method);
        }
    }
}
