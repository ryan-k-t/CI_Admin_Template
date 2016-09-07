<?
header("content-type: application/octet-stream");
if(!isset($filename)){
	$filename = "export.csv";
}
if(!defined("nl")) define("nl","
");
header("Content-Disposition:attachment;filename=$filename");
if(!is_array($data)){
	$data = array();
}
foreach($data as $entry){
	
	foreach($entry as $key=>$value){
		if(strpos($value, '"') !== false)
		{
			$entry[$key] = str_replace('"', '""', $value);
		}
		//$entry[$key] = addslashes($value);
	}
	
	echo "\"".join("\",\"",$entry)."\"".nl;
}
?>