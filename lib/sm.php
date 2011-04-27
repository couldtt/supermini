<?php
/**   
 *@file sm.php 主框架内容;
 * vim: set fdm=marker:
 *@author xurenlu <helloasp@hotmail.com>
 *@version 1.0.1
 *\b License:  \b MIT <http://en.wikipedia.org/wiki/MIT_License>
 <pre>
 *@last_modified 2010-09-27 17:44:37
  \b Homepage: http://www.162cm.com/ 
  \b Slide: http://codeany.com/slides.10.play.miniphpkuangjiasuperminijianjie.shtml
  </pre>

  \b 使用之前要理解并同意的几个关键点
  
  1.Mysql是相当好用的，所以，这个框架只支持用mysql做数据库。没有设计一大堆的DBDriver;
  2.做cache,Memcache足够好用了，因此,内置的一些cache支持是基于memcache的;
  3.只用最好的，最必需的,精简精简再精简~
</pre>
 * 
 */
/**  smPhpEvent 	一些跟事件触发相关的全局函数. */
global $SM_PE_EVENTS,$SM_PE_FILTERS,$sm_config,$sm_temp,$sm_data;
$SM_PE_FILTERS=array();
$SM_PE_EVENTS=array();
/**
*	执行某一事件.
* @param $event String 事件的名称
*@param  $args MISC 要传递给事件的参数;
*/
function smDoEvent($event,$args=null){
	global $SM_PE_EVENTS;
	if(is_array($SM_PE_EVENTS[$event]))
	foreach($SM_PE_EVENTS[$event] as $handle)
	{
		if(function_exists($handle))
		$handle($args);
	}
}
/**
*	加入一个事件处理器
*@name		smAddEvent
*@param	$event	string	"event name"
*@param	$handle	string	"event handle" must be an exists function name
*@return nothing
*/
function smAddEvent($event,$handle){
	global $SM_PE_EVENTS;
	$SM_PE_EVENTS[$event][]=$handle;
}
/**
*	给数据加一个过滤器.
*@name	smAddFilter
*@param	$dataName	string	数据名
*@param	$filterName	string 过滤器名
*@return nothing
*/
function smAddFilter($dataName,$filterName){
	global $SM_PE_FILTERS;
	$SM_PE_FILTERS[$dataName][]=$filterName;
}
/**
*	给数据应用过滤器.
*@name	smApplyEvent
*@param	$data	string	数据
*@param	$dataName	string	数据名
*@return nothing
*/
function  smApplyEvent(&$data,$dataName) {
	global $SM_PE_FILTERS;
	if(is_array($SM_PE_FILTERS[$dataName]))
	foreach($SM_PE_FILTERS[$dataName] as $filter)
	{
		if(function_exists($filter))
		$filter(  $data);
	}
}
/***   sm_gen_url 拼凑URL时用到;*/ 
function sm_gen_url($string,$url_pattern,$get_args=array()){
    $targetURL=$url_pattern;
    foreach($get_args as $k=>$v){
        $targetURL=str_replace("{".$k."}",$v,$targetURL);
    }
    if(strlen($targetURL)>0)
        return  $targetURL;
    else 
        return $string;
}
/**  sm_test_urlencode * 探测一个变量是否已经被urlencode过了。 */
function sm_test_urlencode($var){
    return    (urldecode($var)==$var)?false:true;
}
/**  sm_pagenav_default 分页函数 ，
 * 
 * @param int $total 总记录个数
 * @param int $pagesize 每页记录数
 * @param string $pagestr 其他分页的链接模板
 * @param array $get	一般情况下就是GET数组
 * @param string $page_var_name 一般是page
 * @param int $l 	当前页链接的左边保留多少个链接
 * @param int $r 	当前页链接的右边保留多少个链接
 * @param int $jump 是否加跳转表单。但是当前只有一页时，不显示此跳转表单。
 */
