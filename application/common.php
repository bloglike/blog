<?php
/**
 * FileName: common.php
 * Description: 公共函数库
 * Date: 2017/2/16
 * Time: 14:18
 * Author http://www.elsde.com
 */

// 应用公共文件
/**
 * 密码加密
 * @param unknown_type $password
 * @param unknown_type $salt
 */
function encryption_pass($password, $salt_1,$salt_2){
    return md5(md5(md5($password).$salt_1).$salt_2);
}
/**
 * ajax返回函数
 * @param state
 * @param info
 */
function doAjaxReturn($state, $info = '错误！', $text = '', $token = '')
{
    $re = array();
    $re['state'] = $state;
    $re['info'] = $info;
    if (!empty($text)) {
        $re['text'] = $text;
    }
    if (!empty($token)) {
        $re['token'] = $token;
    }
    return $re;
}

/**
 * 过滤非法字符串
 */
function safeText($text, $badStr = false, $parseBr = true, $transport = false)
{
    $text = htmlspecialchars_decode($text);
    $text = safe($text, 'text');
    if (!$parseBr) {
        $text = str_ireplace(array("\r", "\n", "\t", "&nbsp;"), '', $text);
        $text = htmlspecialchars($text, ENT_QUOTES);
    } else {
        $text = htmlspecialchars($text, ENT_QUOTES);
        if ($transport) {
            $text = nl2br($text);
        }
    }
    $text = trim($text);
    if ($badStr) {
        $badStr = "'|exec|select|update|delete|insert|truncate|char|into|substr|declare|exec|master|chr|truncate|from|declare|sitename|net user|xp_cmdshell|drop|execute|union|--|+|like|%|#|*|$|\"|http|<|>|(|)|https";
        $badArr = explode('|', $badStr);
        foreach ($badArr as $v) {
            if (strpos($text, $v) !== false) {
                $text = str_replace($v, "", $text);
            }
        }
    }
    return $text;
}
function safe($text, $type = 'html', $tagsMethod = true, $attrMethod = true, $xssAuto = 1, $tags = array(), $attr = array(), $tagsBlack = array(), $attrBlack = array())
{
    //无标签格式
    $text_tags = '';
    //只存在字体样式
    $font_tags = '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';

    //标题摘要基本格式
    $base_tags = $font_tags . '<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike>';

    //兼容Form格式
    $form_tags = $base_tags . '<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';

    //内容等允许HTML的格式
    $html_tags = $base_tags . '<ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed><param>';

    //专题等全HTML格式
    $all_tags = $form_tags . $html_tags . '<!DOCTYPE><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';

    //过滤标签
    $text = strip_tags($text, ${$type . '_tags'});

    //过滤攻击代码
    if ($type != 'all') {
        //过滤危险的属性，如：过滤on事件lang js
        while (preg_match('/(<[^><]+) (onclick|onload|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|action|background|codebase|dynsrc|lowsrc)([^><]*)/i', $text, $mat)) {
            $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
        }
        while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
            $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
        }
    }
    return $text;
}
/**
 * 生成token
 * @return string
 *
 */
function make_token($actionName)
{
    $actionName = sha1($actionName);
    $user_id = \think\Session::get('user_id');
    $userid = isset($user_id)? $user_id : 0;
    \think\Session::delete('token.'.$actionName.'');
    $token = sha1(uniqid('lwh_blog') . $userid);
    $token_pre = sha1(uniqid('thisblog') . $userid);
    \think\Session::set('token.'.$actionName.'.'.$token_pre.']',$token);
    $token = $token_pre . '_' . $token;
    return $token;
}

/**
 * 检查token值是否合法，并根据标志销毁token------只要验证失败，均销毁token
 * @param $token
 * @return bool
 */
function check_token($token, $actionName, $flag = true)
{
    $actionName = sha1($actionName);
    $arr = explode('_', $token);
    $str = \think\Session::get('token.'.$actionName.'.'.$arr[0].'');
    if (isset($str) && ($str == $arr[1])) {
        if ($flag) {
            \think\Session::delete('token.'.$actionName.'');
        }
        return true;
    } else {
        \think\Session::delete('token.'.$actionName.'');
        return false;
    }
}
/**
 * 会员登录日志
 *
 */
