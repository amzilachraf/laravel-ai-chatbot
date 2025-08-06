<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .chat-messages {
            scroll-behavior: smooth;
            height: 400px;
            overflow-y: auto;
        }
        .message-loading {
            opacity: 0.6;
        }
        .typing-indicator {
            display: none;
            padding: 10px;
            margin-left: 50px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8 col-xl-6">
                <div class="card shadow-sm">
                    <!-- Header -->
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold">Chat</h5>
                        <a href="{{ route('knowledge.index') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="fas fa-comment me-1"></i> Knowledge
                        </a>
                    </div>

                    <!-- Chat messages -->
                    <div class="card-body p-0">
                        <div class="chat-messages p-3">
                            <!-- Initial messages can be loaded here if needed -->
                            <div class="d-flex mb-3">
                                <img src="{{ asset('default-images/user_1.png') }}"
                                    class="rounded-circle me-3" width="40" height="40" alt="AI">
                                <div>
                                    <div class="bg-light rounded-3 p-3 mb-1">
                                        <p class="mb-0">Hello! How can I help you today?</p>
                                    </div>
                                    <div class="small text-muted">Just now</div>
                                </div>
                            </div>
                        </div>
                        <div class="typing-indicator bg-light rounded-3 p-2 mb-2">
                            <div class="d-flex align-items-center">
                                <div class="spinner-grow spinner-grow-sm me-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <small>AI is typing...</small>
                            </div>
                        </div>
                    </div>

                    <!-- Message input -->
                    <div class="card-footer bg-white py-3">
                        <form class="chat-form">
                            <div class="input-group">
                                <input name="message" type="text" class="form-control rounded-pill border-0 bg-light"
                                    placeholder="Type your message..." autocomplete="off">
                                <button type="submit" class="btn btn-primary rounded-pill px-3">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            const chatMessages = $('.chat-messages');
            const typingIndicator = $('.typing-indicator');
            
            // Scroll to bottom initially
            scrollToBottom();
            
            $('.chat-form').on('submit', function(e) {
                e.preventDefault();
                const messageInput = $(this).find('input[name="message"]');
                const message = messageInput.val().trim();
                
                if (!message) return; // Don't send empty messages

                // Add the sent message to the chat
                addMessageToChat(message, 'sent');
                messageInput.val(''); // Clear the input
                
                // Show typing indicator
                typingIndicator.fadeIn();
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: "{{ route('chat.generate') }}",
                    method: "POST",
                    data: {
                        message: message
                    },
                    success: function(response) {
                        // Hide typing indicator
                        typingIndicator.fadeOut();
                        
                        // Add the received response to the chat
                        if (response && response.trim() !== '') {
                            addMessageToChat(response, 'received');
                        } else {
                            addMessageToChat("I didn't get that. Could you try again?", 'received');
                        }
                    },
                    error: function(xhr, status, error) {
                        typingIndicator.fadeOut();
                        console.error("Error sending message:", error);
                        // Show error message in chat
                        addMessageToChat("Error: Could not get a response. Please try again.", 'received');
                    }
                });
            });
            
            // Function to add messages to the chat
            function addMessageToChat(message, type) {
                const now = new Date();
                const timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                
                if (type === 'sent') {
                    const messageHtml = `
                        <div class="d-flex flex-row-reverse mb-3">
                            <div class="text-end">
                                <div class="bg-primary text-white rounded-3 p-3 mb-1">
                                    <p class="mb-0">${escapeHtml(message)}</p>
                                </div>
                                <div class="small text-muted">${timeString}</div>
                            </div>
                            <img src="{{ asset('default-images/user_2.png') }}"
                                class="rounded-circle ms-3" width="40" height="40" alt="You">
                        </div>
                    `;
                    chatMessages.append(messageHtml);
                } else {
                    const messageHtml = `
                        <div class="d-flex mb-3">
                            <img src="{{ asset('default-images/user_1.png') }}"
                                class="rounded-circle me-3" width="40" height="40" alt="AI">
                            <div>
                                <div class="bg-light rounded-3 p-3 mb-1">
                                    <p class="mb-0">${escapeHtml(message)}</p>
                                </div>
                                <div class="small text-muted">${timeString}</div>
                            </div>
                        </div>
                    `;
                    chatMessages.append(messageHtml);
                }
                
                scrollToBottom();
            }
            
            function scrollToBottom() {
                chatMessages.scrollTop(chatMessages[0].scrollHeight);
            }
            
            function escapeHtml(unsafe) {
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
            
            // Auto-focus input on page load
            $('input[name="message"]').focus();
        });
    </script>
</body>
</html>