function sm_pagenav_default($total,$pagesize=null,$pagestr=null,$get_args=null,$page_var_name="page",$l=4,$r=4,$jump=false){
    global $sm_temp;
    $url_pattern=$sm_temp["url_pattern"];
    if(is_null($pagestr)){
        $arr=array();
        if(is_null($get_args))
            $get_args=$_GET;

        while(list($key,$val)=each($get_args)){
            if(!sm_test_urlencode($val))
                $val=urlencode($val);

            if(strtolower($key)!=$page_var_name)
                $arr[]=$key."=".$val;
        }
        $arr[]=$page_var_name."={page}";
        $pagestr="?".join("&",$arr);
    }
    $get_args[$page_var_name]="{page}";
    if(is_null($pagesize)){
        global $sm_config;
        $pagesize=$sm_config["pagesize"]>0?$sm_config["pagesize"]:20;
    }
    $pagecount=$total/$pagesize;
    if(floor($pagecount)<$pagecount)
        $pagecount= floor($pagecount)+1;

    if(! ($_GET[$page_var_name]>0)){
        $_GET[$page_var_name]=1;
        $pagenow=1;
    }
    else
        $pagenow=$_GET[$page_var_name];

    $sn="page_".rand(1000,9999);
    $str="<form  onsubmit='javascript:return false;'>";
    //$str.=一共".$pagecount."页，".$total."个记录。当前为第".$pagenow."页。";
    if ($pagenow>1){
        $str=$str."<span><a href='".sm_gen_url(str_replace("{page}","1",$pagestr),str_replace("{page}",1,$url_pattern),$get_args)."'>首页</a></span>";
        $str =$str."<span> <a href='".sm_gen_url(str_replace("{page}",($pagenow-1),$pagestr),str_replace("{page}",($pagenow-1),$url_pattern),$get_args)."'>上一页</a></span>";
    }else{
        $str=$str."<span>首页</span><span>&lt;&lt;上一页</span>";
    }
    $startpage=$pagenow-$l;
    $endpage=$pagenow+$r;
    if($startpage<2) $startpage=2;
    if($endpage>=$pagecount) $endpage=$pagecount;
    for($jj=$startpage;$jj<=$endpage;$jj++){
        if($jj==$pagenow)
            $str=$str."<span class='cur'>".$jj."</span>";
        else
            $str=$str."<span ><a href='".sm_gen_url(str_replace("{page}",$jj,$pagestr),str_replace("{page}",$jj,$url_pattern),$get_args)."'>".$jj."</a></span>";
    }
    if($pagenow<$pagecount){
        $str=$str."<span><a href='".sm_gen_url(str_replace("{page}",$pagenow+1,$pagestr),str_replace("{page}",$pagenow+1,$url_pattern),$get_args)."'>下一页</a></span>";
        $str=$str."<span><a href='".sm_gen_url(str_replace("{page}",$pagecount,$pagestr),str_replace("{page}",$pagecount,$url_pattern),$get_args)."'>末页</a></span>";
    }else{
        $str=$str."<span>下一页</span><span>&gt;&gt;尾页</span>";
    }
    if($pagecount>1)
        if($jump){
            $str=$str."跳到<input type=\"text\" name=\"txtpage\" id='input_".$sn."' size=\"3\" class=\"tinput\" / >页";
            $str=$str."<input type=\"button\" value=\"GO\" class=\"tinput\"
                onclick=\"javascript:if((document.getElementById('input_".$sn."').value>=1) &&(document.getElementById('input_".$sn."').value<=".$pagecount.") &&(document.getElementById('input_".$sn."').value!=".$pagenow.")) window.location='".sm_gen_url($pagestr,$url_pattern,$get_args)."'.replace('{page}',document.getElementById('input_".$sn."').value);\"/></form>";
        }
    return $str;
}
/** class smCache 调用memcache 取缓存;*/
class smCache { 
    private $_group_id;
    private $_servers;
    private $_memcache;
    private $_flag=0;
    public $expire=7200;
    /***   __construct */ 
    public function __construct($group_id){
        global $sm_config;
        $this->_group_id=$group_id;
        $this->_servers=$sm_config["memcache"][$group_id];
        $mem=new memcache();
        foreach($this->_servers as $server){
            $mem->addServer($server["host"],$server["port"]);
        }
        $this->_memcache=$mem;
    }
    /** *  get_data 读缓存值 */
    function get_data($key){
        return $this->_memcache->get($key);
    }
    /***  set_data 设置缓存值;*/
    function set_data($key,$val,$expire=7200){
        return  $this->_memcache->set($key,$val,$this->_flag,$expire);
	}
	function delete($key){
		return $this->_memcache->delete($key);
	}
    /***  set_flag */
    function set_flag($flag){
        $this->_flag = $flag;
    }
    function __get($name){
        return $this->get_data($name);
    }
    function __set($name,$value){
        return $this->set_data($name,$value,$this->expire);
    }
}

