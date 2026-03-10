<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            color: #1f2937;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-top: 40px;
            margin-bottom: 40px;
        }
        .header {
            background-color: #4f46e5;
            padding: 30px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.025em;
        }
        .content {
            padding: 40px;
            line-height: 1.6;
        }
        .content p {
            margin-top: 0;
            margin-bottom: 20px;
        }
        .greeting {
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 20px;
            color: #111827;
        }
        .prose-content {
            font-size: 15px;
            color: #4b5563;
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 10px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px 40px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }
        .unsubscribe {
            text-decoration: underline;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CMS Hội Thánh</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                Kính chào quý vị, {{ $userName }},
            </div>
            
            <div class="prose-content">
                {!! nl2br(e($content)) !!}
            </div>
            
            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #f3f4f6;">
                <p style="margin: 0; font-size: 14px; color: #6b7280;">Nguyện xin ân điển và sự bình an của Chúa Jesus Christ luôn ở cùng quý vị.</p>
                <p style="margin: 0; font-size: 14px; font-weight: bold; color: #4f46e5; margin-top: 5px;">Ban Chấp Sự / Ban Mục Sự</p>
            </div>
        </div>
        
        <div class="footer">
            <p>Thư này được gửi tự động từ hệ thống Quản trị CMS Hội Thánh.</p>
            <p>Bạn nhận được email này vì đây là thư chung dành cho mọi tín hữu/thân hữu.</p>
        </div>
    </div>
</body>
</html>
