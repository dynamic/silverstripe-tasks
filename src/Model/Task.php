<?php

namespace Dynamic\Tasks\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;

/**
 * Task DataObject for managing tasks attached to any DataObject
 *
 * @property string $Title
 * @property string $Description
 * @property string $Status
 * @property string $Priority
 * @property string $DueDate
 * @property string $CompletedAt
 * @property string $RelatedClass
 * @property int $RelatedID
 * @property int $AssignedToID
 * @property int $CreatedByID
 * @method Member AssignedTo()
 * @method Member CreatedBy()
 * @method DataObject Related()
 * @method \SilverStripe\ORM\HasManyList Comments()
 */
class Task extends DataObject
{
    private static $table_name = 'Task';

    private static $singular_name = 'Task';

    private static $plural_name = 'Tasks';

    private static $db = [
        'Title' => 'Varchar(255)',
        'Description' => 'Text',
        'Status' => 'Enum("NotStarted,InProgress,Complete,OnHold,Cancelled","NotStarted")',
        'Priority' => 'Enum("Low,Normal,High,Urgent","Normal")',
        'DueDate' => 'Date',
        'CompletedAt' => 'Datetime',
        // Polymorphic relationship fields
        'RelatedClass' => 'Varchar(255)',
        'RelatedID' => 'Int',
    ];

    private static $has_one = [
        'AssignedTo' => Member::class,
        'CreatedBy' => Member::class,
    ];

    private static $has_many = [
        'Comments' => TaskComment::class,
    ];

    private static $summary_fields = [
        'Title' => 'Title',
        'StatusNice' => 'Status',
        'PriorityBadge' => 'Priority',
        'AssignedTo.Name' => 'Assigned To',
        'RelatedDisplay' => 'Related To',
        'DueDate.Nice' => 'Due Date',
    ];

    private static $searchable_fields = [
        'Title',
        'Status',
        'Priority',
        'AssignedToID',
        'CreatedByID',
    ];

    private static $default_sort = 'Created DESC';

    /**
     * Get polymorphic related object
     */
    public function Related()
    {
        if ($this->RelatedClass && $this->RelatedID) {
            $class = $this->RelatedClass;
            if (class_exists($class ?? '')) {
                return $class::get()->byID($this->RelatedID);
            }
        }
        return null;
    }

    /**
     * Display string for related object
     */
    public function getRelatedDisplay(): string
    {
        $related = $this->Related();
        if ($related) {
            $type = $related->i18n_singular_name();
            $title = $related->hasMethod('getTitle') ? $related->getTitle() : $related->ID;
            return "$type: $title";
        }
        return 'N/A';
    }

    /**
     * Get nice status label
     */
    public function getStatusNice(): string
    {
        $labels = [
            'NotStarted' => 'Not Started',
            'InProgress' => 'In Progress',
            'Complete' => 'Complete',
            'OnHold' => 'On Hold',
            'Cancelled' => 'Cancelled',
        ];
        return $labels[$this->Status] ?? $this->Status;
    }

    /**
     * Get priority with emoji badge
     */
    public function getPriorityBadge(): string
    {
        $badges = [
            'Low' => '🔵 Low',
            'Normal' => '⚪ Normal',
            'High' => '🟡 High',
            'Urgent' => '🔴 Urgent',
        ];
        return $badges[$this->Priority] ?? $this->Priority;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Remove polymorphic fields from main form
        $fields->removeByName(['RelatedClass', 'RelatedID', 'CompletedAt', 'CreatedByID']);

        // Update field types
        $fields->dataFieldByName('Title')
            ->setDescription('Brief title for the task');

        $fields->replaceField(
            'Description',
            TextareaField::create('Description', 'Description')
                ->setRows(5)
                ->setDescription('Detailed description of what needs to be done')
        );

        $fields->replaceField(
            'Status',
            DropdownField::create('Status', 'Status', [
                'NotStarted' => 'Not Started',
                'InProgress' => 'In Progress',
                'Complete' => 'Complete',
                'OnHold' => 'On Hold',
                'Cancelled' => 'Cancelled',
            ])
        );

        $fields->replaceField(
            'Priority',
            DropdownField::create('Priority', 'Priority', [
                'Low' => 'Low',
                'Normal' => 'Normal',
                'High' => 'High',
                'Urgent' => 'Urgent',
            ])
        );

        $fields->replaceField(
            'DueDate',
            DateField::create('DueDate', 'Due Date')
                ->setDescription('Optional deadline for this task')
        );

        // AssignedTo dropdown
        $fields->replaceField(
            'AssignedToID',
            DropdownField::create('AssignedToID', 'Assign To', Member::get()->map('ID', 'Name'))
                ->setEmptyString('-- Select Member --')
                ->setDescription('Assign this task to a CMS user')
        );

        // Comments GridField
        if ($this->exists()) {
            $commentsConfig = GridFieldConfig_RecordEditor::create();
            $fields->addFieldToTab(
                'Root.Comments',
                GridField::create('Comments', 'Comments', $this->Comments(), $commentsConfig)
            );
        }

        // Show related record info
        if ($this->exists() && $related = $this->Related()) {
            $fields->addFieldToTab(
                'Root.Main',
                $fields->dataFieldByName('Title'),
                \SilverStripe\Forms\LiteralField::create(
                    'RelatedInfo',
                    sprintf(
                        '<div class="alert alert-info">Related to: <strong>%s</strong></div>',
                        $this->getRelatedDisplay()
                    )
                )
            );
        }

        return $fields;
    }

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // Set CreatedBy on first write
        if (!$this->exists() && !$this->CreatedByID) {
            $currentUser = Security::getCurrentUser();
            if ($currentUser) {
                $this->CreatedByID = $currentUser->ID;
            }
        }

        // Set CompletedAt timestamp when status changes to Complete
        if ($this->Status === 'Complete' && !$this->CompletedAt) {
            $this->CompletedAt = date('Y-m-d H:i:s');
        } elseif ($this->Status !== 'Complete') {
            $this->CompletedAt = null;
        }
    }

    public function canView($member = null)
    {
        return parent::canView($member);
    }

    public function canEdit($member = null)
    {
        return parent::canEdit($member);
    }

    public function canDelete($member = null)
    {
        return parent::canDelete($member);
    }

    public function canCreate($member = null, $context = [])
    {
        return parent::canCreate($member, $context);
    }
}
