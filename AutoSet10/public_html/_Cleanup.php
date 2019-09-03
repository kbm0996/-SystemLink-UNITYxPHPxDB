<?php

DB_Disconnect();

$GameLog->SaveLog();

$PF->stopCheck(PF_PAGE);
$PF->LOG_Save($g_AccountNo);

?>