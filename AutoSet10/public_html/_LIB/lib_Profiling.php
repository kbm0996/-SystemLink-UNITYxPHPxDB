<?php
//-----------------------------
// M2 프로파일링 클래스
//
// PF_PAGE			- 전체 페이지 처리 시간
// PF_LOG			- 로그 처리 시간
// PF_MYSQL_CONN 	- MySQL 연결 처리 시간
// PF_EXTAPI		- 외부 API 처리 시간
//
// 4가지 분류 프로파일링
//
// 로그 내용은 로그 웹 서버로 URL 전송 / 로그 웹 서버의 LogProfiling.php와 연동
//
/* 사용 예
$PF = Profiling::getInstance("http://xx.xx.xx.xx/Log/LogProfiling.php", "test.php");
$PF->startCheck(PF_PAGE);

$PF->startCheck(PF_MYSQL_CONN);
$PF->stopCheck(PF_MYSQL_CONN);

$PF_startCheck(PF_MYSQL);
$PF->stopCheck(PF_MYSQL, "Sleep2");

$PF_startCheck(PF_MYSQL);
$PF->stopCheck(PF_MYSQL, "Sleep3");

$PF_startCheck(PF_LOG);
$PF->stopCheck(PF_LOG);

$PF_startCheck(PF_EXTAPI);
$PF->stopCheck(PF_EXTAPI, "dfdfd");

$PF->stopCheck(PF_PAGE);

$PF->LOG_Save($AccountNo);
*/
//-----------------------------
// 인터프리터 언어인 php의 특징상 php의 define은 검색해서 찾는 수준이므로 c의 매크로와 달리 성능상의 이점은 없다
define("PF_TYPE_START", 1); // Type Range Start
define("PF_PAGE",		1);
define("PF_STARTUP",	2);
define("PF_MYSQL_CONN",	3);
define("PF_MYSQL",		4);
define("PF_EXTAPI",		5);
define("PF_LOG",		6);
define("PF_TYPE_END",	6); // Type Range End

class Profiling
{
	private $LOG_FLAG = FALSE;
	private $LOG_URL = '';
	
	private $_PROFILING = array();
	private $_ACTION = '';

	private $QUERY = '';
	private $COMMENT = '';

	private $_ru_utime_start = 0;
	private $_ru_stime_start = 0;

	function __construct()
	{
		if(function_exists("getrusage")) // 함수의 유무
		{
			// 시스템의 기본 사용 기간을 얻어내는 함수 
			// getrusage 함수는 windows에서 지원 X
			$dat = getrusage(); // 시스템 사용 시간 체크를 위한 값

			$this->_ru_utime_start = $dat['ru_utime.tv_sec'] * 1e6 + $dat['ru_utime.tv_usec']; // System - 사용된 유저 시간
			$this->_ru_stime_start = $dat['ru_stime.tv_sec'] * 1e6 + $dat['ru_stime.tv_usec']; // System - 사용된 시스템 시간
		}
		else
		{
			$this->_ru_utime_start = 0;
			$this->_ru_stime_start = 0;
		}
		$this->_PROFILING[PF_PAGE]['start'] = 0;
		$this->_PROFILING[PF_PAGE]['sum'] = 0;

		$this->_PROFILING[PF_MYSQL_CONN]['start'] = 0;
		$this->_PROFILING[PF_MYSQL_CONN]['sum'] = 0;

		$this->_PROFILING[PF_MYSQL]['start'] = 0;
		$this->_PROFILING[PF_MYSQL]['sum'] = 0;

		$this->_PROFILING[PF_EXTAPI]['start'] = 0;
		$this->_PROFILING[PF_EXTAPI]['sum'] = 0;

		$this->_PROFILING[PF_LOG]['start'] = 0;
		$this->_PROFILING[PF_LOG]['sum'] = 0;
	}

	//---------------------------------------
	// 싱글톤 객체 얻기
	// $SaveURL - 프로파일링 로그 저장 호출할 서버 URL / LogProfiling.php 위치
	// $ActionPath - 액션 URL 경로
	//---------------------------------------
	static function getInstance($SaveURL, $ActionPath)
	{
		global $cnf_PROFILING_LOG_RATE;

		static $instance;
		if(!isset($instance))
		{
			$instance = new Profiling();
		}

		//---------------------------------------
		// PATH 인자가 들어오면 멤버 변수에 입력
		//---------------------------------------
		if($ActionPath != '')
			$instance->_ACTION = $ActionPath;

		//---------------------------------------
		// 로그 저장 URL 세팅
		//---------------------------------------
		$instance->LOG_URL = $SaveURL;
		

		//---------------------------------------
		// 로그 저장할지 말지 플래그,
		// 매번 저장하면 성능에 영향이 있으므로 $cnf_PROFILING_LOG_RATE 확률에 따라서 저장 여부 설정
		//---------------------------------------
		if(rand() % 100 < $cnf_PROFILING_LOG_RATE)
		{
			$instance->LOG_FLAG = TRUE;
		}

		return $instance;
	}