function insert_log($user_id,$type,$title,$event){
    if(intval($user_id)){
        $user_id = intval($user_id);
        $event = safeText($event);
        $title = safeText($title);
        $time = time();
        $address_ip = real_ip();
        $address_name = GetIpLookup($address_ip);
        if(!$address_name){
            $address_name = '未知地区';
        }else{
            $address_name = $address_name['country'].$address_name['province'];
        }
        $prefix=config("database.prefix");
        $res = \think\Db::execute("insert into {$prefix}user_log (`user_id`,`event`,`title`,`addtime`,`type`,`address_ip`,`address_name`) values($user_id,'$event','$title',$time,$type,'$address_ip','$address_name')");
        return $res;
    }else{
        return false;
    }
}
/**
 * 获得用户的真实IP地址
 *
 * @access  public
 * @return  string
 */
function real_ip()
{
    static $realip = NULL;

    if ($realip !== NULL) {
        return $realip;
    }
    /*添加*/
    if (isset($_COOKIE['real_ipd']) && !empty($_COOKIE['real_ipd'])) {
        $realip = $_COOKIE['real_ipd'];
        return $realip;
    }
    /*添加*/
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr AS $ip) {
                $ip = trim($ip);

                if ($ip != 'unknown') {
                    $realip = $ip;

                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '0.0.0.0';
            }
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
    }

    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
    /*添加*/
    setcookie("real_ipd", $realip, time() + 36000, "/");  /*添加*/
    return $realip;
}
function GetIp(){
    $realip = '';
    $unknown = 'unknown';
    if (isset($_SERVER)){
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)){
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach($arr as $ip){
                $ip = trim($ip);
                if ($ip != 'unknown'){
                    $realip = $ip;
                    break;
                }
            }
        }else if(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)){
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        }else if(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)){
            $realip = $_SERVER['REMOTE_ADDR'];
        }else{
            $realip = $unknown;
        }
    }else{
        if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)){
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        }else if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)){
            $realip = getenv("HTTP_CLIENT_IP");
        }else if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)){
            $realip = getenv("REMOTE_ADDR");
        }else{
            $realip = $unknown;
        }
    }
    $realip = preg_match("/[\d\.]{7,15}/", $realip, $matches) ? $matches[0] : $unknown;
    return $realip;
}
function GetIpLookup($ip = ''){
    if(empty($ip)){
        $ip = real_ip();
    }
    $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
    if(empty($res)){ return false; }
    $jsonMatches = array();
    preg_match('#\{.+?\}#', $res, $jsonMatches);
    if(!isset($jsonMatches[0])){ return false; }
    $json = json_decode($jsonMatches[0], true);
    if(isset($json['ret']) && $json['ret'] == 1){
        $json['ip'] = $ip;
        unset($json['ret']);
    }else{
        return false;
    }
    return $json;
}
/**
 * 获取当前自身url
 */
function selfurl(){
    return strip_tags($_SERVER['REQUEST_URI']);
}

/**
 * Ajax方式返回数据到客户端
 * @param unknown_type $stats
 * @param unknown_type $info
 * @param unknown_type $url
 */
function ajax_return($status=true, $info='操作成功', $url=""){
    if ( $status ){
        $data['result'] = $info;
    }else{
        $data ['message'] = $info;
    }
    $data ['status'] = $status;
    if ( !empty( $url ) ){
        $data ['url'] = $url;
    }
    header('Content-Type:application/json; charset=utf-8');
    exit ( json_encode ( $data, JSON_UNESCAPED_UNICODE ) );
}

// 分析枚举类型配置值 格式 a:名称1,b:名称2
function parse_config_attr($string) {
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')){
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    }else{
        $value  =   $array;
    }
    return $value;
}

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}


//去除所有标准的HTML代码
function cutstr_html($string, $sublen){
    $string = strip_tags($string);
    $string = preg_replace ('/\n/is', '', $string);
    $string = preg_replace ('/ |　/is', '', $string);
    $string = preg_replace ('/&nbsp;/is', '', $string);
    preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $t_string);
    if(count($t_string[0]) - 0 > $sublen) $string = join('', array_slice($t_string[0], 0, $sublen))."…";
    else $string = join('', array_slice($t_string[0], 0, $sublen));
    return $string;
}
//输入过滤 同时去除连续空白字符可参考扩展库的remove_xss
function get_replace_input($str,$rptype=0){
    $str = stripslashes($str);
    $str = htmlspecialchars($str);
    $str = get_replace_nb($str);
    return addslashes($str);
}
//去除换行
function get_replace_nr($str){
    $str = str_replace(array("<nr/>","<rr/>"),array("\n","\r"),$str);
    return trim($str);
}
//去除连续空格
function get_replace_nb($str){
    $str = str_replace("&nbsp;",' ',$str);
    $str = str_replace(" ",' ',$str);
    $str = preg_replace("/[\r\n\t ]{1,}/",' ',$str);
    return trim($str);
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}
/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 */
function time_format($time = NULL,$format='Y-m-d H:i'){
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}


