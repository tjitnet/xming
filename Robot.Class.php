<?php
/*
* @name: 机器人核心功能
* @author: www.tjit.net
* @version: V4.0
*/

require './Config.php';
require './function.php';

global $config, $stime, $host, $API_KEY, $Robot, $plus_to, $msg, $type, $qq, $guid; //定义全局变量
$stime = microtime(true);
$config = config();
$host = $config["host"];
$API_KEY = $config["API_KEY"];
$data = file_get_contents("php://input");
$arrs = json_decode($data, true);
$msg = $arrs['Msg'];
$qq = $arrs['FromQQ'];
$Robot = $arrs['Robot'];
if (empty($Robot)) {
    $Robot = $config['Robot'];
}
$guid = $arrs['From'];
$type = $arrs['Event'];
$msg_id = $arrs['MsgId'];
$msg_sn = $arrs['MsgSn'];

if (file_exists("./log/{$Robot}_name.json")) {
    $nickname = file_get_contents("./log/{$Robot}_name.json");
} else {
    $datas = curl_get($host . "/httpAPI?Api=GetNick&Robot={$Robot}&QQ={$Robot}");
    $arr = json_decode($datas, true);
    $nickname = $arr["ReturnText"];
    file_put_contents("./log/{$Robot}_name.json", $nickname);
}
$word_lib = file_get_contents("Word_lib.txt");
$plus_data = curl_get("https://api88.net/keys");
$plus_arr = json_decode($plus_data, true);
$plus_to = $plus_arr["time"]["to"];
if (empty($data)) {
    if ($_GET["type"] == "json") {
        exit(json_encode($config));
    }
    echo "<html>
  <head> 
  <meta charset=\"UTF-8\" /> 
  <meta http-equiv=\"X-UA-Compatible\" content=\"IE=11,IE=10,IE=9,IE=8\" /> 
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0\" /> 
  <meta http-equiv=\"Cache-Control\" content=\"no-siteapp\" /> 
  <title>机器人配置信息</title>
  <link type=\"text/css\" href=\"https://cdn.bootcss.com/twitter-bootstrap/3.4.1/css/bootstrap.min.css\" rel=\"stylesheet\" /> 
  <script type=\"text/javascript\" src=\"https://cdn.bootcss.com/jquery/1.9.1/jquery.min.js\"></script></head> 
  <body style=\"padding: 20px 20px 50px 20px;\">";
    echo ("<h2>当前登陆帐号</h2><li>昵称：{$nickname}</li><li>QQ：{$Robot}</li>");
    echo ("</br><h2>授权信息</h2>");
    echo "你的IP：{$plus_arr["ip"]} ";
    if (!empty($plus_to)) {
        if (time() > strtotime($plus_to)) {
            echo "授权日期：{$plus_arr["time"]["go"]} 到期时间：{$plus_to} 剩余时间：<span style=\"color: #ff5e52;font-weight: bold;\">已到期 " . round((time() - strtotime($plus_to)) / 86400, 0) . " 天</span>";
        } else {
            echo "授权日期：{$plus_arr["time"]["go"]} 到期时间：{$plus_to} 剩余时间：<span style=\"color: #3c763d;font-weight: bold;\">" . round((strtotime($plus_to) - time()) / 86400 + 1, 0) . " 天</span>";
        }
    } else {
        echo "<b>未授权</b></br>获取Pro版授权去除水印，以赞助我们维护和开发更多功能</br>获取Pro版授权联系QQ 523077333";
    }
    echo ("<hr>当期程序版本：V3.0</br>本程序要求PHP版本 ≥ 7.0   你的PHP版本：" . PHP_VERSION);
    echo ("<hr><h2>功能配置</h2>");
    $ban = isset($config["ban"]) ? "开启" : "关闭";
    $ban_type = isset($config["ban_type"]) ? "直接发送违规提示到群里并提示触发的词" : "只提示违规，具体违规的关键词私发给QQ " . $config["ban_qq"];
    $ban_add = isset($config["ban_add"]) ? "开启" : "关闭";
    $ban_del = isset($config["ban_del"]) ? "开启" : "关闭";
    $del_log = isset($config["del_log"]) ? "开启" : "关闭";
    $rand_pic = isset($config["rand_pic"]) ? "本地" : "网络";
    $group_increase = isset($config["group_increase"]) ? "开启" : "关闭";
    $key_word = isset($config["key_word"]) ? "开启" : "关闭";
    echo ("<li>群管监控检测违禁词禁言功能 => {$ban}</li><li>违规次数 => {$config["ban_ber"]}</li><li>禁言时长 => {$config["ban_time"]} 秒</li><li>违禁词触发提示方式 => {$ban_type}</li><li>增加词库功能 => {$ban_add}</li><li>删除词库功能 => {$ban_del}</li><li>清理系统日志功能 => {$del_log}</li><li>随机美图功能图片获取方式 => {$rand_pic}</li><li>欢迎新成员加入开关 => {$group_increase}</li><li>欢迎新成员加入文字 => {$config["group_increase_msg"]}</li><li>关键词监控功能 => {$key_word}");
    echo ("</br></br><hr>版权归属于：同济互联 @小伟 独立开发 QQ523077333 群 34544417");
    echo "
  <script type=\"text/javascript\" src=\"https://cdn.bootcss.com/canvas-nest.js/1.0.1/canvas-nest.min.js\" color=\"69,182,255\" zindex=\"-1\" opacity=\"1\" count=\"99\"></script>
  </body>
</html>";
    exit();
} else {
    echo json_encode(["Code" => 1, "content" => ""]);
}

