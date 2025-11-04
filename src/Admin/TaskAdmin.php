<?php

namespace Dynamic\Tasks\Admin;

use Dynamic\Tasks\Model\Task;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Security\Security;

/**
 * ModelAdmin for managing tasks
 */
class TaskAdmin extends ModelAdmin
{
    private static $managed_models = [
        Task::class,
    ];

    private static $url_segment = 'tasks';

    private static $menu_title = 'Tasks';

    private static $menu_icon_class = 'font-icon-checklist';

    public function getList()
    {
        $list = parent::getList();

        // If viewing tasks, add custom filters
        if ($this->modelClass === Task::class) {
            $currentUser = Security::getCurrentUser();
            
            // Check if "My Tasks" filter is active
            $params = $this->getRequest()->requestVar('filter');
            if (isset($params[Task::class]['AssignedToID']) && $params[Task::class]['AssignedToID'] === 'me') {
                if ($currentUser) {
                    $list = $list->filter('AssignedToID', $currentUser->ID);
                }
            }
        }

        return $list;
    }

    public function getSearchContext()
    {
        $context = parent::getSearchContext();

        // Add "My Tasks" quick filter
        if ($this->modelClass === Task::class) {
            $currentUser = Security::getCurrentUser();
            if ($currentUser) {
                $filters = $context->getFields();
                $assignedFilter = $filters->dataFieldByName('AssignedToID');
                if ($assignedFilter) {
                    $assignedFilter->setSource(
                        $assignedFilter->getSource() + [
                            'me' => 'My Tasks',
                        ]
                    );
                }
            }
        }

        return $context;
    }
}