/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk='id', $pid = 'parent_id', $child = 'child', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array        返回排过序的列表数组
 */
function tree_to_list($tree, $child = 'child', $order='id', &$list = array()){
    if(is_array($tree)) {
        $refer = array();
        foreach ($tree as $key => $value) {
            $reffer = $value;
            if(isset($reffer[$child])){
                unset($reffer[$child]);
                tree_to_list($value[$child], $child, $order, $list);
            }
            $list[] = $reffer;
        }
        $list = list_sort_by($list, $order, $sortby='asc');
    }
    return $list;
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list,$field, $sortby='asc') {
    if(is_array($list)){
        $refer = $resultSet = array();
        foreach ($list as $i => $data)
            $refer[$i] = &$data[$field];
        switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc':// 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ( $refer as $key=> $val)
            $resultSet[] = &$list[$key];
        return $resultSet;
    }
    return false;
}

/**
 * 为了兼容php低版本的数组函数array_column,新增自定认函数
 * @param array $input 需要取出数组列的多维数组（或结果集）
 * @param unknown_type $columnKey 需要返回值的列，它可以是索引数组的列索引，或者是关联数组的列的键。 也可以是NULL
 * @param unknown_type $indexKey 作为返回数组的索引/键的列，它可以是该列的整数索引，或者字符串键值。
 */
function i_array_column($input, $columnKey, $indexKey=null){
    if (! function_exists ( 'array_column' )) {
        $columnKeyIsNumber = (is_numeric ( $columnKey )) ? true : false;
        $indexKeyIsNull = (is_null ( $indexKey )) ? true : false;
        $indexKeyIsNumber = (is_numeric ( $indexKey )) ? true : false;
        $result = array ();
        foreach ( ( array ) $input as $key => $row ) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice ( $row, $columnKey, 1 );
                $tmp = (is_array ( $tmp ) && ! empty ( $tmp )) ? current ( $tmp ) : null;
            } else {
                $tmp = isset ( $row [$columnKey] ) ? $row [$columnKey] : null;
            }
            if (! $indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice ( $row, $indexKey, 1 );
                    $key = (is_array ( $key ) && ! empty ( $key )) ? current ( $key ) : null;
                    $key = is_null ( $key ) ? 0 : $key;
                } else {
                    $key = isset ( $row [$indexKey] ) ? $row [$indexKey] : 0;
                }
            }
            $result [$key] = $tmp;
        }
        return $result;
    } else {
        return array_column ( $input, $columnKey, $indexKey );
    }
}


/**
 * 检测是否为必选字段
 * @param unknown_type $required
 * @param unknown_type $data
 * @return boolean
 */
function _required($required, $data){
    foreach($required as $field){
        if(!isset($data[$field])){
            return false;
        }
    }
    return true;
}

/**
 * 将数组中元素值转为下标
 * @param array $array 被转数组
 * @param string $field 数组元素的下标
 */
function changeArr($array, $field,$type='one'){
    $new_arr = array();
    foreach ( $array as $val ){
        if ( 'one' == $type ){
            $new_arr[$val[$field]] = $val;
        }else{
            $new_arr[$val[$field]][] = $val;
        }
    }
    return $new_arr;
}

/**
 * @author baiping 125618036@qq.com
 * @param str $url post传递的url地址
 * @param array $data 传递的数据
 * @return string
 */
function postCurl($url,$data){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);//把CRUL获取的内容赋值到变量,不直接输出的页面
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);//跟随$url重定向的页面
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    ob_start();
    curl_exec($ch);
    $result = ob_get_contents() ;
    ob_end_clean();
    return $result;
}

/**
 * get curl请求数据
 * @param unknown_type $url
 */
function getCurl($url){
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 1);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //不要http header 加快效率
    curl_setopt($curl, CURLOPT_HEADER, 0);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据
    return $data;
}

