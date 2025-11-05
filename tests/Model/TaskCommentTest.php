<?php

namespace Dynamic\Tasks\Tests\Model;

use Dynamic\Tasks\Model\Task;
use Dynamic\Tasks\Model\TaskComment;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;

/**
 * Tests for TaskComment DataObject
 */
class TaskCommentTest extends SapphireTest
{
    protected static $fixture_file = '../fixtures.yml';

    protected static $extra_dataobjects = [
        Task::class,
        TaskComment::class,
    ];

    /**
     * Test that a TaskComment can be created
     */
    public function testCanCreateTaskComment()
    {
        $task = Task::create();
        $task->Title = 'Test Task';
        $task->write();

        $author = Member::create();
        $author->FirstName = 'Test';
        $author->Surname = 'Author';
        $author->Email = 'test.author@example.com';
        $author->write();

        $comment = TaskComment::create();
        $comment->Comment = 'This is a test comment';
        $comment->TaskID = $task->ID;
        $comment->AuthorID = $author->ID;
        $comment->write();

        $this->assertTrue($comment->exists());
        $this->assertEquals('This is a test comment', $comment->Comment);
        $this->assertEquals($task->ID, $comment->TaskID);
        $this->assertEquals($author->ID, $comment->AuthorID);
    }

    /**
     * Test comment is associated with correct task
     */
    public function testCommentIsAssociatedWithTask()
    {
        $task = Task::create();
        $task->Title = 'Parent Task';
        $task->write();

        $author = Member::create();
        $author->Email = 'commenter@example.com';
        $author->write();

        $comment = TaskComment::create();
        $comment->Comment = 'Associated comment';
        $comment->TaskID = $task->ID;
        $comment->AuthorID = $author->ID;
        $comment->write();

        $this->assertEquals($task->ID, $comment->Task()->ID);
        $this->assertEquals('Parent Task', $comment->Task()->Title);
    }

    /**
     * Test comment has correct author
     */
    public function testCommentHasCorrectAuthor()
    {
        $task = Task::create();
        $task->Title = 'Test Task';
        $task->write();

        $author = Member::create();
        $author->FirstName = 'John';
        $author->Surname = 'Commenter';
        $author->Email = 'john.commenter@example.com';
        $author->write();

        $comment = TaskComment::create();
        $comment->Comment = 'Author test comment';
        $comment->TaskID = $task->ID;
        $comment->AuthorID = $author->ID;
        $comment->write();

        $this->assertEquals($author->ID, $comment->Author()->ID);
        $this->assertEquals('John Commenter', $comment->Author()->getName());
    }

    /**
     * Test comment summary truncates long comments
     */
    public function testCommentSummaryTruncatesLongComments()
    {
        $task = Task::create();
        $task->Title = 'Test Task';
        $task->write();

        $author = Member::create();
        $author->Email = 'author@example.com';
        $author->write();

        $longComment = str_repeat('This is a very long comment. ', 10);

        $comment = TaskComment::create();
        $comment->Comment = $longComment;
        $comment->TaskID = $task->ID;
        $comment->AuthorID = $author->ID;
        $comment->write();

        $summary = $comment->getCommentSummary();

        $this->assertLessThanOrEqual(103, strlen($summary), 'Summary should be 100 chars + "..."');
        $this->assertStringEndsWith('...', $summary, 'Long comment summary should end with "..."');
    }

    /**
     * Test comment summary does not truncate short comments
     */
    public function testCommentSummaryKeepsShortComments()
    {
        $task = Task::create();
        $task->Title = 'Test Task';
        $task->write();

        $author = Member::create();
        $author->Email = 'author@example.com';
        $author->write();

        $shortComment = 'Short comment';

        $comment = TaskComment::create();
        $comment->Comment = $shortComment;
        $comment->TaskID = $task->ID;
        $comment->AuthorID = $author->ID;
        $comment->write();

        $summary = $comment->getCommentSummary();

        $this->assertEquals($shortComment, $summary);
        $this->assertStringEndsNotWith('...', $summary);
    }

    /**
     * Test multiple comments can be added to same task
     */
    public function testMultipleCommentsCanBeAddedToSameTask()
    {
        $task = Task::create();
        $task->Title = 'Multi-comment Task';
        $task->write();

        $author1 = Member::create();
        $author1->Email = 'author1@example.com';
        $author1->write();

        $author2 = Member::create();
        $author2->Email = 'author2@example.com';
        $author2->write();

        $comment1 = TaskComment::create();
        $comment1->Comment = 'First comment';
        $comment1->TaskID = $task->ID;
        $comment1->AuthorID = $author1->ID;
        $comment1->write();

        $comment2 = TaskComment::create();
        $comment2->Comment = 'Second comment';
        $comment2->TaskID = $task->ID;
        $comment2->AuthorID = $author2->ID;
        $comment2->write();

        $comments = $task->Comments();
        $this->assertEquals(2, $comments->count());
    }

