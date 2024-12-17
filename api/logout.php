<?php
session_start(); // 세션 시작

// 세션 종료
session_unset();
session_destroy();

// alert 창을 띄우고 "로그아웃 되었습니다." 메시지 출력
echo "<script>
            alert('로그아웃 되었습니다.');
         </script>";

// 메인 페이지로 리디렉션
header("Location: /index.php");
exit();
