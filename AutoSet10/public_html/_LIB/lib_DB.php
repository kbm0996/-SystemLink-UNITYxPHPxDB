<?php
include_once "_Config_DB.php";
include_once "_LIB/_ResultCode.php";
include_once "_LIB/lib_Log.php";

//---------------------------------------------------------------------------------
// DB 연결
//---------------------------------------------------------------------------------
function DB_Connect()
{
	global $g_DB_IP;
	global $g_DB_ID;
	global $g_DB_PASS;
	global $g_DB_NAME;
	global $g_DB_PORT;
	global $g_DB;

	global $g_AccountNo;
	$g_DB = mysqli_connect($g_DB_IP, $g_DB_ID, $g_DB_PASS, $g_DB_NAME, $g_DB_PORT);
	if(!$g_DB)
	{
		file_put_contents('php://stderr', "{$_SERVER['PHP_SELF']}" . $df_ErrorString[df_RESULT_DB_CONNECT_ERROR]);
		exit;
	}
	mysqli_set_charset($g_DB, "utf8");

	
	// 지속적인 트랜젝션 상태. 트랜젝션이 필요하지 않는 상황에서도 트렌젝션을 생성시켜 효율성이 떨어짐
	// mysqli_autocommit($g_DB, FALSE);
}

//---------------------------------------------------------------------------------
// DB 연결 해제
//---------------------------------------------------------------------------------
function DB_Disconnect()
{
	global $g_DB;
	if(isset($g_DB))
	{
		// 정석대로 넣어본 것임. 필수는 아님, 알아서 APACHE가 다 해줌. 
		// DB 할 일 다 했으면 빨리 접속을 끊어주는게 좋음.
		mysqli_close($g_DB);
	}

}

//---------------------------------------------------------------------------------
// DB 쿼리 실행
//---------------------------------------------------------------------------------
function DB_ExecQuery($Query)
{
	global $g_DB;
	global $g_AccountNo;
	global $df_ErrorString;

	$Result	= mysqli_query($g_DB, $Query);
	if(!$Result)
	{
		LOG_System($g_AccountNo, "{$_SERVER['PHP_SELF']}", $Query . " / " . $df_ErrorString[df_RESULT_DB_QUERY_FAIL]);
		exit;
	}
	return $Result;
}

//---------------------------------------------------------------------------------
// 다수의 DB 쿼리(배열) 실행
//---------------------------------------------------------------------------------
function DB_TransactionQuery($qryArr)
{
	global $g_DB;
	global $g_AccountNo;
	global $df_ErrorString;

	mysqli_begin_Transaction($g_DB);
	foreach($qryArr as $Query)
	{
		if(!is_string($Query))
		{
			LOG_System($g_AccountNo, "{$_SERVER['PHP_SELF']}", $Query . " / " . $df_ErrorString[df_RESULT_DB_FETCH_ERROR]);
			mysqli_rollback($g_DB);
			exit;
		}

		if(!mysqli_query($g_DB, $Query))
		{
			LOG_System($g_AccountNo, "{$_SERVER['PHP_SELF']}", $Query . " / " . $df_ErrorString[df_RESULT_DB_FETCH_ERROR]);
			mysqli_rollback($g_DB);
			exit;
		}
	}

	// LAST_INSERT_ID() : 현재 세션에서 방금 얻은 AUTO_INCREMENT값 반환. 연쇄적으로 같은 사용자의 항목에 접근해야할 경우 사용.
	// 여러 Query문을 한 번에 날릴때 한 값만 얻기 때문에 추후 배열로 저장하여 해결해야함
	$insert_id = mysqli_insert_id($g_DB);
	mysqli_commit($g_DB);

	return $insert_id;
}

//---------------------------------------------------------------------------------
// 클라이언트에게 통보. JSON으로 Client에게 전송
//---------------------------------------------------------------------------------
function ResponseJSON($Array = array())
{
	global $g_AccountNo;

	$ResponseJSON = json_encode($Array);
	LOG_System($g_AccountNo, "{$_SERVER['PHP_SELF']}", "{$ResponseJSON}");
	echo $ResponseJSON;
}
?>