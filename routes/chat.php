<?php

get('/chat', function() {
    $user = requireAuth('/auth/login');
    $threads = []; $active = null; $messages = [];
    if ($user['role'] === 'customer') {
        $threads = dbAll("SELECT t.*, 'Hỗ trợ khách hàng' AS title FROM chat_threads t
            WHERE t.customer_id=? AND t.partner_id=0 ORDER BY t.last_message_at DESC NULLS LAST", [$user['id']]);
    } elseif (in_array($user['role'], ['admin','staff'])) {
        $threads = dbAll("SELECT t.*, u.full_name, u.email, COALESCE(u.full_name, 'Khách hàng') AS title FROM chat_threads t
            LEFT JOIN users u ON u.id=t.customer_id
            ORDER BY t.last_message_at DESC NULLS LAST");
    }

    if (!empty($_GET['thread'])) {
        $tid = intval($_GET['thread']);
        $t = dbGet("SELECT * FROM chat_threads WHERE id=?", [$tid]);
        if ($t) {
            $canAccess = false;
            if ($user['role']==='customer' && $t['customer_id']==$user['id']) $canAccess = true;
            if (in_array($user['role'], ['admin','staff'])) $canAccess = true;

            if ($canAccess) {
                $active = $t;
                if ($user['role']==='customer') {
                    $active['title'] = 'Hỗ trợ khách hàng';
                } else {
                    $cust = dbGet('SELECT full_name, email FROM users WHERE id=?', [$t['customer_id']]);
                    $active['title'] = $cust['full_name'] ?? 'Khách hàng';
                    $active['full_name'] = $cust['full_name'] ?? 'Khách hàng';
                }
                $messages = dbAll("SELECT m.*, COALESCE(u.full_name, 'Admin') as sender_name FROM chat_messages m
                    LEFT JOIN users u ON u.id=m.sender_user_id
                    WHERE m.thread_id=? ORDER BY m.created_at ASC LIMIT 200", [$tid]);

                // Mark as read
                if ($user['role']==='customer') {
                    dbRun('UPDATE chat_threads SET customer_unread=0 WHERE id=?', [$tid]);
                } else {
                    dbRun('UPDATE chat_threads SET partner_unread=0 WHERE id=?', [$tid]); // we keep partner_unread for admin
                }
            }
        }
    }
    
    // Add product context if requested
    if (!empty($_GET['product'])) {
        $active['product_id'] = intval($_GET['product']);
    }
    
    // Render view based on role
    if (in_array($user['role'], ['admin','staff'])) {
        view('admin/chat', ['title'=>'Tin nhắn', 'threads'=>$threads, 'active'=>$active, 'messages'=>$messages]);
    } else {
        view('chat/index', ['title'=>'Tin nhắn', 'threads'=>$threads, 'active'=>$active, 'messages'=>$messages]);
    }
});

post('/chat/send', function() {
    $user = requireAuth('/auth/login'); csrfCheck();
    $tid  = intval($_POST['thread_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    $attachPath = null;

    if (!$tid) { flash('error','Thread không hợp lệ'); redirect('/chat'); return; }
    if (!$content && empty($_FILES['attachment']['name'])) {
        flash('error','Vui lòng nhập nội dung'); redirect('/chat?thread='.$tid); return;
    }

    $t = dbGet("SELECT * FROM chat_threads WHERE id=?", [$tid]);
    if (!$t) { redirect('/chat'); return; }

    // Permission check
    $ok = false;
    if ($user['role']==='customer' && $t['customer_id']==$user['id']) $ok = true;
    if (in_array($user['role'], ['admin','staff'])) $ok = true;
    if (!$ok) { flash('error','Bạn không có quyền'); redirect('/chat'); return; }

    // Handle image/file upload
    if (!empty($_FILES['attachment']['name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/png','image/gif','image/webp','application/pdf'];
        $mime = mime_content_type($_FILES['attachment']['tmp_name']);
        if (in_array($mime, $allowed)) {
            $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
            $fname = 'chat_' . $tid . '_' . time() . '_' . rand(100,999) . '.' . strtolower($ext);
            $dest = '/var/lib/coolingsystems/uploads/chat/' . $fname;
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) {
                $attachPath = $fname;
                if (!$content) $content = '[Ảnh]';
            }
        }
    }

    $role = in_array($user['role'], ['admin','staff']) ? 'admin' : $user['role'];
    // FIX: use sender_user_id (correct column name)
    dbInsert("INSERT INTO chat_messages (thread_id, sender_user_id, sender_role, content, attachment_path) VALUES (?,?,?,?,?)",
        [$tid, $user['id'], $role, $content, $attachPath]);

    $preview = strlen($content) > 60 ? mb_substr($content,0,60).'…' : $content;
    if ($role==='customer') {
        dbRun("UPDATE chat_threads SET last_message=?, last_message_at=datetime('now'), partner_unread=partner_unread+1 WHERE id=?",
            [$preview, $tid]);
    } else {
        dbRun("UPDATE chat_threads SET last_message=?, last_message_at=datetime('now'), customer_unread=customer_unread+1 WHERE id=?",
            [$preview, $tid]);
    }
    redirect('/chat?thread='.$tid);
});

post('/chat/start', function() {
    $user = requireAuth('/auth/login'); csrfCheck();
    if ($user['role'] !== 'customer') { flash('error','Chỉ khách hàng mới có thể khởi tạo chat'); redirect('/chat'); return; }
    
    // partner_id is not used anymore, we use 0 to indicate system/admin
    $partnerId = 0; 

    $existing = dbGet("SELECT id FROM chat_threads WHERE customer_id=? AND partner_id=0", [$user['id']]);
    $tid = 0;
    if ($existing) {
        $tid = $existing['id'];
    } else {
        $tid = dbInsert("INSERT INTO chat_threads (customer_id, partner_id) VALUES (?,0)", [$user['id']]);
    }
    
    $product_param = '';
    if (!empty($_POST['product_id'])) {
        $product_param = '&product=' . intval($_POST['product_id']);
    }
    
    redirect('/chat?thread='.$tid . $product_param);
});

// AJAX: get new messages (polling)
get('/chat/poll', function() {
    header('Content-Type: application/json');
    $user = requireAuth('/auth/login');
    $tid = intval($_GET['thread'] ?? 0);
    $after = intval($_GET['after'] ?? 0);
    if (!$tid) { echo json_encode([]); exit; }

    $t = dbGet("SELECT * FROM chat_threads WHERE id=?", [$tid]);
    if (!$t) { echo json_encode([]); exit; }

    $msgs = dbAll("SELECT m.*, u.full_name as sender_name FROM chat_messages m
        LEFT JOIN users u ON u.id=m.sender_user_id
        WHERE m.thread_id=? AND m.id > ? ORDER BY m.created_at ASC LIMIT 50",
        [$tid, $after]);

    // Mark read
    if ($user['role']==='customer') dbRun('UPDATE chat_threads SET customer_unread=0 WHERE id=?', [$tid]);
    else dbRun('UPDATE chat_threads SET partner_unread=0 WHERE id=?', [$tid]);

    echo json_encode($msgs);
    exit;
});