/**  static class smSql  帮助构造SQL语句的小工具类; */
class smSql{
	var $pagesize=20;
	static function escape_string($v){
		return mysql_escape_string($v);
    }
	/**  update  构造更新SQL
	*@param  $table string 要更新的数据表名
	*@param  $array array要更新的数据
	*@param  $condition string更新条件
	*@param $limit integer 更新的条数;
	*/
	static function update($table, $array, $condition,$limit=1){   
		if(is_array($array)){
			$sql = "UPDATE `".$table."` SET ";
			$comma = ""; 
			foreach ($array AS $_key => $_val)
			{
				$sql .= $comma."`".$_key."` = '".self::escape_string($_val)."'";
				$comma = ", ";
			}

			if ($condition)
			{
				$sql .= " WHERE ".$condition;
            }
            $sql .= " LIMIT $limit";
			return $sql;
		}
		else{
			return false;
		}
    }
	/**  select 构造查询SQL
     *
     * @param  $table string表名字
	 * @param  $columns string 要查找的列，默认是"*"
	 * @param  $conditions string ,默认是null,请给出sql语句 where子句where后面的部分
	 * @param  $order string 
	 * @param  $limit string
	 * @param  $group string
	 */
	static function select($table,$columns="*",$conditions=null,$order=null,$limit=null,$group=null){
		global $config;
		if(is_array($columns))
			$cols=join(",",$columns);
		else
			$cols=$columns;
		$sql="	SELECT ".$columns." From ".$table;
		if(!is_null($conditions))
		    $sql.="	WHERE ".$conditions;
		if(!is_null($group))
		    $sql.="	GROUP BY ".$group;
		if(!is_null($order))
		    $sql.="	ORDER BY ".$order;
		if(!is_null($limit))
		    $sql.="	LIMIT ".$limit;
		return $sql;
    } 
    /***   count 构造count类语句,注意构造出的是count(*) as c ;*/ 
	static function count($table,$conditions=null,$order=null,$limit=null,$group=null){
        return self::SELECT($table,"count(*) as c",$conditions,$order,$limit,$group); 
    }
	/**  insert 得到插入语句的SQL
	*@param  $table string 数据表名字
	*@param  $array array 要插入的一行数据
	*@param $type 要么是INSERT要么是REPLACE,这决定生成的sql语句是insert into 还是replace into 
	**/
	static function insert($table,$array,$type="INSERT"){
		if (is_array($array)){	
			$comma = $key = $value = "";
			foreach ($array AS $_key => $_val){	
				$key .= $comma."`".$_key."` ";
				$value .= $comma."'".self::escape_string($_val)."'";	
				$comma = ", ";
			}
			$sql = "$type INTO ".$table.  "(".$key.") VALUES (".$value.")";
			return $sql;
		}
    }
	/**  delete  得到删除语句的SQL
	* @param  $table string 数据表名字
	* @param  $condition string 条件
	* @param  $limit string limit字段
	*/
	static function delete($table,$condition,$limit="1"){
		$sql="DELETE FROM `".mysql_escape_string($table)."` WHERE ".$condition." LIMIT ".$limit;
		return $sql;
    }
}
/***   _sm_mysql  连接Mysql的实际函数 */ 
function _sm_mysql($id){
    global $sm_config; 
    if(!is_array($sm_config["mysql"][$id])){
        throw new smException("MYSQL configuration 'sm_config[\"mysql\"][$id]' not exists");
    }
    $config=$sm_config["mysql"][$id];
    $conn=mysql_connect($config["host"],$config["user"],$config["password"]);
    $switch=mysql_select_db($config["database"],$conn);
    smDoEvent("select_db",$conn);
    if(!is_resource($conn) || !$switch){
            throw new smException("Mysql error:Can't connect to hosts with : -h ".$config["host"]." -u ".$config["user"]." -p ".substr($config["password"],0,2)."*** ".$config["database"]);
    }
	
    if(!empty($sm_config["prepare_sql"])) sm_query($sm_config["prepare_sql"]."",$conn);
    return $conn;
}
/***   sm_dbo 返回一个连接对象
 * @param integer $id 在sm_config里的mysql相关配置索引;*/ 