if (!in_array($guid, $config["group_guid"])) { //没开启的群直接结束程序
    if ($type == 1) {
        if ($qq != $config["admin"]) { //不是主人QQ私聊就直接结束程序
            exit;
        }
    } else {
        exit;
    }
}
/**
 * 保存消息日志到./log
 */
if ($type != 1 and $type != 2) { //其他消息日志
    file_put_contents("./log/" . $guid . "_info.log", $data . "\n\r", FILE_APPEND);
}
if ($type == 2) { //群消息日志
    file_put_contents("./log/" . $guid . "_group.log", $data . "\n\r", FILE_APPEND);
}
if ($type == 1) { //私聊消息日志
    file_put_contents("./log/" . $qq . "_qq.log", $data . "\n\r", FILE_APPEND);
}

if (strpos($msg, "增加词库") !== false or strpos($msg, "添加词库") !== false) {
    if ($config["ban_add"] != true) {
        Robot::msg_get("功能未开启，如有需要请修改config.php文件:\n\"ban_add\"  => true");
        exit();
    }
    if (ifadmin($qq, $guid)) {
        $surl = getSubstr($msg, "增加词库");
        if (empty($surl)) {
            Robot::msg_get("增加词库失败，违禁词不能为空！");
            exit();
        }
        file_put_contents("Word_lib.txt", $surl . ",", FILE_APPEND);
        Robot::msg_get("已成功增加违禁词：" . $surl);
    } else {
        Robot::msg_get("Sorry，你还不是本群管理员，无法执行该操作。");
    }
    exit();
}

if (strpos($msg, "删除词库") !== false) {
    if ($config["ban_del"] != true) {
        Robot::msg_get("功能未开启，如有需要请修改config.php文件:\n\"ban_del\"  => true");
        exit();
    }
    if (ifadmin($qq, $guid)) {
        $surl = getSubstr($msg, "删除词库");
        if (empty($surl)) {
            Robot::msg_get("删除词库失败，要删除的违禁词不能为空！");
            exit();
        }
        $word_lib_new = str_replace($surl . ",", "", $word_lib);
        file_put_contents("Word_lib.txt", $word_lib_new);
        Robot::msg_get("已成功删除违禁词：" . $surl);
    } else {
        Robot::msg_get("Sorry，你还不是本群管理员，无法执行该操作。");
    }
    exit();
}
/**
 * 违禁词检测并撤回禁言
 */
