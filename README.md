# SilverStripe Tasks Module

A flexible task management system for SilverStripe CMS with assignment, threaded comments, and status tracking.

## Features

- Create tasks attached to any DataObject (polymorphic relationships)
- Assign tasks to CMS members
- Threaded comments for collaboration
- Status tracking (Not Started, In Progress, Complete, On Hold, Cancelled)
- Priority levels (Low, Normal, High, Urgent)
- Due date tracking
- Central task dashboard via ModelAdmin
- "My Tasks" filtering

## Installation

This module is installed as a local vendor module. To use it in your project:

1. Apply `TaskExtension` to your DataObjects in `_config/mysite.yml`:

```yaml
App\Model\Site:
  extensions:
    - Dynamic\Tasks\Extension\TaskExtension

App\Model\Server:
  extensions:
    - Dynamic\Tasks\Extension\TaskExtension

App\Model\Client:
  extensions:
    - Dynamic\Tasks\Extension\TaskExtension
```

2. Run `dev/build` to create database tables

## Usage

### Creating Tasks

1. Open any record that has `TaskExtension` applied
2. Navigate to the "Tasks" tab
3. Click "Add Task"
4. Fill in title, description, assign to user, set priority/due date
5. Save

### Managing Tasks

- View all tasks via the "Tasks" admin section in the CMS
- Filter by status, assignee, or related record type
- Add comments to tasks for discussion
- Update status as work progresses

### Workflow Example

```
Manager: Creates task on Site record
  "Review migration notes before proceeding"
  Assigns to Developer
  
Developer: Adds comment
  "Notes look good, DNS contacts added"
  Status → In Progress
  
Manager: Adds comment
  "Perfect, proceed with migration"
  Status → Complete
```

## License

BSD-3-Clause
