<?php
include_once "_Config_DB.php";
include_once "lib_Profiling.php";
include_once "lib_Call.php";
/*--------------------------------------------
  로그 함수 및 클래스
 외부 URL을 호출하여 로그를 남기도록 한다.

 * 필수사항
  _Config_Log.php가 include 돼있어야하며, 다음 변수가 전역으로 세팅돼있어야 함
  $cnf_SYSTEM_LOG_URL
  $cnf_GAME_LOG_URL

 * 로그 부분에도 프로파일링이 들어가므로
  본 파일을 사용하는 소스에서 lib_Profiling.php가 include돼야하고
  $PF = Profiling::getInstance("http://xx.xx.xx.xx/Log/LogProfiling.php", "test.php");가 전역으로 존재해야만 한다.

 * 실 사용 함수 - 시스템 로그
  LOG_System($AccountNo, $Action, "에러 메시지");

 * 실 사용 함수 - 게임 로그
  $GameLog = GAMELog::getInstance($cnf_GAME_LOG_URL); // 싱글턴 인스턴스 생성
  $GameLog->AddLog($MemberNo, Type, Code, P1, P2, P3, P4, 'PS'); // 로그 추가
  $GameLog->SaveLog(); // 로그 전송
--------------------------------------------*/


//----------------------------------------------------
// SystemLog
//----------------------------------------------------
//Log_System(0, '테스트', '테스트');
function LOG_System($AccountNo, $Action, $Message, $LogLevel = 1)
{
	global $cnf_SYSTEM_LOG_URL;
	global $cnf_LOG_LEVEL;

	// 프로파일링
	global $PF;

	if($cnf_LOG_LEVEL < $LogLevel)
		return;

	// 프로파일링
	if($PF)
	{
		$PF->startCheck(PF_LOG);
	}
	if($AccountNo <= 0|| !isset($AccountNo))
	{
		// 실제 클라이언트 IP 얻기
		// 프록시(캐시)서버가 있을 경우, 클라IP가 아니라 프록시 서버IP가 들어가므로 이를 감지하는 코드가 필요함
		if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
			$AccountNo = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(array_key_exists('REMOTE_ADDR', $_SERVER))
			$AccountNo = $_SERVER['REMOTE_ADDR'];
		else
			$AccountNo = 'local';
	}

	$postField = array("accountno"=>$AccountNo, "action"=>$Action, "message"=>$Message);

	$Response = Call_Curl($cnf_SYSTEM_LOG_URL, $postField, "POST");
	//echo $Response['body'];

	// 프로파일링
	if($PF)
		$PF->stopCheck(PF_LOG);
}

//----------------------------------------------------
// GameLog
// 싱글톤으로 전역 생성되며, AddLog() 를 호출하여 로그를 추가 한 뒤에
// 마지막에 (DB적용 후) SaveLog()를 호출하여 실제로 저장
//----------------------------------------------------
class GAMELog
{
	private $LOG_URL = '';
	private $LogArray = array();

	//-----------------------------------------------
	// 싱글톤 객체 얻기
	// $SaveURL - 게임로그 저장 호출 서버 URL /  LogGame.php의 위치
	//-----------------------------------------------
	static function getInstance($GameLogURL)
	{
		static $instance;
		if(!isset($instance))
			$instance = new GAMELog();

		$instance->LOG_URL = $GameLogURL;

		return $instance;
	}
	//-----------------------------------------------
	// 로그 누적
	// 멤버변수 $LogArray에 누적 저장
	// $AccountNo, $Type, $Code, $Param1, $Param2, $Param3, $Param4, $ParamString
	//-----------------------------------------------
	function AddLog($AccountNo, $Type, $Code, $Param1 = 0, $Param2 = 0, $Param3 = 0, $Param4 = 0, $ParamString = '')
	{
		array_push(
			$this->LogArray, 
			array(
				"AccountNo"		=> $AccountNo,
				"LogType"		=> $Type,
				"LogCode"		=> $Code,
				"Param1"		=> $Param1,
				"Param2"		=> $Param2,
				"Param3"		=> $Param3,
				"Param4"		=> $Param4,
				"ParamString"	=> $ParamString
			)
		);
	}

	//-----------------------------------------------
	// 로그 저장
	// 멤버변수 $LogArray에 쌓인 로그를 한꺼번에 저장
	//-----------------------------------------------
	/*
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
	*/
	function SaveLog()
	{
		//echo "DEBUG : ".count($this->LogArray)."\n";
		if(count($this->LogArray) > 0)
		{
			Call_Socket($this->LOG_URL, array("LogChunk" => json_encode($this->LogArray)), "POST");	
		}
	}
}
?>