/**
 * 获取远程地址的图片并保存到本地
 * @param string $url 远程完整的图片地址
 * @param string $filename 要存储的文件名称
 * @return boolean
 */
function getimg($url = "", $filename = "") {
    if(is_dir(basename($filename))) {
        return "此目录不成写";
        Return false;
    }
    //去除URL连接上面可能的引号
    $url = preg_replace( '/(?:^[\'"]+|[\'"\/]+$)/', '', $url );
    $hander = curl_init();
    $fp = fopen($filename,'wb');
    curl_setopt($hander,CURLOPT_URL,$url);
    curl_setopt($hander,CURLOPT_FILE,$fp);
    curl_setopt($hander,CURLOPT_HEADER,0);
    curl_setopt($hander,CURLOPT_FOLLOWLOCATION,1);
    //curl_setopt($hander,CURLOPT_RETURNTRANSFER,false);//以数据流的方式返回数据,当为false是直接显示出来
    curl_setopt($hander,CURLOPT_TIMEOUT,60);
    curl_exec($hander);
    curl_close($hander);
    fclose($fp);
    Return true;
}
/**
 * 根据时间返回时间状态
 * @param unknown_type $mytime
 */
function getTimeFormat($mytime) {
    $dif = time() - intval($mytime);
    if($dif < 60){//一分钟之内
        return '刚刚';
    }elseif($dif < 3600 && $dif > 0) { //一小时以内
        return intval($dif / 60) . '分钟前';
    }elseif($dif < 3600*24 && $dif >= 0) { //一天以内
        return intval($dif / 3600) . '小时前';
    }elseif($dif < 3600*24*5 && $dif >= 0) { //五天以内
        return intval($dif / (3600*24)) . '天前';
    }else {
        return date('Y-m-d', $mytime);
    }
}

/**
 * 二维数组排序
 * @param unknown_type $arr
 * @param unknown_type $keys
 * @param unknown_type $type
 * @return multitype:unknown
 */
function array_sort($arr, $keys, $type='asc', $isg2u=0){
    $keysvalue = $new_array = array();
    foreach ($arr as $k=>$v){
        $keysvalue[$k] = $v[$keys];
    }
    if($type == 'asc'){
        asort($keysvalue);
    }else{
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k=>$v){
        $new_array[] = $arr[$k];//$new_array[$k] = $arr[$k];加$k下标就是原来的，不加下标就重新排了；
        if ( $isg2u == 1 ){
            g2u($v);
        }
    }
    return $new_array;
}

/**
 * 检测是否为空
 * @param 检测的字符串 $str
 * @return boolean
 */
function checkEmpty($str){
    if ( empty( $str ) || !isset( $str ) ){
        return false;
    }else{
        return true;
    }
}

/**
 *非空验证
 * @param	string	$value	需要验证的值
 * @param	string	$msg	验证失败的提示消息
 */
function notnull($value) {
    if(strlen(trim($value))==0) {
        return false;
    }else{
        return true;
    }
}

/**
 * 检测中文字符串是否达到系统要求的长度
 * @param 被检测的字符串 $str
 * @param 额定长度  $strlen
 */
function checkChineseLen($str, $strlen=2){
    if ( utf8_strlen($str) < $strlen ){
        return false;
    }else{
        return true;
    }
}
/**
 *数字格式验证
 * @param	string	$value	需要验证的值
 * @param	string	$msg	验证失败的提示消息
 */
function isnumber($value) {
    $rules='/^\d+$/';
    if(!preg_match($rules, $value)) {
        return false;
    }else{
        return true;
    }
}
/**
 * 货币格式验证
 * @param	string	$value	需要验证的值
 * @param	string	$msg	验证失败的提示消息
 */
function currency($value, $msg) {
    $rules='/^\d+(\.\d+)?$/';
    if(!preg_match($rules, $value)) {
        return false;
    }else{
        return true;
    }
}

/**
 * url地址验证验证
 * @param	string	$value	需要验证的值
 */
function check_url($url){
    if(!preg_match('/(http|https):\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',$url)){
        return false;
    }else{
        return true;
    }
}
/**
 * 将邮箱地址中间位置替成*
 * @param unknown_type $email
 * @return mixed
 */
function repalceMailHide($email){
    $emailadd = explode("@", $email);
    $email_len = strlen($emailadd[0]);
    $email_jiami = substr($emailadd[0], 1,($email_len-2));
    $email_mi = str_replace($email_jiami,"***",$email);
    return $email_mi;
}
/**
 * 将手机中间位置替换成*
 * @param unknown_type $mobilenum
 * @return mixed
 */
