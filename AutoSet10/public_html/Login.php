<?php
include "_Startup.php";

/*---------------------------------------------
Login.php
	Request
	{	
		"id"		: "사용아이디", 
		"password"	: "사용패스워드" 
	}
	Response
	{
		"ResultCode" : 결과코드 int,
		"ResultMsg" : "결과 메시지",
		"accountno"	: no,
		"session"	: "세션키"
	}
password 는 hash('sha256' ... 으로 해쉬코드 저장.
---------------------------------------------*/
$ID			= mysqli_real_escape_string($g_DB, $_JSONData['id']);
$Password	= mysqli_real_escape_string($g_DB, $_JSONData['password']);

$ID = trim($ID);
$Password = trim($Password);

if($ID == "")
	QuitError(df_RESULT_LOGIN_IDPASS_ERROR, $df_ErrorString[df_RESULT_LOGIN_IDPASS_ERROR]);
if($Password == "")
	QuitError(df_RESULT_LOGIN_IDPASS_ERROR, $df_ErrorString[df_RESULT_LOGIN_IDPASS_ERROR]);

$HashPassword = Hashing64($Password);

$Query = "SELECT accountno FROM account WHERE id = '{$ID}' AND password = '{$HashPassword}'";
$PF->startCheck(PF_MYSQL);
$Result	= DB_ExecQuery($Query);
$PF->stopCheck(PF_MYSQL, $Query);

$Account	= mysqli_fetch_assoc($Result);	//mysqli_fetch_array($Result, MYSQLI_ASSOC); 
mysqli_free_result($Result);

$g_AccountNo = $Account['accountno'];

if($Account === null) // ===는 변수의 타입까지 비교한다
{
	$ResultCode = df_RESULT_LOGIN_IDPASS_ERROR;
	$Session = null;
}
else
{
	$ResultCode = df_RESULT_SUCCESS;
	$Session = KeyGen32();

	$arrQry = array();
	$Query		= "INSERT INTO session (accountno, session, time) VALUES ('{$Account['accountno']}', '{$Session}', NOW()) ON DUPLICATE KEY UPDATE Session = '{$Session}', time=NOW()";
	array_push($arrQry, $Query);

	$Query		= "INSERT INTO login (accountno, time, ip, count) VALUES ('{$Account['accountno']}', UNIX_TIMESTAMP(NOW()), '{$_SERVER['REMOTE_ADDR']}', 1) ON DUPLICATE KEY UPDATE time=UNIX_TIMESTAMP(NOW()), ip='{$_SERVER['REMOTE_ADDR']}', count=count+1";
	array_push($arrQry, $Query);

	$PF->startCheck(PF_MYSQL);
	DB_TransactionQuery($arrQry);
	$PF->stopCheck(PF_MYSQL, "INSERT INTO session, login");
}

$ResponseData = array();
$ResponseData['ResultCode'] = $ResultCode;
$ResponseData['ResultMsg']  = $df_ErrorString[$ResultCode];
$ResponseData['accountno']  = $Account['accountno'];
$ResponseData['session'] = $Session;
ResponseJSON($ResponseData);

include "_Cleanup.php";
?>