<?php

namespace Dynamic\Tasks\Extension;

use Dynamic\Tasks\Model\Task;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDetailForm;

/**
 * Extension to add Task management to any DataObject
 * 
 * Usage: Apply this extension to any DataObject in _config/mysite.yml:
 * ```yaml
 * App\Model\Site:
 *   extensions:
 *     - Dynamic\Tasks\Extension\TaskExtension
 * ```
 */
class TaskExtension extends Extension
{
    /**
     * Get tasks related to this object
     */
    public function Tasks()
    {
        return Task::get()->filter([
            'RelatedClass' => $this->owner->ClassName,
            'RelatedID' => $this->owner->ID,
        ]);
    }

    /**
     * Get count of open tasks
     */
    public function getOpenTaskCount(): int
    {
        return $this->Tasks()->exclude('Status', ['Complete', 'Cancelled'])->count();
    }

    /**
     * Add Tasks tab to CMS
     */
    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->exists()) {
            $config = GridFieldConfig_RecordEditor::create();
            
            // Customize the add new button to pre-set the relationship
            $addButton = $config->getComponentByType(GridFieldAddNewButton::class);
            if ($addButton) {
                $addButton->setButtonName('Add Task');
            }

            // Get the detail form component to set polymorphic values
            $detailForm = $config->getComponentByType(GridFieldDetailForm::class);
            if ($detailForm) {
                $detailForm->setItemRequestClass(TaskDetailForm_ItemRequest::class);
            }

            $tasksGrid = GridField::create(
                'Tasks',
                'Tasks',
                $this->Tasks(),
                $config
            );

            $fields->addFieldToTab('Root.Tasks', $tasksGrid);

            // Add badge to tab if there are open tasks
            $openCount = $this->getOpenTaskCount();
            if ($openCount > 0) {
                $tasksTab = $fields->fieldByName('Root.Tasks');
                if ($tasksTab) {
                    $tasksTab->setTitle("Tasks ($openCount open)");
                }
            }
        }
    }

    /**
     * Update summary fields to show task count
     */
    public function updateSummaryFields(&$fields)
    {
        $fields['OpenTaskCount'] = 'Open Tasks';
    }

    /**
     * Before deleting the owner, delete related tasks
     */
    public function onBeforeDelete()
    {
        foreach ($this->Tasks() as $task) {
            $task->delete();
        }
    }
}

/**
 * Custom GridField ItemRequest to set polymorphic relationship
 */
class TaskDetailForm_ItemRequest extends \SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest
{
    public function doSave($data, $form)
    {
        $record = $this->record;
        
        // Set polymorphic relationship for new records
        if (!$record->exists() && $this->gridField) {
            $owner = $this->gridField->getForm()->getRecord();
            if ($owner && $owner->exists()) {
                $record->RelatedClass = $owner->ClassName;
                $record->RelatedID = $owner->ID;
            }
        }
        
        return parent::doSave($data, $form);
    }
}
