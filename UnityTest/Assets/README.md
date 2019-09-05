# 시스템 연동 - CPP+PHP+DB

## 📐 구조 및 작동 순서

  ![Script_Lifecycle_Flowchart](https://github.com/kbm0996/-SystemLink-UNITYxPHPxDB/blob/master/JPG/Script_Lifecycle_Flowchart.png)
  
  **figure 1. Unity Script Lifecycle Flowchart*
  
  ![Unity_One_Frame](https://github.com/kbm0996/-SystemLink-UNITYxPHPxDB/blob/master/JPG/Unity_One_Frame.jpg)
  
  **figure 2. Unity One Frame*
  
## 📑 구성

### 1. C++ 파트

**📋 _CallHttp.h/cpp** : UTF8↔UTF16 변환 함수, Domain↔IP 변환 함수, Http GET/POST 메세지 보내기 및 받기 함수

### 2. PHP 파트

#### 📂 Home Directory

> **📋 _Config_DB.php** : DB 설정값, LOG 전송 URL, LOG 수준, 프로파일링 확률 
>
> **📋 _Startup.php** : 각종 페이지의 첫 부분. 주요 라이브러리 인클루드, 각종 함수 및 변수 초기화
>
> **📋 Register.php** : 회원 가입 요청 및 응답(POST 메시지, JSON 포맷) 페이지 (C++ ↔ **PHP** ↔ DB) 
>
> **📋 Login.php** : 로그인 요청 및 응답(POST 메시지, JSON 포맷) 페이지 (C++ ↔ **PHP** ↔ DB) 
>
> **📋 Session.phpp** : 세션 갱신(POST 메시지, JSON 포맷) 페이지 (PHP\[Login.php, Session.php\] ↔ **PHP** ↔ DB)
>
> **📋 StageClear.php** : 스테이지 클리어 정보 요청 및 응답 페이지 (C++ ↔ **PHP** ↔ DB) 
>
> **📋 UserInfo.php** : 유저 정보 조회 요청 및 응답 페이지 (C++ ↔ **PHP** ↔ DB) 
>
> **📋 _Cleanup.php** : 각종 페이지의 끝 부분. DB 연결 해제, 로그 저장, 프로파일러 로그 저장
>
>#### 📂 _SQL : 테이블 최초 생성용 sql
>>
>> 📋 game_db.sql, 📋 log_db.sql
>>
>#### 📂 _LIB : 라이브러리 폴더
>>
>> **📋 _ResultCode.php**
>>
>> **📋 lib_Call.php** : Curl 관련 함수
>>
>> **📋 lib_DB.php** : 실질적으로 DB에 Query를 날리는 함수
>>
>> **📋 lib_Key.php** : sha256 인코딩 함수
>>
>> 📋 lib_ErrorHandler.php, 📋 lib_Log.php, 📋 lib_Profiling.php : 디버깅 관련 함수
>>
>#### 📂 _LIB : 실질적으로 로그를 DB에 저장시키는 함수
>>
>> **📋 _Config_LOG.php** : DB 관련 글로벌 변수
>>
>> 📋 LogGame.php.php, 📋 LogProfiling.php, 📋 LogSystem.php : 종류별 로그 날리는 페이지

---

<sup><b id="footnote1">[1](#1)</b></sup> PHP(PHP: Hypertext Preprocessor). 프로그래밍 언어의 일종. 대표적인 서버 사이드 스크립트 언어로 웹 개발에 특화된 언어

<sup><b id="footnote2">[2](#2)</b></sup> MySQL. 오픈 소스 관계형 데이터베이스 관리 시스템(RDBMS)의 일종
