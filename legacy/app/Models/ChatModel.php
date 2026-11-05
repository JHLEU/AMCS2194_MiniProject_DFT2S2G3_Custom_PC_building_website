<?php

namespace App\Models;

use Database;
use PDO;
use Throwable;

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/OrderModel.php';

class ChatModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function saveMessage(string $sessionToken, string $sender, string $message, ?int $userId = null): int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO ChatLogs (UserID, SessionToken, Sender, Message, Timestamp)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $sessionToken, $sender, $message]);
            return (int)$this->db->lastInsertId();
        } catch (Throwable $e) {
            return 0;
        }
    }

    public function getSessionLogs(string $sessionToken): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM ChatLogs 
                WHERE SessionToken = ? 
                ORDER BY Timestamp ASC
            ");
            $stmt->execute([$sessionToken]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function deleteMessage(int $chatId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM ChatLogs WHERE ChatID = ?");
            return $stmt->execute([$chatId]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function clearSession(string $sessionToken): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM ChatLogs WHERE SessionToken = ?");
            return $stmt->execute([$sessionToken]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function generateAiResponse(string $userMsg, string $sessionToken): string
    {
        $lower = mb_strtolower(trim($userMsg));

        // 1. Order Tracking Check
        if (preg_match('/(ORD-\d{8}-[A-Za-z0-9]+)/i', $userMsg, $matches)) {
            $orderNo = strtoupper($matches[1]);
            $orderModel = new OrderModel();
            $order = $orderModel->getByOrderNumber($orderNo);
            if ($order) {
                return "🔍 **查询到您的订单详情：**\n" .
                       "• **订单号：** `{$order['OrderNumber']}`\n" .
                       "• **客户姓名：** " . ($order['CustomerName'] ?? 'GOPC 贵宾') . "\n" .
                       "• **当前状态：** **" . $this->translateStatus($order['OrderStatus']) . "**\n" .
                       "• **物流单号：** `{$order['TrackingNumber']}` ({$order['Carrier']})\n" .
                       "• **总金额：** RM " . number_format($order['TotalSellingAmount'], 2) . "\n\n" .
                       "我们的技术工程师正严格按照出厂标准执行组装与烤机测试，您可以前往【订单追踪】页面查看实时动态！";
            } else {
                return "抱歉，在系统中未找到编号为 `{$orderNo}` 的订单。请核对订单号是否准确，或提供您下单时填写的电话号码。";
            }
        }

        // 2. Budget Build Questions
        if (str_contains($lower, '预算') || str_contains($lower, '推荐') || str_contains($lower, '多少钱') || str_contains($lower, 'budget') || str_contains($lower, 'rm')) {
            if (str_contains($lower, '3000') || str_contains($lower, '4000') || str_contains($lower, 'entry') || str_contains($lower, '入门')) {
                return "💡 **RM 3,500 ~ 4,500 级性价比电竞推荐方案：**\n" .
                       "• **CPU：** AMD Ryzen 5 7600X 或 Intel Core i5-14600K\n" .
                       "• **显卡：** NVIDIA RTX 4060 Ti 8GB (畅玩 1080p / 2K 中高画质)\n" .
                       "• **主板：** B760 / B650 高规格主板\n" .
                       "• **内存：** DDR5 6000MHz 32GB (16G*2)\n" .
                       "• **存储：** 1TB PCIe 4.0 M.2 SSD\n" .
                       "• **电源：** 650W ~ 750W 80+ 金牌认证\n\n" .
                       "⚡ 您可直接点击导航栏的【配件组装 (Builder)】进行一键选配与下单！";
            }
            if (str_contains($lower, '7000') || str_contains($lower, '8000') || str_contains($lower, '10000') || str_contains($lower, '旗舰') || str_contains($lower, '4k')) {
                return "🔥 **RM 7,000 ~ 9,500 级 4K 极限旗舰推荐方案：**\n" .
                       "• **CPU：** AMD Ryzen 7 7800X3D (全球公认游戏神 U) 或 Intel i7-14700K\n" .
                       "• **显卡：** NVIDIA RTX 4080 Super 16GB (无压力运行《黑神话：悟空》全景光追与《赛博朋克2077》4K 100+FPS)\n" .
                       "• **散热：** 360mm 旗舰一体式水冷\n" .
                       "• **电源：** 850W ~ 1000W ATX 3.0 全模组电源\n" .
                       "• **机箱：** 双腔全景海景房机箱\n\n" .
                       "您可以点击【CPU 算力测算】查看该组合的零瓶颈评估报告！";
            }
            return "🎯 **JUSPC 热门配置区间指引：**\n" .
                   "1. **轻度电竞级 (RM 3,000 - 4,000)：** i5-14600K / R5 7600X + RTX 4060\n" .
                   "2. **主力 2K 旗舰 (RM 5,500 - 7,000)：** i7-14700K / R7 7800X3D + RTX 4070 Ti Super\n" .
                   "3. **终极 4K 生产力/游戏 (RM 8,000+)：** R7 7800X3D + RTX 4080 Super / 4090\n\n" .
                   "告诉我您的具体预算和主要玩的游戏（如 Valorant、黑神话、剪映 4K 等），我来帮您定制最强性价比搭配！";
        }

        // 3. Bottleneck / CPU / GPU Calculation Questions
        if (str_contains($lower, '瓶颈') || str_contains($lower, 'bottleneck') || str_contains($lower, 'cpu') || str_contains($lower, '功耗') || str_contains($lower, 'tdp') || str_contains($lower, '电源')) {
            return "⚡ **硬件算力与功耗搭配专业建议：**\n" .
                   "• **CPU 散热选择：** i7-14700K 满载功耗可达 253W，强烈建议配备 360mm 水冷；Ryzen 7 7800X3D 能效比极高（TDP 仅 120W），双塔六热管风冷即可完美压制。\n" .
                   "• **电源额定建议：** RTX 4070 系列建议 **750W** 起步；RTX 4080 Super / 4090 建议 **850W ~ 1000W** 原生 PCIe 5.0 12VHPWR 电源。\n\n" .
                   "👉 您可以使用我们网站顶部的 **【CPU 算力测算】** 功能，输入具体硬件获取准确的瓶颈百分比和瓦数分析！";
        }

        // 4. Payment / Installment
        if (str_contains($lower, '支付') || str_contains($lower, 'payment') || str_contains($lower, 'fpx') || str_contains($lower, '分期') || str_contains($lower, 'tng') || str_contains($lower, 'duitnow')) {
            return "💳 **支付方式支持说明：**\n" .
                   "• **FPX 网银转账：** 支持 Maybank2u, CIMB Clicks, Public Bank, Hong Leong, RHB\n" .
                   "• **电子钱包扫码：** Touch 'n Go eWallet, GrabPay, DuitNow QR 即时核验\n" .
                   "• **信用卡 / 借记卡：** Visa, MasterCard, MyDebit\n" .
                   "• **线下门店自提付款：** 支持吉隆坡 Low Yat 旗舰店现场验机提货付款\n\n" .
                   "所有支付均提供系统电子发票 (Invoice) 与即时订单流水号！";
        }

        // 5. Contact / WhatsApp / Location
        if (str_contains($lower, '联系') || str_contains($lower, '电话') || str_contains($lower, 'whatsapp') || str_contains($lower, '地址') || str_contains($lower, '门市') || str_contains($lower, 'contact')) {
            return "📞 **JUSPC 官方客服与实体展厅信息：**\n" .
                   "• **WhatsApp 直连：** [+60 12-111 2222](https://wa.me/60121112222)\n" .
                   "• **咨询热线：** +60 12-111 2222 / +60 3-2144 8899\n" .
                   "• **旗舰店地址：** Lot 4.28, 4th Floor, Plaza Low Yat, Bukit Bintang, 55100 Kuala Lumpur\n" .
                   "• **营业时间：** 周一至周日 10:00 AM - 9:00 PM\n\n" .
                   "欢迎您随时通过 WhatsApp 发送您的配置需求，装机工程师将在 5 分钟内为您一对一解答！";
        }

        // Default Intelligent Fallback
        return "🤖 感谢您的咨询！我是 JUSPC 智能装机顾问。\n" .
               "您可以向我询问：\n" .
               "1. **【硬件搭配】** 例如：“i7-14700K 配 4070Ti 会有瓶颈吗？”\n" .
               "2. **【预算推荐】** 例如：“推荐一套 RM 5000 左右畅玩 3A 大作的主机”\n" .
               "3. **【订单追踪】** 发送您的订单号（如 `ORD-20261101-8891`）即可实时查询流水\n" .
               "4. **【售后与保固】** 咨询整机保固期与上门技术排期服务。";
    }

    private function translateStatus(string $status): string
    {
        $map = [
            'Pending' => '⏳ 待付款 / 订单待确认',
            'Paid' => '💳 已付款，已派发仓库分拣',
            'Assembling' => '🔧 工程师正在定制组装与走线',
            'Testing' => '🧪 3DMark / FurMark 极限烤机测试中',
            'Shipped' => '🚚 已出库发货，快递转运中',
            'Completed' => '✅ 已签收送达，保固已激活',
            'Cancelled' => '❌ 订单已取消'
        ];
        return $map[$status] ?? $status;
    }
}
