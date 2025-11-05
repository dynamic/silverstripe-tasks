<?php

namespace Dynamic\Tasks\Model;

use Dynamic\Tasks\Service\NotificationService;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Forms\TextareaField;

/**
 * TaskComment for threaded discussion on tasks
 *
 * @property string $Comment
 * @property int $TaskID
 * @property int $AuthorID
 * @method Task Task()
 * @method Member Author()
 */
class TaskComment extends DataObject
{
    private static $table_name = 'TaskComment';

    private static $singular_name = 'Comment';

    private static $plural_name = 'Comments';

    /**
     * Track if this is a new comment for notification logic
     */
    protected $wasNew = false;

    private static $db = [
        'Comment' => 'Text',
    ];

    private static $has_one = [
        'Task' => Task::class,
        'Author' => Member::class,
    ];

    private static $summary_fields = [
        'Author.Name' => 'Author',
        'CommentSummary' => 'Comment',
        'Created.Nice' => 'Posted',
    ];

    private static $default_sort = 'Created ASC';

    /**
     * Get truncated comment for summary
     */
    public function getCommentSummary(): string
    {
        $comment = strip_tags($this->Comment ?? '');
        return strlen($comment) > 100 ? substr($comment, 0, 100) . '...' : $comment;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(['TaskID', 'AuthorID']);

        $fields->replaceField(
            'Comment',
            TextareaField::create('Comment', 'Comment')
                ->setRows(5)
                ->setDescription('Add your comment or update')
        );

        return $fields;
    }

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // Track if this is a new comment
        $this->wasNew = !$this->isInDB();

        // Set Author on first write
        if (!$this->exists() && !$this->AuthorID) {
            $currentUser = Security::getCurrentUser();
            if ($currentUser) {
                $this->AuthorID = $currentUser->ID;
            }
        }
    }

    protected function onAfterWrite()
    {
        parent::onAfterWrite();

        // Send notification only when new comment is created (not on edits)
        if (isset($this->wasNew) && $this->wasNew) {
            NotificationService::sendCommentAddedNotification($this);
        }
    }

    public function canView($member = null)
    {
        return $this->Task()->canView($member);
    }

    public function canEdit($member = null)
    {
        // Only author or task owner can edit
        $member = $member ?: Security::getCurrentUser();
        if ($member && ($this->AuthorID === $member->ID || $this->Task()->canEdit($member))) {
            return true;
        }
        return parent::canEdit($member);
    }

    public function canDelete($member = null)
    {
        // Only author or task owner can delete
        $member = $member ?: Security::getCurrentUser();
        if ($member && ($this->AuthorID === $member->ID || $this->Task()->canDelete($member))) {
            return true;
        }
        return parent::canDelete($member);
    }

    public function canCreate($member = null, $context = [])
    {
        return parent::canCreate($member, $context);
    }
}
