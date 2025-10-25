<?php

/**
 * WhatsApp Integration Module for Task Management System
 * Structured to support future webhook integration with WhatsApp Business API
 * 
 * Features:
 * - Task assignment notifications
 * - Due date reminders
 * - Task completion updates
 * - Webhook handling for WhatsApp messages
 */

class WhatsAppNotificationService {
    
    private $apiUrl;
    private $accessToken;
    private $phoneNumberId;
    
    public function __construct($config = []) {
        $this->apiUrl = $config['api_url'] ?? 'https://graph.facebook.com/v17.0/';
        $this->accessToken = $config['access_token'] ?? '';
        $this->phoneNumberId = $config['phone_number_id'] ?? '';
    }
    
    /**
     * Send task assignment notification
     */
    public function sendTaskAssignmentNotification($userPhone, $taskData) {
        $message = $this->formatTaskAssignmentMessage($taskData);
        return $this->sendMessage($userPhone, $message);
    }
    
    /**
     * Send due date reminder
     */
    public function sendDueDateReminder($userPhone, $taskData) {
        $message = $this->formatDueDateReminderMessage($taskData);
        return $this->sendMessage($userPhone, $message);
    }
    
    /**
     * Send task completion notification
     */
    public function sendTaskCompletionNotification($userPhone, $taskData) {
        $message = $this->formatTaskCompletionMessage($taskData);
        return $this->sendMessage($userPhone, $message);
    }
    
    /**
     * Format task assignment message
     */
    private function formatTaskAssignmentMessage($taskData) {
        return "🎯 *New Task Assigned*\n\n" .
               "📋 *Title:* {$taskData['title']}\n" .
               "📝 *Description:* {$taskData['description']}\n" .
               "📅 *Due Date:* {$taskData['due_date']}\n" .
               "⚡ *Priority:* " . ucfirst($taskData['priority']) . "\n\n" .
               "Please complete this task by the due date.";
    }
    
    /**
     * Format due date reminder message
     */
    private function formatDueDateReminderMessage($taskData) {
        return "⏰ *Task Due Date Reminder*\n\n" .
               "📋 *Task:* {$taskData['title']}\n" .
               "📅 *Due:* {$taskData['due_date']}\n" .
               "⚡ *Priority:* " . ucfirst($taskData['priority']) . "\n\n" .
               "Don't forget to complete this task!";
    }
    
    /**
     * Format task completion message
     */
    private function formatTaskCompletionMessage($taskData) {
        return "✅ *Task Completed*\n\n" .
               "📋 *Task:* {$taskData['title']}\n" .
               "Great job on completing this task!";
    }
    
    /**
     * Send WhatsApp message via API
     */
    private function sendMessage($phoneNumber, $message) {
        // Structure for future implementation
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'type' => 'text',
            'text' => ['body' => $message]
        ];
        
        // Log the notification for now (implement actual API call later)
        error_log("WhatsApp Notification: " . json_encode($data));
        
        return ['success' => true, 'message_id' => 'pending_' . uniqid()];
    }
    
    /**
     * Handle incoming WhatsApp webhook
     */
    public function handleWebhook($webhookData) {
        // Webhook handler for future implementation
        error_log("WhatsApp Webhook received: " . json_encode($webhookData));
        
        // Parse webhook data and respond to user messages
        if (isset($webhookData['messages'])) {
            foreach ($webhookData['messages'] as $message) {
                $this->processIncomingMessage($message);
            }
        }
        
        return ['status' => 'processed'];
    }
    
    /**
     * Process incoming WhatsApp message
     */
    private function processIncomingMessage($message) {
        $text = strtolower($message['text']['body'] ?? '');
        $from = $message['from'];
        
        // Basic command processing
        if (strpos($text, 'tasks') !== false) {
            $this->sendTaskSummary($from);
        } elseif (strpos($text, 'help') !== false) {
            $this->sendHelpMessage($from);
        }
    }
    
    /**
     * Send task summary to user
     */
    private function sendTaskSummary($phoneNumber) {
        $message = "📋 *Your Task Summary*\n\n" .
                  "To get your current tasks, please visit the Task Manager app.\n" .
                  "We'll send you notifications for new assignments and due dates!";
        
        return $this->sendMessage($phoneNumber, $message);
    }
    
    /**
     * Send help message
     */
    private function sendHelpMessage($phoneNumber) {
        $message = "🤖 *Task Manager Bot Help*\n\n" .
                  "Available commands:\n" .
                  "• 'tasks' - Get task summary\n" .
                  "• 'help' - Show this help message\n\n" .
                  "You'll receive automatic notifications for:\n" .
                  "📋 New task assignments\n" .
                  "⏰ Due date reminders\n" .
                  "✅ Task completions";
        
        return $this->sendMessage($phoneNumber, $message);
    }
}

/**
 * Task notification triggers for integration with main API
 */
class TaskNotificationTriggers {
    
    private $whatsappService;
    
    public function __construct($whatsappService) {
        $this->whatsappService = $whatsappService;
    }
    
    /**
     * Trigger notification when task is assigned
     */
    public function onTaskAssigned($taskData, $assigneePhone) {
        if ($assigneePhone) {
            return $this->whatsappService->sendTaskAssignmentNotification($assigneePhone, $taskData);
        }
        return false;
    }
    
    /**
     * Trigger notification for due date reminders
     */
    public function onDueDateReminder($taskData, $assigneePhone) {
        if ($assigneePhone) {
            return $this->whatsappService->sendDueDateReminder($assigneePhone, $taskData);
        }
        return false;
    }
    
    /**
     * Trigger notification when task is completed
     */
    public function onTaskCompleted($taskData, $creatorPhone) {
        if ($creatorPhone) {
            return $this->whatsappService->sendTaskCompletionNotification($creatorPhone, $taskData);
        }
        return false;
    }
}

// Integration points in main API:
// 
// When a task is assigned:
// $whatsappService = new WhatsAppNotificationService($config);
// $notificationTriggers = new TaskNotificationTriggers($whatsappService);
// $notificationTriggers->onTaskAssigned($taskData, $assigneePhone);
//
// For due date reminders (can be triggered by cron job):
// $notificationTriggers->onDueDateReminder($taskData, $assigneePhone);
//
// When task is completed:
// $notificationTriggers->onTaskCompleted($taskData, $creatorPhone);

?>
