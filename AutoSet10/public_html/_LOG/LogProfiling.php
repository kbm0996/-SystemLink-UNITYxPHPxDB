<?php
include_once "_Config_LOG.php";

/*------------------------------------------------------------------------
  프로파일링 로그 남기는 함수
 POST 방식으로 프로파일링 로그 저장 (테이블 정보는 _Config_LOG.php 참고)
------------------------------------------------------------------------*/
if(!isset($_POST['IP']))			$_POST['IP'] = "None";			
if(!isset($_POST['AccountNo']))		$_POST['AccountNo'] = "None";	
if(!isset($_POST['Action']))		$_POST['Action'] = "None";		
if(!isset($_POST['T_Page']))		$_POST['T_Page'] = "None";		// Time Page
if(!isset($_POST['T_Mysql_Conn']))	$_POST['T_Mysql_Conn'] = "None";// Time Mysql Connect
if(!isset($_POST['T_Mysql']))		$_POST['T_Mysql'] = "None";		// Time Mysql
if(!isset($_POST['T_ExtAPI']))		$_POST['T_ExtAPI'] = "None";	// Time Extension API
if(!isset($_POST['T_Log']))			$_POST['T_Log'] = "None";		// Time Logging
if(!isset($_POST['T_ru_u']))		$_POST['T_ru_u'] = "None";		// Time User Used
if(!isset($_POST['T_ru_s']))		$_POST['T_ru_s'] = "None";		// Time System Used
if(!isset($_POST['Query']))			$_POST['Query'] = "None";		// Query
if(!isset($_POST['Comment']))		$_POST['Comment'] = "None";		// ETC

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
$IP				= mysqli_real_escape_string($g_LOGDB, $_POST['IP']);
$AccountNo		= mysqli_real_escape_string($g_LOGDB, $_POST['AccountNo']);
$Action			= mysqli_real_escape_string($g_LOGDB, $_POST['Action']);
$T_Page			= mysqli_real_escape_string($g_LOGDB, $_POST['T_Page']);
$T_Mysql_Conn   = mysqli_real_escape_string($g_LOGDB, $_POST['T_Mysql_Conn']);
$T_Mysql		= mysqli_real_escape_string($g_LOGDB, $_POST['T_Mysql']);
$T_ExtAPI		= mysqli_real_escape_string($g_LOGDB, $_POST['T_ExtAPI']);
$T_Log			= mysqli_real_escape_string($g_LOGDB, $_POST['T_Log']);
$T_ru_u			= mysqli_real_escape_string($g_LOGDB, $_POST['T_ru_u']);
$T_ru_s			= mysqli_real_escape_string($g_LOGDB, $_POST['T_ru_s']);
$Query			= mysqli_real_escape_string($g_LOGDB, $_POST['Query']);
$Comment		= mysqli_real_escape_string($g_LOGDB, $_POST['Comment']);

$TableName = "ProfilingLog_".@date("Ym");
$Query = "INSERT INTO {$TableName} (date, ip, accountno, action, t_page, t_mysql_conn, t_mysql, t_extapi, t_log, t_ru_u, t_ru_s, query, comment) VALUES (NOW(), '{$IP}', '{$AccountNo}', '{$Action}', {$T_Page}, {$T_Mysql_Conn}, {$T_Mysql}, {$T_ExtAPI}, {$T_Log}, {$T_ru_u}, {$T_ru_s}, '{$Query}', '{$Comment}')";
$Result = mysqli_query($g_LOGDB, $Query);
// INSERT DELAYED INTO : MyISAM일 경우 적용, InnoDB일 경우 기본 옵션. 데이터를 추가할 경우에 해당되며, 버퍼에 저장해 놓고 처리함. (클라이언트는 SELECT 가 끝날때까지 기다리지 않음) 항상 '1 rows affected' 의 결과를 냄

if ( !$Result && mysqli_errno($g_LOGDB) == 1146 ) 
{
	mysqli_query($g_LOGDB, "CREATE TABLE {$TableName} LIKE profilinglog_template");
	mysqli_query($g_LOGDB, $Query);
}

if(isset($g_LOGDB))
	mysqli_close($g_LOGDB);

?>