function replaceMobileHide($mobilenum){
    $mobile_num = preg_replace('#(\d{3})\d{5}(\d{3})#', '${1}*****${2}', $mobilenum);
    return$mobile_num;
}
/**
 * 验证用户名是否为英文、数字、汉字
 * @param unknown_type $member_name
 * @return number
 */
function check_user($member_name,$minLen=4, $maxLen=20, $charset='ALL'){
    if(empty($member_name))
        return false;
    switch($charset){
        case 'EN': $match = '/^[_\w\d]{'.$minLen.','.$maxLen.'}$/iu';
            break;
        case 'CN':$match = '/^[_\x{4e00}-\x{9fa5}\d]{'.$minLen.','.$maxLen.'}$/iu';
            break;
        default:$match = '/^[_\w\d\x{4e00}-\x{9fa5}]{'.$minLen.','.$maxLen.'}$/iu';
    }
    if(preg_match($match,$member_name) ){
        return true;
    }else{
        return false;
    }
}
/**
 * 验证密码
 * @param unknown_type $value
 * @param unknown_type $minLen
 * @param unknown_type $maxLen
 * @return boolean|number
 */
function checkPwd($value,$minLen=6,$maxLen=18){
    $match='/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{'.$minLen.','.$maxLen.'}$/';
    $v = trim($value);
    if(empty($v)){
        return false;
    }
    if(preg_match($match,$v)){
        return true;
    }else{
        return false;
    }
}
/**
 * 验证手机号
 */
function check_mobile($mobilephone){
    //手机号码的正则验证
    if(preg_match("/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$|170[059]{1}[0-9]{7}/",$mobilephone)){
        return true;
    }else{
        return false;
    }
}
/**
 * 验证QQ
 * @param unknown_type $qqstr
 */
function check_qq($qqstr){
    $qq_reg = '/^[1-9]{1}[0-9]{4,11}$/';
    if (preg_match($qq_reg, $qqstr)){
        return true;
    }else{
        return false;
    }
}
/**
 * 验证固定电话
 */
function check_tel($tel){
    $tel_pattern = '/^(0?(([1-9]\d)|([3-9]\d{2}))-?)?\d{7,8}$/';
    if (preg_match($tel_pattern, $tel)){
        return true;
    }else{
        return false;
    }
}
/**
 * 验证邮箱
 */
