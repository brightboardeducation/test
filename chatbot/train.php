<?php
// train.php
class ChatBotTrainer {
    private $knowledgeFile = 'data/knowledge.json';
    
    public function __construct() {
        $this->ensureDataDirectory();
    }
    
    private function ensureDataDirectory() {
        if (!is_dir('data')) {
            mkdir('data', 0755, true);
        }
    }
    
    public function handleTraining() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->addTrainingData($_POST);
            $message = "Training data added successfully!";
        }
        
        $this->renderForm($message ?? '');
    }
    
    private function addTrainingData($data) {
        $knowledge = json_decode(file_get_contents($this->knowledgeFile), true);
        
        $newPattern = [
            'pattern' => array_map('trim', explode(',', strtolower($data['patterns']))),
            'response' => trim($data['response']),
            'industry' => trim($data['industry'])
        ];
        
        $knowledge['patterns'][] = $newPattern;
        file_put_contents($this->knowledgeFile, json_encode($knowledge, JSON_PRETTY_PRINT));
    }
    
    private function renderForm($message) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Train ChatBot</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    padding: 50px 20px;
                }
                .container {
                    max-width: 800px;
                    margin: 0 auto;
                    background: white;
                    padding: 40px;
                    border-radius: 20px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                }
                h1 { 
                    color: #4f46e5; 
                    margin-bottom: 30px;
                    text-align: center;
                }
                .form-group { 
                    margin-bottom: 25px; 
                }
                label { 
                    display: block; 
                    margin-bottom: 8px; 
                    font-weight: 600;
                    color: #374151;
                }
                input, textarea, select {
                    width: 100%;
                    padding: 12px 16px;
                    border: 2px solid #e5e7eb;
                    border-radius: 10px;
                    font-size: 1rem;
                    transition: border-color 0.3s;
                }
                input:focus, textarea:focus, select:focus {
                    outline: none;
                    border-color: #4f46e5;
                }
                textarea {
                    height: 120px;
                    resize: vertical;
                }
                button {
                    background: #4f46e5;
                    color: white;
                    padding: 15px 30px;
                    border: none;
                    border-radius: 10px;
                    font-size: 1.1rem;
                    cursor: pointer;
                    width: 100%;
                    transition: background 0.3s;
                }
                button:hover {
                    background: #4338ca;
                }
                .message {
                    padding: 15px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                    text-align: center;
                }
                .success {
                    background: #dcfce7;
                    color: #166534;
                    border: 1px solid #bbf7d0;
                }
                .back-link {
                    text-align: center;
                    margin-top: 20px;
                }
                .back-link a {
                    color: #4f46e5;
                    text-decoration: none;
                }
                .instructions {
                    background: #f8fafc;
                    padding: 20px;
                    border-radius: 10px;
                    margin-bottom: 30px;
                    border-left: 4px solid #4f46e5;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Train Your ChatBot</h1>
                
                <?php if ($message): ?>
                    <div class="message success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                
                <div class="instructions">
                    <h3>Training Instructions:</h3>
                    <p><strong>Patterns:</strong> Enter comma-separated keywords or phrases that should trigger this response</p>
                    <p><strong>Response:</strong> The exact response you want the bot to give</p>
                    <p><strong>Industry:</strong> Specify the industry/domain for better organization</p>
                    <p><strong>Example:</strong><br>
                    Patterns: price, cost, how much<br>
                    Response: Our pricing starts at $99 per month. Would you like to see our detailed pricing plans?<br>
                    Industry: sales</p>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="patterns">Patterns (comma-separated):</label>
                        <input type="text" id="patterns" name="patterns" required 
                               placeholder="hello, hi, hey, good morning">
                    </div>
                    
                    <div class="form-group">
                        <label for="response">Bot Response:</label>
                        <textarea id="response" name="response" required 
                                  placeholder="Enter the exact response you want the bot to give..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="industry">Industry/Department:</label>
                        <select id="industry" name="industry" required>
                            <option value="">Select Industry</option>
                            <option value="general">General</option>
                            <option value="healthcare">Healthcare</option>
                            <option value="finance">Finance</option>
                            <option value="technology">Technology</option>
                            <option value="education">Education</option>
                            <option value="sales">Sales</option>
                            <option value="support">Customer Support</option>
                            <option value="hr">Human Resources</option>
                            <option value="it">IT Support</option>
                            <option value="ecommerce">E-commerce</option>
                            <option value="realestate">Real Estate</option>
                        </select>
                    </div>
                    
                    <button type="submit">Add Training Data</button>
                </form>
                
                <div class="back-link">
                    <a href="index.html">← Back to Chat</a>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}

$trainer = new ChatBotTrainer();
$trainer->handleTraining();
?>