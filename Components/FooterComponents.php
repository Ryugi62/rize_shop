<footer class="footer_component">
    <div class="footer_viewer">
        <div class="footer_top">
            <div class="footer_logo">
                <h2>RIZZ</h2>
                <p>Rise to Your Style</p>
            </div>
            <div class="footer_links">
                <ul>
                    <li><a href="/about">회사 소개</a></li>
                    <li><a href="/terms">이용 약관</a></li>
                    <li><a href="/privacy">개인정보 처리방침</a></li>
                    <li><a href="/faq">자주 묻는 질문</a></li>
                </ul>
            </div>
            <div class="footer_social">
                <a href="https://www.instagram.com/crew_rizz/" target="_blank">Instagram</a>
            </div>
        </div>
        <div class="footer_bottom">
            <p>© 2024 RIZZ. All rights reserved.</p>
            <p>대표: 홍길동 | 사업자등록번호: 123-45-67890 | 통신판매업 신고번호: 제2024-창원-0001호</p>
            <p>주소: 경상남도 창원시 대학로 123번길 45 | 고객센터: 080-123-4567 | 이메일: support@rizz.com</p>
        </div>
    </div>
</footer>

<style>
    .footer_component {
        margin-top: 32px;
        background-color: var(--light-black);
        color: var(--white);
        font-family: Arial, sans-serif;

        .footer_viewer {
            margin: 0 auto;
            padding: 32px 16px;
            max-width: 1200px;
        }

        .footer_top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--gray);
        }

        .footer_logo h2 {
            font-size: 24px;
            color: var(--main);
        }

        .footer_logo p {
            font-size: 14px;
            color: var(--gray);
        }

        .footer_links ul {
            list-style: none;
            padding: 0;
        }

        .footer_links li {
            margin-bottom: 8px;
        }

        .footer_links a {
            color: var(--white);
            text-decoration: none;
            font-size: 14px;
        }

        .footer_links a:hover {
            color: var(--primary-color);
        }

        .footer_social a {
            margin-right: 12px;
            color: var(--white);
            text-decoration: none;
            font-size: 14px;
        }

        .footer_social a:hover {
            color: var(--primary-color);
        }

        .footer_bottom {
            margin-top: 16px;
            font-size: 12px;
            color: var(--gray);
            line-height: 1.5;
        }

        .footer_bottom p {
            margin: 4px 0;
        }

    }
</style>