<?php

namespace Dynamic\Tasks\Tests\Model;

use Dynamic\Tasks\Model\Task;
use Dynamic\Tasks\Model\TaskComment;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;

/**
 * Tests for Task DataObject
 */
class TaskTest extends SapphireTest
{
    protected static $fixture_file = '../fixtures.yml';

    protected static $extra_dataobjects = [
        Task::class,
        TaskComment::class,
    ];

    /**
     * Test that a Task can be created with basic fields
     */
    public function testCanCreateTask()
    {
        $task = Task::create();
        $task->Title = 'Test Task';
        $task->Description = 'This is a test task description';
        $task->Status = 'NotStarted';
        $task->Priority = 'Normal';
        $task->write();

        $this->assertTrue($task->exists());
        $this->assertEquals('Test Task', $task->Title);
        $this->assertEquals('NotStarted', $task->Status);
        $this->assertEquals('Normal', $task->Priority);
    }

    /**
     * Test default status is NotStarted
     */
    public function testDefaultStatusIsNotStarted()
    {
        $task = Task::create();
        $task->Title = 'Test Task';
        $task->write();

        $this->assertEquals('NotStarted', $task->Status);
    }

    /**
     * Test default priority is Normal
     */
    public function testDefaultPriorityIsNormal()
    {
        $task = Task::create();
        $task->Title = 'Test Task';
        $task->write();

        $this->assertEquals('Normal', $task->Priority);
    }

    /**
     * Test all valid status values can be set
     */
    public function testAllStatusValuesAreValid()
    {
        $validStatuses = ['NotStarted', 'InProgress', 'Complete', 'OnHold', 'Cancelled'];

        foreach ($validStatuses as $status) {
            $task = Task::create();
            $task->Title = "Task for {$status}";
            $task->Status = $status;
            $task->write();

            $this->assertEquals($status, $task->Status, "Failed to set status to {$status}");
        }
    }

    /**
     * Test all valid priority values can be set
     */
    public function testAllPriorityValuesAreValid()
    {
        $validPriorities = ['Low', 'Normal', 'High', 'Urgent'];

        foreach ($validPriorities as $priority) {
            $task = Task::create();
            $task->Title = "Task for {$priority}";
            $task->Priority = $priority;
            $task->write();

            $this->assertEquals($priority, $task->Priority, "Failed to set priority to {$priority}");
        }
    }

    /**
     * Test task can be assigned to a member
     */
    public function testCanAssignTaskToMember()
    {
        $member = Member::create();
        $member->FirstName = 'John';
        $member->Surname = 'Doe';
        $member->Email = 'john.doe@example.com';
        $member->write();

        $task = Task::create();
        $task->Title = 'Assigned Task';
        $task->AssignedToID = $member->ID;
        $task->write();

        $this->assertEquals($member->ID, $task->AssignedToID);
        $this->assertEquals('John Doe', $task->AssignedTo()->getName());
    }

    /**
     * Test task can have a creator
     */
    public function testCanSetTaskCreator()
    {
        $creator = Member::create();
        $creator->FirstName = 'Jane';
        $creator->Surname = 'Smith';
        $creator->Email = 'jane.smith@example.com';
        $creator->write();

        $task = Task::create();
        $task->Title = 'Created Task';
        $task->CreatedByID = $creator->ID;
        $task->write();

        $this->assertEquals($creator->ID, $task->CreatedByID);
        $this->assertEquals('Jane Smith', $task->CreatedBy()->getName());
    }

    /**
     * Test task can have a due date
     */
    public function testCanSetDueDate()
    {
        $task = Task::create();
        $task->Title = 'Task with Due Date';
        $task->DueDate = '2025-12-31';
        $task->write();

        $this->assertEquals('2025-12-31', $task->DueDate);
    }

    /**
     * Test polymorphic relationship can be set
     */
    public function testCanSetPolymorphicRelationship()
    {
        $member = Member::create();
        $member->FirstName = 'Test';
        $member->Surname = 'User';
        $member->Email = 'test.user@example.com';
        $member->write();

        $task = Task::create();
        $task->Title = 'Related Task';
        $task->RelatedClass = Member::class;
        $task->RelatedID = $member->ID;
        $task->write();

        $this->assertEquals(Member::class, $task->RelatedClass);
        $this->assertEquals($member->ID, $task->RelatedID);

        $related = $task->Related();
        $this->assertNotNull($related);
        $this->assertEquals($member->ID, $related->ID);
    }

    /**
     * Test task can have comments
     */
    public function testCanAddCommentsToTask()
    {
        $author = Member::create();
        $author->FirstName = 'Comment';
        $author->Surname = 'Author';
        $author->Email = 'comment.author@example.com';
        $author->write();

        $task = Task::create();
        $task->Title = 'Task with Comments';
        $task->write();

        $comment = TaskComment::create();
        $comment->TaskID = $task->ID;
        $comment->AuthorID = $author->ID;
        $comment->Comment = 'This is a test comment';
        $comment->write();

        $comments = $task->Comments();
        $this->assertEquals(1, $comments->count());
        $this->assertEquals('This is a test comment', $comments->first()->Comment);
    }

    /**
     * Test task can have multiple comments
     */
    public function testCanHaveMultipleComments()
    {
        $author = Member::create();
        $author->FirstName = 'Multi';
        $author->Surname = 'Commenter';
        $author->Email = 'multi.commenter@example.com';
        $author->write();

        $task = Task::create();
        $task->Title = 'Task with Multiple Comments';
        $task->write();

        for ($i = 1; $i <= 3; $i++) {
            $comment = TaskComment::create();
            $comment->TaskID = $task->ID;
            $comment->AuthorID = $author->ID;
            $comment->Comment = "Comment number {$i}";
            $comment->write();
        }

        $this->assertEquals(3, $task->Comments()->count());
    }

    /**
     * Test task validation requires a title
     */
    public function testTaskRequiresTitle()
    {
        $task = Task::create();
        $task->Status = 'NotStarted';

        $result = $task->validate();

        $this->assertFalse($result->isValid(), 'Task without title should not be valid');
    }

    /**
     * Test completed at timestamp is set when status changes to Complete
     */
    public function testCompletedAtSetWhenStatusComplete()
    {
        $task = Task::create();
        $task->Title = 'Task to Complete';
        $task->Status = 'InProgress';
        $task->write();

        $this->assertNull($task->CompletedAt);

        $task->Status = 'Complete';
        $task->write();

        $this->assertNotNull($task->CompletedAt, 'CompletedAt should be set when status is Complete');
    }

    /**
     * Test completed at is cleared when status changes from Complete
     */
    public function testCompletedAtClearedWhenStatusChangesFromComplete()
    {
        $task = Task::create();
        $task->Title = 'Completed Task';
        $task->Status = 'Complete';
        $task->write();

        $this->assertNotNull($task->CompletedAt);

        $task->Status = 'InProgress';
        $task->write();

        $this->assertNull($task->CompletedAt, 'CompletedAt should be cleared when status changes from Complete');
    }

    /**
     * Test task can be deleted
     */
    public function testCanDeleteTask()
    {
        $task = Task::create();
        $task->Title = 'Task to Delete';
        $task->write();

        $taskID = $task->ID;
        $task->delete();

        $deletedTask = Task::get()->byID($taskID);
        $this->assertNull($deletedTask, 'Task should be deleted');
    }
}
