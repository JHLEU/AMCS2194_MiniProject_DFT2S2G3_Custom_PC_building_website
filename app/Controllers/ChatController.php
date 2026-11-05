<?php

namespace App\Controllers;

use App\Models\ChatModel;

class ChatController
{
    private ChatModel $chatModel;

    public function __construct()
    {
        $this->chatModel = new ChatModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index()
    {
        $sessionToken = $_SESSION['chat_session_token'] ?? null;
        if (!$sessionToken) {
            $sessionToken = 'sess_' . bin2hex(random_bytes(8));
            $_SESSION['chat_session_token'] = $sessionToken;
        }

        $logs = $this->chatModel->getSessionLogs($sessionToken);

        $data = [
            'pageTitle'    => 'AI 硬件智能顾问 (24/7 Live AI Chat) - JUSPC',
            'activePage'   => 'chat',
            'sessionToken' => $sessionToken,
            'logs'         => $logs,
        ];

        return $this->render('chat', $data);
    }

    public function sendMessage()
    {
        header('Content-Type: application/json; charset=utf-8');

        $sessionToken = $_POST['session_token'] ?? ($_SESSION['chat_session_token'] ?? 'sess_default');
        $userMsg = trim($_POST['message'] ?? '');

        if (empty($userMsg)) {
            echo json_encode(['success' => false, 'reply' => '请输入您的问题！']);
            exit;
        }

        // 1. Save user message to ChatLogs (CRUD - Create)
        $userMsgId = $this->chatModel->saveMessage($sessionToken, 'User', $userMsg);

        // 2. Generate AI response
        $aiReply = $this->chatModel->generateAiResponse($userMsg, $sessionToken);

        // 3. Save AI message to ChatLogs (CRUD - Create)
        $aiMsgId = $this->chatModel->saveMessage($sessionToken, 'AI', $aiReply);

        echo json_encode([
            'success'     => true,
            'user_msg_id' => $userMsgId,
            'ai_msg_id'   => $aiMsgId,
            'reply'       => $aiReply,
            'timestamp'   => date('H:i')
        ]);
        exit;
    }

    public function getHistory()
    {
        header('Content-Type: application/json; charset=utf-8');
        $sessionToken = $_GET['session_token'] ?? ($_SESSION['chat_session_token'] ?? 'sess_default');
        $logs = $this->chatModel->getSessionLogs($sessionToken);
        echo json_encode($logs);
        exit;
    }

    public function clearHistory()
    {
        header('Content-Type: application/json; charset=utf-8');
        $sessionToken = $_POST['session_token'] ?? ($_SESSION['chat_session_token'] ?? 'sess_default');
        $ok = $this->chatModel->clearSession($sessionToken);
        echo json_encode(['success' => $ok]);
        exit;
    }

    public function deleteMessage()
    {
        header('Content-Type: application/json; charset=utf-8');
        $chatId = (int)($_POST['chat_id'] ?? 0);
        $ok = ($chatId > 0) ? $this->chatModel->deleteMessage($chatId) : false;
        echo json_encode(['success' => $ok]);
        exit;
    }

    private function render(string $view, array $data = [])
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "View file [{$view}] not found.";
        }
    }
}
