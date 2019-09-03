<?php
$g_LOGDB_IP = "127.0.0.1";
$g_LOGDB_ID = "root";
$g_LOGDB_PASS = "1234";
$g_LOGDB_NAME = "log_schema";
$g_LOGDB_PORT = 3306;
/*------------------------------------------------------------------------
# 시스템로그 & 게임로그 & 프로파일링 로그 저장소

* DATABASE
	Log - 시스템로그, 게임로그, 프로파일링로그 저장.

* TABLE - SYSTEM LOG
	SystemLog_template - 시스템 로그 테이블 생성용 템플릿 테이블
	SystemLog_YYYYMM - 월 단위로 ( SystemLog_201306 ) 생성되어 저장.

* TABLE - GAME LOG
	GameLog_template - 게임 로그 테이블 생성용 템플릿 테이블
	GameLog_YYYYMM - 월 단위로 ( GameLog_201306 ) 생성되어 저장.

* TABLE - PROFILING LOG
	ProfilingLog_template - 서버 프로파일링 로그 테이블 생성용 템플릿 테이블
	ProfilingLog_YYYYMM - 월 단위로 (ProfilingLog_201306) 생성되어 저장
------------------------------------------------------------------------*/
?>