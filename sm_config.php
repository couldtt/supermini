<?php
/**
 * sm_config 用来设置全局配置;
 * sm_temp 是中间变量,做一些处理工作;
 * sm_data 用来处理输出;
 * */
global $sm_config,$sm_temp,$sm_data;
$sm_config["mysql"][0]=array("host"=>"localhost","user"=>"root","password"=>"842519","database"=>"test");
$sm_config["mysql"][1]=array("host"=>"localhost","user"=>"root","password"=>"842519","database"=>"test");
$sm_config["memcache"]["group_1"]=
    array(
        array("host"=>"localhost","port"=>11211),
        array("host"=>"localhost","port"=>11211)
    );
$sm_config["debug"]=true;
$sm_config["sql_debug"]=true;
$sm_config["pagesize"]=20;
