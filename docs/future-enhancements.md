# SilverStripe Tasks - Future Enhancements

## Notification System Improvements

### Multi-Person Comment Notifications
**Priority:** High  
**Status:** Planned

**Current Behavior:**
When a comment is added to a task, notifications are sent to:
- The task assignee (if different from comment author)
- The task creator (if different from both assignee and author)

**Desired Behavior:**
When a comment is added to a task, notifications should be sent to:
- The task assignee (if different from comment author)
- The task creator (if different from both assignee and author)
- **All previous commenters** (threaded conversation participants, excluding the current comment author)

**Implementation Notes:**
- Query all unique TaskComment authors for the task
- Deduplicate recipients (assignee, creator, previous commenters)
- Exclude the current comment author from the recipient list
- Consider adding a user preference for comment notification opt-in/opt-out
- Maintain existing behavior of not sending self-notifications

**Benefits:**
- Enables true threaded discussions
- All conversation participants stay informed
- Mimics standard collaboration tool behavior (Slack, GitHub, Jira, etc.)

**Technical Considerations:**
- Performance: Limit notifications if comment thread becomes very large (e.g., >50 unique participants)
- Email templates: May need different subject line for "reply to your comment" vs "new comment on task"
- Database queries: Efficient `GROUP BY` on TaskComment.AuthorID with IN clause for filtering

## Additional Enhancement Ideas

### Task Watchers/Followers
**Priority:** Medium  
**Status:** Future consideration

Allow users to explicitly "watch" or "follow" tasks they're interested in, even if not assigned or participating in comments.

### Mention System (@username)
**Priority:** Medium  
**Status:** Future consideration

Allow mentioning specific users in comments using @username syntax to send targeted notifications.

### Digest Email Option
**Priority:** Low  
**Status:** Future consideration

Allow users to opt for daily/weekly digest emails instead of immediate notifications for less urgent updates.

### Mobile Push Notifications
**Priority:** Low  
**Status:** Future consideration

Integrate with mobile notification services for real-time alerts.

---

**Document Version:** 1.0  
**Last Updated:** November 4, 2025
