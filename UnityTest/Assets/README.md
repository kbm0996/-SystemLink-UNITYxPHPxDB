# 유니티xPHP 연동
## 유니티 스크립트 라이프사이클
### ➡ 이벤트 함수
  ![Script_Lifecycle_Flowchart](https://github.com/kbm0996/-SystemLink-UNITYxPHPxDB/blob/master/JPG/Script_Lifecycle_Flowchart.png)
  
  **figure 1. Unity Script Lifecycle Flowchart*
  
**- __Awake()** : 초기화. Start() 함수의 이전 및 프리팹의 인스턴스화 직후에 호출. Start()와 달리 게임 오브젝트가 비활성화 상태라도 호출

**- __OnGUI()** : GUI 이벤트에 따라 프레임마다 여러 차례 호출. 레이아웃 및 리페인트 이벤트가 먼저 처리된 후 레이아웃 및 키보드 / 마우스 이벤트가 각 입력 이벤트에 대해 처리

**- 기타 이벤트 함수** : [유니티 매뉴얼](https://docs.unity3d.com/kr/530/Manual/ExecutionOrder.html) 참고
  
### 🔄 코루틴
Yield Process. 특정 위치에서 실행을 일시 중단하고 다시 시작할 수 있는 여러 진입점을 허용하는 `함수`
동시 실행 루틴이라 부르나, 실제로는 두 가지 흐름을 병렬로 수행하는 것이 아니라 하나의 흐름을 기억했다가 수행

  ![Unity_One_Frame](https://github.com/kbm0996/-SystemLink-UNITYxPHPxDB/blob/master/JPG/Unity_One_Frame.jpg)
  
  **figure 2. Unity One Frame*

**- 작동 원리** : 코루틴(Coroutine)은 `원스레드`지만 코루틴을 사용하여 `멀티스레드`처럼 작동. Update() 호출 시 yield return 할 코루틴이 있는지 확인

**- 스레드와의 차이점** 

- 스레드는 선점형(Preemptive), 코루틴은 비선점형(Non-Premmptive)

- 스레드는 OS가 스케줄링, 코루틴은 유저레벨 스레드

- 멀티스레드는 스레드가 2개 이상, 코루틴은 단일스레드

**- 스레드와의 차이점** 

```c#
    // * 작동 방식 예 :
    public IEnumerator Call()   
    {
        string JSONStr = JsonMapper.ToJson(this);
        UTF8Encoding utf8 = new UTF8Encoding();
        byte[] JsonBytes = utf8.GetBytes(JSONStr);

        // TODO: [Unity Script] WWW 클래스; URL에 메세지를 보내고, 컨텐츠를 받아오는 유틸리티 모듈
        // * 요약 : 지정한 URL에 데이터를 POST 형식으로 Request를 보내고 그 컨텐츠를 받아옴. 
        //        JSON으로 보낸 메시지이므로 웹페이지에서 JSON으로 받는 경우에만 작동
        // * 반환값 : 새로운 WWW 오브젝트(복사가 일어남). 컨텐츠 다운로드 완료시, 생성된 오브젝트로부터 
        //          그 결과를 가져올 수 있다(fetch)
        // * 문제점 : 웹 요청에 대한 반응이 바로 오지 않아서 일시적으로 유니티가 멈춤
        //          (=WWW를 OnClick으로 구현하면 안되는 이유!)
        //          -> 코루틴으로 빠져서 웹 처리를 하도록 해야함
        WWW www = new WWW(szPath, JsonBytes);

        // * yield return : 현 상태 저장 후 리턴
        // * yield break : Iteration 루프 탈출
        yield return www;                     // 첫번째 루프에서 리턴. 매 루프마다 www가 리턴했는지 확인
        Debug.Log("RESPONSE : " + www.text);  // www 리턴 후 실행
    }
```

## NetWWW.cs
### ⚙ 데이터 클래스

```c#
    /*********************************************************
     * 데이터 클래스 인터페이스
    **********************************************************/
    public interface IWebData
    {
        void Recv(JsonData JsonObject); // 받은 데이터 처리
        string URL();
    }
    
    /*********************************************************
     * 데이터 클래스
     *  
     * 용도별로 작성
     * (Login, Register, UpdateSession, StageClear, UserInfo)
    **********************************************************/
    public class WebLogin : IWebData
    {
        public string id { get; set; }
        public string password { get; set; }

        public string URL() { return "Login.php"; }

        public void Recv(JsonData JsonObject)
        {
            NetWWW.INSTANCE().MessageBox("Login Success");
            Debug.Log("Login Success");

            // 받은 session키, accountno 저장
            NetWWW.INSTANCE().session = JsonObject["session"].ToString();
            NetWWW.INSTANCE().accountno = Convert.ToInt32(JsonObject["accountno"].ToString());
        }
    }
    
    ...
```

### 🔄 코루틴
```C#
    IEnumerator SendURL(IWebData SendData)
    {
        Debug.Log("REQUEST : " + (PHP_URL + SendData.URL()));

        //-----------------------------------------------------
        // SendData 클래스를 JSON 형식으로 변환
        //
        //-----------------------------------------------------
        UTF8Encoding utf8 = new UTF8Encoding();   // 유니코드 문자의 UTF-8 인코딩

        // TODO: [LitJson] JsonMapper.ToJson(this); 지정한 객체를 JSON 문자열로 변환하여 리턴
        string szJsonData = JsonMapper.ToJson(SendData);

        //-----------------------------------------------------
        // JSON 데이터 전송 및 결과 컨텐츠 수신
        //
        //-----------------------------------------------------
        // WWW 클래스 인자로 문자열 전달 불가능(포인터도, 메모리도 접근 불가능)
        // 따라서, String을 Byte로 Convert
        byte[] bytes = utf8.GetBytes(szJsonData);

        // TODO: [Unity Script] WWW 클래스; URL에 메세지를 보내고, 컨텐츠를 받아오는 유틸리티 모듈
        // * 요약 : 지정한 URL에 데이터를 POST 형식으로 Request를 보내고 그 컨텐츠를 받아옴. 
        //         JSON으로 보낸 메시지이므로 웹페이지에서 JSON으로 받는 경우에만 작동
        // * 반환값 : 새로운 WWW 오브젝트(복사가 일어남). 컨텐츠 다운로드 완료시, 
        //           생성된 오브젝트로부터 그 결과를 가져올 수 있다(fetch)
        // * 문제점 : 웹 요청에 대한 반응이 바로 오지 않아서 일시적으로 유니티가 멈춤
        //           = WWW를 OnClick으로 구현하면 안되는 이유
        //          -> 코루틴으로 빠져서 웹 처리를 하도록 해야함
        WWW www = new WWW(PHP_URL + SendData.URL(), bytes);

        // * yield return : 현 상태 저장 후 리턴
        // * yield break : Iteration 루프 탈출
        yield return www;   // 답이 오지 않으면 계속 돌면서 답이 왔는지 검사

        //////////////////////////////////////////////////////////////////////
        // * 작동 방식 예 :
        //public IEnumerator Call()   
        //{
        //    string JSONStr = JsonMapper.ToJson(this);
        //    UTF8Encoding utf8 = new UTF8Encoding();
        //    byte[] JsonBytes = utf8.GetBytes(JSONStr);
        //
        //    WWW www = new WWW(szPath, JsonBytes);
        //
        //    yield return www;                     // 첫번째 루프에서 리턴
        //    Debug.Log("RESPONSE : " + www.text);  // 두번째 루프에서 실행
        //}
        //////////////////////////////////////////////////////////////////////

        //-----------------------------------------------------
        // 응답 처리
        //
        //-----------------------------------------------------
        Response(SendData, (www.error != null));    // www.error 값이 null이면 true, null이 아니면 false
        if (www.error == null)
        {
            //-----------------------------------------------------
            // Json 데이터 파싱
            //
            //-----------------------------------------------------
            /* TODO: !!주의!! php측에서 UTF8 + BOM 코드로 인코딩된 다른 php를 include할 경우 에러 발생 */
            JsonData JsonResponse = JsonMapper.ToObject(www.text);

            //-----------------------------------------------------
            // 기본적으로 ResultCode / ResultMsg가 있으므로 확인
            //
            //-----------------------------------------------------
            int ResultCode = Convert.ToInt32(JsonResponse["ResultCode"].ToString());
            string ResultMsg = JsonResponse["ResultMsg"].ToString();

            //-----------------------------------------------------
            // 각각의 WebData 내 Recv()에서 데이터 처리
            //
            //-----------------------------------------------------
            if (ResultCode == 1)
            {
                Debug.Log("RESPONSE : url:" + SendData.URL() + " | Contents:" + www.text);
                SendData.Recv(JsonResponse);
            }
            else
            {
                // 웹페이지 측에서 요청 처리 실패 
                Debug.Log("RESPONSE : url:" + SendData.URL() + " | ResultCode:" + ResultCode + " 
                          | ResultMsg:" + ResultMsg);
                MessageBox(ResultMsg);
            }
        }
        else
        {
            // 전송 에러
            Debug.Log("REQUEST FAILED : url:" + SendData.URL() + " | error:" + www.error);
            MessageBox(www.error);
        }
    }
```
