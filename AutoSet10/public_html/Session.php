<?php
include "_Startup.php";

/*---------------------------------------------
Login.php
	Request
	{	
		"accountno"		: "번호", 
		"session"	: "세션키" 
	}
	Response
	{
		"ResultCode" : 결과코드 int,
		"ResultMsg" : "결과 메시지",
		"accountno"	: no,
		"session"	: "세션키"
	}
---------------------------------------------*/
$AccountNo		= mysqli_real_escape_string($g_DB, $_JSONData['accountno']);
$SessionKey		= mysqli_real_escape_string($g_DB, $_JSONData['session']);



$Query = "SELECT COUNT(accountno) AS Cnt FROM session WHERE accountno = '{$AccountNo}' AND session = '{$SessionKey}' AND NOW() > time + 500"; // 세션 갱신 시간 500초
$PF->startCheck(PF_MYSQL);
$Result		= DB_ExecQuery($Query);
$PF->stopCheck(PF_MYSQL, $Query);

print_r($Result);

$Session	= mysqli_fetch_assoc($Result);	//mysqli_fetch_array($Result, MYSQLI_ASSOC); 

print_r($Session);


mysqli_free_result($Result);

if($Session['Cnt'] == 0)
{
	$ResultCode = df_RESULT_PLAYER_NOT_FOUND;
	$AccountNo = null;
	$New_SessionKey = null;
}
else
{
	$ResultCode = df_RESULT_SUCCESS;
	$New_SessionKey = KeyGen32();

	$arrQry = array();

	$Query		= "INSERT INTO session (accountno, session, time) VALUES ({$AccountNo}, '{$New_SessionKey}', NOW()) ON DUPLICATE KEY UPDATE session = '{$New_SessionKey}', time=NOW()";
	array_push($arrQry, $Query);
	
	$PF->startCheck(PF_MYSQL);
	$AccountNo = DB_TransactionQuery($arrQry);
	$PF->stopCheck(PF_MYSQL, "Insert");
}

$ResponseData = array();
$ResponseData['ResultCode'] = $ResultCode;
$ResponseData['ResultMsg']  = $df_ErrorString[$ResultCode];
$ResponseData['accountno']  = $AccountNo;
$ResponseData['session'] = $New_SessionKey;
ResponseJSON($ResponseData);

include "_Cleanup.php";
?>