    /**
     * Test comments are sorted by created date ascending by default
     */
    public function testCommentsAreSortedByCreatedAsc()
    {
        $task = Task::create();
        $task->Title = 'Sorted Comments Task';
        $task->write();

        $author = Member::create();
        $author->Email = 'author@example.com';
        $author->write();

        // Create comments with explicit Created timestamps to ensure different order
        $comment1 = TaskComment::create();
        $comment1->Comment = 'First comment';
        $comment1->TaskID = $task->ID;
        $comment1->AuthorID = $author->ID;
        $comment1->Created = '2023-01-01 12:00:00';
        $comment1->write();

        $comment2 = TaskComment::create();
        $comment2->Comment = 'Second comment';
        $comment2->TaskID = $task->ID;
        $comment2->AuthorID = $author->ID;
        $comment2->Created = '2023-01-01 12:00:01';
        $comment2->write();

        $comments = $task->Comments();
        $this->assertEquals('First comment', $comments->first()->Comment);
        $this->assertEquals('Second comment', $comments->last()->Comment);
    }

    /**
     * Test comment can be edited
     */
    public function testCommentCanBeEdited()
    {
        $task = Task::create();
        $task->Title = 'Test Task';
        $task->write();

        $author = Member::create();
        $author->Email = 'author@example.com';
        $author->write();

        $comment = TaskComment::create();
        $comment->Comment = 'Original comment';
        $comment->TaskID = $task->ID;
        $comment->AuthorID = $author->ID;
        $comment->write();

        $commentID = $comment->ID;

        $comment->Comment = 'Edited comment';
        $comment->write();

        $reloadedComment = TaskComment::get()->byID($commentID);
        $this->assertEquals('Edited comment', $reloadedComment->Comment);
    }

    /**
     * Test comment can be deleted
     */
    public function testCommentCanBeDeleted()
    {
        $task = Task::create();
        $task->Title = 'Test Task';
        $task->write();

        $author = Member::create();
        $author->Email = 'author@example.com';
        $author->write();

        $comment = TaskComment::create();
        $comment->Comment = 'Comment to delete';
        $comment->TaskID = $task->ID;
        $comment->AuthorID = $author->ID;
        $comment->write();

        $commentID = $comment->ID;
        $comment->delete();

        $deletedComment = TaskComment::get()->byID($commentID);
        $this->assertNull($deletedComment, 'Comment should be deleted');
        $this->assertEquals(0, $task->Comments()->count());
    }

    /**
     * Test comment summary strips HTML tags
     */
    public function testCommentSummaryStripsHTMLTags()
    {
        $task = Task::create();
        $task->Title = 'Test Task';
        $task->write();

        $author = Member::create();
        $author->Email = 'author@example.com';
        $author->write();

        $comment = TaskComment::create();
        $comment->Comment = '<p>This is <strong>HTML</strong> comment</p>';
        $comment->TaskID = $task->ID;
        $comment->AuthorID = $author->ID;
        $comment->write();

        $summary = $comment->getCommentSummary();

        $this->assertStringNotContainsString('<p>', $summary);
        $this->assertStringNotContainsString('<strong>', $summary);
        $this->assertStringNotContainsString('</p>', $summary);
        $this->assertStringNotContainsString('</strong>', $summary);
        $this->assertStringContainsString('This is HTML comment', $summary);
    }

    /**
     * Test deleting task cascades to delete comments
     */
    public function testDeletingTaskDeletesComments()
    {
        $task = Task::create();
        $task->Title = 'Task to Delete';
        $task->write();

        $author = Member::create();
        $author->Email = 'author@example.com';
        $author->write();

        $comment = TaskComment::create();
        $comment->Comment = 'Comment on task to delete';
        $comment->TaskID = $task->ID;
        $comment->AuthorID = $author->ID;
        $comment->write();

        $commentID = $comment->ID;
        $task->delete();

        $orphanedComment = TaskComment::get()->byID($commentID);
        $this->assertNull($orphanedComment, 'Comment should be deleted when parent task is deleted');
    }
}
