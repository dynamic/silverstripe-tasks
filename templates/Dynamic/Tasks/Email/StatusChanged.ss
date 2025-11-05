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
            background: #ffc107;
            color: #333;
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
            border-left: 4px solid #ffc107;
        }
        .task-details h3 {
            margin-top: 0;
            color: #333;
        }
        .status-change {
            background: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
            border: 1px solid #ffc107;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
        }
        .status-old {
            background: #e9ecef;
            color: #495057;
        }
        .status-new {
            background: #28a745;
            color: white;
        }
        .arrow {
            display: inline-block;
            margin: 0 15px;
            font-size: 20px;
            color: #666;
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
            background: #ffc107;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
            font-weight: bold;
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
        <h1 style="margin: 0;">🔄 Task Status Changed</h1>
    </div>
    
    <div class="content">
        <p>Hi <strong>$Recipient.FirstName</strong>,</p>
        
        <p>The status of a task has been updated<% if $ChangedBy %> by <strong>$ChangedBy.Name</strong><% end_if %>.</p>
        
        <div class="task-details">
            <h3>$Task.Title</h3>
            
            <div class="status-change">
                <strong>Status Update:</strong><br>
                <div style="margin-top: 10px;">
                    <% if $PreviousStatus == "NotStarted" %>
                        <span class="status-badge status-old">Not Started</span>
                    <% else_if $PreviousStatus == "InProgress" %>
                        <span class="status-badge status-old">In Progress</span>
                    <% else_if $PreviousStatus == "Complete" %>
                        <span class="status-badge status-old">Complete</span>
                    <% else_if $PreviousStatus == "OnHold" %>
                        <span class="status-badge status-old">On Hold</span>
                    <% else_if $PreviousStatus == "Cancelled" %>
                        <span class="status-badge status-old">Cancelled</span>
                    <% else %>
                        <span class="status-badge status-old">$PreviousStatus</span>
                    <% end_if %>
                    
                    <span class="arrow">→</span>
                    
                    <% if $NewStatus == "NotStarted" %>
                        <span class="status-badge status-new">Not Started</span>
                    <% else_if $NewStatus == "InProgress" %>
                        <span class="status-badge status-new">In Progress</span>
                    <% else_if $NewStatus == "Complete" %>
                        <span class="status-badge status-new">Complete</span>
                    <% else_if $NewStatus == "OnHold" %>
                        <span class="status-badge status-new">On Hold</span>
                    <% else_if $NewStatus == "Cancelled" %>
                        <span class="status-badge status-new">Cancelled</span>
                    <% else %>
                        <span class="status-badge status-new">$NewStatus</span>
                    <% end_if %>
                </div>
            </div>
            
            <% if $Task.Description %>
            <p style="color: #666;">$Task.Description</p>
            <% end_if %>
            
            <div class="detail-row">
                <span class="detail-label">Priority:</span>
                <span class="detail-value">$Task.Priority</span>
            </div>
            
            <% if $Task.AssignedTo %>
            <div class="detail-row">
                <span class="detail-label">Assigned To:</span>
                <span class="detail-value">$Task.AssignedTo.Name</span>
            </div>
            <% end_if %>
            
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
        
        <a href="$TaskLink" class="button">View Task Details</a>
        
        <p style="margin-top: 20px; font-size: 14px; color: #666;">
            Click the button above to view the full task details and add comments if needed.
        </p>
    </div>
    
    <div class="footer">
        <p>This is an automated notification from your task management system.</p>
    </div>
</body>
</html>
