<?php
include_once "_LIB/_ResultCode.php";
include_once "_LIB/lib_Log.php";
/*----------------------------------------------------------------------
 웹으로 POST/GET 각각의 메소드에 맞는 프로토콜로 메세지 보내기

 1. Fiddler 이용
 2. Call_Socket 
	$Params = array("id"=>"f", "pass"=>"1234");
	echo Call_Socket("http://127.0.0.1/auth_login.php", $Params, 'GET');
 3. Call_Curl
	$postField = array("id"=>"f", "pass"=>"1234");
	$Response = Call_Curl("http://127.0.0.1/auth_login.php", $postField, "GET");
	echo $Response['body']; < 결과 body
	echo $response['code']; < 결과 code 
----------------------------------------------------------------------*/
function Call_Socket($url, $params, $type = 'POST')
{
	/*----------------------------------------
	 인자(Key=Value) & 붙이기
	----------------------------------------*/
	//  입력된 $params를 $post_params이라는 배열로 key=value 타입으로 생성
	// value가 배열인 경우 ','로 나열.
	foreach ($params as $key => &$val)
	{
		if (is_array($val))
			$val = implode(',', $val);
		$post_params[] = $key.'='.urlencode($val);
	}
	//  $post_params 에는 [0]id=test1/[1]pass=test1 형태로 들어감.
	// 이를 & 기준으로 하나의 스트링으로 붙임.
	$post_string = implode('&', $post_params); 
	//$post_string = http_build_query($params, '', '&');

	/*----------------------------------------
	 소켓 접속시 timeout 시간 제어
	----------------------------------------*/
	// http인지 https인지 구분
	$parts = parse_url($url);  
	if ($parts['scheme'] == 'http')
	{
		$fp = fsockopen($parts['host'], isset($parts['port'])?$parts['port']:80, $errno, $errstr, 10);
	}
	else if($parts['scheme'] == 'https')
	{
		$fp = fsockopen("ssl://" . $parts['host'], isset($parts['port'])?$parts['port']:443, $errno, $errstr, 30);
	}
	if(!$fp)
	{
		LOG_System(0, "Call_Socket()", 'Socket Open Fail');
		return 0;
	}
	/*----------------------------------------
	 HTTP 프로토콜 생성
	----------------------------------------*/
	// GET 방식은 URL에 parameter를 ?key=value&.. 형식으로 이어붙임
	if('GET' == $type)
	{
		$parts['path'] .= '?' . $post_string;
		$ContentsLength = 0;
	}
	else if('POST' == $type)
	{
		$ContentsLength = strlen($post_string);  // body의 크기가 틀리면 해독하지 않음
	}

	$out = "$type ".$parts['path']." HTTP/1.1\r\n";
	$out.= "Host: ".$parts['host']."\r\n";
	$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
	$out.= "Content-Length:".$ContentsLength."\r\n";
	$out.= "Connection: Close\r\n\r\n";
	if('POST' == $type)	// POST 방식이면 프로토콜 뒤에 parameter를 붙임
		$out.= $post_string;

	$Result = fwrite($fp, $out);
	// 바로 끊어버리는 경우, 서버측에서 이를 무시해버리는 경우가 있음. (ex. cafe24)
	// fread를 한 번 호출하여 조금이라도 받아주는 것으로 이를 해결 가능
	$Response = fread($fp, 1000);
	// echo $Response; // 디버깅용
	fclose($fp);
	return $Result;
}

function Call_Curl($url, $postFields = array(), $method_type = 'POST')
{
	$ci = curl_init();
	switch(strtoupper($method_type))
	{
		case 'POST':
			curl_setopt($ci, CURLOPT_POST, TRUE);
			curl_setopt($ci, CURLOPT_POSTFIELDS, $postFields);
			break;
		case 'GET':
			curl_setopt($ci, CURLOPT_POST, FALSE);
			$post_string = http_build_query($postFields, '', '&');
			$url .= '?' . $post_string;
			break;
		default:
			curl_close($ci);
			return false;
	}
	curl_setopt($ci, CURLOPT_USERAGENT, " TEST AGENT ");
	curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ci, CURLOPT_TIMEOUT, 30);
	curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);		// 리턴값 반환 여부
	curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);	// 보안 설정 https 사용 여부
	curl_setopt($ci, CURLOPT_HEADER, FALSE);			// 리턴값 헤더 출력 여부
	curl_setopt($ci, CURLOPT_URL, $url);				// 접속할 URL 주소

	//---------------------------------------------
	// 실제 HTTP 전송
	//---------------------------------------------
	$response = array();
	$response['body'] = curl_exec($ci);					// 동기화 문제 상주
	$response['code'] = curl_getinfo($ci, CURLINFO_HTTP_CODE);
	curl_close($ci);
	return $response;
}

?>