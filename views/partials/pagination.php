
<?php
/**
 * Render phân trang thông minh - dùng ellipsis khi nhiều trang
 * @param int $currentPage Trang hiện tại (1-based)
 * @param int $totalPages Tổng số trang
 * @param string $baseUrl URL base (không có ?page=)
 * @param array $extraParams Tham số query string bổ sung
 */
function renderPagination(int $currentPage, int $totalPages, string $baseUrl = '', array $extraParams = []): void {
    if ($totalPages <= 1) return;
    
    $extra = '';
    foreach ($extraParams as $k => $v) {
        if ($v !== '' && $k !== 'page') $extra .= '&' . urlencode($k) . '=' . urlencode($v);
    }
    
    $makeUrl = function(int $p) use ($baseUrl, $extra): string {
        $base = $baseUrl ?: strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        return $base . '?page=' . $p . $extra;
    };
    
    // Tính các trang cần hiển thị (window = 2 trang xung quanh current)
    $window = 2;
    $pages = [];
    
    // Luôn hiện trang 1
    $pages[] = 1;
    
    // Ellipsis trước nếu cần
    if ($currentPage - $window > 2) $pages[] = '...';
    
    // Window xung quanh current page
    for ($i = max(2, $currentPage - $window); $i <= min($totalPages - 1, $currentPage + $window); $i++) {
        $pages[] = $i;
    }
    
    // Ellipsis sau nếu cần
    if ($currentPage + $window < $totalPages - 1) $pages[] = '...';
    
    // Luôn hiện trang cuối
    if ($totalPages > 1) $pages[] = $totalPages;
    
    echo '<nav class="pagination-wrap" aria-label="Phân trang">';
    echo '<div class="pagination">';
    
    // Nút Previous
    if ($currentPage > 1) {
        echo '<a href="' . htmlspecialchars($makeUrl($currentPage - 1)) . '" class="pg-btn pg-prev" aria-label="Trang trước">&#8249;</a>';
    } else {
        echo '<span class="pg-btn pg-prev disabled">&#8249;</span>';
    }
    
    // Các trang
    foreach ($pages as $p) {
        if ($p === '...') {
            echo '<span class="pg-btn pg-ellipsis">&#8230;</span>';
        } elseif ($p === $currentPage) {
            echo '<span class="pg-btn pg-current" aria-current="page">' . $p . '</span>';
        } else {
            echo '<a href="' . htmlspecialchars($makeUrl($p)) . '" class="pg-btn">' . $p . '</a>';
        }
    }
    
    // Nút Next
    if ($currentPage < $totalPages) {
        echo '<a href="' . htmlspecialchars($makeUrl($currentPage + 1)) . '" class="pg-btn pg-next" aria-label="Trang sau">&#8250;</a>';
    } else {
        echo '<span class="pg-btn pg-next disabled">&#8250;</span>';
    }
    
    echo '</div>';
    
    // Info tổng
    echo '<div class="pg-info">Trang ' . $currentPage . ' / ' . $totalPages . '</div>';
    echo '</nav>';
}
?>
