// app/controllers/LLMController.php
<?php
class LLMController extends Controller {
    private $llmService;
    private $analyticsService;
    
    public function __construct() {
        parent::__construct();
        $this->llmService = new LLMService();
        $this->analyticsService = new AnalyticsService();
    }
    
    public function generateRecommendations() {
        header('Content-Type: application/json');
        
        try {
            $organisasiData = $this->getOrganisasiData();
            if (!$organisasiData) {
                throw new Exception('Data organisasi tidak ditemukan');
            }
            
            // Get analytics data
            $interestData = $this->analyticsService->analyzeStudentInterests($organisasiData['id']);
            $previousEvents = $this->analyticsService->getPreviousEvents($organisasiData['id']);
            $chartData = $this->analyticsService->getChartData($organisasiData['id']);
            
            // Generate recommendations
            $recommendations = $this->llmService->generateEventRecommendations(
                $interestData,
                $organisasiData['jenis_organisasi'],
                $previousEvents
            );
            
            // Save recommendations to database
            $this->saveRecommendations($organisasiData['id'], $recommendations);
            
            echo json_encode([
                'success' => true,
                'recommendations' => $recommendations['recommendations'],
                'trend_analysis' => $recommendations['trend_analysis'],
                'key_insights' => $recommendations['key_insights'],
                'chartData' => $chartData,
                'analytics_data' => $interestData
            ]);
            
        } catch (Exception $e) {
            error_log("LLM Recommendation Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Gagal generate rekomendasi: ' . $e->getMessage()
            ]);
        }
    }
    
    public function chatbot() {
        header('Content-Type: application/json');
        
        try {
            $message = $_POST['message'] ?? '';
            $sessionId = $_POST['sessionId'] ?? uniqid('chat_');
            
            if (empty($message)) {
                throw new Exception('Pesan tidak boleh kosong');
            }
            
            $userData = $this->getUserData();
            $userType = $_SESSION['role'] ?? 'mahasiswa';
            
            // Get conversation history
            $history = $this->getChatHistory($sessionId, 5); // Last 5 messages
            
            // Generate response
            $response = $this->llmService->generateChatResponse($message, $userType, $history);
            
            // Save conversation
            $this->saveChatMessage($userData['id'], $userType, $sessionId, $message, $response['message']);
            
            // Detect intent and provide suggestions
            $intent = $this->detectIntent($message);
            $suggestions = $this->generateSuggestions($intent, $userType);
            
            echo json_encode([
                'success' => true,
                'message' => $response['message'],
                'suggestions' => $suggestions,
                'sessionId' => $sessionId,
                'timestamp' => $response['timestamp']
            ]);
            
        } catch (Exception $e) {
            error_log("Chatbot Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Maaf, terjadi kesalahan. Silakan coba lagi.'
            ]);
        }
    }
    
    public function getAnalytics() {
        header('Content-Type: application/json');
        
        try {
            $organisasiData = $this->getOrganisasiData();
            $chartData = $this->analyticsService->getChartData($organisasiData['id'] ?? null);
            $interestData = $this->analyticsService->analyzeStudentInterests($organisasiData['id'] ?? null);
            
            echo json_encode([
                'success' => true,
                'chartData' => $chartData,
                'analytics' => $interestData
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    private function getOrganisasiData() {
        if ($_SESSION['role'] !== 'organisasi') {
            return null;
        }
        
        $organisasiModel = $this->model('Organisasi');
        return $organisasiModel->getByUserId($_SESSION['user_id']);
    }
    
    private function getUserData() {
        $userModel = $this->model('User');
        return $userModel->getUserById($_SESSION['user_id']);
    }
    
    private function saveRecommendations($organisasiId, $recommendations) {
        $db = new Database();
        $conn = $db->getConnection();
        
        $query = "INSERT INTO llm_recommendations (organisasi_id, input_data, llm_response, confidence_score) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        $inputData = json_encode(['timestamp' => date('Y-m-d H:i:s')]);
        $llmResponse = json_encode($recommendations);
        $avgConfidence = $this->calculateAverageConfidence($recommendations['recommendations']);
        
        $stmt->bind_param("issd", $organisasiId, $inputData, $llmResponse, $avgConfidence);
        return $stmt->execute();
    }
    
    private function getChatHistory($sessionId, $limit = 5) {
        $db = new Database();
        $conn = $db->getConnection();
        
        $query = "SELECT message, response FROM chatbot_conversations WHERE session_id = ? ORDER BY created_at DESC LIMIT ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $sessionId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        
        return array_reverse($history); // Reverse to get chronological order
    }
    
    private function saveChatMessage($userId, $userType, $sessionId, $message, $response) {
        $db = new Database();
        $conn = $db->getConnection();
        
        $intent = $this->detectIntent($message);
        
        $query = "INSERT INTO chatbot_conversations (user_id, user_type, session_id, message, response, intent) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssss", $userId, $userType, $sessionId, $message, $response, $intent);
        
        return $stmt->execute();
    }
    
    private function detectIntent($message) {
        $message = strtolower($message);
        
        if (preg_match('/\b(event|acara|kegiatan)\b/', $message)) {
            return 'event_inquiry';
        }
        if (preg_match('/\b(rekomendasi|saran|ide)\b/', $message)) {
            return 'recommendation_request';
        }
        if (preg_match('/\b(daftar|registrasi|ikut)\b/', $message)) {
            return 'registration_inquiry';
        }
        
        return 'general_chat';
    }
    
    private function generateSuggestions($intent, $userType) {
        $suggestions = [
            'event_inquiry' => [
                'Event apa saja yang tersedia?',
                'Cara mendaftar event',
                'Event populer bulan ini'
            ],
            'recommendation_request' => [
                'Ide event untuk organisasi saya',
                'Analisis tren minat mahasiswa',
                'Event yang cocok untuk semester ini'
            ],
            'registration_inquiry' => [
                'Syarat pendaftaran event',
                'Cara membatalkan pendaftaran',
                'Status pendaftaran saya'
            ],
            'general_chat' => [
                'Lihat event terbaru',
                'Bantuan sistem',
                'FAQ'
            ]
        ];
        
        return $suggestions[$intent] ?? $suggestions['general_chat'];
    }
    
    private function calculateAverageConfidence($recommendations) {
        $total = 0;
        $count = count($recommendations);
        
        foreach ($recommendations as $rec) {
            $total += $rec['confidence_score'] ?? 0.5;
        }
        
        return $count > 0 ? $total / $count : 0.5;
    }
}
?>