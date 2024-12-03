<div class="loading_components">
    <div class="loading_viewer">
        <div class="loading_spinner"></div>
        <p class="loading_text">로딩 중...</p>
    </div>
</div>

<style>
    .loading_components {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;

        .loading_viewer {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }

        .loading_spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--main);
            border-top-color: var(--light-gray);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading_text {
            font-size: 16px;
            color: var(--white);
        }
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>