if ($config["ban"] == true and $qq != $Robot and $type == 2 and ifadmin($qq, $guid) == false) {
    if (in_array($guid, $config["ban_guid"])) {
        if (time() < strtotime($plus_to)) {
            preg_match("/\[pic=(.*?)\]/", $msg, $mat);
            if ($mat[1]) {
                $rets = curl_get($GLOBALS["host"] . "/httpAPI?Api=GetPicLink&Robot={$GLOBALS["Robot"]}&Group={$guid}&ImgType=1&Guid=" . urlencode("[pic={$mat[1]}]"));
                $arrocr = json_decode($rets, true);
                //$str = curl_get("https://api.ocr.space/parse/imageurl?apikey=5cbc1fd77788957&language=chs&url=".$arrocr["ReturnText"]);
                $str = curl_get("https://api88.net/api/ocr/?key=" . $GLOBALS["API_KEY"] . "&img=" . urlencode($arrocr["ReturnText"]));
                $arrocr = json_decode($str, true);
                //$msg .= $arrocr["ParsedResults"][0]["ParsedText"];

                foreach ($arrocr["words_result"] as $arnum) {
                    $msg .= "\n" . $arnum["words"];
                }

                file_put_contents("./log/_ad_pic.log", $msg . $rets . $str . "\n", FILE_APPEND);
            }
        }

        $var = explode(",", $word_lib);
        //if (strpos($msg, "file=") == false) {
        foreach ($var as $key) {
            if (strpos($msg, $key) !== false) {
                curl_get($GLOBALS["host"] . "/httpAPI?Api=WithdrawMsg&Robot={$Robot}&Group=" . $guid . "&MsgSn=" . $msg_sn . "&MsgId=" . $msg_id);
                $ban = file_get_contents("./ban/" . $qq . ".json");
                $ban = json_decode($ban, true);
                if ($ban["ban"] >= $config["ban_ber"]) {
                    curl_get($GLOBALS["host"] . "/httpAPI?Api=Shutup&Robot={$Robot}&Group=" . $guid . "&QQ=" . $qq . "&Duration=" . $config["ban_time"]);
                    Robot::msg_get("由于您多次违规，已禁言 " . Sec2Time($config["ban_time"]));
                    if (!$config["ban_type"]) {
                        $GLOBALS['qq'] = $config["ban_qq"];
                        $GLOBALS['type'] = 1;
                        Robot::msg_get("群：" . $guid . "\n违规QQ：" . $qq . "\n违禁词：" . $key);
                    }
                } else {
                    if ($config["ban_type"]) {
                        Robot::msg_get("您的发言有违规字符，请端正您的发言，再次违规将禁言。\n触发的违禁词是“" . $key . "”");
                    } else {
                        Robot::msg_get("您的发言有违规字符，请端正您的发言，再次违规将禁言。");
                        $GLOBALS['qq'] = $config["ban_qq"];
                        $GLOBALS['type'] = 1;
                        Robot::msg_get("群：" . $guid . "\n违规QQ：" . $qq . "\n违禁词：" . $key . "\n" . $msg);
                    }
                    file_put_contents("./ban/" . $qq . ".json", json_encode(["ban" => $ban["ban"] + 1]));
                }
                exit();
            }
        }
    }
}

if ($config["key_word"] == true) {
    foreach ($config["key_word_text"] as $key) {
        if (strpos($msg, $key["key"]) !== false) {
            Robot::msg_get($key["word"]);
        }
    }
}

if ($msg == "授权信息") {
    if (!empty($plus_to)) {
        if (time() > strtotime($plus_to)) {
            $msg_get =  "授权日期：" . $plus_arr["time"]["go"] . "\n到期时间：" . $plus_to . "\n剩余时间：已到期 " . round((time() - strtotime($plus_to)) / 86400, 0) . " 天";
        } else {
            $msg_get =  "授权日期：" . $plus_arr["time"]["go"] . "\n到期时间：" . $plus_to . "\n剩余时间：" . round((strtotime($plus_to) - time()) / 86400 + 1, 0) . " 天";
        }
    } else {
        $msg_get = "未授权\n获取Pro版授权去除水印，以赞助我们维护和开发更多功能\n获取Pro版授权请联系QQ 523077333";
    }
    Robot::msg_get($msg_get);
    exit();
}
if ($type == 2005 or $type == 2001 or $type == 2002 or $type == 2004) {
    if ($config["group_increase"] == true) {
        $GLOBALS['type'] = 2;
        Robot::msg_get(str_replace("[menu]", menu(), $config["group_increase_msg"]));
        Robot::msg_get('欢迎您的加入成为本群的一员，为了您和群主的合法权益，腾讯默认对付费入群的成员禁言5分钟，这是腾讯的规定，群主及管理员无法解除，请耐心等待5分钟后再发言，谢谢！');
    }
    exit();
}