function sm_dbo($id=0){
    global $sm_config,$sm_temp;
    return is_resource($sm_temp["connections"][$id])?$sm_temp["connections"][$id]:_sm_mysql($id);
}
/***   sm_query 执行一条sql查询并返回结果 */ 
function sm_query($sql,$conn=null){
    global $sm_config,$sm_temp;
    $sm_temp["sqls"][]=$sql;
    if($sm_config["sql_debug"]){
        error_log($sql);
    }
    smDoEvent("before_query",$sql);
    $ret=is_null($conn)?mysql_query($sql):mysql_query($sql,$conn);//不指定conn时,mysql会调用默认连接
    if(!$ret){
        if(is_null($conn)){
            smDoEvent("query_fail",array("sql"=>$sql,"error_no"=>mysql_error()));
            throw new smException("mysql error:sql:$sql,error descrption:".mysql_errno().":".str_replace("\n","",mysql_error()));
        }
        else{
            smDoEvent("query_fail",array("sql"=>$sql,"error_no"=>mysql_error($conn)));
            throw new smException("mysql error:sql:$sql,error descrption:".mysql_errno($conn).":".str_replace("\n","",mysql_error($conn)));
        }
    }else{
        smDoEvent("query_succeed",array("sql"=>$sql,"resource"=>$ret));
    }
    smDoEvent("after_query",$sql);
    return $ret;
}
/**  sm_fetch_row 取出sql查询的一条结果 */
function sm_fetch_row($sql,$conn=null){
    $rs=sm_query($sql,$conn);
    return empty($rs)? null:mysql_fetch_assoc($rs);
}

/*!   sm_fetch_rows 取出sql查询的多条结果 */ 
function sm_fetch_rows($sql,$conn=null,$type=MYSQL_ASSOC){
    global $sm_config;
    $rs=sm_query($sql,$conn);
    if(!empty($rs)){
        $rows=array();
        while($row=mysql_fetch_array($rs,$type)){
            $rows[]=$row;
        }
        return $rows;
    }
}
/**  smObject class
* 实现一个比较灵活的功能,调用它的属性时，会自动地创建数据库，创建缓存对象等.
**/
class smObject {
	/** 数据表的默认主键 */
    public $default_primary_key = "id";
    /**  存放回调钩子 */
    public $callbacks=array();
    /** 设置对某个属性调用时触发的钩子 */
    public function set_callback($name,$callback){
        $this->callbacks[$name]=$callback;
    }
    /***   __get 
     * the main magic method
     */ 
    public function __get($name){
        global $sm_config,$sm_temp,$sm_data;
        if(!empty($sm_config[$name]))
            return $sm_config[$name];
        if(!empty($sm_temp[$name]))
            return $sm_temp[$name];
        if(!empty($sm_data[$name]))
            return $sm_data[$name];
        if($this->callbacks[$name]){
            $sm_temp[$name]=$this->callbacks[$name]();
            return $sm_temp[$name];
        }

        if(preg_match("/^get_(.*)+$/",$name)){
            $id = substr($name,4,strlen($name));
            $sm_temp["get_".$id]=$_GET[$id];
            return $sm_temp["get_$id"];
        }
        if(preg_match("/^env_(.*)+$/",$name)){
            $id = substr($name,4,strlen($name));
            $sm_temp["env_".$id]=$_SERVER[$id];
            return $sm_temp["env_$id"];
        }
        if(preg_match("/^post_(.*)+$/",$name)){
            $id = substr($name,5,strlen($name));
            $sm_temp["post_".$id]=$_POST[$id];
            return $sm_temp["post_$id"];
        }
        if(preg_match("/^dbo_(.*)+$/",$name)){
            $id = substr($name,4,strlen($name));
            $sm_temp["dbo_".$id]=sm_dbo($id);
            return $sm_temp["dbo_$id"];
        }
        if(preg_match("/^table_(.*)$/",$name)){
            $table_name = substr($name,6,strlen($name));
            $table = new smTable($table_name,$this->default_primary_key,$this->dbo_0,$this->dbo_1);
            $sm_temp[$name]=$table;
            return $table;
        }
        if(preg_match("/^cache_(.*)$/",$name)){
            $cache_group = substr($name,6,strlen($name));
            $cache = new smCache($cache_group);
            $sm_temp[$name]=$cache;
            return $cache;
        }
    }
}
/** class smException ,就是一个空类，继承了exception */
class smException  extends Exception{}
/**  class smTable,实现sql查询相关的一些功能; **/
class smTable{
    private $_rconn=null;
    private $_wconn=null;
    private $_table=null;
    private $_pagesize=null;
    private $_page_var = "page";
    private $_extra_args = null;
    private $_temp=array();
    function reset_temp(){
    	$this->_temp = array("where"=>null,"group"=>null,"order"=>null,"limit"=>null,"select"=>"*");
    }
    function __construct($table,$primary_key="id",$rconn=null,$wconn=null){
        global $sm_config;
        $this->_table=$table;
        if(is_null($rconn))
            $rconn=sm_dbo(0);
        $this->_rconn=$rconn;
        if(is_null($wconn))
            $wconn=$rconn;
        $this->_wconn=$wconn;
        $this->_extra_args=$_GET;
        $this->_pagesize=($sm_config["pagesize"]>0)?$sm_config["pagesize"]:20;
		$this->reset_temp();
    }
    /***  set variables */ 
    public function __set($varname,$value){
        $this->$varname=$value;
    }
    /** get last inserted id */
    public function insert_id(){
       return mysql_insert_id($this->_wconn); 
    }
    /***   get_select_conditions */ 
    function get_select_conditions($columns,$values){
            $conditions=array();
            foreach($columns as $k=>$v){
                $conditions[]="`".$v."` = '".mysql_escape_string($values[$k])."'";
            }
            return $conditions; 
    }
    function desc(){
        $rows=sm_fetch_rows("desc ".$this->_table);
        return $rows;
    }
    public function cache_then_find_by($key,$conditions=null,$wanted="*",$order_by=null,$limit=null,$group_by=null){
        global $sm,$sm_config;
        $temp=unserialize($sm->cache_group_1->$key);
        if(!empty($temp)){
            return $temp;
        }
        $temp=$this->find_by($conditions,$wanted,$order_by,$limit,$group_by);
        $sm->cache_group_1->set_data("$key",serialize($temp));
        return $temp;

    }
    /***   find_by 根据指定的条件来查询*/ 
    public function find_by($conditions=null,$wanted="*",$order_by=null,$limit=null,$group_by=null){
        $sql=smSql::select($this->_table,$wanted,$conditions,$order_by,$limit,$group_by);
        $rows=sm_fetch_rows($sql,$this->_rconn);
        return $rows;
    }
    public function cache_then_find_row_by($key,$conditions=null,$wanted="*",$order_by=null,$limit=1,$group_by=null){
        global $sm,$sm_config;
        $temp=unserialize($sm->cache_group_1->$key);
        if(!empty($temp))
            return $temp;
        $temp=$this->find_row_by($conditions,$wanted,$order_by,$limit,$group_by);
        $sm->cache_group_1->set_data("$key",serialize($temp));
        return $temp;
    }
    /***  find_row_by 根据条件列查找数据,直接调用find_by并返回第一条数据;*/ 
    public function find_row_by($conditions=null,$wanted="*",$order_by=null,$limit=1,$group_by=null){
        return array_shift($this->find_by($conditions,$wanted,$order_by,$limit,$group_by));
    }
    public function cache_then_page_by($conditions=null,$wanted="*",$order_by=null,$limit=null,$group_by=null){
        global $sm,$sm_config;
        $temp=unserialize($sm->cache_group_1->$key);
        if(!empty($temp))
            return $temp;
        $temp=$this->page_by($conditions,$wanted,$order_by,$limit,$group_by);
        $sm->cache_group_1->set_data("$key",serialize($temp));
        return $temp;
    }
    
