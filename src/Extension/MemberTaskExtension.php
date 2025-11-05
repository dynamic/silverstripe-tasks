<?php

namespace Dynamic\Tasks\Extension;

use Dynamic\Tasks\Model\Task;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\ORM\DataList;
use SilverStripe\Security\Member;

/**
 * Extension to add Task listing to Member profile
 *
 * Shows all tasks assigned to the member in their CMS profile
 *
 * @property Member|MemberTaskExtension $owner
 */
class MemberTaskExtension extends Extension
{
    /**
     * Default sort order for task list
     */
    private const TASK_SORT_ORDER = 'DueDate ASC, Priority DESC';

    /**
     * Task status constants to match Task model enum values
     */
    private const STATUS_COMPLETE = 'Complete';
    private const STATUS_CANCELLED = 'Cancelled';

    /**
     * Get all tasks assigned to this member
     */
    public function AssignedTasks(): DataList
    {
        return Task::get()->filter('AssignedToID', $this->owner->ID);
    }

    /**
     * Get count of open tasks assigned to this member
     * Excludes tasks with Complete or Cancelled status
     */
    public function getOpenTaskCount(): int
    {
        return $this->AssignedTasks()
            ->exclude('Status', [self::STATUS_COMPLETE, self::STATUS_CANCELLED])
            ->count();
    }

    /**
     * Add Assigned Tasks tab to Member profile
     */
    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->exists()) {
            // Create a viewer config (read-only from member profile)
            $config = GridFieldConfig_RecordViewer::create();

            // Customize columns
            $dataColumns = $config->getComponentByType(GridFieldDataColumns::class);
            if ($dataColumns) {
                $dataColumns->setDisplayFields([
                    'Title' => 'Task',
                    'StatusNice' => 'Status',
                    'Priority' => 'Priority',
                    'DueDate' => 'Due Date',
                    'RelatedDisplay' => 'Related To',
                    'CreatedBy.Name' => 'Created By',
                    'Created' => 'Created',
                ]);
            }

            $tasksGrid = GridField::create(
                'AssignedTasks',
                'My Assigned Tasks',
                $this->AssignedTasks()->sort(self::TASK_SORT_ORDER),
                $config
            );

            $fields->addFieldToTab('Root.AssignedTasks', $tasksGrid);

            // Add badge to tab if there are open tasks
            $tasksTab = $fields->fieldByName('Root.AssignedTasks');
            if ($tasksTab) {
                $openCount = $this->getOpenTaskCount();
                if ($openCount > 0) {
                    $tasksTab->setTitle("Assigned Tasks ($openCount open)");
                }
            }
        }
    }
}
