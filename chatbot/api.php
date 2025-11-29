<?php
// api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

class ChatBotAPI {
    private $knowledgeFile = 'data/knowledge.json';
    
    public function __construct() {
        $this->ensureDataDirectory();
    }
    
    private function ensureDataDirectory() {
        if (!is_dir('data')) {
            mkdir('data', 0755, true);
        }
        
        if (!file_exists($this->knowledgeFile)) {
            $this->initializeKnowledgeBase();
        }
    }
    
    private function initializeKnowledgeBase() {
        $initialData = [
            'patterns' => [
                [
                    'pattern' => ['hello', 'hi', 'hey'],
                    'response' => 'Hello! How can I assist you today?',
                    'industry' => 'general'
                ],
                [
                    'pattern' => ['how are you', 'how are you doing'],
                    'response' => 'I\'m functioning well, thank you for asking! How can I help you?',
                    'industry' => 'general'
                ],
                [
                    'pattern' => ['bye', 'goodbye', 'see you'],
                    'response' => 'Goodbye! Feel free to come back if you have more questions.',
                    'industry' => 'general'
                ]
            ]
        ];
        
        file_put_contents($this->knowledgeFile, json_encode($initialData, JSON_PRETTY_PRINT));
    }
    
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($input['message'])) {
                $response = $this->generateResponse($input['message']);
                echo json_encode(['response' => $response]);
            } else {
                echo json_encode(['response' => 'Please provide a message.']);
            }
        } else {
            echo json_encode(['response' => 'Method not allowed']);
        }
    }
    
    private function generateResponse($message) {
        $knowledge = json_decode(file_get_contents($this->knowledgeFile), true);
        $message = strtolower(trim($message));
        
        // Check for exact pattern matches
        foreach ($knowledge['patterns'] as $pattern) {
            foreach ($pattern['pattern'] as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    return $pattern['response'];
                }
            }
        }
        
        // Fallback: Use keyword matching with scoring
        $bestMatch = $this->findBestMatch($message, $knowledge['patterns']);
        if ($bestMatch) {
            return $bestMatch;
        }
        
        // Final fallback
        return "I'm still learning. Could you rephrase your question or train me with the correct response using the training panel?";
    }
    
    private function findBestMatch($message, $patterns) {
        $bestScore = 0;
        $bestResponse = null;
        
        foreach ($patterns as $pattern) {
            foreach ($pattern['pattern'] as $keyword) {
                similar_text($message, $keyword, $score);
                if ($score > $bestScore && $score > 60) {
                    $bestScore = $score;
                    $bestResponse = $pattern['response'];
                }
            }
        }
        
        return $bestResponse;
    }
}

$api = new ChatBotAPI();
$api->handleRequest();
?>