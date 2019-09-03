<?php
include_once "_LIB/lib_DB.php";
include_once "_LIB/lib_Key.php";
include_once "_Lib/lib_ErrorHandler.php";
include_once "_LIB/lib_Log.php";
$g_AccountNo = 0;
// 게임 로그 & 프로파일러 
$GameLog = GAMELog::getInstance($cnf_GAME_LOG_URL);
$PF = Profiling::getInstance($cnf_PROFILING_LOG_URL, $_SERVER['PHP_SELF']);

// * file_get_contents("php://input");
// POST 방식으로 보낸 http 패킷의 body에 접근할 수 있다. 일반적으로 PHP에서는 form방식 전송을 이용하는 경우가 대부분이지만 body에 JSON으로 만든 raw data를 넣어서 보내는 경우에 서버단에서 접근하려면 위와 같은 명령어를 이용해야 한다.
$_RequestData = file_get_contents("php://input");

// php 7.2버전부터 한글 데이터를 받으면 JSON_ERROR_UTF8 발생
if(function_exists('mb_detect_encoding'))
{
	if(mb_detect_encoding($_RequestData, "EUC-KR, UTF-8, ASCII") == "EUC-KR")
	{
		 $_RequestData = iconv("EUC-KR", "UTF-8//TRANSLIT", $_RequestData);
		 //$_RequestData = utf8_encode($_RequestData);
	}
}


$_JSONData = json_decode($_RequestData, true);
if(!is_array($_JSONData))	
{
	echo "JSON Data Error : ".json_last_error()."\n";
	exit;
}

$PF->startCheck(PF_PAGE);
$PF->startCheck(PF_MYSQL_CONN);
DB_Connect();

$PF->stopCheck(PF_MYSQL_CONN);

?>