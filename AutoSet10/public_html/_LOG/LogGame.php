<?php
include_once "_Config_LOG.php";

/*------------------------------------------------------------------------
 게임 로그 남기는 함수 / DB, 테이블 정보는 _Config.php 참고

대게 DB에 변화가 있을때 로그를 남긴다. (오류로 인한 컨텐츠 실행 실패는 DB에 변화가 없으므로 게임로그를 남기지 않는다)

 로그만 가고 적용이 안된다던가 문제가 발생할 여지가 있으니 Transaction처리해서 한꺼번에 넘겨야한다. 편의상 그때그때 로그 배열에 넣고 적용시킬때 한꺼번에 DB에 저장한다.
 많은 로그를 묶어서 DB에 저장할 방법이 PHP나 html 구조상 딱히 없음. 때문에 JSON 사용
 
 JSON 디코드, 배열로 여러번 보내기
ㅇ JSON →
array(array(param1, param2, param3), array(param1, param2, param3));

ㅇ PHP →
array(
		array(param1, param2, param3), 
		array(param1, param2, param3)
	)

ㅇ DB →
LogChunk = {
		{"param1":value, "param2":value, "param3":value}
		{"param1":value, "param2":value, "param3":value}
	}

 2차원 배열로 한 번에 여러개의 로그가 몰아서 들어옴.
 POST 방식으로 프로파일링 로그 저장
 $_POST['AccountNo']		
 $_POST['LogType']		
 $_POST['LogCode']		
 $_POST['Param1']			
 $_POST['Param2']			
 $_POST['Param3']			
 $_POST['Param4']			
 $_POST['ParamString']	
------------------------------------------------------------------------*/
if(!isset($_POST['LogChunk']))
	exit;

$LogChunk = json_decode($_POST['LogChunk'], true);
if(!is_array($LogChunk))
	exit;

$g_LOGDB = mysqli_connect($g_LOGDB_IP, $g_LOGDB_ID, $g_LOGDB_PASS, $g_LOGDB_NAME, $g_LOGDB_PORT);
if(!$g_LOGDB)
{
	file_put_contents('php://stderr', "Log DB ERROR # mysqli_connect() : " . mysqli_connect_error());
	exit;
}

mysqli_set_charset($g_LOGDB, "utf8");

foreach($LogChunk as $cnt)
{
	//----------------------------------------------------------------------------
	//  문자열 인자의 공격 검사는 하지 않음
	// 내부 서버 IP 외에는 본 파일을 호출하지 못하도록 방화벽에서 차단
	//----------------------------------------------------------------------------
	$AccountNo		= mysqli_real_escape_string($g_LOGDB, $cnt['AccountNo']);
	$LogType		= mysqli_real_escape_string($g_LOGDB, $cnt['LogType']);
	$LogCode		= mysqli_real_escape_string($g_LOGDB, $cnt['LogCode']);
	$Param1			= mysqli_real_escape_string($g_LOGDB, $cnt['Param1']);
	$Param2			= mysqli_real_escape_string($g_LOGDB, $cnt['Param2']);
	$Param3			= mysqli_real_escape_string($g_LOGDB, $cnt['Param3']);
	$Param4			= mysqli_real_escape_string($g_LOGDB, $cnt['Param4']);
	$ParamString	= mysqli_real_escape_string($g_LOGDB, $cnt['ParamString']);

	$TableName = "GameLog_".@date("Ym");
	$Query = "INSERT INTO {$TableName} (date, accountno, logtype, logcode, param1, param2, param3, param4, paramstring) VALUES (NOW(), '{$AccountNo}', {$LogType}, {$LogCode}, {$Param1}, {$Param2}, {$Param3}, {$Param4}, '{$ParamString}')";
	$Result = mysqli_query($g_LOGDB, $Query);
	// INSERT DELAYED INTO : MyISAM일 경우 적용, InnoDB일 경우 기본 옵션. 데이터를 추가할 경우에 해당되며, 버퍼에 저장해 놓고 처리함. (클라이언트는 SELECT 가 끝날때까지 기다리지 않음) 항상 '1 rows affected' 의 결과를 냄

	// * 테이블 없을시 (errno = 1146) 테이블 생성 후 재입력
	if ( !$Result && mysqli_errno($g_LOGDB) == 1146 ) 
	{
		mysqli_query($g_LOGDB, "CREATE TABLE {$TableName} LIKE gamelog_template");
		mysqli_query($g_LOGDB, $Query);
	}
}

if(isset($g_LOGDB))
	mysqli_close($g_LOGDB);
?>