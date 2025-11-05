<?php

namespace Dynamic\Tasks\Service;

use Dynamic\Tasks\Model\Task;
use Dynamic\Tasks\Model\TaskComment;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Service for sending task-related email notifications
 */
class NotificationService
{
    /**
     * Send notification when a task is assigned to a user
     *
     * @param Task $task
     * @param Member|null $previousAssignee
     * @return bool
     */
    public static function sendTaskAssignedNotification(Task $task, ?Member $previousAssignee = null): bool
    {
        $assignee = $task->AssignedTo();
        
        // Don't send if no assignee or self-assigned
        if (!$assignee || !$assignee->exists()) {
            return false;
        }
        
        // Don't send if assigned to the same person
        if ($previousAssignee && $previousAssignee->ID === $assignee->ID) {
            return false;
        }
        
        // Don't send if the person who created/updated the task is the assignee
        $currentUser = Security::getCurrentUser();
        if ($currentUser && $currentUser->ID === $assignee->ID) {
            return false;
        }
        
        if (!$assignee->Email) {
            return false;
        }
        
        $email = Email::create()
            ->setTo($assignee->Email)
            ->setSubject(sprintf('Task Assigned: %s', $task->Title))
            ->setHTMLTemplate('Dynamic/Tasks/Email/TaskAssigned')
            ->setData([
                'Task' => $task,
                'Assignee' => $assignee,
                'AssignedBy' => $currentUser,
                'TaskLink' => self::getTaskEditLink($task),
            ]);
        
        return $email->send();
    }
    
    /**
     * Send notification when a comment is added to a task
     *
     * @param TaskComment $comment
     * @return bool
     */
    public static function sendCommentAddedNotification(TaskComment $comment): bool
    {
        $task = $comment->Task();
        if (!$task || !$task->exists()) {
            return false;
        }
        
        $author = $comment->Author();
        $assignee = $task->AssignedTo();
        
        // Don't send if no assignee or if assignee is the comment author
        if (!$assignee || !$assignee->exists() || !$assignee->Email) {
            return false;
        }
        
        if ($author && $author->ID === $assignee->ID) {
            return false;
        }
        
        $email = Email::create()
            ->setTo($assignee->Email)
            ->setSubject(sprintf('New Comment on Task: %s', $task->Title))
            ->setHTMLTemplate('Dynamic/Tasks/Email/CommentAdded')
            ->setData([
                'Task' => $task,
                'Comment' => $comment,
                'Author' => $author,
                'Assignee' => $assignee,
                'TaskLink' => self::getTaskEditLink($task),
            ]);
        
        // Also notify the task creator if they're not the author or assignee
        $creator = $task->CreatedBy();
        if ($creator && $creator->exists() && $creator->Email) {
            $isCreatorNotified = $creator->ID === $assignee->ID;
            $isCreatorAuthor = $author && $creator->ID === $author->ID;
            
            if (!$isCreatorNotified && !$isCreatorAuthor) {
                $creatorEmail = Email::create()
                    ->setTo($creator->Email)
                    ->setSubject(sprintf('New Comment on Your Task: %s', $task->Title))
                    ->setHTMLTemplate('Dynamic/Tasks/Email/CommentAdded')
                    ->setData([
                        'Task' => $task,
                        'Comment' => $comment,
                        'Author' => $author,
                        'Assignee' => $creator,
                        'TaskLink' => self::getTaskEditLink($task),
                    ]);
                $creatorEmail->send();
            }
        }
        
        return $email->send();
    }
    
    /**
     * Send notification when task status changes
     *
     * @param Task $task
     * @param string $previousStatus
     * @return bool
     */
    public static function sendStatusChangedNotification(Task $task, string $previousStatus): bool
    {
        // Don't send notification if status hasn't actually changed
        if ($task->Status === $previousStatus) {
            return false;
        }
        
        $recipients = [];
        $currentUser = Security::getCurrentUser();
        
        // Notify assignee (if not the one making the change)
        $assignee = $task->AssignedTo();
        if ($assignee && $assignee->exists() && $assignee->Email) {
            if (!$currentUser || $currentUser->ID !== $assignee->ID) {
                $recipients[$assignee->ID] = $assignee;
            }
        }
        
        // Notify creator (if not the assignee or the one making the change)
        $creator = $task->CreatedBy();
        if ($creator && $creator->exists() && $creator->Email) {
            $isAlreadyNotified = isset($recipients[$creator->ID]);
            $isCurrentUser = $currentUser && $currentUser->ID === $creator->ID;
            
            if (!$isAlreadyNotified && !$isCurrentUser) {
                $recipients[$creator->ID] = $creator;
            }
        }
        
        if (empty($recipients)) {
            return false;
        }
        
        $sent = false;
        foreach ($recipients as $recipient) {
            $email = Email::create()
                ->setTo($recipient->Email)
                ->setSubject(sprintf('Task Status Changed: %s', $task->Title))
                ->setHTMLTemplate('Dynamic/Tasks/Email/StatusChanged')
                ->setData([
                    'Task' => $task,
                    'Recipient' => $recipient,
                    'ChangedBy' => $currentUser,
                    'PreviousStatus' => $previousStatus,
                    'NewStatus' => $task->Status,
                    'TaskLink' => self::getTaskEditLink($task),
                ]);
            
            if ($email->send()) {
                $sent = true;
            }
        }
        
        return $sent;
    }
    
    /**
     * Get the CMS edit link for a task
     *
     * @param Task $task
     * @return string
     */
    protected static function getTaskEditLink(Task $task): string
    {
        return Director::absoluteURL(
            sprintf('/admin/tasks/Dynamic-Tasks-Model-Task/EditForm/field/Dynamic-Tasks-Model-Task/item/%d/edit', $task->ID)
        );
    }
}
