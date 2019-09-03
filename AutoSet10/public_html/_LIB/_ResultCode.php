<?php
//------------------------------------------------------------
/*
	결과전송 코드들.
*/
//------------------------------------------------------------

	$df_ErrorString = array();
	
	//------------------------------------------------------------
	// 모든 요청에 대한 성공
	//------------------------------------------------------------
	define("df_RESULT_SUCCESS",				1);

	$df_ErrorString[df_RESULT_SUCCESS]	= "SUCCESS";
	
	//------------------------------------------------------------
	// DB 관련 에러
	//------------------------------------------------------------
	define("df_RESULT_DB_CONNECT_ERROR",	10000);
	define("df_RESULT_DB_QUERY_FAIL",		10001);
	define("df_RESULT_DB_FETCH_ERROR",		10002);

	$df_ErrorString[df_RESULT_DB_CONNECT_ERROR]	= "SERVER ERROR. DB_C";
	$df_ErrorString[df_RESULT_DB_QUERY_FAIL]	= "SERVER ERROR. DB_Q";
	$df_ErrorString[df_RESULT_DB_FETCH_ERROR]	= "SERVER ERROR. DB_F";

	
	//------------------------------------------------------------
	// 회원가입 에러
	//------------------------------------------------------------
	define("df_RESULT_REGISTER_DUPLICATE",		20000);
	define("df_RESULT_REGISTER_ERROR",			20001);
	define("df_RESULT_REGISTER_ID_ERROR",		20002);
	define("df_RESULT_REGISTER_PASSWORD_ERROR",	20003);

	$df_ErrorString[df_RESULT_REGISTER_DUPLICATE]		= "ACCOUNT DUPLICATE";
	$df_ErrorString[df_RESULT_REGISTER_ERROR]			= "Account Regitser error";
	$df_ErrorString[df_RESULT_REGISTER_ID_ERROR]		= "Account Regitser ID error";
	$df_ErrorString[df_RESULT_REGISTER_PASSWORD_ERROR]	= "Account Regitser PASSWORD error";

	
	//------------------------------------------------------------
	// 로그인 에러
	//------------------------------------------------------------
	define("df_RESULT_LOGIN_IDPASS_ERROR",		30000);
	define("df_RESULT_LOGIN_SESSION_ERROR",		30001);

	$df_ErrorString[df_RESULT_LOGIN_IDPASS_ERROR]		= "id/password fail";
	$df_ErrorString[df_RESULT_LOGIN_SESSION_ERROR]		= "Session error";


	//------------------------------------------------------------
	// 스테이지 에러
	//------------------------------------------------------------
	define("df_RESULT_STAGE_ERROR",				40000);
	define("df_RESULT_STAGE_ALREADY_CLEAR",		40001);

	$df_ErrorString[df_RESULT_STAGE_ERROR]			= "Stage error";
	$df_ErrorString[df_RESULT_STAGE_ALREADY_CLEAR]	= "aready stage clear";

	//------------------------------------------------------------
	// 플레이어 관련 에러
	//------------------------------------------------------------
	define("df_RESULT_PLAYER_NOT_FOUND",		50000);

	$df_ErrorString[df_RESULT_PLAYER_NOT_FOUND]		= "Player not found";
	
?>