<?php

/**
 * @name: 小明同学机器人入口文件
 * @author: www.tjit.net
 * @version: V4.0
 */
//入口参数
ini_set("display_errors", "Off");
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Headers:Origin, X-Requested-With, Content-Type, Accept');

require './Robot.Class.php';