function check_email($email){
    $chars = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
    if (strpos($email, '@') !== false && strpos($email, '.') !== false){
        if (preg_match($chars, $email)){
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
}
/**
 * [unique_arr 将二维数组中的重复值去除]
 * @param  [type]  $array2D  [description]
 * @param  boolean $stkeep   [description]
 * @param  boolean $ndformat [description]
 * @return [type]            [description]
 */
function unique_arr($array2D,$stkeep=false,$ndformat=true){
    // 判断是否保留一级数组键 (一级数组键可以为非数字)
    if($stkeep) $stArr = array_keys($array2D);
    // 判断是否保留二级数组键 (所有二级数组键必须相同)
    if($ndformat) $ndArr = array_keys(end($array2D));
    //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
    foreach ($array2D as $v){
        $v = join(",",$v);
        $temp[] = $v;
    }
    //去掉重复的字符串,也就是重复的一维数组
    $temp = array_unique($temp);
    //再将拆开的数组重新组装
    foreach ($temp as $k => $v){
        if($stkeep) $k = $stArr[$k];
        if($ndformat)
        {
            $tempArr = explode(",",$v);
            foreach($tempArr as $ndkey => $ndval) $output[$k][$ndArr[$ndkey]] = $ndval;
        }
        else $output[$k] = explode(",",$v);
    }
    return $output;
}
/**
 * [rebuild_array 将二维数组转成一维数组]
 * @param  [type] $arr [description]
 * @return [type]      [description]
 */
function rebuild_array($arr){  //rebuild a array
    static $tmp=array();
    foreach($arr as $key=>$val){
        if(is_array($val)){
            rebuild_array($val);
        }else{
            $tmp[] = $val;
        }
    }
    return $tmp;
}

/**
 * 将一个二维数组中还包含的数组进行从简
 * @param unknown_type $arr
 */
function array2array($arr){
    if ( is_array( $arr ) ){
        $new_arr = array();
        foreach ( $arr as $key => $val ){
            if ( empty( $val ) ){
                unset( $arr[$key] );
            }
            if ( is_array( $val ) ){
                foreach ( $val as $key2 => $val2){
                    $new_arr[$key2] = $val2;
                }
            }
        }
        return $new_arr;
    }
    return false;
}
/**
 * 通过汉字生成字母前缀
 * @param unknown_type $s0
 * @return unknown|string|number
 */
function get_letter($s0){
    $firstchar_ord = ord(strtoupper($s0{0}));
    if (($firstchar_ord>=65 and $firstchar_ord<=91)or($firstchar_ord>=48 and $firstchar_ord<=57)) return $s0{0};
    $s = iconv("UTF-8","gb2312", $s0);
    $asc = ord($s{0})*256+ord($s{1})-65536;
    if($asc>=-20319 and $asc<=-20284)return "A";
    if($asc>=-20283 and $asc<=-19776)return "B";
    if($asc>=-19775 and $asc<=-19219)return "C";
    if($asc>=-19218 and $asc<=-18711)return "D";
    if($asc>=-18710 and $asc<=-18527)return "E";
    if($asc>=-18526 and $asc<=-18240)return "F";
    if($asc>=-18239 and $asc<=-17923)return "G";
    if($asc>=-17922 and $asc<=-17418)return "H";
    if($asc>=-17417 and $asc<=-16475)return "J";
    if($asc>=-16474 and $asc<=-16213)return "K";
    if($asc>=-16212 and $asc<=-15641)return "L";
    if($asc>=-15640 and $asc<=-15166)return "M";
    if($asc>=-15165 and $asc<=-14923)return "N";
    if($asc>=-14922 and $asc<=-14915)return "O";
    if($asc>=-14914 and $asc<=-14631)return "P";
    if($asc>=-14630 and $asc<=-14150)return "Q";
    if($asc>=-14149 and $asc<=-14091)return "R";
    if($asc>=-14090 and $asc<=-13319)return "S";
    if($asc>=-13318 and $asc<=-12839)return "T";
    if($asc>=-12838 and $asc<=-12557)return "W";
    if($asc>=-12556 and $asc<=-11848)return "X";
    if($asc>=-11847 and $asc<=-11056)return "Y";
    if($asc>=-11055 and $asc<=-10247)return "Z";
    return 0;
}

/**
 * 对象转为数组
 * @param unknown_type $array
 * @author Baip
 */
function object_array($array) {
    if(is_object($array)) {
        $array = (array)$array;
    }
    if(is_array($array)) {
        foreach($array as $key=>$value) {
            $array[$key] = object_array($value);
        }
    }
    return $array;
}
/**
 * 将数组转为对象
 * @param unknown_type $e
 */
function arrayToObject($e){
    if( gettype($e)!='array' ) return;
    foreach($e as $k=>$v){
        if( gettype($v)=='array' || getType($v)=='object' )
            $e[$k]=(object)arrayToObject($v);
    }
    return (object)$e;
}


/**
 * 替换中文日期中的年月日为-
 */
function str_replace_date($str_date){
    if ( !empty( $str_date ) ){
        $str_new_date = str_replace('年', '-', $str_date);
        $str_new_date = str_replace('月', '-', $str_new_date);
        $str_new_date = str_replace('日', '', $str_new_date);
        return $str_new_date;
    }
}

// 不区分大小写的in_array实现
function in_array_case($value,$array){
    return in_array(strtolower($value),array_map('strtolower',$array));
}

/**
 * 按不同类型创建随机值
 * @param	string	type of random string.  basic, alpha, alnum, numeric, nozero, unique, md5, encrypt and sha1
 * @param	int	number of characters
 * @return	string
 */
if ( ! function_exists('random_string')){
    function random_string($type = 'alnum', $len = 6){
        switch ($type){
            case 'basic':
                return mt_rand();
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
                switch ($type)
                {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'sha1':
                return sha1(uniqid(mt_rand(), TRUE));
        }
    }
}
/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 单位 秒
 * @return string
 */
function gamepoint_encrypt($data, $key = '', $expire = 0) {
    $key  = md5(empty($key) ? \think\Config::get('data_auth_key') : $key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    $str = sprintf('%010d', $expire ? $expire + time():0);

    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
    }
    return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param  string $key  加密密钥
 * @return string
 */
function gamepoint_decrypt($data, $key = ''){
    $key    = md5(empty($key) ? \think\Config::get('data_auth_key') : $key);
    $data   = str_replace(array('-','_'),array('+','/'),$data);
    $mod4   = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    $data   = base64_decode($data);
    $expire = substr($data,0,10);
    $data   = substr($data,10);

    if($expire > 0 && $expire < time()) {
        return '';
    }
    $x      = 0;
    $len    = strlen($data);
    $l      = strlen($key);
    $char   = $str = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }else{
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

function checkMethod($type="post",$msg = "请求类型非法"){
    $request = \think\Request::instance();
    switch ($type) {
        case 'post':
            if (!$request->isPost()) {
                ajax_return(false, $msg);
            }
            break;
        case 'get':
            if (!$request->isGet()) {
                ajax_return(false, $msg);
            }
            break;
    }
    return $request;
}

// 转换成JS
function t2js($l1, $l2=1){
    $I1 = str_replace(array("\r", "\n"), array('', '\n'), addslashes($l1));
    return $l2 ? "document.write(\"$I1\");" : $I1;
}
//utf8转gbk
function u2g($str){
    return iconv("UTF-8","GBK",$str);
}
//gbk转utf8
function g2u($str){
    return iconv("GBK","UTF-8//ignore",$str);
}
function gbk2utf($str){
    return iconv("gbk", "utf-8", $str);
}
function utf2gbk($str){
    return iconv("utf-8", "gbk", $str);
}

/**
 * 对二维数组中指定的下标修改值
 * @param $array
 * @param $key
 * @param $replace_val
 * @author Baip 125618036@qq.com
 */
function my_array_replace($array, $key, $replace_val){
    if ( !is_array($array) ){
        return false;
    }
    foreach ( $array as $k => $value){
        $array[$k][$key] = $replace_val;
    }
    return $array;
}

/**
 * 发送邮件
 *  @$address发送邮件地址
 *  @$title发送邮件标题
 *  @message发送邮件内容
 ***/
function SendMail($address,$title,$message){
    \think\Loader::import('PHPMailer.phpmailer',EXTEND_PATH);
    $mail= new PHPMailer();
    // 设置PHPMailer使用SMTP服务器发送Email
    $mail->IsSMTP();
    // 设置邮件的字符编码，若不指定，则为'UTF-8'
    $mail->CharSet='UTF-8';
    //设置输出内容
    $mail->ContentType="text/html";
    // 添加收件人地址，可以多次使用来添加多个收件人
    $mail->AddAddress($address);
    // 设置邮件正文
    $mail->Body=$message;
    // 设置邮件头的From字段。
    $mail->From= \think\Config::get('mail_address');
    // 设置发件人名字
    $mail->FromName=\think\Config::get('fromname');
    // 设置邮件标题
    $mail->Subject=$title;
    // 设置SMTP服务器。
    $mail->Host=\think\Config::get('mail_smtp');
    // 设置为"需要验证"
    $mail->SMTPAuth=true;
    // 设置用户名和密码。
    $mail->Username=\think\Config::get('mail_loginname');
    $mail->Password=\think\Config::get('mail_password');
    // 发送邮件。
    return($mail->Send());
}

/**
 * 复制目录
 * @param $src
 * @param $dst
 */
function copy_dir($src,$dst){
    think\Config::load(APP_PATH .'project.php');
    $path = think\Config::get('upload_config.rootPath');
    $realsrc = $path.$src;
    $realdst = $path.$dst;
    $dir = opendir($realsrc);
    if(!file_exists($realdst)){
        mkdir($realdst);
    }
    while(false !== ($file = readdir($dir))){
        if(($file != '.')&& ($file !='..')){
            if(is_dir($realsrc.'/'.$file)){
                CopyDir($src.'/'.$file,$dst.'/'.$file);
            }else{
                copy($realsrc.'/'.$file,$realdst.'/'.$file);
            }
        }
    }
    closedir($dir);
}

/**
 * 获取数组中指定下标的最大值
 * @param $array
 * @param string $field
 * @return array|bool|string
 * @author Baip 125618036@qq.com
 */
function get_array_max($array, $field="ab_num"){
    if(empty($array)) {
        return false;
    }
    $disArr = array();
    foreach($array as $value) {
        $disArr[] =$value[$field];
    }
    sort($disArr);
    $resArr = !empty($disArr) ? array($disArr[0]) : '';
    unset($disArr);
    return $resArr;
}