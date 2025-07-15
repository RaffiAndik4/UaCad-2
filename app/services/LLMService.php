// app/services/LLMService.php
<?php
class LLMService {
    private $apiKey;
    private $apiUrl;
    private $model;
    
    public function __construct() {
        $config = include '../config/env.php';
        $this->apiKey = $config['GROQ_API_KEY'];
        $this->apiUrl = $config['GROQ_API_URL'];
        $this->model = $config['LLM_MODEL'];
    }
    
    public function generateEventRecommendations($interestData, $organizationType, $previousEvents = []) {
        $systemPrompt = $this->getSystemPrompt();
        $userPrompt = $this->buildEventRecommendationPrompt($interestData, $organizationType, $previousEvents);
        
        $requestData = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 2000
        ];
        
        $response = $this->callGroqAPI($requestData);
        return $this->parseRecommendationResponse($response);
    }
    
    public function generateChatResponse($message, $userType = 'mahasiswa', $conversationHistory = []) {
        $systemPrompt = $this->getChatSystemPrompt($userType);
        
        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        
        // Add conversation history
        foreach ($conversationHistory as $msg) {
            $messages[] = ['role' => 'user', 'content' => $msg['message']];
            $messages[] = ['role' => 'assistant', 'content' => $msg['response']];
        }
        
        $messages[] = ['role' => 'user', 'content' => $message];
        
        $requestData = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.8,
            'max_tokens' => 500
        ];
        
        $response = $this->callGroqAPI($requestData);
        return $this->parseChatResponse($response);
    }
    
    private function buildEventRecommendationPrompt($interestData, $organizationType, $previousEvents) {
        $interestText = "";
        foreach ($interestData as $data) {
            $interestText .= "- {$data['category']}: {$data['count']} peserta (rating: {$data['avg_rating']}/5, trend: {$data['trend']})\n";
        }
        
        $previousEventsText = "";
        foreach ($previousEvents as $event) {
            $previousEventsText .= "- $event\n";
        }
        
        return "
Analisis data minat mahasiswa untuk organisasi $organizationType:

DATA MINAT MAHASISWA:
$interestText

EVENT SEBELUMNYA:
$previousEventsText

Berikan rekomendasi 5 ide event yang:
1. Sesuai dengan tren minat mahasiswa
2. Belum pernah dilakukan (tidak sama dengan event sebelumnya)
3. Relevan untuk organisasi $organizationType
4. Dapat meningkatkan engagement mahasiswa

Format output dalam JSON dengan struktur:
{
    \"recommendations\": [
        {
            \"title\": \"Nama Event\",
            \"category\": \"Kategori\",
            \"description\": \"Deskripsi detail\",
            \"target_audience\": \"Target peserta\",
            \"estimated_participants\": \"Estimasi jumlah\",
            \"confidence_score\": 0.85,
            \"reasoning\": \"Alasan rekomendasi\",
            \"implementation_tips\": [\"tip1\", \"tip2\"]
        }
    ],
    \"trend_analysis\": \"Analisis tren secara keseluruhan\",
    \"key_insights\": [\"insight1\", \"insight2\"]
}";
    }
    
    private function getSystemPrompt() {
        return "Anda adalah AI assistant yang ahli dalam analisis data mahasiswa dan perencanaan event kampus Indonesia. 
Tugas Anda adalah menganalisis data minat mahasiswa dan memberikan rekomendasi event yang:

1. Data-driven: Berdasarkan analisis mendalam dari data minat dan partisipasi
2. Praktis: Dapat diimplementasikan oleh organisasi mahasiswa dengan budget terbatas
3. Inovatif: Mengikuti tren terbaru namun tetap relevan dengan budaya kampus Indonesia
4. Engaging: Dapat menarik partisipasi mahasiswa yang tinggi

Selalu berikan jawaban dalam format JSON yang valid dan lengkap dalam bahasa Indonesia.
Confidence score harus berdasarkan kekuatan data dan relevansi rekomendasi (0.0 - 1.0).";
    }
    
    private function getChatSystemPrompt($userType) {
        $role = $userType === 'mahasiswa' ? 'mahasiswa' : 'organisasi kampus';
        return "Anda adalah asisten AI untuk sistem informasi kampus UACAD yang membantu $role.
Berikan informasi yang akurat tentang event kampus, cara pendaftaran, dan hal terkait.
Jawab dengan ramah, informatif, dan dalam bahasa Indonesia yang mudah dipahami.
Jika ditanya tentang hal di luar konteks kampus/event, arahkan kembali ke topik yang relevan.";
    }
    
    private function callGroqAPI($requestData) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($requestData),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("API Error: HTTP $httpCode - $response");
        }
        
        return json_decode($response, true);
    }
    
    private function parseRecommendationResponse($response) {
        if (!isset($response['choices'][0]['message']['content'])) {
            throw new Exception('Invalid API response format');
        }
        
        $content = $response['choices'][0]['message']['content'];
        
        // Clean up response jika ada markdown
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        
        $decoded = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse LLM response: ' . json_last_error_msg());
        }
        
        return $decoded;
    }
    
    private function parseChatResponse($response) {
        if (!isset($response['choices'][0]['message']['content'])) {
            throw new Exception('Invalid chat response format');
        }
        
        return [
            'message' => trim($response['choices'][0]['message']['content']),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
?>