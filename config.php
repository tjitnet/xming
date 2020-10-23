<?php
function config()
{
    return array(
        //以下功能: 开启填写true,关闭改为null
		"API_KEY"  => "1193755ae99702b0",  //api88.net平台的KEY密钥，请求接口所需，申请地址 api88.net/reg
		"host"     => "http://127.0.0.1",  //HTTPAPI接口地址，本地默认即可
		"Robot"    => "206006691",  //机器人QQ
		"group_guid" => array( //开启机器人所有功能的群，一行一个
                      "34544417",
					  ),
        "ban"      => true,  //开启群管监控检测违禁词禁言功能
		"ban_guid" => array( //开启群管监控的群，一行一个
                      "34544417",
					  ),
        "ban_ber"  => "2",   //发言包含违禁词超过此违规次数将禁言
        "ban_time" => "600", //禁言时长（秒）
		"ban_type" => true,  //违禁词触发提示方式；当此项为true则直接发送违规提示到群里并提示触发的词，否则只提示违规，具体违规的关键词私发消息给指定QQ，接收的QQ号在下面项目配置
		"ban_qq"   => "523077333",//触发违禁词接收的QQ号。
        "ban_add"  => true,  //增加词库功能
        "ban_del"  => true,  //删除词库功能
        "del_log"  => true,  //清理系统日志功能
        "rand_pic" => true,  //随机美图功能图片获取方式：true本地img目录的图片随机获取；null本地img_data.txt中随机读取网络图片URL
        "group_increase" => true, //欢迎新加入群成员开关
        "group_increase_msg" => "欢迎您的加入成为本群的一员，[menu]", //欢迎新加入群成员的文字,[menu]代表功能菜单,\n代表换行
        "key_word" => true,   //监控指定关键词回复，开关
        "key_word_text" => array(  //监控指定关键词回复，一行一个主意结尾小逗号,前面是关键词，后面是指定回复的内容。
            array(
                "key" => "官网",
                "word" => "我们的博客网址：\nhttps://www.tjit.net"
            ),
            array(
                "key" => "博客",
                "word" => "我们的博客网址：\nhttps://www.tjit.net"
            ),
            array(
                "key" => "接口",
                "word" => "我们的接口大全：\nhttps://api88.net\n接口文档：\nhttp://doc.tjit.net/"
            ),
			array(
                "key" => "密码",
                "word" => "模块密码/解压密码：\n群主QQ：523077333"
            ),
			array(
                "key" => "直连",
                "word" => "https://cloud.tjit.net/\n开发者存放文件，直连下载，地址永久不变，百兆带宽。可做云更新，云存储，存放程序数据，更新数据，分享文档等。"
            ),
			array(
                "key" => "云盘",
                "word" => "https://cloud.tjit.net/\n开发者存放文件，直连下载，地址永久不变，百兆带宽。可做云更新，云存储，存放程序数据，更新数据，分享文档等。"
            ),
			array(
                "key" => "网盘",
                "word" => "https://cloud.tjit.net/\n开发者存放文件，直连下载，地址永久不变，百兆带宽。可做云更新，云存储，存放程序数据，更新数据，分享文档等。"
            ),
			array(
                "key" => "直链",
                "word" => "https://cloud.tjit.net/\n开发者存放文件，直连下载，地址永久不变，百兆带宽。可做云更新，云存储，存放程序数据，更新数据，分享文档等。"
            ),
			array(
                "key" => "外链",
                "word" => "https://cloud.tjit.net/\n开发者存放文件，直连下载，地址永久不变，百兆带宽。可做云更新，云存储，存放程序数据，更新数据，分享文档等。"
            ),

        ),

    );
}
