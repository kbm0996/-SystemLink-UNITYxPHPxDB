/*-------------------------------------------------------------------------------
  유니티 PHP 통신

 가상 함수 Recv()와 URL()을 가진 인터페이스 IWebData를 사용 
IWebData 인터페이스를 상속받은 클래스들을 패킷의 종류에 따라 각각 작성

 * MonoBehaviour 상속받은 클래스 :  웹통신의 기본적인 부분(순서제어, WWW 보내기, 받기) 처리
 * 각각의 데이터 클래스(IWebData 상속받은 클래스) : 받은 결과를 처리

 - 사용법
 웹통신이 필요한 시점에서 다음과 같이 데이터를 생성하여 호출
  WebRegister Reg = new WebRegister();
  
  Reg.id = this.id;
  Reg.password = this.password;

  NetWWW.INSTANCE().Send(Reg, true); 
  
 * Send의 인자로 들어간 Reg는 NetWWW에 복사되어 내부 SendList에 등록됨
  그 후 코루틴으로 WWW 객체를 호출시키며 응답이 왔을 때 Reg.Recv() 함수가 호출됨

 * 웹요청과 그에 따른 응답은 대기시간이 걸리므로 비동기로 처리를 해야 함
  해당 코드의 경우, WebRegister 요청 후 완료 처리는 Reg.Recv()에서 진행
---------------------------------------------------------------------------------*/
using System;
using System.Collections;
using System.Collections.Generic;
using System.Text;
using UnityEngine;
using LitJson; // TODO: [LitJson] `LitJson.dll` 필요

/*********************************************************
 * 데이터 클래스 인터페이스
 * 
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
        ///Globals.INSTANCE.nowstage = 1;
        ///Application.LoadLevel("StageA");
        ///
        NetWWW.INSTANCE().MessageBox("Login Success");
        Debug.Log("Login Success");

        // 받은 session키, accountno 저장
        NetWWW.INSTANCE().session = JsonObject["session"].ToString();
        NetWWW.INSTANCE().accountno = Convert.ToInt32(JsonObject["accountno"].ToString());
    }
}

public class WebRegister : IWebData
{
    public string id { get; set; }
    public string password { get; set; }

    public string URL() { return "Register.php"; }

    public void Recv(JsonData JsonObject)
    {
        NetWWW.INSTANCE().MessageBox("Register Success");
        Debug.Log("Register Success");
    }
}

public class WebUserInfo : IWebData
{
    public int accountno { get; set; }
    public string session { get; set; }

    public string URL() { return "Userinfo.php"; }

    public void Recv(JsonData JsonObject)
    {
        String str = "Level : " + Convert.ToInt32(JsonObject["level"].ToString()) + " / Exp : " + Convert.ToInt32(JsonObject["exp"].ToString());
        NetWWW.INSTANCE().MessageBox(str);
        Debug.Log(str);
    }
}

public class WebSession : IWebData
{
    public int accountno { get; set; }
    public string session { get; set; }

    public string URL() { return "Session.php"; }

    public void Recv(JsonData JsonObject)
    {
        NetWWW.INSTANCE().MessageBox("Session Update Success");
        Debug.Log("Session Update Success");

        // 받은 session키, accountno 저장
        NetWWW.INSTANCE().session = JsonObject["session"].ToString();
        NetWWW.INSTANCE().accountno = Convert.ToInt32(JsonObject["accountno"].ToString());
    }
}

public class WebStageClear : IWebData
{
    public int accountno { get; set; }
    public string session { get; set; }
    public int stageid { get; set; }

    public string URL() { return "StageClear.php"; }

    public void Recv(JsonData JsonObject)
    {
        NetWWW.INSTANCE().MessageBox("Stage Clear");
        Debug.Log("Stage Clear");
    }
}

/*********************************************************
 * 웹통신 네트워크 싱글톤 클래스
 * 
 * 코루틴(Coroutine)이 있는 클래스는 일반 싱글톤이 아닌 유니티 객체 싱글톤이어야 함
**********************************************************/
// TODO: [Unity Script] MonoBehaviour 상속 클래스; 유니티와 스크립트를 연결. 유니티 관련 함수를 내부에서 호출 가능
// MonoBehaviour에는 Unity 실행에 중요한 `기본 함수`들을 지원 (Script_Lifecycle_Flowchart 참고)
//
// :: INITIALIZE :: 주어진 스크립트에 대해 한 번만 호출
// - Awake() : 게임 오브젝트가 비활성화 상태라도 호출
// - Start() : 게임 오브젝트가 비활성화 상태면 호출되지 않음
//
// :: PHYSICS :: 한 프레임당 한 번 이상 발생
// - FixedUpdate() : 프레임마다 진행되는 시간은 일정하지 않은데 이 함수는 고정된 일정 시간을 간격으로 한번씩 호출
//
// :: GAME LOGIC :: 유니티 내부의 프레임 속도에 맞춰 매 프레임마다 한번씩 호출
// - Update(), LateUpdate()
//
// :: GUI RENDERING ::
// - OnGUI() : 한 프레임에 여러번 호출
public class NetWWW : MonoBehaviour
{
    const string PHP_URL = "http://127.0.0.1/";
    
