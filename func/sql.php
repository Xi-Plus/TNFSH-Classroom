<?php
require_once("consolelog.php");
class query
{
	//ALL
	public $dbname = null;
	public $table = null;
	public $blindlist = array();
	// SELECT DELETE UPDATE
	public $where = null;
	public $limit = null;
	// SELECT
	public $column = "*";
	public $order = null;
	public $group = null;
	// INSERT UPDATE
	public $value = null;
}
function dsn($host,$dbname,$type="mysql"){
	return $type.":dbname=".$dbname.";host=".$host.";charset=utf8";
}
function connect($dbname){
	require("../config/db.php");
	try {
		if(isset($dbname))$link=new PDO(dsn($db->host,$dbname),$db->username,$db->password);
		else $link=new PDO(dsn($db->host,$db->dbname),$db->username,$db->password);
	}catch (PDOException $e){
		$errormessage="DatabaseError: ".iconv("BIG5", "UTF-8", $e->getMessage());
		echo $errormessage;
	}
	return $link;
}
function randomkey($length){
	$pattern="abcdefghijklmnopqrstuvwxyz";
	$key="";
	for($i=0;$i<$length;$i++){
		$key.=$pattern{rand(0,25)};
	}
	return $key;
}
function createbind($text,$value){
	$bindvalue=randomkey(8);
	$text->blindlist[$bindvalue]=$value;
	return ":".$bindvalue;
}
function bind($text,$sth){
	foreach($text->blindlist as $index => $value){
		$sth->bindValue($index,$value);
	}
	return $sth;
}
function fetch($link,$query,$text){
	$sth=$link->prepare($query);
	$sth=bind($text,$sth);
	$sth->execute();
	$sth->setFetchMode(PDO::FETCH_ASSOC);
	$result=$sth->fetchAll();
	return $result;
}
function fetchone($result){
	foreach($result as $temp){
		return $temp;
	}
	return null;
}
function WHERE($text){
	$where=$text->where;
	if($where==null)return "";
	if(!is_array($where)){
		die("WHERE isn't a array");
	}else if(!is_array($where[0])){
		$where=array($where);
	}
	$query="WHERE ";
	foreach($where as $index => $value){
		if($index!=0)$query.="AND ";
		if(isset($value[2])){
			if($value[2]==="REGEXP"){
				$query.="`".$value[0]."` REGEXP ".str_replace("+","[+]",createbind($text,$value[1]))." ";
			}else {
				$query.="`".$value[0]."`".$value[2].createbind($text,$value[1])." ";
			}
		}else {
			$query.="`".$value[0]."`=".createbind($text,$value[1])." ";
		}
	}
	return $query;
}
function LIMIT($limit){
	if($limit==null||$limit=="all"){
		return "";
	}else if(is_array($limit)){
		if(!isset($limit[1])){
			die("LIMIT offset 2 undefined");
		}else if(!is_numeric($limit[0])||!is_numeric($limit[1])){
			die("LIMIT wrong type");
		}else {
			return "LIMIT ".$limit[0].",".$limit[1]." ";
		}
	}else if(is_numeric($limit)){
		return "LIMIT ".$limit." ";
	}else {
		die("LIMIT wrong type");
	}
}
function SELECT($text){
	$link=connect($text->dbname);
	$query="SELECT ";
	if(is_string($text->column)){
		$query.=$text->column." ";
	}else if(is_array($text->column)){
		foreach($text->column as $index => $value){
			if($index!=0)$query.=",";
			$query.=$value;
		}
		$query.=" ";
	}
	$query.="FROM `".$text->table."` ";
	$query.=WHERE($text);
	if($text->group!==null){
		if(!is_array($text->group)){
			$text->group=array($text->group);
		}
		$query.="GROUP BY ";
		foreach($text->group as $index => $value){
			if($index!=0)$query.=",";
			$query.="`".$value."`";
		}
		$query.=" ";
	}
	if($text->order!==null){
		if(!is_array($text->order)){
			die("ORDER isn't a array");
		}else if(!is_array($text->order[0])){
			$text->order=array($text->order);
		}
		$query.="ORDER BY ";
		foreach($text->order as $index => $value){
			if($index!=0)$query.=",";
			if(isset($value[1]))$query.="`".$value[0]."` ".$value[1];
			else $query.="`".$value[0]."` ASC";
		}
		$query.=" ";
	}
	if($text->limit!==null)$query.=LIMIT($text->limit);
	$result=fetch($link,$query,$text);
	return $result;
}
function INSERT($text){
	$link=connect($text->dbname);
	$query="INSERT INTO `".$text->table."` (";
	if(!is_array($text->value[0])){
		$text->value=array($text->value);
	}
	foreach($text->value as $index => $temp){
		if($index!=0)$query.=",";
		$query.="`".$temp[0]."`";
	}
	$query.=")VALUES(";
	foreach($text->value as $index => $temp){
		if($index!=0)$query.=",";
		$query.=createbind($text,$temp[1]);
	}
	$query.=")";
	$result=fetch($link,$query,$text);
	return $result;
}
function UPDATE($text){
	$link=connect($text->dbname);
	$query="UPDATE `".$text->table."` SET ";
	if(!is_array($text->value[0])){
		$text->value=array($text->value);
	}
	foreach($text->value as $index => $temp){
		if($index!=0)$query.=",";
		$query.="`".$temp[0]."`=".createbind($text,$temp[1]);
	}
	$query.=" ".WHERE($text).LIMIT($text->limit);
	$result=fetch($link,$query,$text);
	return $result;
}
function DELETE($text){
	$link=connect($text->dbname);
	$query="DELETE FROM `".$text->table."` ".WHERE($text).LIMIT($text->limit);
	$result=fetch($link,$query,$text);
	return $result;
}
function SQL($query){
	$link=connect($text->dbname);
	$result=$link->query($query);
	return $result;
}
?>