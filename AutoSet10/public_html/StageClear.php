<?php
include "_Startup.php";

/*---------------------------------------------
StageClear.php
	Request
	{	
		"accountno"	: bigint, 
		"session"	: "세션", 
		"stageid"	: stageid, 
	}
	Response
	{
		"ResultCode" : 결과코드 int,
		"ResultMsg" : "결과 메시지",
		"exp" : 경험치 (내 보유 경험치)
		"level" : 레벨 (내 레벨)
	}
* data_stage 에 stageid 의 존재여부 확인
* 유저의 클리어 여부 확인 후 경험치 지급
* 레벨업 처리까지
---------------------------------------------*/
$AccountNo 	= mysqli_real_escape_string($g_DB, $_JSONData['accountno']);
$SessionKey = mysqli_real_escape_string($g_DB, $_JSONData['session']);
$StageID	= mysqli_real_escape_string($g_DB, $_JSONData['stageid']);

//////////////////////////////////////////////////////////////////////////
// Session Check
$Query = "SELECT COUNT(accountno) as Cnt FROM session WHERE accountno = {$AccountNo} AND session = '{$SessionKey}' AND NOW() < time + 500"; // 유효시간 500초
$PF->startCheck(PF_MYSQL);
$Result	= DB_ExecQuery($Query);
$PF->stopCheck(PF_MYSQL, $Query);
$SessionCheck = mysqli_fetch_array($Result, MYSQLI_ASSOC); 
mysqli_free_result($Result);
if ($SessionCheck['Cnt'] == 0)
{
	QuitError(df_RESULT_PLAYER_NOT_FOUND, $df_ErrorString[df_RESULT_PLAYER_NOT_FOUND]);
}
else
{
	$g_AccountNo = $AccountNo;
}

//////////////////////////////////////////////////////////////////////////
// Get Player Level & Exp
$Query = "SELECT level, exp FROM player WHERE accountNo = {$AccountNo}";
$PF->startCheck(PF_MYSQL);
$Result	= DB_ExecQuery($Query);
$PF->stopCheck(PF_MYSQL, $Query);
$Data_Player = mysqli_fetch_array($Result, MYSQLI_ASSOC);
mysqli_free_result($Result);
if($Data_Player['exp'] == NULL)
{
	QuitError(df_RESULT_PLAYER_NOT_FOUND, $df_ErrorString[df_RESULT_PLAYER_NOT_FOUND]); 
}
else 
{
	$PlayerLevel = $Data_Player['level'];
	$PlayerExp = $Data_Player['exp'];
}

//////////////////////////////////////////////////////////////////////////
// is Clear This Stage?
$Query = "SELECT COUNT(accountno) as Cnt FROM clearstage 
    WHERE accountno = {$AccountNo} AND stageid = '{$StageID}'"; 
$PF->startCheck(PF_MYSQL);
$Result	= DB_ExecQuery($Query);
$PF->stopCheck(PF_MYSQL, $Query);

$isClearStage = mysqli_fetch_array($Result, MYSQLI_ASSOC);
mysqli_free_result($Result);
if($isClearStage['Cnt'] != 0) // 클리어하지 않은 경우에만 Exp 획득
{
	$ResultCode = df_RESULT_STAGE_ALREADY_CLEAR;
}
else
{
	//////////////////////////////////////////////////////////////////////////
	// Get Stage Clear EXP
	$Query = "SELECT clearexp FROM data_stage WHERE stageid = {$StageID}";
	$PF->startCheck(PF_MYSQL);
	$Result	= DB_ExecQuery($Query);
	$PF->stopCheck(PF_MYSQL, $Query);

	$Data_Stage = mysqli_fetch_array($Result, MYSQLI_ASSOC);
	mysqli_free_result($Result);
	if ($Data_Stage['clearexp'] == NULL)
	{
		QuitError(df_RESULT_STAGE_ERROR, $df_ErrorString[df_RESULT_STAGE_ERROR]);
	}
	else
	{
		$ClearExp = $Data_Stage['clearexp'];
	}

	//////////////////////////////////////////////////////////////////////////
	// Get Level from `data_levelup` Table
	$Query = "SELECT level FROM data_levelup WHERE exp <= {$PlayerExp}+{$ClearExp} order by exp desc limit 1";
	$PF->startCheck(PF_MYSQL);
	$Result	= DB_ExecQuery($Query);
	$PF->stopCheck(PF_MYSQL, $Query);

	$Data_Level = mysqli_fetch_array($Result, MYSQLI_ASSOC);
	mysqli_free_result($Result);

	// 정말로 존재하지 않거나, 경험치가 올랐는데도 level이 1에서 오르지 않은 경우
	if($Data_Level['level'] == NULL)
	{
		// 경험치만 
		$PlayerExp += $ClearExp;
	}
	else 
	{
		$PlayerLevel = $Data_Level['level'];
		$PlayerExp += $ClearExp;
	}

	//////////////////////////////////////////////////////////////////////////
	// Updating Session & Tables(clearstage, player) 
	$SetQuery = array();
	array_push($SetQuery, "UPDATE session SET time = NOW() WHERE accountno = {$AccountNo}");
	array_push($SetQuery, "INSERT INTO clearstage (accountno, stageid) VALUES ({$AccountNo}, {$StageID}) ON DUPLICATE KEY UPDATE stageid = {$StageID}");
	array_push($SetQuery, "INSERT INTO player (accountno, level, exp) VALUES ({$AccountNo}, {$PlayerLevel}, {$PlayerExp}) ON DUPLICATE KEY UPDATE level = {$PlayerLevel}, exp = {$PlayerExp}");

	$PF->startCheck(PF_MYSQL);
	DB_TransactionQuery($SetQuery);
	$PF->stopCheck(PF_MYSQL, "Insert clearstage, player");

	$ResultCode = df_RESULT_SUCCESS;
}

$ResponseData = array();
$ResponseData['ResultCode'] = $ResultCode;
$ResponseData['ResultMsg']	= $df_ErrorString[$ResultCode];
$ResponseData['exp']		= $PlayerExp;
$ResponseData['level']		= $PlayerLevel;
ResponseJSON($ResponseData);

include "_Cleanup.php";
?>