if (strpos($msg, "清理系统日志") !== false) {
    if ($config["del_log"] != true) {
        Robot::msg_get("功能未开启，如有需要请修改config.php文件:\n\"del_log\"  => true");
        exit();
    }
    if (ifadmin($qq, $guid)) {
        array_map('unlink', glob(dirname(__FILE__) . "/ban/*.*"));
        array_map('unlink', glob(dirname(__FILE__) . "/log/*.*"));
        Robot::msg_get("系统日志/缓存已清理完成。");
    } else {
        Robot::msg_get("Sorry，你还不是本群管理员，无法执行该操作。");
    }
    exit();
}

if ($msg == "菜单" or $msg == "功能") {
    Robot::msg_get(menu());
    exit();
}

$fun_arr = function_list();
$fun_name = array_keys($fun_arr);
$fnc = new Robot_function();

foreach ($fun_name as $value) {
    if (strpos($msg, $value) !== false) {
        if ($value != 'function') {
            $fun = $fun_arr[$value];
            $fnc->$fun();
        }
    }
}

$fun_function_arr = $fun_arr['function'];
foreach ($fun_function_arr as $value) {
    $fnc->$value();
}

/**
 * 机器人聊天接口，默认是青云客的免费接口，根据自己需求修改
 */

if (strpos($msg, "[@{$Robot}]") !== false or $type == 1 or $msg == $nickname or $msg == "机器人") {
    if (strpos($msg, "[@{$Robot}]") !== false) {
        $msg = getSubstr($msg, "[@{$Robot}]");
    }
    //file_put_contents("get.log", $msg . "\n\r", FILE_APPEND);
    if (strpos($msg, "[pic=") !== false or empty($msg) == true or $msg == "help" or $msg == "帮助" or $msg == $nickname or $msg == "机器人") {
        //$str = curl_get("http://api.qingyunke.com/api.php?key=free&appid=0&msg=".urlencode(“help”));
        //$str = json_decode($str,true);
        Robot::msg_get(menu());
    } else {
        $str = curl_get("http://api.qingyunke.com/api.php?key=free&appid=0&msg=" . urlencode($msg));
        $str = json_decode($str, true);
        $emsg = str_replace("{br}", "\n", $str["content"]);
        Robot::msg_get($emsg);
    }
}