    /***  page_by 根据条件查询数据,同时自带分页;*/ 
    public function page_by($conditions=null,$wanted="*",$order_by=null,$limit=null,$group_by=null){
        if(! ($_GET[$this->_page_var]>0))
            $pagenow=1;
        else
            $pagenow=$_GET[$this->_page_var];
        $limit=($pagenow-1)*$this->_pagesize.",".$this->_pagesize;
        $rows=$this->find_by($conditions,$wanted,$order_by,$limit,$group_by); 
        $count_sql=smSql::count($this->_table,$conditions,$order_by,1,$group_by);
        $row=sm_fetch_row($count_sql,$this->_rconn);
        $total=$row["c"];
        $pagestr = sm_pagenav_default($total,$this->_pagesize,$this->_pagestr,$this->_extra_args,$this->_page_var,3,3);
        return array("total"=>$total,"entries"=>$rows,"page"=>$pagestr);
     }
     /***  update_by 根据条件更新数据;*/ 
     public function update_by($conditions,$values,$limit=1){
		$sql=smSql::update($this->_table,$values,$conditions,$limit);
        return sm_query($sql,$this->_wconn); 
     }
     /***  delete_by */ 
     public function delete_by($conditions,$limit=1){
		    $sql=smSql::delete($this->_table,$conditions);
		    return sm_query($sql,$this->_wconn);
     }
     /***   create */ 
     public function create($row,$type="INSERT"){
         $sql=smSql::insert($this->_table,$row,$type);
         return sm_query($sql,$this->_wconn); 
     }
     public function __call($name,$args){
     	if(in_array($name,array("where","group","order","limit","select"))){
     		$this->_temp["$name"]=$args[0];
     		return $this;
     	}
     	if($name=="data"){
     		$this->_temp["data"]=$args[0];
     	}
     	if($name=="row"){
     		$temp= $this->find_row_by($this->_temp["where"],$this->_temp["select"],$this->_temp["order"],$this->_temp["limit"],$this->_temp["group"]);
     		$this->reset_temp();
     		return $temp;
     	}
     	if($name=="rows"){
     		$temp= $this->find_by($this->_temp["where"],$this->_temp["select"],$this->_temp["order"],$this->_temp["limit"],$this->_temp["group"]);
     		$this->reset_temp();
     		return $temp;
     	}
     	if($name=="page"){
     		$temp= $this->page_by($this->_temp["where"],$this->_temp["select"],$this->_temp["order"],$this->_temp["limit"],$this->_temp["group"]);
     		$this->reset_temp();
     		return $temp;
     	}
     }
}