	//---------------------------------------
	// 프로파일링 시작 - 특정 Type에 대해 시작 / 같은 Type을 여러번 시작, 중지하여 누적시킬 수 있음
	//---------------------------------------
	function startCheck($Type)
	{
		if($this->LOG_FLAG == FALSE)
			return;

		if($Type < PF_TYPE_START || $Type > PF_TYPE_END) // Type을 벗어나면 불가
			return;

		$this->_PROFILING[$Type]['start'] = microtime(TRUE); // 해당 Type 시작 시간 체크
	}

	//---------------------------------------
	// 프로파일링 중지
	//---------------------------------------
	function stopCheck($Type, $comment='')
	{
		if($this->LOG_FLAG == FALSE)
			return;

		if($Type < PF_TYPE_START || $Type > PF_TYPE_END)
			return;

		$endTime = microtime(TRUE);

		$this->_PROFILING[$Type]['sum'] += ($endTime - $this->_PROFILING[$Type]['start']);

		if($Type == PF_MYSQL)
			$this->QUERY .= $comment . "\n";
		else
			$this->COMMENT .= $comment . "\n";
	}

	//---------------------------------------
	// 프로파일링 로그 저장
	// 외부 로그저장 URL로 전송
	//---------------------------------------
	function LOG_Save($AccountNo = 0)
	{
		if($this->LOG_FLAG == FALSE)
			return;

		if(function_exists("getrusage"))
		{
			// 시스템의 기본 사용 기간을 얻어내는 함수 
			// getrusage 함수는 windows에서 지원 X
			$dat = getrusage(); // 시스템 사용 시간 체크를 위한 값

			$ru_utime_end = $dat['ru_utime.tv_sec'] * 1e6 + $dat['ru_utime.tv_usec']; // System - 사용된 유저 시간
			$ru_stime_end = $dat['ru_stime.tv_sec'] * 1e6 + $dat['ru_stime.tv_usec']; // System - 사용된 시스템 시간

			$utime = ($ru_utime_end - $this->_ru_utime_start)/1e6; // System - 사용된 유저시간
			$stime = ($ru_stime_end - $this->_ru_stime_start)/1e6; // System - 사용된 PHP 시스템 시간
		}
		else
		{
			$utime = 0;
			$stime = 0;
		}
		//---------------------------------------
		// 로그 저장 URL 전송
		//---------------------------------------
		$postField = array(
			"IP"			=> $_SERVER['REMOTE_ADDR'],
			"AccountNo"		=> $AccountNo,
			"Action"		=> $this->_ACTION,
			"T_Page"		=> $this->_PROFILING[PF_PAGE]['sum'],
			"T_Mysql_Conn"	=> $this->_PROFILING[PF_MYSQL_CONN]['sum'],
			"T_Mysql"		=> $this->_PROFILING[PF_MYSQL]['sum'],
			"T_ExtAPI"		=> $this->_PROFILING[PF_EXTAPI]['sum'],
			"T_Log"			=> $this->_PROFILING[PF_LOG]['sum'],
			"T_ru_u"		=> $utime,
			"T_ru_s"		=> $stime,
			"Query"			=> $this->QUERY,
			"Comment"		=> $this->COMMENT
			);
		$Response = $this->http_request_async($this->LOG_URL, $postField);
	}

	//---------------------------------------
	// Curl을 사용하여 로그저장 URL 전송
	// 해당 결과값을 배열로 리턴 [body] / [code]
	//---------------------------------------
	private function http_request_async($url, $params)
	{
		$post_string = http_build_query($params, '', '&');

		$parts = parse_url($url); 

		$fp = fsockopen($parts['host'], isset($parts['port'])?$parts['port']:80, $errno, $errstr, 30);

		if(!$fp)
			return 0;

		$out = "POST ".$parts['path']." HTTP/1.1\r\n";
		$out.= "Host: ".$parts['host']."\r\n";
		$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out.= "Content-Length:".strlen($post_string)."\r\n";
		$out.= "Connection: Close\r\n\r\n";
		$out.= $post_string;
		$Result = fwrite($fp, $out);
		fclose($fp);
		
		return $Result;
	}
}
?>