<?php
include "_Startup.php";

/*---------------------------------------------
Register.php
	Request
	{	
		"id"		: "사용아이디", 
		"password"	: "사용패스워드" 
	}
	Response
	{
		"ResultCode" : 결과코드 int,
		"ResultMsg" : "결과 메시지"
	}
---------------------------------------------*/

echo " \n";

$ID			= mysqli_real_escape_string($g_DB, $_JSONData['id']);
$Password	= mysqli_real_escape_string($g_DB, $_JSONData['password']);

$ID = trim($ID);
$Password = trim($Password);

if($ID == "")
	QuitError(df_RESULT_REGISTER_ID_ERROR, $df_ErrorString[df_RESULT_REGISTER_ID_ERROR]);
if($Password == "")
	QuitError(df_RESULT_REGISTER_PASSWORD_ERROR, $df_ErrorString[df_RESULT_REGISTER_PASSWORD_ERROR]);

$Query		= "SELECT COUNT(accountno) AS Cnt FROM account WHERE id = '{$ID}'";
$PF->startCheck(PF_MYSQL);
$Result		= DB_ExecQuery($Query);
$PF->stopCheck(PF_MYSQL, $Query);

//ASSOC:컬럼명으로 값, NUM:인덱스로 값, BOTH:ASSOC/NUM 혼합
$Account	= mysqli_fetch_assoc($Result);	//mysqli_fetch_array($Result, MYSQLI_ASSOC); 

mysqli_free_result($Result);

if($Account['Cnt'] != 0)
{
	$ResultCode = df_RESULT_REGISTER_DUPLICATE;
}
else
{
	$ResultCode = df_RESULT_SUCCESS;
	$HashPass = Hashing64($Password);
	$arrQry = array();
	$Query		= "INSERT INTO account (id, password) VALUES ('{$ID}', '{$HashPass}')";
	array_push($arrQry, $Query);
	$Query		= "INSERT INTO player (level, exp) VALUES (1, 0)";
	array_push($arrQry, $Query);
	$PF->startCheck(PF_MYSQL);
	$AccountNo = DB_TransactionQuery($arrQry);
	$PF->stopCheck(PF_MYSQL, "INSERT account, player");
	
	// /* account가 다른 DB에 있을 경우? */
	// 트랜잭션 처리가 불가능하여 account는 있는데 player는 없을 수 있음
	// - 해결 방안? 매번 player가 있는지 없는지 확인하는 것은 너무나 비효율적. 왜냐하면 이런 현상 발생 빈도가 매우 희박한데 모든 컨텐츠에서 이에 대한 검사를 시도하면 부하가 너무 늘어남.  DB가 트랜잭션 처리하는 원리를 알아야함.
	// - DB 트랜잭션의 원리 : commit 하기 전까지는 어떤 임시 파일에 저장되고 있는 상태로 commit 됐다는 flag가 서면 삭제되고 DB에 적용됨. 실패하면 rollback (임시 파일 삭제)
	//		1. DB에게 적용되기 전에 binary 데이터를 어딘가에 저장
	//		2. 반영이 완료되면 binary 데이터 삭제
	//		3. 반영 되다가 말았으면 싹 다 지우기. 반영됐는데 로그 지우다가 DB가 죽으면 식별하여 ~~
	//	즉, 외부에서 DB에서 반영이 됐는지 안됐는지, binary데이터가 지워졌는지 안지워졌는지를 식별한 후 그에 따른 처리를 해주어야함
	//	그러나! DB는 시작과 끝을 알 수 있다. 그런데 웹은 시작과 끝이라는 개념이 없기 때문에 완벽한 안전장치를 마련하는 것은 어려움.
	//	따라서, 최소한 account는 있고 player는 없는 현상만 막아주자. player가 없을 경우 account는 삭제시켜버리기
}

$ResponseData = array();
$ResponseData['ResultCode'] = $ResultCode;
$ResponseData['ResultMsg']  = $df_ErrorString[$ResultCode];
ResponseJSON($ResponseData);

include "_Cleanup.php";
?>