/**
* @brief	smApplication Mvc 功能主要在这里实现
* 
* 这里只实现特别简单的MVC功能,一般建议用户将app目录设置为./app/,controller文件就旅行团在app/***.php里，
* 而views层的文件则放在./app/views/{controller}/{action}.php里。
*/
class smApplication{
    public $_app="smapplication";
    public $_name="smapplication";
    public $_last_action="index";
    public $before_filters=array();
    public $after_filters=array();
    
    function __construct($name="smapplication"){
        global $sm_config;
        $this->_name=$name;
        if(!class_exists($name)){
            include_once($sm_config["app_root"]."/app/".strtolower($name).".php");
            $this->_app = new $name($name);
        }else{
            $this->_app=$this;
        }
    }
    /***   _before_filter */ 
    function _before_filter($action){
		
        $var_name = "before_filters_$action";
        foreach($this->$var_name as $method){
            $this->$method();
        }
        foreach($this->before_filters as $method){
            $this->$method();
        }
    }
     /***  _after_filter      */ 
    function _after_filter($action){
	
        $var_name = "after_filters_$action";
        foreach($this->$var_name as $method){
            $this->$method();
        }
        foreach($this->after_filters as $method){
            $this->$method();
        }
     }
    /***   method_miss */ 
    private function _method_missing($method) {
        throw new smException("method  missing:".$this->_name."->".$method);
    }
    /***  v include the view files;*/
    function v($action=null){
        global $sm_config;
        if(is_null($action))
            $action=$this->_last_action;
        else
            $this->_last_action=$action;
        $mod=$this->_name;
        if($sm_config["use_layout"]){
            //如果使用布局并且布局文件存在...
                if(!include($sm_config["app_root"]."/app/layouts/$mod.php") )
                    return include($sm_config["app_root"]."/app/views/$mod/$action.php");
                else return true;
        }
        else{
               return include($sm_config["app_root"]."/app/views/$mod/$action.php");
        }
    }
    /***   dispatch run the filters and real action method;*/ 
    public function dispatch($action){
        global $sm_config;
        $this->_last_action =$this->_app->_last_action= $action;
        $methods=get_class_methods($this->_app);
        if(
        (in_array($action,$methods) && ($method=$action)) ||
        ( in_array("action_".$action,$methods) &&($method="action_".$action))){
            $this->_app->_before_filter($action);
            $this->_app->$method();
            $this->_app->_after_filter($action);
            return true;
        }
        /* 如果views 文件也不存在,那就调用method_missing方法; */
        $this->_app->_method_missing($action);
        return false;
    }
    public function yield(){
        global $sm_config;
        return include($sm_config["app_root"]."/app/views/".$this->_name."/".$this->_last_action.".php");
    }
    /***  establish_connect 建立默认连接,默认情况下读写用同一个链接; */
    public function establish_connect(){
        global $sm;
        $this->_rconn=$sm->dbo_1;
        $this->_wconn=$sm->dbo_0;
    }
    /***  __get magic method;*/
    public function __get($var){
		return array();
    }
}
function sm_tag($tagname,$html_attrs=array(),$inner_html=""){
    $str="<$tagname ";
    if(is_array($html_attrs)){
    foreach($html_attrs as $k=>$v){
        $str.= "$k='".htmlspecialchars($v)."' ";
    }
    }
    else{
        $str .= $html_attrs;
    }
    if(!empty($inner_html))
        $str .= ">".$inner_html."</$tagname>";
    else
        $str .= "/>";
    return $str;
}
function sm_image_tag($src,$html_attrs=array()){
    global $sm_config;
	if(!preg_match("/http:/",$src))
    	$src = $sm_config["image_path"]."$src";
    if(is_array($html_attrs))
        return sm_tag("img",array_merge($html_attrs,array("src"=>$src)));
    else
        return sm_tag("img",$html_attrs." src='".$src."'");
}

