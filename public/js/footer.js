document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const chatIcon = document.getElementById('chatIcon');
        const chatBox = document.getElementById('chatBox');
        const closeChat = document.getElementById('closeChat');
        const sendBtn = document.getElementById('sendMessageBtn');
        const messageInput = document.getElementById('chatMessageInput');
        const chatBody = document.getElementById('chatBody');

        // Toggle Chat Open/Close
        chatIcon.addEventListener('click', () => {
            chatBox.classList.add('active');
        });

        closeChat.addEventListener('click', () => {
            chatBox.classList.remove('active');
        });

        // Send Message Function
        function sendMessage() {
            const text = messageInput.value.trim();
            
            if (text !== "") {
                // 1. Add User Message
                addMessage(text, 'user-message');
                messageInput.value = ''; // Clear input

                // 2. Simulate AI Response after 1 second
                setTimeout(() => {
                    const responses = [
                        "Thanks for reaching out! One of our Lovine agents will be with you shortly.",
                        "That's a great question about our jewelry.",
                        "Could you provide your order number?",
                        "We are currently checking our stock for that item."
                    ];
                    // Pick a random response
                    const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                    addMessage(randomResponse, 'ai-message');
                }, 1000);
            }
        }

        // Helper to create HTML for message
        function addMessage(text, className) {
            const div = document.createElement('div');
            div.classList.add('message', className);
            
            // Get current time
            const now = new Date();
            const timeString = now.getHours() + ":" + (now.getMinutes()<10?'0':'') + now.getMinutes();

            div.innerHTML = `
                <p>${text}</p>
                <span class="time">${timeString}</span>
            `;
            
            chatBody.appendChild(div);
            // Auto scroll to bottom
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        // Event Listeners for Sending
        sendBtn.addEventListener('click', sendMessage);

        // Allow pressing "Enter" to send
        messageInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    });