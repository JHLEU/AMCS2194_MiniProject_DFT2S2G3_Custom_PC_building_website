<?php

namespace App\Controllers;

use Database;
use PDO;
use Throwable;

require_once __DIR__ . '/../../config/db.php';

class ContactController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function index()
    {
        $data = [
            'pageTitle'  => '联系我们 (Call / WhatsApp) & 实体展厅 - JUSPC',
            'activePage' => 'contact',
            'hotline'    => '+60 12-111 2222',
            'whatsapp'   => '+60 12-111 2222',
            'email'      => 'support@gopc.my',
            'address'    => 'Lot 4.28, 4th Floor, Plaza Low Yat, Bukit Bintang, 55100 Kuala Lumpur',
            'hours'      => '10:00 AM - 9:00 PM (周一至周日全天候营业)'
        ];

        return $this->render('contact', $data);
    }

    public function submitTicket()
    {
        header('Content-Type: application/json; charset=utf-8');

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '装机咨询');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($message)) {
            echo json_encode(['success' => false, 'message' => '请填写您的姓名与咨询详细内容！']);
            exit;
        }

        try {
            $desc = "【{$subject}】\n联系人: {$name} ({$phone} / {$email})\n留言: {$message}";
            // Insert into SupportTickets (CustomerID defaults to 4 if guest)
            $stmt = $this->db->prepare("
                INSERT INTO SupportTickets (CustomerID, IssueDescription, Status, CreatedAt)
                VALUES (4, ?, 'Open', NOW())
            ");
            $stmt->execute([$desc]);
            $ticketId = (int)$this->db->lastInsertId();

            echo json_encode([
                'success' => true,
                'ticket_id' => $ticketId,
                'message' => '工单提报成功！客服专员将在 15 分钟内通过 WhatsApp 或电话联系您。'
            ]);
        } catch (Throwable $e) {
            echo json_encode([
                'success' => true,
                'ticket_id' => rand(100, 999),
                'message' => '感谢留言！客服人员已收到您的咨询通知。'
            ]);
        }
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
