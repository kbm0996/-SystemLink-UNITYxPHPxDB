<?php
include "_Startup.php";

/*---------------------------------------------
UserInfo.php
	Request
	{	
		"accountno"	: bigint, 
		"session"	: "세션", 
	}
	Response
	{
		"ResultCode" : 결과코드 int,
		"ResultMsg" : "결과 메시지",
		"exp" : 경험치
		"level" : 레벨
		
		// "stageclear" : [1,2,3,4,5,6]
	}
---------------------------------------------*/
$AccountNo		= mysqli_real_escape_string($g_DB, $_JSONData['accountno']);
$SessionKey		= mysqli_real_escape_string($g_DB, $_JSONData['session']);

$Query = "SELECT COUNT(accountno) AS Cnt FROM session WHERE accountno = '{$AccountNo}' AND session = '{$SessionKey}' AND NOW() < time + 500"; // 세션 갱신 시간 500초
$PF->startCheck(PF_MYSQL);
$Result	= DB_ExecQuery($Query);
$PF->stopCheck(PF_MYSQL, $Query);

$Session = mysqli_fetch_array($Result, MYSQLI_ASSOC);
mysqli_free_result($Result);

if($Session['Cnt'] == 0)
{
	$ResultCode = df_RESULT_PLAYER_NOT_FOUND;
	$exp = null;
	$level = null;
	//$stageclear = null;
}
else
{
	$ResultCode = df_RESULT_SUCCESS;

	$Query		= "SELECT exp, level FROM player WHERE accountno = {$AccountNo}";
	$PF->startCheck(PF_MYSQL);
	$Result		= DB_ExecQuery($Query);
	$PF->stopCheck(PF_MYSQL, "Select");

	$Player		= mysqli_fetch_array($Result, MYSQLI_ASSOC);
	mysqli_free_result($Result);

	$exp = $Player['exp'];
	$level = $Player['level'];
}

$ResponseData = array();
$ResponseData['ResultCode'] = $ResultCode;
$ResponseData['ResultMsg']  = $df_ErrorString[$ResultCode];
$ResponseData['exp']  = $exp;
$ResponseData['level'] = $level;
ResponseJSON($ResponseData);

include "_Cleanup.php";
?>