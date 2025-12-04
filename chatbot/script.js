class ChatBot {
    constructor() {
        this.chatMessages = document.getElementById('chatMessages');
        this.userInput = document.getElementById('userInput');
        this.sendButton = document.getElementById('sendButton');
        this.status = document.getElementById('status');
        
        // Cache for the knowledge base so we don't fetch it every time
        this.knowledgeBase = null;
        
        this.setupEventListeners();
        // Pre-load the knowledge base
        this.loadKnowledgeBase();
    }
    
    setupEventListeners() {
        this.sendButton.addEventListener('click', () => this.sendMessage());
        this.userInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });
    }
    
    async loadKnowledgeBase() {
        try {
            // Fetch the JSON file directly
            const response = await fetch('data/knowledge.json');
            if (!response.ok) throw new Error('Failed to load knowledge');
            this.knowledgeBase = await response.json();
            this.setStatus('Ready');
        } catch (error) {
            console.error('Error loading knowledge base:', error);
            this.setStatus('Error loading brain');
        }
    }
    
    async sendMessage() {
        const message = this.userInput.value.trim();
        if (!message) return;
        
        this.addMessage(message, 'user');
        this.userInput.value = '';
        this.setStatus('Thinking...');
        
        // Simulate a small delay to feel natural
        setTimeout(() => {
            try {
                const response = this.getBotResponse(message);
                this.addMessage(response, 'bot');
                this.setStatus('Ready');
            } catch (error) {
                console.error('Error:', error);
                this.addMessage('Sorry, I encountered an error.', 'bot');
                this.setStatus('Error occurred');
            }
        }, 500); // 500ms delay
    }
    
    getBotResponse(message) {
        if (!this.knowledgeBase) {
            return "I'm still waking up. Please try again in a second.";
        }

        const cleanMessage = message.toLowerCase();
        const patterns = this.knowledgeBase.patterns;
        
        // 1. Exact/Keyword Match
        // We check if the user message contains any of the keywords defined in patterns
        for (const entry of patterns) {
            for (const keyword of entry.pattern) {
                if (cleanMessage.includes(keyword.toLowerCase())) {
                    return entry.response;
                }
            }
        }
        
        // 2. Fallback
        return "I'm still learning. Could you rephrase your question?";
    }
    
    addMessage(content, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.textContent = content;
        
        messageDiv.appendChild(contentDiv);
        this.chatMessages.appendChild(messageDiv);
        this.scrollToBottom();
    }
    
    scrollToBottom() {
        this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
    }
    
    setStatus(text) {
        this.status.textContent = text;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    new ChatBot();
});
