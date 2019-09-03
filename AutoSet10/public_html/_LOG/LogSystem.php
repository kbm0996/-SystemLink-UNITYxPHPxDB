<?php
include_once "_Config_LOG.php";
/*------------------------------------------------------------------------
  시스템 로그 남기는 함수
 POST 방식으로 시스템 로그 저장 (테이블 정보는 _Config_LOG.php 참고)
------------------------------------------------------------------------*/

if(!isset($_POST['accountno']))	$_POST['accountno'] = "None";	// 유저
if(!isset($_POST['action']))	$_POST['action'] = "None";		// 액션
if(!isset($_POST['message']))	$_POST['message'] = "None";		// LogString

$g_LOGDB = mysqli_connect($g_LOGDB_IP, $g_LOGDB_ID, $g_LOGDB_PASS, $g_LOGDB_NAME, $g_LOGDB_PORT);
if(!$g_LOGDB)
{
	file_put_contents('php://stderr', "Log DB ERROR # mysqli_connect() : " . mysqli_connect_error());
	exit;
}

mysqli_set_charset($g_LOGDB, "utf8");

//--------------------------------------------------------------------------
// 문자열 인자의 공격 검사는 하지 않음
// 내부 서버 IP 외에는 본 파일을 호출하지 못하도록 방화벽에서 차단
//--------------------------------------------------------------------------
$AccountNo  = mysqli_real_escape_string($g_LOGDB, $_POST['accountno']);
$Action     = mysqli_real_escape_string($g_LOGDB, $_POST['action']);
$Message    = mysqli_real_escape_string($g_LOGDB, $_POST['message']);

$TableName = "SystemLog_".@date("Ym");
$Query = "INSERT INTO {$TableName} (date, accountno, action, message) VALUES (NOW(), '{$AccountNo}', '{$Action}', '{$Message}')";
$Result = mysqli_query($g_LOGDB, $Query);
// INSERT DELAYED INTO : MyISAM일 경우 적용, InnoDB일 경우 기본 옵션. 데이터를 추가할 경우에 해당되며, 버퍼에 저장해 놓고 처리함. (클라이언트는 SELECT 가 끝날때까지 기다리지 않음) 항상 '1 rows affected' 의 결과를 냄

// * 테이블 없을시 (errno = 1146) 테이블 생성 후 재입력
if ( !$Result && mysqli_errno($g_LOGDB) == 1146 ) 
{
	mysqli_query($g_LOGDB, "CREATE TABLE {$TableName} LIKE systemlog_template");
	mysqli_query($g_LOGDB, $Query);
}

if(isset($g_LOGDB))
	mysqli_close($g_LOGDB);
?>