# 유니티 C# 파트
## ➡ 이벤트 함수
  ![Script_Lifecycle_Flowchart](https://github.com/kbm0996/-SystemLink-UNITYxPHPxDB/blob/master/JPG/Script_Lifecycle_Flowchart.png)
  
  **figure 1. Unity Script Lifecycle Flowchart*
  
**- __Awake()** : 초기화. Start() 함수의 이전 및 프리팹의 인스턴스화 직후에 호출. Start()와 달리 게임 오브젝트가 비활성화 상태라도 호출

**- __OnGUI()** : GUI 이벤트에 따라 프레임마다 여러 차례 호출. 레이아웃 및 리페인트 이벤트가 먼저 처리된 후 레이아웃 및 키보드 / 마우스 이벤트가 각 입력 이벤트에 대해 처리

**- 기타 이벤트 함수** : [유니티 매뉴얼](https://docs.unity3d.com/kr/530/Manual/ExecutionOrder.html) 참고
  
## 🔄 코루틴
Yield Process. 특정 위치에서 실행을 일시 중단하고 다시 시작할 수 있는 여러 진입점을 허용하는 `함수`
동시 실행 루틴이라 부르나, 실제로는 두 가지 흐름을 병렬로 수행하는 것이 아니라 하나의 흐름을 기억했다가 수행

**- 작동 원리** : 코루틴(Coroutine)은 `단일쓰레드`지만 코루틴을 사용하여 `멀티쓰레드`처럼 작동. Update() 호출 시 yield return 할 코루틴이 있는지 확인

**- 스레드와의 차이점** 

- 스레드는 선점형(Preemptive), 코루틴은 비선점형(Non-Premmptive)

- 스레드는 OS가 스케줄링, 코루틴은 유저레벨 스레드

- 멀티스레드는 스레드가 2개 이상, 코루틴은 단일스레드

  ![Unity_One_Frame](https://github.com/kbm0996/-SystemLink-UNITYxPHPxDB/blob/master/JPG/Unity_One_Frame.jpg)
  
  **figure 2. Unity One Frame*
