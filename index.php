<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RISZE - SHOP</title>

    <!-- import style -->
    <link rel="stylesheet" href="./style.css">

</head>

<body>
    <?php include("./Components/HeaderComponents.php") ?>

    <main>
        <div class="index_view">
            <div class="vedio_box">
                <video src="./assets/video/main_vedio.mp4" poster="./assets/images/main_video_poster.png" autoplay loop muted>
                    영상을 불러올 수 없습니다.
                </video>
                <!-- 텍스트 애니메이션 추가 -->
                <div class="text_animation">
                    <div class="line odd">Join the rizz Crew!</div>
                    <div class="line even">Join the rizz Crew!</div>
                    <div class="line odd">Join the rizz Crew!</div>
                    <div class="line even">Join the rizz Crew!</div>
                </div>
            </div>

            <div class="index_view__left">
                <div class="index_view__left__title">
                    <h1>WELCOME TO RISZE SHOP</h1>
                </div>
            </div>
        </div>
    </main>
</body>

</html>

<style>
    main {
        .index_view {
            display: flex;
            max-width: 1200px;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            margin: 0 auto;
            padding: 0 16px;
        }

        .vedio_box {
            position: relative;
            width: 100%;
            height: 100%;
            /* 필요에 따라 비디오 크기 조절 */
        }

        .vedio_box video {
            width: 100%;
            height: auto;
            display: block;
        }

        /* 텍스트 애니메이션 스타일 */
        .text_animation {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            pointer-events: none;
            /* 텍스트가 비디오 상호작용을 방해하지 않도록 */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .text_animation .line {
            font-size: 30px;
            color: rgba(255, 255, 255, 0.5);
            /* 텍스트 색상 및 투명도 조절 */
            white-space: nowrap;
            margin: 10px 0;
            /* 각 줄 간격 */
            animation-duration: 10s;
            animation-timing-function: linear;
            animation-iteration-count: infinite;
        }

        .text_animation .odd {
            animation-name: moveLeft;
        }

        .text_animation .even {
            animation-name: moveRight;
        }

        /* 애니메이션 키프레임 정의 */

        @keyframes moveLeft {
            0% {
                transform: translateX(100%);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        @keyframes moveRight {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        /* 추가적인 스타일 (선택 사항) */
        .index_view__left__title h1 {
            /* 기존 스타일에 맞게 조정 */
            font-size: 2em;
            color: #333;
            text-align: center;
        }

        /* 반응형 디자인 (선택 사항) */
        @media (max-width: 768px) {
            .text_animation .line {
                font-size: 20px;
                margin: 5px 0;
                animation-duration: 8s;
            }

            .index_view__left__title h1 {
                font-size: 1.5em;
            }
        }
    }
</style>