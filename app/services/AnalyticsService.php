// app/services/AnalyticsService.php
<?php
class AnalyticsService {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function analyzeStudentInterests($organisasiId = null) {
        $conn = $this->db->getConnection();
        
        $whereClause = $organisasiId ? "WHERE e.organisasi_id = ?" : "";
        
        $query = "
            SELECT 
                e.kategori as category,
                COUNT(DISTINCT ep.id) as count,
                AVG(COALESCE(ef.rating, 4.0)) as avg_rating,
                COUNT(CASE WHEN ep.registered_at > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_count,
                COUNT(CASE WHEN ep.registered_at > DATE_SUB(NOW(), INTERVAL 60 DAY) AND ep.registered_at <= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as previous_count
            FROM events e
            LEFT JOIN event_participants ep ON e.id = ep.event_id
            LEFT JOIN event_feedback ef ON e.id = ef.event_id
            $whereClause
            GROUP BY e.kategori
            HAVING e.kategori IS NOT NULL
            ORDER BY count DESC
        ";
        
        $stmt = $conn->prepare($query);
        if ($organisasiId) {
            $stmt->bind_param("i", $organisasiId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $trend = $this->calculateTrend($row['recent_count'], $row['previous_count']);
            
            $data[] = [
                'category' => $row['category'],
                'count' => (int)$row['count'],
                'avg_rating' => round($row['avg_rating'], 1),
                'trend' => $trend
            ];
        }
        
        return $data;
    }
    
    public function getChartData($organisasiId = null) {
        $interests = $this->analyzeStudentInterests($organisasiId);
        
        return [
            'labels' => array_column($interests, 'category'),
            'data' => array_column($interests, 'count'),
            'trends' => array_column($interests, 'trend'),
            'ratings' => array_column($interests, 'avg_rating')
        ];
    }
    
    public function getPreviousEvents($organisasiId, $limit = 10) {
        $conn = $this->db->getConnection();
        
        $query = "
            SELECT nama_event 
            FROM events 
            WHERE organisasi_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $organisasiId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row['nama_event'];
        }
        
        return $events;
    }
    
    private function calculateTrend($recent, $previous) {
        if ($previous == 0) return $recent > 0 ? 'increasing' : 'stable';
        
        $change = ($recent - $previous) / $previous;
        
        if ($change > 0.2) return 'increasing';
        if ($change < -0.2) return 'decreasing';
        return 'stable';
    }
    
    public function saveInteraction($mahasiswaId, $eventCategory, $interactionType) {
        $conn = $this->db->getConnection();
        
        $query = "INSERT INTO student_interests (mahasiswa_id, event_category, interaction_type) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $mahasiswaId, $eventCategory, $interactionType);
        
        return $stmt->execute();
    }
}
?>