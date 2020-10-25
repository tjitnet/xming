<?php
/*
* @name: 机器人功能函数模块文件
* @author: www.tjit.net
* @version: V4.0
*/

class Robot_function
{
    /**
     * @name: 点歌
     */
    public function requ_song()
    {
        if (preg_match("/^(点歌|来一首)(.*?)$/", $GLOBALS['msg'], $mat)) {
            //$key_word = getSubstr($GLOBALS['msg'], "点歌");
            if (!$mat[2]) {
                Robot::msg_get('要点歌，请回复指令：点歌 歌名或歌手');
                exit;
            }
            $str = curl_get('https://api88.net/api/qqmusic/?key=' . $GLOBALS["API_KEY"] . '&type=so&cache=1&id=' . $mat[2]);
            $ret = json_decode($str, true);
            $music_arr = $ret['Body'][0];
            if (!$music_arr) {
                Robot::msg_get('搜嘎，找不到你要点的歌曲！');
                exit;
            }
            $pic = get_redirect_url('https://api88.net/api/qqmusic/?key=' . $GLOBALS["API_KEY"] . '&type=pic&cache=1&id=' . $music_arr['mid']);
            $music_json = [
                'app' => 'com.tencent.structmsg',
                'config' => [
                    'autosize' => true,
                    'forward' => true,
                    'type' => 'normal',
                ],
                'desc' => '音乐',
                'meta' => [
                    'music' => [
                        'action' => '',
                        'android_pkg_name' => '',
                        'app_type' => 1,
                        'appid' => 100497308,
                        'desc' => $music_arr['author'],
                        'jumpUrl' => 'https://y.qq.com/n/yqq/song/' . $music_arr['mid'] . '.html',
                        'musicUrl' => 'http://c6.y.qq.com/rsc/fcgi-bin/fcg_pyq_play.fcg?songid=0&songmid=' . $music_arr['mid'] . '&songtype=1&fromtag=50&uin=&code=56ea2',
                        'preview' => $pic,
                        'sourceMsgId' => '0',
                        'source_icon' => '',
                        'source_url' => '',
                        'tag' => '小明同学机器人',
                        'title' => $music_arr['title'],
                    ],
                ],
                'prompt' => '小明同学分享音乐',
                'ver' => '0.0.0.1',
                'view' => 'music',
            ];
            Robot::msg_get_json($music_json);
            exit;
        }
    }
    /**
     * @name: 翻译
     */
    public function translate()
    {
        $left = strpos($GLOBALS['msg'], '翻译');
        $key_word = substr($GLOBALS['msg'], $left + strlen('翻译'));
        //$key_word = getSubstr($GLOBALS['msg'], "翻译");
        if (empty($key_word)) {
            Robot::msg_get("翻译失败，内容不能为空！");
            exit();
        }
        $sdata = curl_get("https://api88.net/api/fanyi/?key={$GLOBALS["API_KEY"]}&from=anto&to=zh&text=" . urlencode($key_word));
        $arr = json_decode($sdata, true);
        $arrstr = $arr["trans_result"];
        $str = '';
        foreach ($arrstr as $ar) {
            $str .= $ar['dst'] . "\n";
        }
        Robot::msg_get($key_word . " 的翻译结果：\n" . $str);
        exit();
    }
    /**
     * @name: 查当前天气
     */
    public function Check_weather()
    {
        include_once("Weather.Code.php");
        $code_arr = code();
        if (strpos($GLOBALS['msg'], "天气") !== false and strpos($GLOBALS['msg'], "查明日天气") === false) {
            $key_word = getSubstr($GLOBALS['msg'], "天气");
            if (empty($key_word) and tqtext($GLOBALS['msg']) == false and !$code_arr[tqtext($GLOBALS['msg'])]) {
                Robot::msg_get("查询失败，地名不能为空！");
                exit();
            }
            if (!$code_arr[$key_word]) {
                //file_put_contents(dirname(__FILE__) . "\\log\\_get.log",tqtext($GLOBALS['msg'])."\n", FILE_APPEND);
                if (tqtext($GLOBALS['msg']) == false or !$code_arr[tqtext($GLOBALS['msg'])]) {
                    Robot::msg_get("查询失败，暂时没有你要查的天气数据！");
                    exit();
                } else {
                    $key_word = tqtext($GLOBALS['msg']);
                }
            }
            $sdata = curl_get("https://api88.net/api/tianqi/?type=add&name=" . $key_word . "&key=" . $GLOBALS["API_KEY"]);
            $arr = json_decode($sdata, true);
            $str = $key_word . "天气预报：\n天气：" . $arr["realtime"]["weather"]["info"] . "\n温度: " . $arr["realtime"]["weather"]["temperature"] . " ℃\n湿度：" . $arr["realtime"]["weather"]["humidity"] . "％\n舒适度：" . $arr["life"]["info"]["shushidu"][0] . "\n风力：" . $arr["realtime"]["wind"]["power"] . "\n风向：" . $arr["realtime"]["wind"]["direct"] .  "\nAQI：" . $arr["pm25"]["aqi"][0] . "\nPM2.5：" . $arr["pm25"]["pm25"][0] . "\n" . $arr["life"]["info"]["shushidu"][1] . "\n中国气象局 " . date("Y年m月d日 H时i分s秒", $arr["realtime"]["dataUptime"]) . " 发布";
            Robot::msg_get($str);
        }
    }
    /**
     * @name: 查明日天气
     *
     */
    public function Check_tomorrow_weather()
    {
        $key_word = getSubstr($GLOBALS['msg'], "查明日天气");
        if (empty($key_word)) {
            Robot::msg_get("查询失败，地名不能为空！");
            exit();
        }
        include_once("Weather.Code.php");
        $code_arr = code();
        if (empty($code_arr[$key_word])) {
            Robot::msg_get("查询失败，暂时没有" . $key_word . "的天气数据！");
            exit();
        }
        $sdata = curl_get("https://api88.net/api/tianqi/?type=add&name=" . $key_word . "&key=" . $GLOBALS["API_KEY"]);
        $arr = json_decode($sdata, true);
        $str = $key_word . "“明日”天气预报：\n天气：白天" . $arr["weather"][1]["info"]["day"][1] . "，夜间" . $arr["weather"][1]["info"]["night"][1] . "\n温度: 早上温度" . $arr["weather"][1]["info"]["dawn"][2] . "℃，白天温度" . $arr["weather"][1]["info"]["day"][2] . "℃，夜间温度" . $arr["weather"][1]["info"]["night"][2] . " ℃\n" . "中国气象局 " . date("Y年m月d日 H时i分s秒", $arr["realtime"]["dataUptime"]) . " 发布";
        Robot::msg_get($str);
    }
    /**
     * @name: 疫情地图
     *
     */
    public function Epidemic_map()
    {
        $str_json = [
            'app' => 'com.tencent.structmsg',
            'config' => [
                'autosize' => true,
                'forward' => true,
                'type' => 'normal',
            ],
            'desc' => '新闻',
            'meta' => [
                'news' => [
                    'action' => '',
                    'android_pkg_name' => '',
                    'app_type' => 1,
                    'appid' => 1104666732,
                    'desc' => '新型冠状病毒肺炎疫情实时大数据报告',
                    'jumpUrl' => 'https://voice.baidu.com/act/newpneumonia/newpneumonia/',
                    'preview' => 'https://hhyfeed.sogoucdn.com/js/pages/epidemic/epi_header_8_20200224.jpg',
                    'source_icon' => '',
                    'source_url' => '',
                    'tag' => '小明同学机器人',
                    'title' => '实时更新：新型冠状病毒肺炎疫情地图',
                ],
            ],
            'prompt' => '新型冠状病毒肺炎疫情实时大数据报告',
            'ver' => '0.0.0.1',
            'view' => 'news',
        ];
        Robot::msg_get_json($str_json);
        exit;
    }
    /**
     * @name: 随机美图
     * 此框架还不支持发图片，所以此功能暂时搁置
     */
    public function _rand_img()
    {
        if ($GLOBALS['config']["rand_pic"] == true) {
            $img_array = glob(dirname(__FILE__) . "\img\*.{gif,jpg,png,jpeg}", GLOB_BRACE);
            $img = array_rand($img_array);
            $imgfile = $img_array[$img];
            list($width, $height) = getimagesize($imgfile);
            $size = formatBytes(filesize($imgfile));
            //print_r($size);
            //print_r(getimagesize($img_array[$img]));
            $fileinfo = $width . "x" . $height . " " . $size;
            Robot::msg_get("美图来啦:\n[pic=file:///{$imgfile}]\n{$fileinfo}\n" . rand_img_word());
        } else {
            Robot::msg_get("美图获取中，请骚等片刻！");
            Robot::msg_get("美图来啦:\n[pic=https://api88.net/api/img/rand/]\n" . rand_img_word());
        }
        //$i = rand(1, 26612);
        //Robot::msg_get("美图来啦,这是图库26612张的第". $i ."张:\n[CQ:image,file=file:///C:\\phpstudy_pro\\WWW\\post\\img\\tjit.net_".$i. ".jpg]".rand_img_word());
        //Robot::msg_get("来了老弟:\n" .rand_img());

    }
    /**
     * @name: cdn查询
     *
     */
    public function query_cdn()
    {
        $key_word = getSubstr($GLOBALS['msg'], "查询");
        if (empty($key_word)) {
            Robot::msg_get("查询失败，网址不能为空！");
            exit();
        }
        $sdata = cdn($key_word);
        $arr = json_decode($sdata, true);
        Robot::msg_get("CDN查询结果：\n" . $arr["info"]);
        exit();
    }
    /**
     * @name: 查快递
     *
     */
    public function query_Express()
    {
        $key_word = getSubstr($GLOBALS['msg'], "查快递");
        if (empty($key_word)) {
            Robot::msg_get("查询失败，快递单号不能为空！");
            exit();
        }
        $sdata = curl_get("https://api88.net/api/kuaidi/?key=" . $GLOBALS["API_KEY"] . "&id=" . $key_word);
        $arr = json_decode($sdata, true);
        //foreach ($sarr['data'] as $arr) {
        //$str = $str . '时间:' . $arr['time'] . "\n" . $arr['desc'] . "\n";
        $strs = $arr['data'][0];
        $str =  '时间:' . $strs['time'] . "\n" . $strs['context'] . "\n";
        if (empty($strs)) {
            Robot::msg_get("查询失败:\n" . $arr["message"]);
        } else {
            //file_put_contents("code.txt", $sdata."\n\r", FILE_APPEND);//4303278821340
            Robot::msg_get("查询成功，当前快递信息:\n" . $str);
        }
    }
    /**
     * @name: 图片文字识别
     *
     */
    public function Image_character_recognition()
    {
        preg_match("/\[pic=(.*?)\]/", $GLOBALS['msg'], $mat);
        if ($mat[1]) {
            $rets = curl_get($GLOBALS["host"] . "/httpAPI?Api=GetPicLink&Robot={$GLOBALS["Robot"]}&Group={$GLOBALS['guid']}&ImgType=1&Guid=" . urlencode("[pic={$mat[1]}]"));
            //$str = curl_get("https://api.ocr.space/parse/imageurl?apikey=5cbc1fd77788957&language=chs&url=".$arrocr["ReturnText"]);
            if ($GLOBALS['type'] == 2) {
                $arrocr = json_decode($rets, true);
                $str = curl_get("https://api88.net/api/ocr/?key=" . $GLOBALS["API_KEY"] . "&img=" . urlencode($arrocr["ReturnText"]));
            } else {
                $rets = preg_replace("/(\[pic=(.*?)-(.*?)-)|(\}.jpg\])/", "", $rets);
                $arrocr = json_decode($rets, true);
                $str = curl_get("https://api88.net/api/ocr/?key=" . $GLOBALS["API_KEY"] . "&img=" . urlencode($arrocr["ReturnText"]));
            }

            $arrocr = json_decode($str, true);
            //$GLOBALS['msg'] .= $arr["ParsedResults"][0]["ParsedText"];
            $echo ='';
            foreach ($arrocr["words_result"] as $arnum) {
                $echo .= "\n" . $arnum["words"];
            }
            file_put_contents("./log/_ad_pic.log", $GLOBALS['msg'] . "\n" . $rets . "\n" . $str . "\n", FILE_APPEND);
            Robot::msg_get("图片文字识别成功：" . $echo);
        } else {
            Robot::msg_get("识别失败，请检查你的图片！");
        }
        exit();
    }
    /**
     * @name: 查ip
     *
     */
    public function query_ip(){
        if (strpos($GLOBALS['msg'], "查ip") !== false or strpos($GLOBALS['msg'], "查IP") !== false) {
            $key_word = getSubstr($GLOBALS['msg'], "查ip");
            if (!checkIp($key_word)) {
                $key_word = getSubstr($GLOBALS['msg'], "查IP");
                if (!checkIp($key_word)) {
                    Robot::msg_get("查询失败，ip地址或域名不正确！");
                    exit();
                }
            }
            if (!filter_var($key_word, FILTER_VALIDATE_IP)) {
                $key_word = gethostbyname($key_word);
            }
            $sdata = curl_get("https://api88.net/sip?key=" . $GLOBALS["API_KEY"] . "&ip=" . $key_word);
            $arr = json_decode($sdata, true);
            //file_put_contents(dirname(__FILE__) . "\\log\\_get.log", $sdata . "\n", FILE_APPEND);
            Robot::msg_get("查询成功:\nip地址：" . $arr['ip'] . "\n归属地：" . $arr['local']);
        }
    }
    /**
     * @name: ping
     *
     */
    public function _ping(){
        if (strpos($GLOBALS['msg'], "ping") !== false) {
            if (strpos($GLOBALS['msg'], "超级ping") === false) {
                $key_word = getSubstr($GLOBALS['msg'], "ping");
                if (!checkIp($key_word)) {
                    Robot::msg_get("要ping的域名/IP格式不正确，例如：\nping www.tjit.net\nping 119.29.29.29");
                    exit();
                }
                Robot::msg_get("ping " . $key_word . " 的结果:\n" . ping($key_word));
            }
        }
    }
    /**
     * @name: 超级ping
     *
     */
    public function add_ping(){
        if (strpos($GLOBALS['msg'], "超级ping") !== false) {
            $key_word = getSubstr($GLOBALS['msg'], "超级ping");
            if (!checkIp($key_word)) {
                Robot::msg_get("要ping的域名/IP格式不正确，例如：\nping www.tjit.net\nping 119.29.29.29");
                exit();
            }
            Robot::msg_get("ping " . $key_word . " 的结果:\n" . sping($key_word));
        }
    }
    /**
     * @name: 生成短网址
     *
     */
    public function add_dwz(){

        if (strpos($GLOBALS['msg'], "生成短网址") !== false) {
            $key_word = getSubstr($GLOBALS['msg'], "生成短网址");
            if (empty($key_word)) {
                Robot::msg_get("生成失败，原网址不能为空！");
                exit();
            }
            if (strpos($GLOBALS['msg'], "http") === false) {
                Robot::msg_get("生成失败，网址不正确！");
                exit();
            }
            $sdata = curl_get("https://api88.net/api/dwz/?type=t&url=" . $key_word . "&key=" . $GLOBALS["API_KEY"]);
            //$sdata = short($key_word);
            $arr = json_decode($sdata, true);
            Robot::msg_get("生成短网址成功，你的短网址为" . $arr['short_url']);
        }
    }
    /**
     * @name: 生成二维码
     * 此框架还不支持发图片，所以此功能暂时搁置
     */
    public function add_qrcode(){
        if (strpos($GLOBALS['msg'], "生成二维码") !== false) {
            $key_word = getSubstr($GLOBALS['msg'], "生成二维码");
            if (empty($key_word)) {
                Robot::msg_get("生成失败，内容不能为空！");
                exit();
            }
            $data = file_get_contents('https://api88.net/api/code/?text=' . urlencode($key_word) . '&type=img');
            //$str = post($GLOBALS["host"] . '/httpAPI', $post_data);
            //msg_get($str);
        }
    }
    /**
     * @name: 生成加群链接
     * 
     */
    public function add_group_link(){
        if (strpos($GLOBALS['msg'], "生成加群链接") !== false) {
            $key_word = getSubstr($GLOBALS['msg'], "生成加群链接");
            if (empty($key_word)) {
                Robot::msg_get("生成失败，群号不能为空！");
                exit();
            }
            $sdata = curl_get("https://api88.net/api/qun/?guin=" . $key_word);
            $arr = json_decode($sdata, true);
            Robot::msg_get("生成成功:\n群号:" . $key_word . "\nPC无弹窗直接加群链接:" . $arr['pc'] . "\n手机加群链接:" . $arr['phone'] . "\n通用加群链接:" . $arr['url']);
        }
    }
    /**
     * @name: 查归属地
     * 
     */
    public function query_city(){
        if (strpos($GLOBALS['msg'], "查归属地") !== false) {
            $key_word = getSubstr($GLOBALS['msg'], "查归属地");
            if (empty($key_word)) {
                Robot::msg_get("查询失败，手机号不能为空！");
                exit();
            }
            $sdata = curl_get("https://api88.net/api/tel/?tel=" . $key_word . "&key=" . $GLOBALS["API_KEY"]);
            $arr = json_decode($sdata, true);
            Robot::msg_get("查询成功:\n手机号:" . $key_word . "\n省份:" . $arr['local']['province'] . "\n运营商:" . $arr['local']['catName'] . "\n归属地:" . $arr['local']['carrier']);
        }
    }
    /**
     * @name: 快手解析
     * 
     */
    public function decode_kuaishou(){
        if (strpos($GLOBALS['msg'], "快手解析") !== false) {
            $key_word = getSubstr($GLOBALS['msg'], "快手解析");
            if (empty($key_word)) {
                Robot::msg_get("解析失败，快手分享链接不能为空！");
                exit();
            }
            $sdata = curl_get("https://api88.net/api/kuaishou/?url=" . $key_word);
            $arr = json_decode($sdata, true);
            Robot::msg_get("解析成功，无水印下载地址:" . shorturl($arr['play']));
        }
    }
    /**
     * @name: 抖音解析
     * 
     */
    public function decode_douyin(){
        if (strpos($GLOBALS['msg'], "抖音解析") !== false) {
            $key_word = getSubstr($GLOBALS['msg'], "抖音解析");
            if (empty($key_word)) {
                Robot::msg_get("解析失败，抖音分享链接不能为空！");
                exit();
            }
            $sdata = curl_get("https://api88.net/api/douyin/?key={$GLOBALS['API_KEY']}&url=" . $key_word);
            $arr = json_decode($sdata, true);
            Robot::msg_get("解析成功:\n" . $arr['desc'] . "\n无水印下载地址:" . shorturl($arr['play']));
        }
    }
    /**
     * @name: 5sing解析
     * 
     */
    public function decode_5sing(){
        if (strpos($GLOBALS['msg'], "5sing解析") !== false or strpos($GLOBALS['msg'], "5SING解析") !== false) {
            $key_word = getSubstr($GLOBALS['msg'], "解析");
            if (empty($key_word)) {
                Robot::msg_get("5sing音乐地址有误，例如：\nhttp://5sing.kugou.com/fc/17309078.html");
                exit();
            }
            $str = curl_get("https://api88.net/api/5sing/?key=" . $GLOBALS["API_KEY"] . "&url=" . $key_word);
            $arr = json_decode($str, true);
            $arr = $arr['info'];
            Robot::msg_get("解析结果:\n歌曲名：" . $arr["data"]["songName"] . "\n普通音质：" . shorturl($arr["data"]["lqurl"]) . "\n高品音质：" . shorturl($arr["data"]["hqurl"]) . "\n无损音质：" . shorturl($arr["data"]["squrl"]));
        }
    }
    /**
     * @name: y2002解析
     * 
     */
    public function decode_y2002(){
        if (strpos($GLOBALS['msg'], "y2002解析") !== false or strpos($GLOBALS['msg'], "Y2002解析") !== false) {
            $key_word = getSubstr($GLOBALS['msg'], "解析");
            if (empty($key_word)) {
                Robot::msg_get("y2002音乐地址有误，例如：\nhttp://www.y2002.com/Songs/703375/4603796.html");
                exit();
            }
            $str = curl_get("https://api88.net/api/y2/?key=" . $GLOBALS["API_KEY"] . "&url=" . $key_word);
            $arr = json_decode($str, true);
            Robot::msg_get("解析结果:\n歌曲名：" . $arr["songs"] . "\n普通音质：" . shorturl($arr["file_m4a"]) . "\n高品音质：" . shorturl($arr["file_mp3"]));
        }
    }
    /**
     * @name: QQ音乐解析
     * 
     */
    public function decode_qqmusic(){
        if (strpos($GLOBALS['msg'], "QQ音乐解析") !== false) {
            $key_word = getSubstr($GLOBALS['msg'], "QQ音乐解析");
            if (empty($key_word)) {
                Robot::msg_get("解析失败，地址不能为空！例如：https://y.qq.com/n/yqq/song/002RZd8A4W3djY.html");
                exit();
            }
            if (strrpos($key_word, 'base')) {
                $header = get_headers($key_word, 1);
                $key_word = $header['Location'];
                preg_match('/songmid=(.*?)&/i', $key_word, $mid);
                if (empty($mid)) {
                    preg_match('/songid=(.*?)&/i', $key_word, $songid);
                    $html = curl_get('https://y.qq.com/n/yqq/song/' . $songid[1] . '_num.html');
                    preg_match('/"songmid":"(.*?)",/is', $html, $mid);
                }
            } else {
                preg_match('/song\/(.*?).html/is', $key_word, $mid);
            }
            if (empty($mid)) {
                Robot::msg_get("解析失败，地址有误！例如：https://y.qq.com/n/yqq/song/002RZd8A4W3djY.html");
                exit();
            } else {
                $json = curl_get('https://api88.net/api/qqmusic/?key=' . $GLOBALS["API_KEY"] . '&type=song&cache=1&id=' . $mid[1]);
                $arr = json_decode($json, true);
                $vid = $arr['vid'];
                if (!empty($vid)) {
                    $mvinfo = curl_get('https://api88.net/api/mv/?key=' . $GLOBALS["API_KEY"] . '&mv=qq&type=info&id=' . $vid);
                    $mvarr = json_decode($mvinfo, true);
                    Robot::msg_get("解析成功：\n" . $arr["title"] . " - " . $arr["author"] . "\n最高音质试听或下载地址:" . shorturl("https://api88.net/api/qqmusic/?key=" . $GLOBALS["API_KEY"] . "&type=url&size=flac&id=" . $mid[1]) . "\n该歌曲相关MV下载地址:\n【1080P画质】:" . shorturl($mvarr['mvurl']['1080p']) . "\n【720P画质】:" . shorturl($mvarr['mvurl']['720p']) . "\n【480P画质】:" . shorturl($mvarr['mvurl']['480p']) . "\n【360P画质】:" . shorturl($mvarr['mvurl']['360p']));
                } else {
                    Robot::msg_get("解析成功：\n" . $arr["title"] . " - " . $arr["author"] . "\n最高音质试听或下载地址:" . shorturl("https://api88.net/api/qqmusic/?key=" . $GLOBALS["API_KEY"] . "&type=url&size=flac&id=" . $mid[1]));
                }
            }
        }
    }
    /**
     * @name: 网易云音乐解析
     * 
     */
    public function decode_netease(){
        if (strpos($GLOBALS['msg'], "网易云音乐解析") !== false) {
            $key_word = getSubstr($GLOBALS['msg'], "网易云音乐解析");
            if (empty(getSubstr($key_word, "id="))) {
                Robot::msg_get("解析失败，地址错误！例如：https://music.163.com/song?id=1363948882");
                exit();
            }
            $sdata = netease_Url(getSubstr($key_word, "id="));
            Robot::msg_get("解析成功，歌曲试听或下载地址:\n" . shorturl($sdata));
        }
    }
}
/*
* 以下函数是上面自定义功能所调用的函数
*/
function ping($ip)
{
    exec("ping $ip", $info);
    //print_r($info);
    //$info_time = end($info);
    $str = '';
    foreach ($info as $ar) {
        $str .= $ar . "\n";
    }
    //$str = $info[1] . "\n" . $info[7] . "\n" . $info[8] . "\n" . $info[9] . "\n" . $info[10];
    $str = mb_convert_encoding($str, 'utf-8', 'gbk');
    //$str = $info[1]."\n".$info[7]."\n".$info[8]."\n".$info[9]."\n".$info[10];
    return $str;
}
function netease_Url($id)
{
    $url = 'http://music.163.com/song/media/outer/url?id=' . $id . '.mp3';
    $resInfo = get_headers($url, true);
    //$resInfo = str_replace('http', 'https', $resInfo);
    return $resInfo['Location'];
}
function rand_img_word()
{
    $file = file('img_word.txt');
    $um = count($file);
    $rand = rand(0, $um);
    $file_url = $file[$rand];
    return str_replace(array("\r\n", "\r", "\n"), "", $file_url);
}
function shorturl($str)
{
    $data = curl_get("http://tjit.net/?url=" . urlencode($str));
    $arr = json_decode($data, true);
    return $arr["shorturl"];
}
function checkIp($ip)
{
    $ip = str_replace(" ", "", $ip);
    $ip = strtolower($ip);
    $ip = str_replace("http://", "", $ip);
    $ip = str_replace("https://", "", $ip);
    if (preg_match('/[A-Za-z_-]/', $ip)) {
        if (preg_match('/^([\w-]+\.)+((com)|(net)|(org)|(gov\.cn)|(info)|(cc)|(com\.cn)|(net\.cn)|(org\.cn)|(name)|(biz)|(tv)|(cn)|(mobi)|(name)|(sh)|(ac)|(io)|(tw)|(com\.tw)|(hk)|(com\.hk)|(ws)|(travel)|(us)|(tm)|(la)|(me\.uk)|(org\.uk)|(ltd\.uk)|(plc\.uk)|(in)|(eu)|(it)|(jp)|(co)|(me)|(mx)|(ca)|(ag)|(com\.co)|(net\.co)|(nom\.co)|(com\.ag)|(net\.ag)|(fr)|(org\.ag)|(am)|(asia)|(at)|(be)|(bz)|(com\.bz)|(net\.bz)|(net\.br)|(com\.br)|(de)|(es)|(com\.es)|(nom\.es)|(org\.es)|(fm)|(gs)|(co\.in)|(firm\.in)|(gen\.in)|(ind\.in)|(net\.in)|(org\.in)|(jobs)|(ms)|(com\.mx)|(nl)|(nu)|(co\.nz)|(net\.nz)|(org\.nz)|(tc)|(tk)|(org\.tw)|(idv\.tw)|(co\.uk)|(vg)|(ad)|(ae)|(af)|(ai)|(al)|(an)|(ao)|(aq)|(ar)|(as)|(au)|(aw)|(az)|(ba)|(bb)|(bd)|(bf)|(bg)|(bh)|(bi)|(bj)|(bm)|(bn)|(bo)|(br)|(bs)|(bt)|(bv)|(bw)|(by)|(cd)|(cf)|(cg)|(ch)|(ci)|(ck)|(cl)|(cm)|(cr)|(cu)|(cv)|(cx)|(cy)|(cz)|(dj)|(dk)|(dm)|(do)|(dz)|(ec)|(ee)|(eg)|(er)|(et)|(fi)|(fj)|(fk)|(fo)|(ga)|(gd)|(ge)|(gf)|(gg)|(gh)|(gi)|(gl)|(gm)|(gn)|(gp)|(gq)|(gr)|(gt)|(gu)|(gw)|(gy)|(hm)|(hn)|(hr)|(ht)|(hu)|(id)|(ie)|(il)|(im)|(iq)|(ir)|(is)|(je)|(jm)|(jo)|(ke)|(kg)|(kh)|(ki)|(km)|(kn)|(kr)|(kw)|(ky)|(kz)|(lb)|(lc)|(li)|(lk)|(lr)|(ls)|(lt)|(lu)|(lv)|(ly)|(ma)|(mc)|(md)|(mg)|(mh)|(mk)|(ml)|(mm)|(mn)|(mo)|(mp)|(mq)|(mr)|(mt)|(mu)|(mv)|(mw)|(my)|(mz)|(na)|(nc)|(ne)|(nf)|(ng)|(ni)|(no)|(np)|(nr)|(nz)|(om)|(pa)|(pe)|(pf)|(pg)|(ph)|(pk)|(pl)|(pm)|(pn)|(pr)|(ps)|(pt)|(pw)|(py)|(qa)|(re)|(ro)|(ru)|(rw)|(sa)|(sb)|(sc)|(sd)|(se)|(sg)|(si)|(sk)|(sl)|(sm)|(sn)|(sr)|(st)|(sv)|(sy)|(sz)|(td)|(tf)|(tg)|(th)|(tj)|(tl)|(tn)|(to)|(tr)|(tt)|(tz)|(ua)|(ug)|(uk)|(uy)|(uz)|(va)|(vc)|(ve)|(vi)|(vn)|(vu)|(wf)|(ye)|(yt)|(yu)|(za)|(zm)|(zw))$/i', $ip)) {
            return true;
        } else {
            return true;
        }
    } else {
        $ipArray = explode(".", $ip);
        $j = count($ipArray);
        if ($j != 4) {
            return false;
        }
        for ($i = 0; $i < 4; $i++) {

            if (strlen($ipArray[$i]) == 0 || $ipArray[$i] > 255) {
                return false;
            }
        }
        return true;
    }
}
function cdn($url)
{
    $data = array(
        "url" => $url,
    );
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "http://tools.bugscaner.com/api/whichcdn/");
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_REFERER, "http://tools.bugscaner.com/whichcdn/");
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
}
function formatBytes($size)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2) . $units[$i];
}
function sping($host)
{
    $str = curl_get("https://api88.net/api/ping/?host=" . $host . "&key=" . $GLOBALS["API_KEY"]);
    $arr = json_decode($str, true);
    $data = '';
    foreach ($arr["data"] as $vue) {
        $time = $vue["ping"]["time"];
        if ($time == 0) {
            $time = "超时";
        }
        $ttl = $vue["ping"]["ttl"];
        if ($ttl == 0) {
            $ttl = "";
        } else {
            $ttl = "，TTL：" . $ttl;
        }
        $data .= $vue["name"] . "：" . $time . $ttl . "\n";
    }
    return $data;
}
function tqtext($str)
{
    preg_match('/查天气(.*?)(市|区|县)/', $str, $mat);
    if ($mat[1]) {
        return $mat[1];
    } else {
        preg_match('/查(.*?)(市|区|县)天气/', $str, $mat);
        if ($mat[1]) {
            return $mat[1];
        } else {
            preg_match('/查(.*?)天气/', $str, $mat);
            if ($mat[1]) {
                return $mat[1];
            }
        }
    }
    return false;
}
function get_redirect_url($url)
{
    $header = get_headers($url, 1);
    if (strpos($header[0], '301') !== false || strpos($header[0], '302') !== false) {
        if (is_array($header['Location'])) {
            return $header['Location'][count($header['Location']) - 1];
        } else {
            return $header['Location'];
        }
    } else {
        return $url;
    }
}
