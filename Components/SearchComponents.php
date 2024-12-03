<div class="search_component">
    <input type="text" class="search_input" onkeydown="if (event.key === 'Enter') searchProduct()">
    <button class="search_button" onclick="searchProduct()">검색</button>
</div>

<script>
    function searchProduct() {
        const searchButton = document.querySelector('.search_button');
        const searchInput = document.querySelector('.search_input');
        const searchValue = searchInput.value.trim();

        // 검색어가 없을 경우
        if (searchValue === '') {
            alert('검색어를 입력해주세요.');
            return;
        }

        // 추가로 함수를 실행시키면 안되니까 막아놓음
        searchInput.onkeydown = function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        };
        searchButton.onclick = function(e) {
            e.preventDefault();
        };

        // 로딩 상태를 true로 설정하고 로딩 표시 시작
        showLoading();

        // 3초 후에 로딩 상태를 false로 변경하고 로딩 표시 제거
        setTimeout(() => {
            hideLoading();

            // 실제 검색 기능 수행
            const products = document.querySelectorAll('.product');
            let anyVisible = false;
            products.forEach(product => {
                const productName = product.querySelector('.product_name').innerText;
                if (productName.includes(searchValue)) {
                    product.style.display = 'block';
                    anyVisible = true;
                } else {
                    product.style.display = 'none';
                }
            });

            if (!anyVisible) {
                alert('검색 결과가 없습니다.');
            }

            // 검색 버튼 클릭 이벤트 다시 등록
            searchButton.onclick = searchProduct;
            searchInput.onkeydown = function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchProduct();
                }
            };
        }, 1000); // 3초
    }

    function showLoading() {
        // 로딩 표시 요소 생성
        const loadingComponents = document.querySelector('.loading_components');
        loadingComponents.style.display = 'flex';
    }

    function hideLoading() {
        const loadingComponents = document.querySelector('.loading_components');
        loadingComponents.style.display = 'none';
    }
</script>

<style>
    .search_component {
        display: flex;

        .search_input {
            width: 200px;
            height: 40px;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            padding: 0 8px;
            font-size: 16px;
        }

        .search_button {
            border: 1px solid var(--light-gray);
            width: 80px;
            height: 40px;
            background-color: var(--black);
            color: white;
            border-radius: 4px;
            margin-left: 8px;
            cursor: pointer;
        }

        .search_button:hover {
            border-color: var(--light-gray-hover);
            background-color: var(--black-hover);
        }

        .search_button:active {
            border-color: var(--light-gray-active);
            background-color: var(--black-active);
        }
    }
</style>