<?php
// api/chat.php
header('Content-Type: application/json');

// read POST body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['message'])) {
    http_response_code(400);
    echo json_encode(['error'=>'No message provided']);
    exit;
}

$userMessage = $input['message'];

// Load API key from environment or file
$openai_key = getenv('OPENAI_API_KEY'); // recommended
if (!$openai_key) {
    // fallback: keep an outside-webroot file with key (NOT RECOMMENDED on shared hosts)
    $cfgFile = __DIR__ . '/../.openai_key';
    if (file_exists($cfgFile)) $openai_key = trim(file_get_contents($cfgFile));
}

if (!$openai_key) {
    http_response_code(500);
    echo json_encode(['error'=>'API key not configured']);
    exit;
}

// Build messages array: include a system prompt for basic persona/specialization
$system_prompt = "You are a helpful assistant specialized in GENERAL. If this site is configured for an industry, adapt answers accordingly.";

// Optionally: load a more specific system prompt from disk for specialization
$special_prompt_file = __DIR__ . '/../specialization_prompt.txt';
if (file_exists($special_prompt_file)) {
    $sp = trim(file_get_contents($special_prompt_file));
    if ($sp !== '') $system_prompt = $sp;
}

$messages = [
    ["role" => "system", "content" => $system_prompt],
    ["role" => "user", "content" => $userMessage]
];

// Prepare payload for Chat Completions
$postData = [
    "model" => "gpt-3.5-turbo",
    "messages" => $messages,
    // "temperature" => 0.2, // tweak for creativity
    "max_tokens" => 900
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $openai_key"
]);
$response = curl_exec($ch);
$err = curl_error($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err) {
    http_response_code(500);
    echo json_encode(['error' => "cURL error: $err"]);
    exit;
}

if ($httpcode >= 400) {
    http_response_code($httpcode);
    echo json_encode(['error' => 'OpenAI returned HTTP ' . $httpcode, 'raw' => json_decode($response, true)]);
    exit;
}

$respJson = json_decode($response, true);
$reply = '';
if (isset($respJson['choices'][0]['message']['content'])) {
    $reply = $respJson['choices'][0]['message']['content'];
}

echo json_encode(['reply' => $reply, 'raw' => $respJson]);