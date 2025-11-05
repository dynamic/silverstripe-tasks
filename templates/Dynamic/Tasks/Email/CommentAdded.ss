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
            background: #28a745;
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
        .task-info {
            background: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            border-left: 4px solid #28a745;
        }
        .task-info h3 {
            margin-top: 0;
            color: #28a745;
        }
        .comment-box {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .comment-author {
            font-weight: bold;
            color: #0078D4;
            margin-bottom: 10px;
        }
        .comment-text {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            font-style: italic;
            border-left: 3px solid #ccc;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #28a745;
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
        <h1 style="margin: 0;">💬 New Comment</h1>
    </div>
    
    <div class="content">
        <p>Hi <strong>$Assignee.FirstName</strong>,</p>
        
        <p><strong>$Author.Name</strong> added a comment to a task.</p>
        
        <div class="task-info">
            <h3>$Task.Title</h3>
            <p style="margin: 5px 0; color: #666;">
                <strong>Status:</strong> $Task.StatusNice &nbsp;|&nbsp; 
                <strong>Priority:</strong> $Task.Priority
            </p>
        </div>
        
        <div class="comment-box">
            <div class="comment-author">$Author.Name commented:</div>
            <div class="comment-text">
                $Comment.Comment
            </div>
            <p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">
                Posted on $Comment.Created.Nice
            </p>
        </div>
        
        <a href="$TaskLink" class="button">View Task & Reply</a>
        
        <p style="margin-top: 20px; font-size: 14px; color: #666;">
            Click the button above to view the full conversation and add your response.
        </p>
    </div>
    
    <div class="footer">
        <p>This is an automated notification from your task management system.</p>
    </div>
</body>
</html>