    // 객체들을 각각의 패킷으로 취급, 직렬화하여 순차적으로 전송 (디자인 패턴 중 Command Pattern 참고)
    /*static*/ List<IWebData> _Sendlist = new List<IWebData>();

    // 접근 지정자 public.  Unity의 inspector에서 제어 가능
    public string id;       
    public string password;
    public string session { get; set; }
    public int accountno { get; set; }
    public int stageid;

    public bool _bBlockinput = false;
    bool _bMsgbox = false;
    string _szMsg;

    protected static NetWWW _instance;
    public static NetWWW INSTANCE()
    {
        // 인스턴스가 없을 경우 찾아봄. (유니티 객체이므로 유니티 함수로 찾음)
        if (null == _instance)
            _instance = FindObjectOfType(typeof(NetWWW)) as NetWWW;

        // 위 시도에서도 못찾으면 유니티 객체 동적 생성
        // 먼저 비어있는 GameObject를 생성한다. 사실 객체 이름은 크게 중요하지 않음
        // 그리고 빈 오브젝트에 이 네트워크 클래스를 스크립트로 연동! <
        // 이건 스크립트 파일을 객체에 드래그 해서 붙이는 과정과 같음
        if (null == _instance)
        {
            GameObject obj = new GameObject(typeof(NetWWW).ToString());
            _instance = obj.AddComponent<NetWWW>();
        }

        return _instance;
    }

    void Awake()
    {
        // TODO: [Unity Script] DontDestroyOnLoad(this);
        // 해당 스크립트가 있는 오브젝트는 씬이 바뀌어도 파괴되지 않음
        DontDestroyOnLoad(this);

        PlayerPrefs.SetString("Id", "test"); // 자동 로그인을 위한 Key Value를 유니티에 저장
        id = PlayerPrefs.GetString("Id");   // 반드시 로그인마다 세션 갱신
        // 서로 다른 여러 기기에서 '자동 로그인'을 가능케하려면 한 명의 유저에게 세션이 여러개 붙도록 설계
    }

    void OnGUI()
    {
        if (_bMsgbox)
        {
            
            GUI.Box(new Rect(Screen.width / 4, Screen.height / 4, Screen.width / 2, Screen.height / 2), _szMsg);
            if (GUI.Button(new Rect(Screen.width / 2 - 50, Screen.height - Screen.height / 4, 100, 50), "OK"))
            {
                _bMsgbox = false;
                GUI.enabled = true;
            }
            GUI.enabled = false;
        }
        else
        {
           GUI.enabled = !_bBlockinput;
        }

        GUI.Box(new Rect(10, 10, 150, 180), "Loader Menu");
        {
            if (GUI.Button(new Rect(20, 40, 130, 20), "회원가입"))
            {
                // MonoBehaviour를 상속받지 않은 일반 클래스
                // C#의 모든 객체는 동적할당해야 사용 가능. 할당된 객체는 Garbage Collector에 의해 자동으로 파괴
                WebRegister Reg = new WebRegister();

                Reg.id = this.id;
                Reg.password = this.password;

                // 내부에서 코루틴 함수 호출
                NetWWW.INSTANCE().Send(Reg, true);
                // TODO: !!주의!! 유니티 코루틴 사용 시 참고 사항
                // 이 부분에서 STartCoroutine()을 직접 호출해선 안됨. 
                // 1. 코루틴 종료가 늦어지거나 설계상의 문제로 종료되지 않을 경우, 메모리 정리를 하지 않기 때문에 가끔씩 튐 
                // 2. 양적으로 많아지면 매 프레임마다 다 들려야 하므로 느려짐
                // -> 라이브에서는 해당 예제처럼 객체들을 각각의 패킷으로 여기고 직렬화하여 직렬화하여 순차적으로 전송해야함 (디자인 패턴 중 Command Pattern 참고)
            }
            if (GUI.Button(new Rect(20, 40 + 30, 130, 20), "로그인"))
            {
                WebLogin Login = new WebLogin();

                Login.id = this.id;
                Login.password = this.password;

                NetWWW.INSTANCE().Send(Login, true);
            }
            if (GUI.Button(new Rect(20, 40 + 60, 130, 20), "유저 정보"))
            {
                WebUserInfo Info = new WebUserInfo();

                Info.accountno = this.accountno;
                Info.session = this.session;

                NetWWW.INSTANCE().Send(Info, true);
            }

            if (GUI.Button(new Rect(20, 40 + 90, 130, 20), "세션 갱신"))
            {
                WebSession Session = new WebSession();

                Session.accountno = this.accountno;
                Session.session = this.session;

                NetWWW.INSTANCE().Send(Session, true);
            }

            if (GUI.Button(new Rect(20, 40 + 120, 130, 20), "스테이지 클리어"))
            {
                WebStageClear Clear = new WebStageClear();

                Clear.accountno = this.accountno;
                Clear.session = this.session;
                Clear.stageid = this.stageid;

                NetWWW.INSTANCE().Send(Clear, true);
            }
        }

        GUI.enabled = true;
    }

