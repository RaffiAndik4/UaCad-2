<?php
class OrganisasiController extends Controller
{
    private $organisasiModel;
    private $eventModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisasi') {
            $this->redirect('auth/login');
        }

        $this->organisasiModel = $this->model('Organisasi');
        $this->eventModel = $this->model('Event');
    }

    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);

            if (!$orgData) {
                throw new Exception("Data organisasi tidak ditemukan");
            }

            // Get comprehensive event data
            $all_events = $this->eventModel->getEventsByOrganisasiId($orgData['id']);

            // Calculate stats with proper error handling
            $rawStats = $this->eventModel->getStatsByOrganisasiId($orgData['id']);

            // Ensure all required keys exist with default values
            $stats = [
                'total_events' => $rawStats['total_events'] ?? 0,
                'total_capacity' => $rawStats['total_capacity'] ?? 0,
                'active_events' => $rawStats['active_events'] ?? 0,
                'draft' => 0,
                'aktif' => 0,
                'selesai' => 0,
                'dibatalkan' => 0
            ];

            // Calculate detailed status counts
            if (!empty($all_events)) {
                foreach ($all_events as $event) {
                    switch ($event['status']) {
                        case 'draft':
                            $stats['draft']++;
                            break;
                        case 'aktif':
                            $stats['aktif']++;
                            break;
                        case 'selesai':
                            $stats['selesai']++;
                            break;
                        case 'dibatalkan':
                            $stats['dibatalkan']++;
                            break;
                    }
                }
            }

            // Get other data with error handling
            $trend_data = $this->eventModel->getTrendData($orgData['id']) ?? [];
            $category_data = $this->eventModel->getCategoryData($orgData['id']) ?? [];
            $active_events = $this->eventModel->getActiveEvents($orgData['id']) ?? [];

            $data = [
                'title' => 'Dashboard Organisasi',
                'org_data' => $orgData,
                'stats' => $stats,
                'trend_data' => $trend_data,
                'category_data' => $category_data,
                'active_events' => $active_events,
                'all_events' => $all_events, // Include all events for comprehensive data
                'user_session' => [
                    'user_id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'role' => $_SESSION['role']
                ]
            ];

            // Handle AJAX create event
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama_event'])) {
                $this->handleCreateEvent($orgData);
                return;
            }

            $this->view('organisasi/dashboard', $data);

        } catch (Exception $e) {
            error_log("Dashboard error: " . $e->getMessage());

            // Provide safe fallback data
            $this->view('organisasi/dashboard', [
                'title' => 'Dashboard Organisasi',
                'error' => $e->getMessage(),
                'org_data' => [
                    'nama_organisasi' => 'Organisasi',
                    'username' => $_SESSION['username'] ?? 'Admin',
                    'jenis_organisasi' => 'Organisasi'
                ],
                'stats' => [
                    'total_events' => 0,
                    'total_capacity' => 0,
                    'active_events' => 0,
                    'draft' => 0,
                    'aktif' => 0,
                    'selesai' => 0,
                    'dibatalkan' => 0
                ],
                'trend_data' => [],
                'category_data' => [],
                'active_events' => [],
                'all_events' => []
            ]);
        }
    }

    public function events()
    {
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);

            if (!$orgData) {
                throw new Exception("Data organisasi tidak ditemukan");
            }

            // Handle AJAX requests
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';

                switch ($action) {
                    case 'get_events':
                        $this->handleGetEvents($orgData);
                        return;
                    case 'delete_event':
                        $this->handleDeleteEvent();
                        return;
                    case 'publish_event':
                        $this->handlePublishEvent();
                        return;
                }
            }

            // Get all events data
            $all_events = $this->eventModel->getEventsByOrganisasiId($orgData['id']);
            $stats = $this->calculateEventStats($all_events);

            $data = [
                'title' => 'Kelola Event',
                'org_data' => $orgData,
                'all_events' => $all_events,
                'stats' => $stats
            ];

            $this->view('organisasi/events', $data);

        } catch (Exception $e) {
            error_log("Events error: " . $e->getMessage());
            $this->view('organisasi/events', [
                'title' => 'Kelola Event',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function analytics()
    {
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);

            if (!$orgData) {
                throw new Exception("Data organisasi tidak ditemukan");
            }

            // Get comprehensive analytics data
            $all_events = $this->eventModel->getEventsByOrganisasiId($orgData['id']);
            $stats = $this->calculateEventStats($all_events);
            $trend_data = $this->eventModel->getTrendData($orgData['id']);
            $category_data = $this->eventModel->getCategoryData($orgData['id']);

            $data = [
                'title' => 'Analitik Event',
                'org_data' => $orgData,
                'all_events' => $all_events,
                'stats' => $stats,
                'trend_data' => $trend_data,
                'category_data' => $category_data
            ];

            $this->view('organisasi/analytics', $data);

        } catch (Exception $e) {
            error_log("Analytics error: " . $e->getMessage());
            $this->view('organisasi/analytics', [
                'title' => 'Analitik Event',
                'error' => $e->getMessage()
            ]);
        }
    }

    // Update participants method in OrganisasiController.php
    public function participants()
    {
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);

            if (!$orgData) {
                throw new Exception("Data organisasi tidak ditemukan");
            }

            // Handle AJAX requests
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';

                switch ($action) {
                    case 'update_status':
                        $this->updateParticipantStatus($orgData['id']);
                        return;
                    case 'get_detail':
                        $this->getParticipantDetail($orgData['id']);
                        return;
                    case 'delete_participant':
                        $this->deleteParticipant($orgData['id']);
                        return;
                }
            }

            // Handle export
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'export') {
                $this->exportParticipants($orgData['id']);
                return;
            }

            // Get organization events
            $events = $this->eventModel->getEventsByOrganisasiId($orgData['id']);

            // Get selected event
            $selectedEventId = $_GET['event'] ?? null;
            $selectedEvent = null;
            $participants = [];
            $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];

            if ($selectedEventId) {
                $selectedEvent = $this->eventModel->getById($selectedEventId);
                if ($selectedEvent && $selectedEvent['organisasi_id'] == $orgData['id']) {
                    $participants = $this->getEventParticipants($selectedEventId);
                    $stats = $this->calculateParticipantStats($participants);
                }
            }

            $data = [
                'title' => 'Kelola Peserta',
                'org_data' => $orgData,
                'events' => $events,
                'selected_event' => $selectedEvent,
                'participants' => $participants,
                'stats' => $stats,
                'current_page' => 'participants'
            ];

            $this->view('organisasi/participants', $data);

        } catch (Exception $e) {
            error_log("Participants error: " . $e->getMessage());
            $this->view('organisasi/participants', [
                'title' => 'Kelola Peserta',
                'error' => $e->getMessage(),
                'current_page' => 'participants'
            ]);
        }
    }

    // Get event participants with detailed info
    private function getEventParticipants($eventId)
    {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            $query = "SELECT 
                        ep.id as participant_id,
                        ep.status,
                        ep.registered_at,
                        ep.attended_at,
                        ep.notes,
                        u.id as user_id,
                        u.username,
                        u.email,
                        m.id as mahasiswa_id,
                        m.nim,
                        m.nama_lengkap,
                        m.fakultas,
                        m.jurusan,
                        m.angkatan,
                        m.no_hp,
                        m.alamat,
                        m.foto_profil,
                        m.dokumen_jadwal
                      FROM event_participants ep
                      JOIN users u ON ep.user_id = u.id
                      JOIN mahasiswa m ON u.id = m.user_id
                      WHERE ep.event_id = ?
                      ORDER BY ep.registered_at DESC";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $result = $stmt->get_result();

            $participants = [];
            while ($row = $result->fetch_assoc()) {
                $participants[] = $row;
            }

            return $participants;

        } catch (Exception $e) {
            error_log("Error getting event participants: " . $e->getMessage());
            return [];
        }
    }

    // Calculate participant statistics
    private function calculateParticipantStats($participants)
    {
        $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'attended' => 0];

        foreach ($participants as $participant) {
            $stats['total']++;
            $status = $participant['status'];
            if (isset($stats[$status])) {
                $stats[$status]++;
            }
        }

        return $stats;
    }

    // Update participant status
    private function updateParticipantStatus($orgId)
    {
        header('Content-Type: application/json');

        try {
            $participantId = $_POST['participant_id'] ?? null;
            $status = $_POST['status'] ?? null;
            $notes = trim($_POST['notes'] ?? '');

            if (!$participantId || !$status) {
                echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
                return;
            }

            // Validate status
            $validStatuses = ['pending', 'approved', 'rejected', 'attended'];
            if (!in_array($status, $validStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
                return;
            }

            $db = new Database();
            $conn = $db->getConnection();

            // Verify participant belongs to organization's event
            $verifyQuery = "SELECT ep.*, e.organisasi_id 
                           FROM event_participants ep 
                           JOIN events e ON ep.event_id = e.id 
                           WHERE ep.id = ? AND e.organisasi_id = ?";
            $stmt = $conn->prepare($verifyQuery);
            $stmt->bind_param("ii", $participantId, $orgId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Peserta tidak ditemukan']);
                return;
            }

            // Update participant status
            $updateQuery = "UPDATE event_participants SET 
                           status = ?, 
                           notes = ?,
                           attended_at = CASE WHEN ? = 'attended' THEN CURRENT_TIMESTAMP ELSE attended_at END,
                           updated_at = CURRENT_TIMESTAMP
                           WHERE id = ?";

            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("sssi", $status, $notes, $status, $participantId);

            if ($stmt->execute()) {
                $statusText = [
                    'pending' => 'Pending',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'attended' => 'Hadir'
                ];

                echo json_encode([
                    'success' => true,
                    'message' => "Status peserta berhasil diubah menjadi {$statusText[$status]}"
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mengupdate status']);
            }

        } catch (Exception $e) {
            error_log("Update status error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }

    // Get participant detail
    private function getParticipantDetail($orgId)
    {
        header('Content-Type: application/json');

        try {
            $participantId = $_POST['participant_id'] ?? null;

            if (!$participantId) {
                echo json_encode(['success' => false, 'message' => 'ID peserta tidak valid']);
                return;
            }

            $db = new Database();
            $conn = $db->getConnection();

            $query = "SELECT 
                        ep.*,
                        e.nama_event,
                        e.tanggal_mulai,
                        u.username,
                        u.email,
                        u.created_at as user_created,
                        m.nim,
                        m.nama_lengkap,
                        m.fakultas,
                        m.jurusan,
                        m.angkatan,
                        m.minat,
                        m.bio,
                        m.no_hp,
                        m.alamat,
                        m.foto_profil,
                        m.dokumen_jadwal
                      FROM event_participants ep
                      JOIN events e ON ep.event_id = e.id
                      JOIN users u ON ep.user_id = u.id
                      JOIN mahasiswa m ON u.id = m.user_id
                      WHERE ep.id = ? AND e.organisasi_id = ?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $participantId, $orgId);
            $stmt->execute();
            $result = $stmt->get_result();
            $participant = $result->fetch_assoc();

            if (!$participant) {
                echo json_encode(['success' => false, 'message' => 'Peserta tidak ditemukan']);
                return;
            }

            // Generate HTML for modal
            $html = $this->generateParticipantDetailHTML($participant);

            echo json_encode(['success' => true, 'html' => $html]);

        } catch (Exception $e) {
            error_log("Get detail error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }

    // Generate participant detail HTML
    private function generateParticipantDetailHTML($participant)
    {
        $statusBadge = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'approved' => '<span class="badge bg-success">Disetujui</span>',
            'rejected' => '<span class="badge bg-danger">Ditolak</span>',
            'attended' => '<span class="badge bg-primary">Hadir</span>'
        ];

        $html = '
        <div class="row">
            <div class="col-md-4 text-center">
                <div class="participant-detail">
                    <div class="mb-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                            ' . strtoupper(substr($participant['nama_lengkap'], 0, 1)) . '
                        </div>
                    </div>
                    <h5>' . htmlspecialchars($participant['nama_lengkap']) . '</h5>
                    <p class="text-muted">' . htmlspecialchars($participant['nim']) . '</p>
                    ' . ($statusBadge[$participant['status']] ?? '') . '
                </div>
            </div>
            <div class="col-md-8">
                <div class="participant-detail">
                    <div class="detail-label">Informasi Akademik</div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Fakultas:</strong><br>
                            <span class="detail-value">' . htmlspecialchars($participant['fakultas']) . '</span>
                        </div>
                        <div class="col-6">
                            <strong>Jurusan:</strong><br>
                            <span class="detail-value">' . htmlspecialchars($participant['jurusan']) . '</span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <strong>Angkatan:</strong><br>
                            <span class="detail-value">' . htmlspecialchars($participant['angkatan']) . '</span>
                        </div>
                        <div class="col-6">
                            <strong>Minat:</strong><br>
                            <span class="detail-value">' . htmlspecialchars($participant['minat'] ?: '-') . '</span>
                        </div>
                    </div>
                </div>
                
                <div class="participant-detail">
                    <div class="detail-label">Informasi Kontak</div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Email:</strong><br>
                            <span class="detail-value">' . htmlspecialchars($participant['email']) . '</span>
                        </div>
                        <div class="col-6">
                            <strong>No. HP:</strong><br>
                            <span class="detail-value">' . htmlspecialchars($participant['no_hp'] ?: '-') . '</span>
                        </div>
                    </div>
                    ' . ($participant['alamat'] ? '
                    <div class="mt-2">
                        <strong>Alamat:</strong><br>
                        <span class="detail-value">' . htmlspecialchars($participant['alamat']) . '</span>
                    </div>
                    ' : '') . '
                </div>
                
                <div class="participant-detail">
                    <div class="detail-label">Informasi Pendaftaran</div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Tanggal Daftar:</strong><br>
                            <span class="detail-value">' . date('d M Y H:i', strtotime($participant['registered_at'])) . '</span>
                        </div>
                        <div class="col-6">
                            <strong>Event:</strong><br>
                            <span class="detail-value">' . htmlspecialchars($participant['nama_event']) . '</span>
                        </div>
                    </div>
                    ' . ($participant['attended_at'] ? '
                    <div class="mt-2">
                        <strong>Waktu Hadir:</strong><br>
                        <span class="detail-value">' . date('d M Y H:i', strtotime($participant['attended_at'])) . '</span>
                    </div>
                    ' : '') . '
                    ' . ($participant['notes'] ? '
                    <div class="mt-2">
                        <strong>Catatan:</strong><br>
                        <span class="detail-value">' . htmlspecialchars($participant['notes']) . '</span>
                    </div>
                    ' : '') . '
                </div>
                
                ' . ($participant['dokumen_jadwal'] ? '
                <div class="participant-detail">
                    <div class="detail-label">Dokumen</div>
                    <a href="' . BASE_URL . 'uploads/mahasiswa/' . $participant['dokumen_jadwal'] . '" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-file-pdf"></i> Lihat Jadwal Kuliah
                    </a>
                </div>
                ' : '') . '
            </div>
        </div>';

        return $html;
    }

    // Delete participant
    private function deleteParticipant($orgId)
    {
        header('Content-Type: application/json');

        try {
            $participantId = $_POST['participant_id'] ?? null;

            if (!$participantId) {
                echo json_encode(['success' => false, 'message' => 'ID peserta tidak valid']);
                return;
            }

            $db = new Database();
            $conn = $db->getConnection();

            // Verify participant belongs to organization's event
            $verifyQuery = "SELECT ep.*, e.organisasi_id 
                           FROM event_participants ep 
                           JOIN events e ON ep.event_id = e.id 
                           WHERE ep.id = ? AND e.organisasi_id = ?";
            $stmt = $conn->prepare($verifyQuery);
            $stmt->bind_param("ii", $participantId, $orgId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Peserta tidak ditemukan']);
                return;
            }

            // Delete participant
            $deleteQuery = "DELETE FROM event_participants WHERE id = ?";
            $stmt = $conn->prepare($deleteQuery);
            $stmt->bind_param("i", $participantId);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Peserta berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus peserta']);
            }

        } catch (Exception $e) {
            error_log("Delete participant error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }

    // Export participants data
    private function exportParticipants($orgId)
    {
        try {
            $eventId = $_GET['event'] ?? null;
            $format = $_GET['format'] ?? 'csv';

            if (!$eventId) {
                header('Location: ' . BASE_URL . 'organisasi/participants');
                return;
            }

            // Verify event belongs to organization
            $event = $this->eventModel->getById($eventId);
            if (!$event || $event['organisasi_id'] != $orgId) {
                header('Location: ' . BASE_URL . 'organisasi/participants');
                return;
            }

            $participants = $this->getEventParticipants($eventId);

            if ($format === 'excel') {
                $this->exportToExcel($participants, $event['nama_event']);
            } else {
                $this->exportToCSV($participants, $event['nama_event']);
            }

        } catch (Exception $e) {
            error_log("Export error: " . $e->getMessage());
            header('Location: ' . BASE_URL . 'organisasi/participants');
        }
    }

    // Export to CSV
    private function exportToCSV($participants, $eventName)
    {
        $filename = 'peserta_' . preg_replace('/[^a-zA-Z0-9]/', '_', $eventName) . '_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fputs($output, "\xEF\xBB\xBF");

        // Headers
        fputcsv($output, [
            'No',
            'Nama Lengkap',
            'NIM',
            'Email',
            'Fakultas',
            'Jurusan',
            'Angkatan',
            'No HP',
            'Status',
            'Tanggal Daftar',
            'Tanggal Hadir',
            'Catatan'
        ]);

        // Data
        foreach ($participants as $index => $participant) {
            fputcsv($output, [
                $index + 1,
                $participant['nama_lengkap'],
                $participant['nim'],
                $participant['email'],
                $participant['fakultas'],
                $participant['jurusan'],
                $participant['angkatan'],
                $participant['no_hp'] ?: '-',
                ucfirst($participant['status']),
                date('d/m/Y H:i', strtotime($participant['registered_at'])),
                $participant['attended_at'] ? date('d/m/Y H:i', strtotime($participant['attended_at'])) : '-',
                $participant['notes'] ?: '-'
            ]);
        }

        fclose($output);
    }

    // Export to Excel (simple HTML table that Excel can read)
    private function exportToExcel($participants, $eventName)
    {
        $filename = 'peserta_' . preg_replace('/[^a-zA-Z0-9]/', '_', $eventName) . '_' . date('Y-m-d') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF"; // BOM for UTF-8
        echo '<table border="1">';
        echo '<tr>';
        echo '<th>No</th>';
        echo '<th>Nama Lengkap</th>';
        echo '<th>NIM</th>';
        echo '<th>Email</th>';
        echo '<th>Fakultas</th>';
        echo '<th>Jurusan</th>';
        echo '<th>Angkatan</th>';
        echo '<th>No HP</th>';
        echo '<th>Status</th>';
        echo '<th>Tanggal Daftar</th>';
        echo '<th>Tanggal Hadir</th>';
        echo '<th>Catatan</th>';
        echo '</tr>';

        foreach ($participants as $index => $participant) {
            echo '<tr>';
            echo '<td>' . ($index + 1) . '</td>';
            echo '<td>' . htmlspecialchars($participant['nama_lengkap']) . '</td>';
            echo '<td>' . htmlspecialchars($participant['nim']) . '</td>';
            echo '<td>' . htmlspecialchars($participant['email']) . '</td>';
            echo '<td>' . htmlspecialchars($participant['fakultas']) . '</td>';
            echo '<td>' . htmlspecialchars($participant['jurusan']) . '</td>';
            echo '<td>' . htmlspecialchars($participant['angkatan']) . '</td>';
            echo '<td>' . htmlspecialchars($participant['no_hp'] ?: '-') . '</td>';
            echo '<td>' . ucfirst($participant['status']) . '</td>';
            echo '<td>' . date('d/m/Y H:i', strtotime($participant['registered_at'])) . '</td>';
            echo '<td>' . ($participant['attended_at'] ? date('d/m/Y H:i', strtotime($participant['attended_at'])) : '-') . '</td>';
            echo '<td>' . htmlspecialchars($participant['notes'] ?: '-') . '</td>';
            echo '</tr>';
        }

        echo '</table>';
    }
    public function reports()
    {
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);

            if (!$orgData) {
                throw new Exception("Data organisasi tidak ditemukan");
            }

            $eventId = $_GET['event'] ?? null;
            $data = [
                'title' => 'Laporan Event',
                'org_data' => $orgData
            ];

            if ($eventId) {
                $event = $this->eventModel->getById($eventId);
                $report_data = $this->eventModel->getEventReport($eventId);

                $data['event'] = $event;
                $data['report_data'] = $report_data;
            } else {
                $all_reports = $this->eventModel->getAllReports($orgData['id']);
                $data['all_reports'] = $all_reports;
            }

            $this->view('organisasi/reports', $data);

        } catch (Exception $e) {
            error_log("Reports error: " . $e->getMessage());
            $this->view('organisasi/reports', [
                'title' => 'Laporan Event',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function profile()
    {
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);

            if (!$orgData) {
                throw new Exception("Data organisasi tidak ditemukan");
            }

            // Get stats for profile display
            $all_events = $this->eventModel->getEventsByOrganisasiId($orgData['id']);
            $stats = $this->calculateEventStats($all_events);

            $data = [
                'title' => 'Profil Organisasi',
                'org_data' => $orgData,
                'all_events' => $all_events,
                'stats' => $stats
            ];

            // Handle profile update
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handleUpdateProfile($orgData);
                return;
            }

            $this->view('organisasi/profile', $data);

        } catch (Exception $e) {
            error_log("Profile error: " . $e->getMessage());
            $this->view('organisasi/profile', [
                'title' => 'Profil Organisasi',
                'error' => $e->getMessage()
            ]);
        }
    }

    private function handleCreateEvent($orgData)
    {
        header('Content-Type: application/json');

        try {
            // Handle poster upload - SIMPLE VERSION
            $posterName = null;
            if (isset($_FILES['poster_event']) && $_FILES['poster_event']['error'] == 0) {
                $poster = $_FILES['poster_event'];

                // Simple validation
                $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
                $fileExt = strtolower(pathinfo($poster['name'], PATHINFO_EXTENSION));

                if (in_array($fileExt, $allowedTypes) && $poster['size'] <= 5000000) { // 5MB
                    // Create folder if not exists
                    $uploadDir = '../public/uploads/posters/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    // Simple filename
                    $posterName = time() . '_' . $poster['name'];
                    move_uploaded_file($poster['tmp_name'], $uploadDir . $posterName);
                }
            }

            // Simple event data with validation
            $eventData = [
                'organisasi_id' => $orgData['id'],
                'nama_event' => trim($_POST['nama_event'] ?? ''),
                'deskripsi' => trim($_POST['deskripsi'] ?? ''),
                'kategori' => trim($_POST['kategori'] ?? ''),
                'tanggal_mulai' => $_POST['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $_POST['tanggal_selesai'] ?? null,
                'lokasi' => trim($_POST['lokasi'] ?? ''),
                'kapasitas' => intval($_POST['kapasitas'] ?? 0),
                'poster' => $posterName,
                'status' => $_POST['status'] ?? 'draft'
            ];

            // Simple validation
            if (empty($eventData['nama_event']) || empty($eventData['kategori']) || empty($eventData['lokasi'])) {
                echo json_encode(['success' => false, 'message' => 'Data tidak lengkap!']);
                return;
            }

            if ($eventData['kapasitas'] <= 0) {
                echo json_encode(['success' => false, 'message' => 'Kapasitas harus lebih dari 0!']);
                return;
            }

            if (empty($eventData['tanggal_mulai'])) {
                echo json_encode(['success' => false, 'message' => 'Tanggal mulai harus diisi!']);
                return;
            }

            $eventId = $this->eventModel->create($eventData);

            if ($eventId) {
                echo json_encode(['success' => true, 'message' => 'Event berhasil dibuat!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan event!']);
            }

        } catch (Exception $e) {
            error_log("Create event error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    private function handleGetEvents($orgData)
    {
        header('Content-Type: application/json');

        try {
            $all_events = $this->eventModel->getEventsByOrganisasiId($orgData['id']);
            $stats = $this->calculateEventStats($all_events);

            echo json_encode([
                'success' => true,
                'events' => $all_events,
                'stats' => $stats
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function handleDeleteEvent()
    {
        header('Content-Type: application/json');

        try {
            $eventId = $_POST['event_id'] ?? null;

            if (!$eventId) {
                echo json_encode(['success' => false, 'message' => 'Event ID tidak valid!']);
                return;
            }

            if ($this->eventModel->delete($eventId)) {
                echo json_encode(['success' => true, 'message' => 'Event berhasil dihapus!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus event!']);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function handlePublishEvent()
    {
        header('Content-Type: application/json');

        try {
            $eventId = $_POST['event_id'] ?? null;

            if (!$eventId) {
                echo json_encode(['success' => false, 'message' => 'Event ID tidak valid!']);
                return;
            }

            $updateData = ['status' => 'aktif'];

            if ($this->eventModel->update($eventId, $updateData)) {
                echo json_encode(['success' => true, 'message' => 'Event berhasil dipublikasi!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mempublikasi event!']);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function calculateEventStats($events)
    {
        $stats = [
            'total' => count($events),
            'draft' => 0,
            'aktif' => 0,
            'selesai' => 0,
            'dibatalkan' => 0,
            'total_capacity' => 0,
            'total_events' => count($events),
            'active_events' => 0
        ];

        foreach ($events as $event) {
            $stats[$event['status']]++;
            $stats['total_capacity'] += intval($event['kapasitas'] ?? 0);

            if ($event['status'] === 'aktif') {
                $stats['active_events']++;
            }
        }

        return $stats;
    }

    private function handleUpdateProfile($orgData)
    {
        // Handle profile update logic here
        // This is a placeholder for profile update functionality
    }
    // app/controllers/OrganisasiController.php - Tambahkan method
    public function aiRecommendations()
    {
        try {
            $orgData = $this->organisasiModel->getByUserId($_SESSION['user_id']);

            if (!$orgData) {
                throw new Exception("Data organisasi tidak ditemukan");
            }

            $data = [
                'title' => 'AI Recommendations',
                'org_data' => $orgData,
                'current_page' => 'ai-recommendations'
            ];

            $this->view('organisasi/llm-dashboard', $data);

        } catch (Exception $e) {
            error_log("AI Recommendations error: " . $e->getMessage());
            $this->view('organisasi/ai-recommendations', [
                'title' => 'AI Recommendations',
                'error' => $e->getMessage(),
                'current_page' => 'ai-recommendations'
            ]);
        }
    }
}