function sm_link_css($src){
    global $sm_config;
	if(!preg_match("/http:/",$src))
    	$src = $sm_config["css_path"]."$src";
	if(is_array($html_attrs)){
		$html_attrs["rel"]="stylesheet";
		$html_attrs["type"]="text/css";
        return sm_tag("link",array_merge($html_attrs,array("href"=>$src)));
	}
    else{
		$html_attrs .= 'rel="stylesheet" type="text/css"';
        return sm_tag("link",$html_attrs." href='".$src."'");
	}
}
/**  class Form ,旨在减化生成表单的一些操作;**/
class smForm {
    var $_values=array();
    var $_name="";
    /** 构造函数;
	 *
	 * @param $name String 一般设置为表的名字;
     * @param $values Array 各个域的值;
	 * 如果$arg是一维数组,则表示数据表中的一列数据;
     * 如果$arg是对象,则视为smTable对象,暂未实现;
     * 如果$arg是字符串,则视为mysql数据表名字,暂未实现;
     * */
    function __construct($name,$values=null){
        $this->_name=$name;
        $this->_values=$values;
    }
	/** Form表单的<form action=** method="***">部分
	*
	* @param $action String,Form表单的提交地址。
	* @param $html_attrs Array,Form表单附加的其他属性;
	*/
    function begin($action,$html_attrs=array("method"=>"POST")){
        $str="<form  action='$action' ";
        foreach($html_attrs as $k=>$v){
            $str.=" $k='".$v."' ";
        }
        $str.= ">";
        return $str;
    }
	/** 关闭Form标签; 
	*/
    function end(){
        return "</form>";
    }

    function caption($field_name,$html_attrs=array(),$caption=""){
        $html_attrs["for"]=$this->_name."_".$field_name;
        return $this->label("$field_name",$html_attrs,$caption);
    }

