<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #0078D4;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border: 1px solid #dee2e6;
            border-top: none;
        }
        .task-details {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            border-left: 4px solid #0078D4;
        }
        .task-details h3 {
            margin-top: 0;
            color: #0078D4;
        }
        .detail-row {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
            display: inline-block;
            width: 120px;
        }
        .detail-value {
            color: #333;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #0078D4;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">✓ Task Assigned</h1>
    </div>
    
    <div class="content">
        <p>Hi <strong>$Assignee.FirstName</strong>,</p>
        
        <p>You have been assigned a new task<% if $AssignedBy %> by <strong>$AssignedBy.Name</strong><% end_if %>.</p>
        
        <div class="task-details">
            <h3>$Task.Title</h3>
            
            <% if $Task.Description %>
            <p>$Task.Description</p>
            <% end_if %>
            
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value">$Task.StatusNice</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Priority:</span>
                <span class="detail-value">$Task.Priority</span>
            </div>
            
            <% if $Task.DueDate %>
            <div class="detail-row">
                <span class="detail-label">Due Date:</span>
                <span class="detail-value">$Task.DueDate.Nice</span>
            </div>
            <% end_if %>
            
            <% if $Task.RelatedDisplay %>
            <div class="detail-row">
                <span class="detail-label">Related To:</span>
                <span class="detail-value">$Task.RelatedDisplay</span>
            </div>
            <% end_if %>
        </div>
        
        <a href="$TaskLink" class="button">View Task</a>
        
        <p style="margin-top: 20px; font-size: 14px; color: #666;">
            You can add comments, update the status, or view more details by clicking the button above.
        </p>
    </div>
    
    <div class="footer">
        <p>This is an automated notification from your task management system.</p>
    </div>
</body>
</html>
