<?php
// app/Services/NotificationService.php
namespace App\Services;

class NotificationService
{
    public function notifyReportComment($report, $comment) {
        // Send notification to report author
    }
    
    public function notifyCommentReply($parentComment, $reply) {
        // Send notification to parent comment author
    }
    
    // ... other notification methods
}

// app/Services/MentionService.php  
namespace App\Services;

class MentionService
{
    public function extractMentions(string $text): array {
        preg_match_all('/@(\w+)/', $text, $matches);
        return $matches[1] ?? [];
    }
    
    // ... mention handling logic
}