	/** 输出一个textarea 标记;
	*
	* @param $field_name String 域名字;
	* @param $html_attrs Array HTML属性;
	*/
function text_area($field_name,$html_attrs=array()){
		$value=$this->_get_value($field_name,$html_attrs);
        $html= $this->_left("textarea",$field_name,$html_attrs);
        $html.=">".htmlspecialchars($value)."</textarea>";
        return $html;
    }
	/** 输出一个文本输入框 */
    function text_field($field_name,$html_attrs=array()){
        $value=$this->_get_value($field_name,$html_attrs);
		if(empty($html_attrs["type"]))
        	$html_attrs["type"]="text";
        $html_attrs["value"]=$value;
        return $this->input($field_name,$html_attrs);
    }
	/** 属出一个checkbox */
    function check_box($field_name,$html_attrs=array("type"=>"check_box")){
        if(!isset($html_attrs["value"]))
            throw new smException("check_box must specific a value");
		$html_attrs["type"]="check_box";
        $checked_value=$this->_get_value($field_name,array());
        if($checked_value==$html_attrs["value"])
            $html_attrs["checked"]="checked";
        return $this->input($field_name,$html_attrs);
    }
	/** 输出一个提交按钮 */
    function submit($value="提交",$html_attrs=array()){
        $html_attrs["type"]="submit";
        return $this->button("",$html_attrs,$value);
    }
	/** 输出一个SELECT下拉框
	* @param $field_name String,字段域名字
	* @param $values Array,一个二维数组;比如:array(array("1","属性1"),array("2","属性2"),array(3,"属性3"))
	* @param $html_attrs Array HTML属性集
	*/
    function select($field_name,$values,$html_attrs=array()){
        $value=$this->_get_value($field_name,$html_attrs);
        $select_html = $this->_left("select",$field_name,$html_attrs);
        $strs=array();
        foreach($values as $v){
            if(sizeof($v)!=2) throw new smException("you assign a bad value for select ,file:".__FILE__.",line:".__LINE__);
            if(!empty($value) && $v[0]==$value ){
                $temp=$strs[]=$this->option("",array("value"=>$v[0],"selected"=>"selected"),$v[1]);
            }else{
                $strs[]=$this->option("",array("value"=>$v[0]),$v[1]);
            }
        }
        return "$select_html>".join("",$strs)."</select>";
    }
	/** smForm类未明确给出的形形色色的其他各种HTML标记 
	*
	* @param $name String HTML tag 的种类，可以是img,marquee,fieldset,iframe等等;
	* @param $args Array,一个有三个项的数组,第一个项是数据域名字,第二个是HTML属性,第三个是包含在标记里的innerhtml。
	*/
    function __call($name,$args){
        $tag_name = $name;
        $field_name= array_shift($args);
        $html_attrs=array_shift($args);
        $inner_html=array_shift($args);
        $left_htmls = $this->_left($tag_name,$field_name,$html_attrs);
        $str.=$left_htmls.">".$inner_html."</$tag_name>";
        return $str;
    }
    function _get_value($field_name,$html_attrs){
        if(!empty($this->_values)){
            $value=$this->_values[$field_name];
        }
        if(!empty($html_attrs["value"]))
            $value=$html_attrs["value"];
        return $value;
    }
    function _left($tag_name,$field_name,$html_attrs){
        if($field_name){
            $str="<".$tag_name." id='".$this->_name."_".$field_name."' name='".$this->_name."[".$field_name."]' ";
        }
        else{
            $str="<".$tag_name." ";
        }
        foreach($html_attrs as $k=>$v){
            $str .=$k."=\"".$v."\" ";
        }
        return $str;
    }
}
/**   run_sm 跑MVC流程 
* @param $controller controller名字;
* @param $action action名字;
*/ 
function run_sm($controller=null,$action=null) {
    global $sm_temp,$sm_config;
    if(is_null($controller)) 
        $sm_temp["controller"]=empty($_GET["controller"])?  "smapplication":strtolower($_GET["controller"]);
    else
        $sm_temp["controller"]=$controller;
    if(is_null($action))
        $sm_temp["action"]=empty($_GET["action"])?  "index":strtolower($_GET["action"]);
    else
        $sm_temp["action"]=$action;

    smDoEvent("before_run_sm",array("controller"=>$sm_temp["controller"],"action"=>$sm_temp["action"]));

    if(!class_exists($sm_temp["controller"]))
        include_once($sm_config["app_root"]."/app/".strtolower($sm_temp["controller"]).".php");
    $app=new $sm_temp["controller"]($sm_temp["controller"]);
    return $app->dispatch($sm_temp["action"]); 
}
$sm= new smObject();

/**
* @example 
* 
* @see sm_pagenav_default
* @code
* echo sm_pagenav_default(18332,20);
* echo sm_pagenav_default(18244,25,"index.php?page={page}",array("key"=>1),"page",3,3);
* echo sm_pagenav_default(18244,25,null,array("key"=>1),"page",3,3);
* @endcode
*
* @see smObject
* @code
*	$sm=new smObject;
*	//此时自动用memcache1的配置创建memcache对象.
*	print $sm->cache_memcache1->get_data("site_config_name");
*	print $sm->dbo_default;//自动连接数据库了,用的是sm_config["mysql"]["default"];
* @endcode
*
* @see smSql
* @code
* smSql::update( "users", array( "id"=>"111", "name"=>"uxferwe'fdsf", "pass"=>"fdsfdsfu2323\\fsdfdsf/'fsdfsdf\""), "id=9999");
* smSql::insert( "users", array( "id"=>"111", "name"=>"uxferwe'fdsf", "pass"=>"fdsfdsfu2323\\fsdfdsf/'fsdfsdf\""));
* smSql::select( "users", "*", "id>9999", "id desc", "limit 100", "age");
* @endcode
* 
* @see smForm
* 表单处理部分:modify_info.php
* @code
* $userinfo=array("email"=>"xurenlu@gmail.com","age"=>20,"");
* $f=new smForm("user",$userinfo);
* echo $f->begin("saveuser.php");
* echo $f->text_field("email");
* echo $f->select("age",array(arra("19","19岁"),array(20,"20岁")));
* echo $f->submit();
* echo $f->end(); 
* @endcode
* 保存用户信息部分:saveuesr.php
* @code 
* $user=new smTable("users");
* $user->update_by("id=12",$_POST["user"]);
* @endcode
*/
