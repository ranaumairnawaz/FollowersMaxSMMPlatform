<?php
function get_sleek_email_html($pdo, $title, $message, $cta_text = '', $cta_link = '') {
    $theme_font = get_setting($pdo, 'theme_font', 'Plus Jakarta Sans');
    $theme_primary = get_setting($pdo, 'theme_primary', '#f97316');
    $theme_primary_dark = get_setting($pdo, 'theme_primary_dark', '#ea580c');
    $theme_dark_bg = get_setting($pdo, 'theme_dark_bg', '#0f172a');
    
    $font_url = str_replace(' ', '+', $theme_font);
    
    $cta_html = '';
    if ($cta_text && $cta_link) {
        $cta_html = "
        <div style='text-align: center; margin-top: 30px;'>
            <a href='{$cta_link}' style='display: inline-block; padding: 14px 28px; background-color: {$theme_primary}; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 8px; font-size: 16px;'>{$cta_text}</a>
        </div>";
    }

    return "
    <!DOCTYPE html>
    <html>
    <head>
        <link href='https://fonts.googleapis.com/css2?family={$font_url}:wght@400;600;700&display=swap' rel='stylesheet'>
        <style>
            body { font-family: '{$theme_font}', sans-serif; background-color: #f4f7f6; margin: 0; padding: 40px 0; color: #333; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
            .header { background-color: {$theme_dark_bg}; padding: 30px; text-align: center; border-bottom: 4px solid {$theme_primary}; }
            .header h1 { margin: 0; color: #ffffff; font-size: 24px; letter-spacing: 1px; }
            .header span { color: {$theme_primary}; }
            .content { padding: 40px 30px; line-height: 1.6; font-size: 16px; color: #4a5568; }
            .title { font-size: 22px; font-weight: bold; color: {$theme_dark_bg}; margin-top: 0; text-align: center; margin-bottom: 20px; }
            .footer { background-color: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1><span>Followers</span>Max</h1>
            </div>
            <div class='content'>
                <h2 class='title'>{$title}</h2>
                <div style='text-align: center;'>{$message}</div>
                {$cta_html}
            </div>
            <div class='footer'>
                &copy; " . date('Y') . " FollowersMax. All rights reserved.<br>
                This is an automated message, please do not reply.
            </div>
        </div>
    </body>
    </html>
    ";
}

function send_smtp_email($pdo, $to_email, $subject, $body_html) {
    if (empty($to_email)) return false;
    
    $smtp_host = get_setting($pdo, 'smtp_host');
    $smtp_port = get_setting($pdo, 'smtp_port', 587);
    $smtp_user = get_setting($pdo, 'smtp_user');
    $smtp_pass = get_setting($pdo, 'smtp_pass');
    $smtp_from = get_setting($pdo, 'smtp_from', 'admin@followersmax.com');
    $smtp_name = get_setting($pdo, 'smtp_name', 'FollowersMax System');
    
    // If SMTP is not properly setup in DB, silently skip or return false to not break UX
    if (empty($smtp_host) || empty($smtp_user) || empty($smtp_pass)) {
        return false; 
    }

    $crlf = "\r\n";
    $boundary = md5(uniqid(time()));

    $headers = [
        "From: =?UTF-8?B?" . base64_encode($smtp_name) . "?= <{$smtp_from}>",
        "To: <{$to_email}>",
        "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=UTF-8"
    ];

    $header_str = implode($crlf, $headers) . $crlf . $crlf;

    // Simple robust socket SMTP client
    $socket = @fsockopen(($smtp_port == 465 ? "ssl://" : "") . $smtp_host, $smtp_port, $errno, $errstr, 10);
    if (!$socket) {
        error_log("SMTP Connect Failed: $errstr");
        return false;
    }

    $response = "";
    function read_smtp($sock) {
        $res = "";
        while ($str = fgets($sock, 515)) {
            $res .= $str;
            if (substr($str, 3, 1) == " ") break;
        }
        return $res;
    }

    read_smtp($socket); // read banner
    
    fwrite($socket, "EHLO {$smtp_host}{$crlf}");
    read_smtp($socket);

    if (in_array($smtp_port, [587, 25])) {
        fwrite($socket, "STARTTLS{$crlf}");
        $res = read_smtp($socket);
        if (substr($res, 0, 3) == '220') {
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fwrite($socket, "EHLO {$smtp_host}{$crlf}");
            read_smtp($socket);
        }
    }

    fwrite($socket, "AUTH LOGIN{$crlf}");
    read_smtp($socket);
    fwrite($socket, base64_encode($smtp_user) . $crlf);
    read_smtp($socket);
    fwrite($socket, base64_encode($smtp_pass) . $crlf);
    $auth_res = read_smtp($socket);
    
    if (substr($auth_res, 0, 3) != '235') {
        error_log("SMTP Auth Failed: $auth_res");
        fclose($socket);
        return false;
    }

    fwrite($socket, "MAIL FROM:<{$smtp_from}>{$crlf}");
    read_smtp($socket);

    fwrite($socket, "RCPT TO:<{$to_email}>{$crlf}");
    read_smtp($socket);

    fwrite($socket, "DATA{$crlf}");
    read_smtp($socket);

    fwrite($socket, $header_str . $body_html . $crlf . "." . $crlf);
    $final_res = read_smtp($socket);

    fwrite($socket, "QUIT{$crlf}");
    fclose($socket);

    return (substr($final_res, 0, 3) == '250');
}
?>