    public void MessageBox(string Message)
    {
        INSTANCE()._szMsg = Message;
        INSTANCE()._bMsgbox = true;
    }

    public static void SendMessage(IWebData SendData, bool bBlockinput)
    {
        INSTANCE().Send(SendData, bBlockinput);
    }

    public void Send(IWebData SendData, bool blockinput)
    {
        _bBlockinput = blockinput;

        // TODO: _SENDLIST ADD
        _Sendlist.Add(SendData);
        Debug.Log("_Sendlist add(" + _Sendlist.Count + ")");
        if (_Sendlist.Count == 1)
        {
            // TODO: [Unity Script] StartCoroutine(); 
            // 코루틴 함수 호출
            StartCoroutine(SendURL(_Sendlist[0]));
        }
    }

    void Response(IWebData List, bool bError)
    {
        if (bError) // 에러 발생
        {
            // 종료
            ///Application.Quit();

            // TODO: _SENDLIST REMOVE
            _Sendlist.RemoveAt(0);
            Debug.Log("_Sendlist remove(" + _Sendlist.Count + ") + ERROR");

            _bBlockinput = false;
        }
        else
        {
            // TODO: _SENDLIST REMOVE
            _Sendlist.RemoveAt(0);
            Debug.Log("_Sendlist remove(" + _Sendlist.Count + ")");
            if (_Sendlist.Count > 0)    // 남아있으면 추가 전송
            {
                StartCoroutine(SendURL(_Sendlist[0]));
            }
            else
            {
                _bBlockinput = false;
            }
        }
    }


    /**********************************************************
     * TODO: [Unity Script] 코루틴(Coroutine)
     * Yield Process 개념. 특정 위치에서 실행을 일시 중단하고 다시 시작할 수 있는 여러 진입점을 허용하는 함수
     * `동시실행루틴`이라 부르나, 실제로는 두 가지 흐름을 병렬로 수행하는 것이 아니라 하나의 흐름을 기억했다가 수행
     * 
     * - 작동 원리 : 코루틴(Coroutine)은 `단일쓰레드`지만 코루틴을 사용하여 `멀티쓰레드`처럼 작동.
     *              Update() 호출 시 yield return 할 코루틴이 있는지 확인. (Script_Lifecycle_Flowchart 참고)
     * 
     * - 스레드와의 차이점 : 1. 스레드는 선점형(Preemptive), 코루틴은 비선점형(Non-Premmptive)
     *                       2. 스레드는 OS가 스케줄링, 코루틴은 유저레벨 스레드
     *                       3. 멀티스레드는 스레드가 2개 이상, 코루틴은 단일스레드
     * 
     * - 서브루틴(subroutine) : 진입점과 중단점이 각각 하나씩인 코루틴
     * 
     * - IEnumerable 인터페이스 :  단순히 GetEnumerator()를 구현하기 위한 인터페이스
     * public interface IEnumerable{      
     *     IEnumerator GetEnumerator();    // 내부 데이터를 foreach 같은 것으로 열거할 수 있다
     * }
     * 
     * - IEnumerator 함수 : 코루틴
     * public interface IEnumerator{   
     *     object Current { get; }
     *     bool MoveNext();
     *     void Reset();
     * } 
     *********************************************************/
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
        // * 요약 : 지정한 URL에 데이터를 POST 형식으로 Request를 보내고 그 컨텐츠를 받아옴. JSON으로 보낸 메시지이므로 웹페이지에서 JSON으로 받는 경우에만 작동
        // * 반환값 : 새로운 WWW 오브젝트(복사가 일어남). 컨텐츠 다운로드 완료시, 생성된 오브젝트로부터 그 결과를 가져올 수 있다(fetch)
        // * 문제점 : 웹 요청에 대한 반응이 바로 오지 않아서 일시적으로 유니티가 멈춤( = WWW를 OnClick으로 구현하면 안되는 이유!)
        //          -> Yield Process(유저레벨 스레드)로 빠져서 웹 처리를 하도록 해야함 = 유니티에서는 코루틴(Coroutine)
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
                Debug.Log("RESPONSE : url:" + SendData.URL() + " | ResultCode:" + ResultCode + " | ResultMsg:" + ResultMsg);
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
}