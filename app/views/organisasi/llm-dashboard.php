<!-- app/views/organisasi/llm-dashboard.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Recommendations - UACAD</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        .recommendation-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            background: white;
        }
        .recommendation-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .confidence-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .chart-container {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }
        .btn-generate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 40px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php 
    $current_page = 'ai-recommendations';
    include '../app/views/layouts/organisasi_sidebar.php'; 
    ?>

    <!-- Main Content -->
    <div class="main-content" style="margin-left: 260px; padding: 24px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>🤖 AI Recommendations</h2>
                <p class="text-muted">Dapatkan rekomendasi event berbasis AI dan analisis minat mahasiswa</p>
            </div>
            <button class="btn-generate" onclick="generateRecommendations()">
                <i class="fas fa-magic"></i> Generate Rekomendasi AI
            </button>
        </div>

        <!-- Analytics Chart -->
        <div class="chart-container">
            <h5><i class="fas fa-chart-bar text-primary"></i> Analisis Minat Mahasiswa</h5>
            <canvas id="interestChart" style="height: 300px;"></canvas>
        </div>

        <!-- Loading State -->
        <div class="loading-spinner" id="loadingSpinner">
            <i class="fas fa-robot fa-3x text-primary mb-3"></i>
            <h5>AI sedang menganalisis data...</h5>
            <div class="spinner-border text-primary" role="status"></div>
        </div>

        <!-- Recommendations Container -->
        <div id="recommendationsContainer" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="fas fa-lightbulb text-warning"></i> Rekomendasi Event AI</h4>
                <small class="text-muted" id="generatedTime"></small>
            </div>
            
            <!-- Trend Analysis -->
            <div class="alert alert-info" id="trendAnalysis" style="display: none;">
                <h6><i class="fas fa-trending-up"></i> Analisis Tren:</h6>
                <p id="trendText"></p>
            </div>

            <!-- Recommendations Grid -->
            <div id="recommendationsGrid" class="row">
                <!-- Dynamic content akan dimuat di sini -->
            </div>
        </div>
    </div>

    <!-- Chatbot -->
    <div id="chatbot" class="position-fixed" style="bottom: 20px; right: 20px; z-index: 1000;">
        <div class="card" style="width: 350px; height: 500px; display: none;" id="chatWindow">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-robot"></i> AI Assistant</h6>
                    <button class="btn btn-sm text-white" onclick="toggleChat()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body d-flex flex-column p-0">
                <div id="chatMessages" class="flex-grow-1 p-3" style="height: 350px; overflow-y: auto;">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Halo! Saya AI Assistant UACAD. Tanya apa saja tentang event kampus!
                    </div>
                </div>
                <div class="border-top p-3">
                    <div class="input-group">
                        <input type="text" id="chatInput" class="form-control" placeholder="Ketik pesan...">
                        <button class="btn btn-primary" onclick="sendMessage()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chat Toggle Button -->
        <button class="btn btn-primary rounded-circle" style="width: 60px; height: 60px;" onclick="toggleChat()" id="chatToggle">
            <i class="fas fa-robot fa-lg"></i>
        </button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let interestChart;
        let chatSessionId = null;
        
        // Initialize page
        $(document).ready(function() {
            loadAnalytics();
            initializeChat();
        });
        
        // Generate AI Recommendations
        function generateRecommendations() {
            $('#loadingSpinner').show();
            $('#recommendationsContainer').hide();
            
            $.ajax({
                url: '<?= BASE_URL ?>llm/generateRecommendations',
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayRecommendations(response);
                        updateChart(response.chartData);
                    } else {
                        showAlert('danger', 'Error: ' + response.message);
                    }
                },
                error: function() {
                    showAlert('danger', 'Terjadi kesalahan saat menghubungi server');
                },
                complete: function() {
                    $('#loadingSpinner').hide();
                }
            });
        }
        
        // Display recommendations
        function displayRecommendations(data) {
            const container = $('#recommendationsGrid');
            container.empty();
            
            // Show trend analysis
            if (data.trend_analysis) {
                $('#trendText').text(data.trend_analysis);
                $('#trendAnalysis').show();
            }
            
            // Show generation time
            $('#generatedTime').text('Dibuat: ' + new Date().toLocaleString('id-ID'));
            
            // Display recommendations
            data.recommendations.forEach((rec, index) => {
                const card = createRecommendationCard(rec, index);
                container.append(card);
            });
            
            $('#recommendationsContainer').show();
        }
        
        // Create recommendation card
        function createRecommendationCard(recommendation, index) {
            const confidencePercent = Math.round(recommendation.confidence_score * 100);
            const confidenceColor = confidencePercent >= 80 ? 'success' : confidencePercent >= 60 ? 'warning' : 'secondary';
            
            return `
                <div class="col-md-6 mb-4">
                    <div class="recommendation-card h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title">${recommendation.title}</h5>
                            <span class="confidence-badge">${confidencePercent}% confident</span>
                        </div>
                        
                        <div class="mb-3">
                            <span class="badge bg-secondary">${recommendation.category}</span>
                        </div>
                        
                        <p class="text-muted mb-3">${recommendation.description}</p>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted"><strong>Target:</strong></small>
                                <div>${recommendation.target_audience}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted"><strong>Estimasi:</strong></small>
                                <div>${recommendation.estimated_participants}</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted"><strong>💡 Alasan:</strong></small>
                            <p class="small">${recommendation.reasoning}</p>
                        </div>
                        
                        ${recommendation.implementation_tips ? `
                            <div class="mb-3">
                                <small class="text-muted"><strong>📋 Tips Implementasi:</strong></small>
                                <ul class="small">
                                    ${recommendation.implementation_tips.map(tip => `<li>${tip}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                        
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm" onclick="implementRecommendation(${index})">
                                <i class="fas fa-plus"></i> Buat Event
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="saveRecommendation(${index})">
                                <i class="fas fa-bookmark"></i> Simpan
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Load analytics data
        function loadAnalytics() {
            $.ajax({
                url: '<?= BASE_URL ?>llm/getAnalytics',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        updateChart(response.chartData);
                    }
                }
            });
        }
        
        // Update interest chart
        function updateChart(chartData) {
            const ctx = document.getElementById('interestChart').getContext('2d');
            
            if (interestChart) {
                interestChart.destroy();
            }
            
            interestChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Jumlah Peserta',
                        data: chartData.data,
                        backgroundColor: chartData.labels.map((_, index) => {
                            const colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe'];
                            return colors[index % colors.length];
                        }),
                        borderWidth: 0,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Distribusi Minat Mahasiswa Berdasarkan Kategori Event'
                        },
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
        
        // Chatbot functions
        function initializeChat() {
            chatSessionId = sessionStorage.getItem('chatSessionId') || generateSessionId();
            sessionStorage.setItem('chatSessionId', chatSessionId);
            
            // Enter key handler
            $('#chatInput').keypress(function(e) {
                if (e.which === 13) {
                    sendMessage();
                }
            });
        }
        
        function toggleChat() {
            const chatWindow = $('#chatWindow');
            const chatToggle = $('#chatToggle');
            
            if (chatWindow.is(':visible')) {
                chatWindow.hide();
                chatToggle.show();
            } else {
                chatWindow.show();
                chatToggle.hide();
                $('#chatInput').focus();
            }
        }
        
        function sendMessage() {
            const input = $('#chatInput');
            const message = input.val().trim();
            
            if (!message) return;
            
            // Add user message to chat
            addMessageToChat('user', message);
            input.val('');
            
            // Show typing indicator
            addTypingIndicator();
            
            // Send to server
            $.ajax({
                url: '<?= BASE_URL ?>llm/chatbot',
                method: 'POST',
                data: {
                    message: message,
                    sessionId: chatSessionId
                },
                dataType: 'json',
                success: function(response) {
                    removeTypingIndicator();
                    
                    if (response.success) {
                        addMessageToChat('bot', response.message);
                        
                        // Add suggestions if available
                        if (response.suggestions && response.suggestions.length > 0) {
                            addSuggestions(response.suggestions);
                        }
                    } else {
                        addMessageToChat('bot', 'Maaf, terjadi kesalahan. Silakan coba lagi.');
                    }
                },
                error: function() {
                    removeTypingIndicator();
                    addMessageToChat('bot', 'Maaf, saya tidak dapat merespons saat ini.');
                }
            });
        }
        
        function addMessageToChat(sender, message) {
            const chatMessages = $('#chatMessages');
            const messageClass = sender === 'user' ? 'text-end' : 'text-start';
            const bgClass = sender === 'user' ? 'bg-primary text-white' : 'bg-light';
            const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            
            const messageHtml = `
                <div class="${messageClass} mb-3">
                    <div class="d-inline-block p-2 rounded ${bgClass}" style="max-width: 80%;">
                        ${message}
                    </div>
                    <div class="small text-muted mt-1">${time}</div>
                </div>
            `;
            
            chatMessages.append(messageHtml);
            chatMessages.scrollTop(chatMessages[0].scrollHeight);
        }
        
        function addTypingIndicator() {
            const chatMessages = $('#chatMessages');
            const typingHtml = `
                <div class="typing-indicator text-start mb-3">
                    <div class="d-inline-block p-2 rounded bg-light">
                        <i class="fas fa-circle" style="animation: pulse 1.5s infinite; color: #ccc;"></i>
                        <i class="fas fa-circle" style="animation: pulse 1.5s infinite 0.2s; color: #ccc;"></i>
                        <i class="fas fa-circle" style="animation: pulse 1.5s infinite 0.4s; color: #ccc;"></i>
                    </div>
                </div>
            `;
            chatMessages.append(typingHtml);
            chatMessages.scrollTop(chatMessages[0].scrollHeight);
        }
        
        function removeTypingIndicator() {
            $('.typing-indicator').remove();
        }
        
        function addSuggestions(suggestions) {
            const chatMessages = $('#chatMessages');
            const suggestionsHtml = `
                <div class="suggestions mb-3">
                    <div class="small text-muted mb-2">Pertanyaan yang sering diajukan:</div>
                    <div class="d-flex flex-wrap gap-1">
                        ${suggestions.map(suggestion => 
                            `<button class="btn btn-outline-primary btn-sm" onclick="sendSuggestion('${suggestion}')">${suggestion}</button>`
                        ).join('')}
                    </div>
                </div>
            `;
            chatMessages.append(suggestionsHtml);
            chatMessages.scrollTop(chatMessages[0].scrollHeight);
        }
        
        function sendSuggestion(suggestion) {
            $('#chatInput').val(suggestion);
            sendMessage();
        }
        
        function generateSessionId() {
            return 'chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }
        
        // Action handlers
        function implementRecommendation(index) {
            // Redirect to create event page with pre-filled data
            showAlert('info', 'Fitur ini akan mengarahkan ke halaman buat event dengan data yang sudah terisi.');
        }
        
        function saveRecommendation(index) {
            showAlert('success', 'Rekomendasi berhasil disimpan!');
        }
        
        function showAlert(type, message) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            $('body').prepend(alertHtml);
            
            setTimeout(() => {
                $('.alert').fadeOut();
            }, 5000);
        }
        
        // CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse {
                0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
                40% { transform: scale(1); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>