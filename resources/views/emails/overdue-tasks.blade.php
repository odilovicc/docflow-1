<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просроченные задачи</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background-color: white;
            padding: 20px;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .task-item {
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 10px 0;
            background-color: #fff5f5;
            border-radius: 4px;
        }
        .task-title {
            font-weight: bold;
            color: #1a202c;
            margin-bottom: 5px;
        }
        .task-document {
            color: #4a5568;
            margin-bottom: 5px;
        }
        .task-deadline {
            color: #dc3545;
            font-weight: bold;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Просроченные задачи</h1>
        </div>
        
        <div class="content">
            <p>Здравствуйте, <strong>{{ $user->name }}</strong>!</p>
            
            <p>У вас есть <strong>{{ $tasks->count() }}</strong> просроченных задач в системе DocsFlow:</p>
            
            @foreach($tasks as $task)
            <div class="task-item">
                <div class="task-title">{{ $task->title }}</div>
                <div class="task-document">Документ: {{ $task->document->title }}</div>
                <div class="task-deadline">
                    Срок выполнения: {{ $task->due_date->format('d.m.Y H:i') }}
                    (просрочена на {{ $task->due_date->diffForHumans() }})
                </div>
            </div>
            @endforeach
            
            <p>Пожалуйста, выполните эти задачи как можно скорее.</p>
            
            <a href="{{ url('/tasks/my') }}" class="button">Перейти к задачам</a>
        </div>
        
        <div class="footer">
            <p>Это автоматическое уведомление из системы DocsFlow.<br>
               Если у вас есть вопросы, обратитесь к администратору.</p>
        </div>
    </div>
</body>
</html>