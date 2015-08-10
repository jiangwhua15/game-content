<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/6
 * Time: 10:00
 */


$Dir = __DIR__;

$Filename = 'wp-autopost-function.php';

$path = $Dir.'/'.$Filename;
$filesize = filesize($path);
$handle = fopen($path,'r');

$info = fread($handle,$filesize);

fclose($handle);

/**
 * 把文件当中的16进制转换成字符串
 */

$info = preg_replace_callback('/\\\x[a-z|A-Z|0-9]{2}/',function($matches){

  return chr(hexdec($matches[0]));
},$info);


/**
 * 添加换行字符
 */
$info = preg_replace('/\$\{"(GLOBALS)"\}/','\$\\1',$info);
$info = str_replace(';$',";\n$",$info);





$newfile = $Dir.'/'.'we-autopost-function-po.php';

if(!file_exists($newfile)){
    touch($newfile);
    chmod($newfile,0777);
}
$handle = fopen($newfile,'w');
fwrite($handle,$info);
fclose($handle);








