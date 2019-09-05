# 시스템 연동 - UNITY+PHP+DB
## 📢 개요

 ![capture](https://github.com/kbm0996/-SystemLink-CPPxPHPxDB/blob/master/jpg/figure0.png)

 Unity와 PHP<sup id="1">[1](#footnote1)</sup>, PHP와 MySQL<sup id="2">[2](#footnote2)</sup>(이하 DB) 간의 통신으로 시스템 연동하는 예제이다. C++는 PHP에 어떤 데이터를 JSON 형태로 PHP에 전송하고, PHP는 그 데이터를 쿼리문으로 만들어 DB에 전달하고 작동한다. 그 다음엔 결과를 DB가 PHP로 전송하고, PHP에서는 JSON 형태로 C++ 애플리케이션에 전달한다. 

 웹 서버든 게임 서버든 간에 서버측 컴퓨터에서 직접 데이터를 보관하는 경우는 거의 없다. 보안 상의 이유도 있지만 게임 서버의 경우에는 그 규모가 클수록 클라이언트들로부터 오는 각종 요청들을 처리하기에도 부하가 큰데 데이터베이스까지 겸하면 서비스가 불가능할 정도로 무거워진다.
 
 단, 서버를 분산하면 네트워크 통신을 해야하기 때문에 네트워크 지연 시간이 발생한다. 따라서 DB서버와 통신하는 서버 간의 물리적인 거리가 너무 멀어서는 안된다.

 그리고 규모가 지나치게 커지면 DB 작업 자체가 느려질 수 밖에 없기 때문에 서버를 분산해도 감당이 안된다. 이런 경우에는 샤딩(Sharding)으로 수평 파티셔닝(horizontal partitioning)을 한다던가, 리플리케이션(Replication; master-slave 구조) 구현하는 수 밖에 없다.  
 
## 💻 예제
 Unity C#(send/recv msg) ↔ PHP(react msg, react db query) ↔ DB(react query) example with autoset10(php7.1)

  ![capture](https://github.com/kbm0996/-SystemLink-CPPxPHPxDB/blob/master/jpg/figure1.png)
  
  **figure 1. Run Result*


## 📐 구조 및 작동 순서

  ![capture](https://github.com/kbm0996/-SystemLink-CPPxPHPxDB/blob/master/jpg/figure2.PNG)
  
  **figure 2. Structure*
  
## 📑 구성

### 1. C# 파트

**📋 NetWWW.cs** : 로그인, 회원가입, 세션 갱신 등 버튼 GUI를 포함한 컴포넌트(Component; Class that inherit the Mobobehaviour class), 코루틴(Coroutine)

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
