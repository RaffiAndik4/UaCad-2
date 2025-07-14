<?php
class Event {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->createTableIfNotExists();
    }
    
    private function createTableIfNotExists() {
        $checkTable = "SHOW TABLES LIKE 'events'";
        $result = $this->conn->query($checkTable);
        
        if ($result->num_rows == 0) {
            $createTable = "CREATE TABLE events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                organisasi_id INT NOT NULL,
                nama_event VARCHAR(200) NOT NULL,
                deskripsi TEXT,
                kategori VARCHAR(50),
                tanggal_mulai DATETIME,
                tanggal_selesai DATETIME,
                lokasi VARCHAR(200),
                kapasitas INT DEFAULT 0,
                poster VARCHAR(255),
                status ENUM('draft', 'aktif', 'selesai', 'dibatalkan') DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $this->conn->query($createTable);
        } else {
            // Add poster column if not exists
            $checkColumn = "SHOW COLUMNS FROM events LIKE 'poster'";
            $result = $this->conn->query($checkColumn);
            if ($result->num_rows == 0) {
                $this->conn->query("ALTER TABLE events ADD poster VARCHAR(255) AFTER kapasitas");
            }
        }
        
        // Create event_participants table
        $checkParticipantsTable = "SHOW TABLES LIKE 'event_participants'";
        $result = $this->conn->query($checkParticipantsTable);
        
        if ($result->num_rows == 0) {
            $createParticipantsTable = "CREATE TABLE event_participants (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_id INT NOT NULL,
                user_id INT NOT NULL,
                status ENUM('terdaftar', 'hadir', 'tidak_hadir') DEFAULT 'terdaftar',
                registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                attended_at TIMESTAMP NULL,
                FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
                UNIQUE KEY unique_registration (event_id, user_id)
            )";
            $this->conn->query($createParticipantsTable);
        }
    }
    
    public function create($data) {
        try {
            $query = "INSERT INTO events (organisasi_id, nama_event, deskripsi, kategori, tanggal_mulai, tanggal_selesai, lokasi, kapasitas, poster, status) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("isssssssss",
                $data['organisasi_id'],
                $data['nama_event'],
                $data['deskripsi'],
                $data['kategori'],
                $data['tanggal_mulai'],
                $data['tanggal_selesai'],
                $data['lokasi'],
                $data['kapasitas'],
                $data['poster'],
                $data['status']
            );
            
            if ($stmt->execute()) {
                return $this->conn->insert_id;
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Event create error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getStatsByOrganisasiId($orgId) {
        try {
            $query = "SELECT 
                        COUNT(*) as total_events,
                        COUNT(CASE WHEN status = 'aktif' THEN 1 END) as active_events,
                        COALESCE(SUM(CASE WHEN kapasitas IS NOT NULL AND kapasitas > 0 THEN kapasitas ELSE 0 END), 0) as total_capacity
                      FROM events WHERE organisasi_id = ?";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("i", $orgId);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            
            return [
                'total_events' => (int)($data['total_events'] ?? 0),
                'active_events' => (int)($data['active_events'] ?? 0), 
                'total_capacity' => (int)($data['total_capacity'] ?? 0)
            ];
            
        } catch (Exception $e) {
            error_log("getStatsByOrganisasiId error: " . $e->getMessage());
            
            return [
                'total_events' => 0,
                'active_events' => 0, 
                'total_capacity' => 0
            ];
        }
    }
    
    public function getEventsByOrganisasiId($orgId, $status = null) {
        try {
            $query = "SELECT * FROM events WHERE organisasi_id = ?";
            $params = [$orgId];
            $types = "i";
            
            if ($status) {
                $query .= " AND status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            $query .= " ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                return [];
            }
            
            $result = $stmt->get_result();
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                // Get participant count for each event
                $row['participants_count'] = $this->getParticipantsCount($row['id']);
                $events[] = $row;
            }
            
            return $events;
            
        } catch (Exception $e) {
            error_log("getEventsByOrganisasiId error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getParticipants($eventId) {
        try {
            $query = "SELECT ep.*, u.username, u.email, m.nama_lengkap, m.nim, m.fakultas, m.jurusan
                      FROM event_participants ep
                      JOIN users u ON ep.user_id = u.id
                      LEFT JOIN mahasiswa m ON u.id = m.user_id
                      WHERE ep.event_id = ?
                      ORDER BY ep.registered_at DESC";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param("i", $eventId);
            
            if (!$stmt->execute()) {
                return [];
            }
            
            $result = $stmt->get_result();
            
            $participants = [];
            while ($row = $result->fetch_assoc()) {
                $participants[] = [
                    'id' => $row['id'],
                    'user_id' => $row['user_id'],
                    'nama' => $row['nama_lengkap'] ?? $row['username'],
                    'nim' => $row['nim'] ?? 'N/A',
                    'fakultas' => $row['fakultas'] ?? 'N/A',
                    'jurusan' => $row['jurusan'] ?? 'N/A',
                    'email' => $row['email'],
                    'status' => $row['status'],
                    'tanggal_daftar' => $row['registered_at'],
                    'tanggal_hadir' => $row['attended_at']
                ];
            }
            
            return $participants;
            
        } catch (Exception $e) {
            error_log("getParticipants error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getParticipantsCount($eventId) {
        try {
            $query = "SELECT COUNT(*) as count FROM event_participants WHERE event_id = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return 0;
            }
            
            $stmt->bind_param("i", $eventId);
            
            if (!$stmt->execute()) {
                return 0;
            }
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)($row['count'] ?? 0);
            
        } catch (Exception $e) {
            error_log("getParticipantsCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getEventReport($eventId) {
        try {
            $event = $this->getById($eventId);
            if (!$event) {
                return null;
            }
            
            // Get participant statistics
            $totalRegistered = $this->getParticipantsCount($eventId);
            $totalAttended = $this->getAttendeesCount($eventId);
            $attendanceRate = $totalRegistered > 0 ? round(($totalAttended / $totalRegistered) * 100, 1) : 0;
            
            // Get demographics
            $demographics = $this->getEventDemographics($eventId);
            
            // Get registration timeline
            $timeline = $this->getRegistrationTimeline($eventId);
            
            // Get feedback statistics (mock data for now)
            $feedbackCount = $totalAttended; // Assume all attendees gave feedback
            $ratingAverage = 4.5; // Mock rating
            $satisfactionRate = 85; // Mock satisfaction rate
            
            return [
                'total_registered' => $totalRegistered,
                'total_attended' => $totalAttended,
                'attendance_rate' => $attendanceRate,
                'rating_average' => $ratingAverage,
                'feedback_count' => $feedbackCount,
                'satisfaction_rate' => $satisfactionRate,
                'demographics' => $demographics,
                'timeline' => $timeline
            ];
            
        } catch (Exception $e) {
            error_log("getEventReport error: " . $e->getMessage());
            return null;
        }
    }
    
    public function getEventDemographics($eventId) {
        try {
            $query = "SELECT m.fakultas, COUNT(*) as count
                      FROM event_participants ep
                      JOIN users u ON ep.user_id = u.id
                      JOIN mahasiswa m ON u.id = m.user_id
                      WHERE ep.event_id = ?
                      GROUP BY m.fakultas";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return ['by_faculty' => []];
            }
            
            $stmt->bind_param("i", $eventId);
            
            if (!$stmt->execute()) {
                return ['by_faculty' => []];
            }
            
            $result = $stmt->get_result();
            
            $byFaculty = [];
            while ($row = $result->fetch_assoc()) {
                $byFaculty[$row['fakultas']] = (int)$row['count'];
            }
            
            return [
                'by_faculty' => $byFaculty
            ];
            
        } catch (Exception $e) {
            error_log("getEventDemographics error: " . $e->getMessage());
            return ['by_faculty' => []];
        }
    }
    
    public function getRegistrationTimeline($eventId) {
        try {
            $query = "SELECT DATE(registered_at) as date, COUNT(*) as registrations
                      FROM event_participants
                      WHERE event_id = ?
                      GROUP BY DATE(registered_at)
                      ORDER BY date";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param("i", $eventId);
            
            if (!$stmt->execute()) {
                return [];
            }
            
            $result = $stmt->get_result();
            
            $timeline = [];
            while ($row = $result->fetch_assoc()) {
                $timeline[] = [
                    'date' => $row['date'],
                    'registrations' => (int)$row['registrations']
                ];
            }
            
            return $timeline;
            
        } catch (Exception $e) {
            error_log("getRegistrationTimeline error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAttendeesCount($eventId) {
        try {
            $query = "SELECT COUNT(*) as count FROM event_participants WHERE event_id = ? AND status = 'hadir'";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return 0;
            }
            
            $stmt->bind_param("i", $eventId);
            
            if (!$stmt->execute()) {
                return 0;
            }
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)($row['count'] ?? 0);
            
        } catch (Exception $e) {
            error_log("getAttendeesCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getAllReports($orgId) {
        try {
            $events = $this->getEventsByOrganisasiId($orgId, 'selesai');
            $reports = [];
            
            foreach ($events as $event) {
                $reportData = $this->getEventReport($event['id']);
                if ($reportData) {
                    $reports[] = [
                        'event' => $event,
                        'summary' => $reportData
                    ];
                }
            }
            
            return $reports;
            
        } catch (Exception $e) {
            error_log("getAllReports error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAnalytics($orgId) {
        try {
            // Get comprehensive analytics data
            $stats = $this->getStatsByOrganisasiId($orgId);
            $trendData = $this->getTrendData($orgId);
            $categoryData = $this->getCategoryData($orgId);
            
            // Additional analytics
            $popularEvents = $this->getPopularEvents($orgId);
            $monthlyStats = $this->getMonthlyStats($orgId);
            $performanceMetrics = $this->getPerformanceMetrics($orgId);
            
            return [
                'stats' => $stats,
                'trend_data' => $trendData,
                'category_data' => $categoryData,
                'popular_events' => $popularEvents,
                'monthly_stats' => $monthlyStats,
                'performance_metrics' => $performanceMetrics
            ];
            
        } catch (Exception $e) {
            error_log("getAnalytics error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPopularEvents($orgId, $limit = 5) {
        try {
            $query = "SELECT e.*, COUNT(ep.id) as participant_count
                      FROM events e
                      LEFT JOIN event_participants ep ON e.id = ep.event_id
                      WHERE e.organisasi_id = ?
                      GROUP BY e.id
                      ORDER BY participant_count DESC
                      LIMIT ?";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param("ii", $orgId, $limit);
            
            if (!$stmt->execute()) {
                return [];
            }
            
            $result = $stmt->get_result();
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            return $events;
            
        } catch (Exception $e) {
            error_log("getPopularEvents error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getMonthlyStats($orgId) {
        try {
            $query = "SELECT 
                        YEAR(created_at) as year,
                        MONTH(created_at) as month,
                        COUNT(*) as events_created,
                        SUM(kapasitas) as total_capacity,
                        (SELECT COUNT(*) FROM event_participants ep 
                         JOIN events e2 ON ep.event_id = e2.id 
                         WHERE e2.organisasi_id = ? 
                         AND YEAR(e2.created_at) = YEAR(e.created_at)
                         AND MONTH(e2.created_at) = MONTH(e.created_at)) as total_participants
                      FROM events e
                      WHERE organisasi_id = ?
                      AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                      GROUP BY YEAR(created_at), MONTH(created_at)
                      ORDER BY year, month";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param("ii", $orgId, $orgId);
            
            if (!$stmt->execute()) {
                return [];
            }
            
            $result = $stmt->get_result();
            
            $stats = [];
            while ($row = $result->fetch_assoc()) {
                $stats[] = [
                    'year' => (int)$row['year'],
                    'month' => (int)$row['month'],
                    'events_created' => (int)$row['events_created'],
                    'total_capacity' => (int)$row['total_capacity'],
                    'total_participants' => (int)$row['total_participants']
                ];
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("getMonthlyStats error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPerformanceMetrics($orgId) {
        try {
            // Calculate various performance metrics
            $totalEvents = $this->getStatsByOrganisasiId($orgId)['total_events'];
            
            if ($totalEvents == 0) {
                return [
                    'average_attendance_rate' => 0,
                    'average_capacity_utilization' => 0,
                    'event_completion_rate' => 0,
                    'participant_satisfaction' => 0
                ];
            }
            
            // Average attendance rate
            $query = "SELECT 
                        AVG(
                            CASE 
                                WHEN e.kapasitas > 0 THEN 
                                    (SELECT COUNT(*) FROM event_participants ep WHERE ep.event_id = e.id AND ep.status = 'hadir') / e.kapasitas * 100
                                ELSE 0 
                            END
                        ) as avg_attendance_rate,
                        AVG(
                            CASE 
                                WHEN e.kapasitas > 0 THEN 
                                    (SELECT COUNT(*) FROM event_participants ep WHERE ep.event_id = e.id) / e.kapasitas * 100
                                ELSE 0 
                            END
                        ) as avg_capacity_utilization,
                        (COUNT(CASE WHEN status = 'selesai' THEN 1 END) / COUNT(*)) * 100 as completion_rate
                      FROM events e
                      WHERE organisasi_id = ?";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param("i", $orgId);
            
            if (!$stmt->execute()) {
                return [];
            }
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return [
                'average_attendance_rate' => round($row['avg_attendance_rate'] ?? 0, 1),
                'average_capacity_utilization' => round($row['avg_capacity_utilization'] ?? 0, 1),
                'event_completion_rate' => round($row['completion_rate'] ?? 0, 1),
                'participant_satisfaction' => 85.0 // Mock data - could be calculated from feedback
            ];
            
        } catch (Exception $e) {
            error_log("getPerformanceMetrics error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTrendData($orgId) {
        try {
            $query = "SELECT MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as count
                      FROM events WHERE organisasi_id = ? 
                      AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                      GROUP BY YEAR(created_at), MONTH(created_at)
                      ORDER BY year, month";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param("i", $orgId);
            
            if (!$stmt->execute()) {
                return [];
            }
            
            $result = $stmt->get_result();
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = [
                    'month' => (int)$row['month'],
                    'year' => (int)$row['year'],
                    'count' => (int)$row['count']
                ];
            }
            return $data;
            
        } catch (Exception $e) {
            error_log("getTrendData error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getCategoryData($orgId) {
        try {
            $query = "SELECT kategori, COUNT(*) as count FROM events 
                      WHERE organisasi_id = ? 
                      GROUP BY kategori 
                      ORDER BY count DESC";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param("i", $orgId);
            
            if (!$stmt->execute()) {
                return [];
            }
            
            $result = $stmt->get_result();
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = [
                    'kategori' => $row['kategori'],
                    'count' => (int)$row['count']
                ];
            }
            return $data;
            
        } catch (Exception $e) {
            error_log("getCategoryData error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getActiveEvents($orgId, $limit = 5) {
        try {
            $query = "SELECT * FROM events 
                      WHERE organisasi_id = ? AND status IN ('aktif', 'draft')
                      ORDER BY created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT ?";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return [];
            }
            
            if ($limit) {
                $stmt->bind_param("ii", $orgId, $limit);
            } else {
                $stmt->bind_param("i", $orgId);
            }
            
            if (!$stmt->execute()) {
                return [];
            }
            
            $result = $stmt->get_result();
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            return $events;
            
        } catch (Exception $e) {
            error_log("getActiveEvents error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getById($id) {
        try {
            $query = "SELECT * FROM events WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return null;
            }
            
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                return null;
            }
            
            $result = $stmt->get_result();
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log("getById error: " . $e->getMessage());
            return null;
        }
    }
    
    public function update($id, $data) {
        try {
            $query = "UPDATE events SET 
                        nama_event = ?, deskripsi = ?, kategori = ?, 
                        tanggal_mulai = ?, tanggal_selesai = ?, lokasi = ?, 
                        kapasitas = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                      WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return false;
            }
            
            $stmt->bind_param("ssssssssi",
                $data['nama_event'],
                $data['deskripsi'],
                $data['kategori'],
                $data['tanggal_mulai'],
                $data['tanggal_selesai'],
                $data['lokasi'],
                $data['kapasitas'],
                $data['status'],
                $id
            );
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Event update error: " . $e->getMessage());
            return false;
        }
    }
    
    public function delete($id) {
        try {
            $query = "DELETE FROM events WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return false;
            }
            
            $stmt->bind_param("i", $id);
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Event delete error: " . $e->getMessage());
            return false;
        }
    }
    
    public function markAttendance($eventId, $userId) {
        try {
            $query = "UPDATE event_participants 
                      SET status = 'hadir', attended_at = CURRENT_TIMESTAMP 
                      WHERE event_id = ? AND user_id = ?";
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                return false;
            }
            
            $stmt->bind_param("ii", $eventId, $userId);
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("markAttendance error: " . $e->getMessage());
            return false;
        }
    }
    public function countMahasiswaEvents($mahasiswaId) {
    try {
        $query = "SELECT COUNT(*) as count 
                  FROM event_participants ep
                  JOIN mahasiswa m ON ep.user_id = m.user_id
                  WHERE m.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $mahasiswaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)($row['count'] ?? 0);
        
    } catch (Exception $e) {
        error_log("Error counting mahasiswa events: " . $e->getMessage());
        return 0;
    }
}

public function countAttendedEvents($mahasiswaId) {
    try {
        $query = "SELECT COUNT(*) as count 
                  FROM event_participants ep
                  JOIN mahasiswa m ON ep.user_id = m.user_id
                  WHERE m.id = ? AND ep.status = 'hadir'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $mahasiswaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)($row['count'] ?? 0);
        
    } catch (Exception $e) {
        error_log("Error counting attended events: " . $e->getMessage());
        return 0;
    }
}

public function countUpcomingEvents($mahasiswaId) {
    try {
        $query = "SELECT COUNT(*) as count 
                  FROM event_participants ep
                  JOIN events e ON ep.event_id = e.id
                  JOIN mahasiswa m ON ep.user_id = m.user_id
                  WHERE m.id = ? 
                  AND e.tanggal_mulai > NOW()
                  AND e.status = 'aktif'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $mahasiswaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)($row['count'] ?? 0);
        
    } catch (Exception $e) {
        error_log("Error counting upcoming events: " . $e->getMessage());
        return 0;
    }
}

// Event Registration Methods
public function isMahasiswaRegistered($eventId, $mahasiswaId) {
    try {
        $query = "SELECT COUNT(*) as count 
                  FROM event_participants ep
                  JOIN mahasiswa m ON ep.user_id = m.user_id
                  WHERE ep.event_id = ? AND m.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $eventId, $mahasiswaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return ($row['count'] ?? 0) > 0;
        
    } catch (Exception $e) {
        error_log("Error checking mahasiswa registration: " . $e->getMessage());
        return false;
    }
}

public function isEventFull($eventId) {
    try {
        $query = "SELECT e.kapasitas, COUNT(ep.id) as registered_count
                  FROM events e
                  LEFT JOIN event_participants ep ON e.id = ep.event_id
                  WHERE e.id = ?
                  GROUP BY e.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if (!$row) return true; // Event not found, consider it full
        
        return ($row['registered_count'] ?? 0) >= ($row['kapasitas'] ?? 0);
        
    } catch (Exception $e) {
        error_log("Error checking if event is full: " . $e->getMessage());
        return true; // Return true to be safe
    }
}

public function registerMahasiswaToEvent($eventId, $mahasiswaId) {
    try {
        // Get user_id from mahasiswa_id
        $userQuery = "SELECT user_id FROM mahasiswa WHERE id = ?";
        $stmt = $this->conn->prepare($userQuery);
        $stmt->bind_param("i", $mahasiswaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $mahasiswa = $result->fetch_assoc();
        
        if (!$mahasiswa) {
            throw new Exception("Mahasiswa not found");
        }
        
        $userId = $mahasiswa['user_id'];
        
        // Register to event
        $query = "INSERT INTO event_participants (event_id, user_id, status, registered_at) 
                  VALUES (?, ?, 'terdaftar', CURRENT_TIMESTAMP)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $eventId, $userId);
        
        return $stmt->execute();
        
    } catch (Exception $e) {
        error_log("Error registering mahasiswa to event: " . $e->getMessage());
        return false;
    }
}

public function cancelMahasiswaRegistration($eventId, $mahasiswaId) {
    try {
        // Get user_id from mahasiswa_id
        $userQuery = "SELECT user_id FROM mahasiswa WHERE id = ?";
        $stmt = $this->conn->prepare($userQuery);
        $stmt->bind_param("i", $mahasiswaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $mahasiswa = $result->fetch_assoc();
        
        if (!$mahasiswa) {
            throw new Exception("Mahasiswa not found");
        }
        
        $userId = $mahasiswa['user_id'];
        
        // Cancel registration
        $query = "DELETE FROM event_participants WHERE event_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $eventId, $userId);
        
        return $stmt->execute();
        
    } catch (Exception $e) {
        error_log("Error canceling mahasiswa registration: " . $e->getMessage());
        return false;
    }
}

// Event Recommendations and Discovery
public function getRecommendationsForMahasiswa($fakultas, $minat, $mahasiswaId) {
    try {
        $query = "SELECT e.*, o.nama_organisasi,
                         COUNT(ep.id) as registered_count,
                         (e.kapasitas - COUNT(ep.id)) as remaining_slots
                  FROM events e
                  JOIN organisasi o ON e.organisasi_id = o.id
                  LEFT JOIN event_participants ep ON e.id = ep.event_id
                  WHERE e.status = 'aktif' 
                  AND e.tanggal_mulai > NOW()
                  AND e.id NOT IN (
                      SELECT ep2.event_id FROM event_participants ep2 
                      JOIN mahasiswa m ON ep2.user_id = m.user_id 
                      WHERE m.id = ?
                  )
                  AND (e.kategori LIKE ? OR e.nama_event LIKE ? OR e.deskripsi LIKE ?)
                  GROUP BY e.id
                  HAVING remaining_slots > 0
                  ORDER BY e.tanggal_mulai ASC
                  LIMIT 6";
        
        $minatPattern = "%$minat%";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isss", $mahasiswaId, $minatPattern, $minatPattern, $minatPattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = $row;
        }
        
        return $recommendations;
        
    } catch (Exception $e) {
        error_log("Error getting event recommendations: " . $e->getMessage());
        return [];
    }
}

public function getDistinctCategories() {
    try {
        $query = "SELECT DISTINCT kategori FROM events WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori";
        $result = $this->conn->query($query);
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['kategori'];
        }
        
        return $categories;
        
    } catch (Exception $e) {
        error_log("Error getting distinct categories: " . $e->getMessage());
        return [];
    }
}

public function getParticipationHistory($mahasiswaId) {
    try {
        $query = "SELECT e.*, o.nama_organisasi, ep.status as participation_status, 
                         ep.registered_at, ep.attended_at
                  FROM event_participants ep
                  JOIN events e ON ep.event_id = e.id
                  JOIN organisasi o ON e.organisasi_id = o.id
                  JOIN mahasiswa m ON ep.user_id = m.user_id
                  WHERE m.id = ?
                  ORDER BY ep.registered_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $mahasiswaId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        
        return $history;
        
    } catch (Exception $e) {
        error_log("Error getting participation history: " . $e->getMessage());
        return [];
    }
}

// Recent Events Methods
public function getRecentEventsForMahasiswa($mahasiswaId, $limit = 5) {
    try {
        $query = "SELECT e.*, o.nama_organisasi, 
                         COUNT(ep.id) as registered_count,
                         (e.kapasitas - COUNT(ep.id)) as remaining_slots,
                         CASE WHEN mep.event_id IS NOT NULL THEN 1 ELSE 0 END as is_registered
                  FROM events e
                  JOIN organisasi o ON e.organisasi_id = o.id
                  LEFT JOIN event_participants ep ON e.id = ep.event_id
                  LEFT JOIN event_participants mep ON e.id = mep.event_id 
                       AND mep.user_id = (SELECT user_id FROM mahasiswa WHERE id = ?)
                  WHERE e.status = 'aktif' 
                  AND e.tanggal_mulai > NOW()
                  GROUP BY e.id
                  ORDER BY e.created_at DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $mahasiswaId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        return $events;
        
    } catch (Exception $e) {
        error_log("Error getting recent events for mahasiswa: " . $e->getMessage());
        return [];
    }
}

// Available Events with Filters
public function getAvailableEvents($limit = null, $offset = 0, $filters = []) {
    try {
        $query = "SELECT e.*, o.nama_organisasi, 
                         COUNT(ep.id) as registered_count,
                         (e.kapasitas - COUNT(ep.id)) as remaining_slots
                  FROM events e
                  JOIN organisasi o ON e.organisasi_id = o.id
                  LEFT JOIN event_participants ep ON e.id = ep.event_id
                  WHERE e.status = 'aktif' 
                  AND e.tanggal_mulai > NOW()";
        
        $params = [];
        $types = "";
        
        // Add filters
        if (!empty($filters['category'])) {
            $query .= " AND e.kategori = ?";
            $params[] = $filters['category'];
            $types .= "s";
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (e.nama_event LIKE ? OR e.deskripsi LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        $query .= " GROUP BY e.id
                   HAVING remaining_slots > 0
                   ORDER BY e.tanggal_mulai ASC";
        
        if ($limit) {
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        return $events;
        
    } catch (Exception $e) {
        error_log("Error getting available events: " . $e->getMessage());
        return [];
    }
}

// Mahasiswa Event Management
public function getMahasiswaEvents($mahasiswaId) {
    try {
        $query = "SELECT e.*, o.nama_organisasi, ep.status as participation_status, 
                         ep.registered_at, ep.attended_at
                  FROM event_participants ep
                  JOIN events e ON ep.event_id = e.id
                  JOIN organisasi o ON e.organisasi_id = o.id
                  JOIN mahasiswa m ON ep.user_id = m.user_id
                  WHERE m.id = ?
                  ORDER BY e.tanggal_mulai DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $mahasiswaId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        return $events;
        
    } catch (Exception $e) {
        error_log("Error getting mahasiswa events: " . $e->getMessage());
        return [];
    }
}

public function getMahasiswaEventHistory($mahasiswaId) {
    try {
        $query = "SELECT e.*, o.nama_organisasi, ep.status as participation_status, 
                         ep.registered_at, ep.attended_at
                  FROM event_participants ep
                  JOIN events e ON ep.event_id = e.id
                  JOIN organisasi o ON e.organisasi_id = o.id
                  JOIN mahasiswa m ON ep.user_id = m.user_id
                  WHERE m.id = ? AND e.status = 'selesai'
                  ORDER BY e.tanggal_mulai DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $mahasiswaId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        return $events;
        
    } catch (Exception $e) {
        error_log("Error getting mahasiswa event history: " . $e->getMessage());
        return [];
    }
}

public function getMahasiswaEventSchedule($mahasiswaId) {
    try {
        $query = "SELECT e.*, o.nama_organisasi, ep.status as participation_status
                  FROM event_participants ep
                  JOIN events e ON ep.event_id = e.id
                  JOIN organisasi o ON e.organisasi_id = o.id
                  JOIN mahasiswa m ON ep.user_id = m.user_id
                  WHERE m.id = ? 
                  AND e.status IN ('aktif', 'draft')
                  AND e.tanggal_mulai >= CURDATE()
                  ORDER BY e.tanggal_mulai ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $mahasiswaId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        return $events;
        
    } catch (Exception $e) {
        error_log("Error getting mahasiswa event schedule: " . $e->getMessage());
        return [];
    }
}
}