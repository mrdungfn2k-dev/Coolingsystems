// Policies
get('/policies', function() {
    $page = dbGet("SELECT * FROM static_pages WHERE slug='chinh-sach'");
    if (!$page) $page = ['title'=>'Chính sách','body'=>'<p>Nội dung đang được cập nhật.</p>'];
    view('public/static-page', ['title'=>$page['title'],'page'=>$page]);
});
get('/policies/:slug', function($slug) {
    $page = dbGet("SELECT * FROM static_pages WHERE slug=?", [$slug]);
    if (!$page) { http_response_code(404); view('public/404', ['title'=>'Không tìm thấy']); return; }
    view('public/static-page', ['title'=>$page['title'],'page'=>$page]);
});
// Customer chat
get('/customer/chat', function() {
    $user = requireRole('customer', '/auth/login');
    $thread = dbGet("SELECT * FROM chat_threads WHERE customer_user_id=? ORDER BY last_message_at DESC LIMIT 1", [$user['id']]);
    if (!$thread) {
        $threadId = dbInsert("INSERT INTO chat_threads (customer_user_id, subject, status, created_at, last_message_at) VALUES (?, 'Hỗ trợ', 'open', datetime('now','localtime'), datetime('now','localtime'))", [$user['id']]);
        $thread = dbGet("SELECT * FROM chat_threads WHERE id=?", [$threadId]);
    }
    $messages = dbAll("SELECT * FROM chat_messages WHERE thread_id=? ORDER BY created_at ASC", [$thread['id']]);
    dbRun("UPDATE chat_threads SET customer_unread=0 WHERE id=?", [$thread['id']]);
    view('customer/chat', ['title'=>'Chat với Admin','thread'=>$thread,'messages'=>$messages]);
});