class Robot
{
    /**
     * 发送json消息
     *
     * @param [type] $json
     */
    public static function msg_get_json($json)
    {
        $str = json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($GLOBALS['type'] == 2) {
            $log = curl_get($GLOBALS["host"] . "/httpAPI?Api=SendJson&Robot=" . $GLOBALS["Robot"] . "&Type=2&Group=" . $GLOBALS["guid"] . "&JsonMsg=" . urlencode($str));
            file_put_contents("./log/" . $GLOBALS["guid"] . "_group_get.log", $str . "\n", FILE_APPEND);
        }
        if ($GLOBALS['type'] == 1) {
            $log = curl_get($GLOBALS["host"] . "/httpAPI?Api=SendJson&Robot=" . $GLOBALS["Robot"] . "&Type=1&JsonMsg=" . urlencode($str) . "&QQ=" . $GLOBALS['qq']);
            file_put_contents("./log/" . $GLOBALS['qq'] . "_qq_get.log", $str . "\n", FILE_APPEND);
        }
        file_put_contents("./log/_get.log", $log . "\n", FILE_APPEND);
        exit;
    }
    /**
     * 发送消息
     *
     * @param [type] $str
     */
    public static function msg_get($str)
    {
        $etime = microtime(true);
        $total = $etime - $GLOBALS["stime"];
        $total = round($total, 2);
        $str .= "\n——系统处理时间：{$total}秒";

        if (time() > strtotime($GLOBALS["plus_to"]) or empty($GLOBALS["plus_to"])) {
            $str .= "\n——来自tjit.net基于PHP开发";
        }

        $str = preg_replace('/[\r\n]+/', "\n", $str);
        if ($GLOBALS['type'] == 2) {
            $log = curl_get($GLOBALS["host"] . "/httpAPI?Api=SendMsg&Robot=" . $GLOBALS["Robot"] . "&Type=2&Group=" . $GLOBALS["guid"] . "&Content=" . urlencode("[@" . $GLOBALS['qq'] . "] \n" . $str));
            file_put_contents("./log/" . $GLOBALS["guid"] . "_group_get.log", $str . "\n", FILE_APPEND);
        }
        if ($GLOBALS['type'] == 1) {
            $log = curl_get($GLOBALS["host"] . "/httpAPI?Api=SendMsg&Robot=" . $GLOBALS["Robot"] . "&Type=1&Content=" . urlencode($str) . "&QQ=" . $GLOBALS['qq']);
            file_put_contents("./log/" . $GLOBALS['qq'] . "_qq_get.log", $str . "\n", FILE_APPEND);
        }
        file_put_contents("./log/_get.log", $log . "\n", FILE_APPEND);
    }
}
/**
 * 取出消息关键词右边字符
 * @param [type] $str
 * @param [type] $leftStr
 */
function getSubstr($str, $leftStr)
{
    $left = strpos($str, $leftStr);
    return trimall(substr($str, $left + strlen($leftStr)));
}
/**
 * 去空格换行
 *
 * @param [type] $str
 */
function trimall($str)
{
    $qian = array(" ", "　", "\t", "\n", "\r", "amp;", "&lt;");
    return str_replace($qian, '', $str);
}
/**
 * 公共CURL请求函数
 *
 * @param [type] $url
 */
function curl_get($url)
{
    $refer = "http://Robot.xiaomingtongxue";
    $header = array(
        'User-Agent: Robot.xiaomingtongxue'
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_REFERER, $refer);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
/**
 * 格式化时间
 *
 * @param [type] $time
 */
function Sec2Time($time)
{
    if (is_numeric($time)) {
        $value = array(
            "years" => 0, "days" => 0, "hours" => 0,
            "minutes" => 0, "seconds" => 0,
        );
        if ($time >= 31556926) {
            $value["years"] = floor($time / 31556926);
            $time = ($time % 31556926);
        }
        if ($time >= 86400) {
            $value["days"] = floor($time / 86400);
            $time = ($time % 86400);
        }
        if ($time >= 3600) {
            $value["hours"] = floor($time / 3600);
            $time = ($time % 3600);
        }
        if ($time >= 60) {
            $value["minutes"] = floor($time / 60);
            $time = ($time % 60);
        }
        $value["seconds"] = floor($time);
        //return (array) $value;
        $t = $value["years"] . "年" . $value["days"] . "天" . $value["hours"] . "小时" . $value["minutes"] . "分" . $value["seconds"] . "秒";
        return $t;
    } else {
        return (bool) false;
    }
}
/**
 * 判断是否管理员
 *
 * @param [type] $qq
 * @param [type] $group
 */
function ifadmin($qq, $group)
{ 
    if ($qq == $GLOBALS["config"]["admin"]) {
        return true;
    }
    $Robot = $GLOBALS["Robot"];
    if (file_exists("./log/{$group}_admin.json")) {
        $str = file_get_contents("./log/{$group}_admin.json");
    } else {
        $str = curl_get($GLOBALS["host"] . "/httpAPI?Api=GetAdminList&Robot={$Robot}&Group={$group}");
        file_put_contents("./log/{$group}_admin.json", $str);
    }
    $arr = json_decode($str);
    $list = $arr->ReturnText;
    $list_arr = explode("\r\n", $list);
    if (in_array($qq, $list_arr)) {
        return true;
    } else {
        return false;
    }
}
