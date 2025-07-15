<?php
// app/views/organisasi/participants.php - Simple CRUD Participants Management

// Session check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisasi') {
    header('Location: ' . BASE_URL . 'auth/login');
    exit;
}

// Handle AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $action = $_POST['action'];
        
        switch ($action) {
            case 'update_status':
                $participant_id = intval($_POST['participant_id']);
                $status = trim($_POST['status'] ?? '');
                $reason = trim($_POST['reason'] ?? '');
                
                // Debug more detailed
                error_log("Raw POST data: " . print_r($_POST, true));
                error_log("Status value: '" . $status . "' (length: " . strlen($status) . ")");
                error_log("Status bytes: " . bin2hex($status));
                
            case 'update_status':
                $participant_id = intval($_POST['participant_id']);
                $status = trim($_POST['status'] ?? '');
                $reason = trim($_POST['reason'] ?? '');
                
                // Sanitize and validate status
                $status = strtolower(preg_replace('/[^a-zA-Z]/', '', $status));
                
                // Map status to ensure exact match
                $status_mapping = [
                    'pending' => 'pending',
                    'accepted' => 'accepted',
                    'rejected' => 'rejected'
                ];
                
                if (!array_key_exists($status, $status_mapping)) {
                    error_log("Invalid status received: '$status'");
                    throw new Exception("Status tidak valid: '$status'. Harus pending, accepted, atau rejected");
                }
                
                $final_status = $status_mapping[$status];
                
                // Check if participant exists and belongs to this organization
                $check_stmt = $conn->prepare("
                    SELECT ep.id FROM event_participants ep
                    JOIN events e ON ep.event_id = e.id
                    JOIN organisasi o ON e.organisasi_id = o.id
                    WHERE ep.id = ? AND o.user_id = ?
                ");
                $check_stmt->bind_param("ii", $participant_id, $_SESSION['user_id']);
                $check_stmt->execute();
                
                if ($check_stmt->get_result()->num_rows === 0) {
                    throw new Exception('Peserta tidak ditemukan atau bukan milik organisasi Anda!');
                }
                
                // Update with exact values
                $update_stmt = $conn->prepare("
                    UPDATE event_participants 
                    SET verification_status = ?, 
                        rejected_reason = ?, 
                        verified_by = ?, 
                        verified_at = NOW() 
                    WHERE id = ?
                ");
                $update_stmt->bind_param("ssii", $final_status, $reason, $_SESSION['user_id'], $participant_id);
                
                if ($update_stmt->execute() && $update_stmt->affected_rows > 0) {
                    $message = match($final_status) {
                        'accepted' => 'Peserta berhasil diterima!',
                        'rejected' => 'Peserta berhasil ditolak!',
                        'pending' => 'Status berhasil diubah ke pending!',
                        default => 'Status berhasil diperbarui!'
                    };
                    echo json_encode(['success' => true, 'message' => $message]);
                } else {
                    throw new Exception('Gagal mengupdate status peserta!');
                }
                break;
                
            case 'get_participants':
                $event_id = intval($_POST['event_id']);
                
                $stmt = $conn->prepare("
                    SELECT ep.id, ep.verification_status, ep.rejected_reason, ep.registered_at, ep.attended_at,
                           m.nama_lengkap, m.nim, m.fakultas, m.jurusan, m.angkatan, u.email
                    FROM event_participants ep
                    JOIN users u ON ep.user_id = u.id
                    JOIN mahasiswa m ON u.id = m.user_id
                    JOIN events e ON ep.event_id = e.id
                    JOIN organisasi o ON e.organisasi_id = o.id
                    WHERE ep.event_id = ? AND o.user_id = ?
                    ORDER BY ep.registered_at DESC
                ");
                $stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
                $stmt->execute();
                $participants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                
                echo json_encode(array('success' => true, 'participants' => $participants));
                break;
                
            default:
                echo json_encode(array('success' => false, 'message' => 'Action tidak valid!'));
        }
        
    } catch (Exception $e) {
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
    exit;
}

// Get organization events for dropdown
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT o.*, u.username FROM organisasi o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.user_id = ?
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $org_data = $stmt->get_result()->fetch_assoc();
    
    $stmt = $conn->prepare("
        SELECT e.id, e.nama_event, e.tanggal_mulai, e.status, COUNT(ep.id) as total_participants
        FROM events e
        LEFT JOIN event_participants ep ON e.id = ep.event_id
        JOIN organisasi o ON e.organisasi_id = o.id
        WHERE o.user_id = ?
        GROUP BY e.id
        ORDER BY e.tanggal_mulai DESC
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Peserta - UACAD</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Inter', sans-serif; }
        .sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: 260px; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); z-index: 1000; }
        .main-content { margin-left: 260px; padding: 24px; }
        .page-header { background: white; padding: 24px; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 24px; }
        .content-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .status-pending { background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 12px; font-size: 11px; }
        .status-accepted { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 12px; font-size: 11px; }
        .status-rejected { background: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 12px; font-size: 11px; }
        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 6px; }
        .table-hover tbody tr:hover { background: #f8fafc; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php 
    $current_page = 'participants';
    include '../app/views/layouts/organisasi_sidebar.php'; 
    ?>

    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <h2>Kelola Peserta Event</h2>
            <p class="text-muted">Verifikasi dan kelola pendaftaran peserta untuk event Anda</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="content-card">
            <!-- Event Selection -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Pilih Event:</label>
                    <select class="form-select" id="eventSelect" onchange="loadParticipants()">
                        <option value="">-- Pilih Event --</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?= $event['id'] ?>" data-name="<?= htmlspecialchars($event['nama_event']) ?>">
                                <?= htmlspecialchars($event['nama_event']) ?> 
                                (<?= $event['total_participants'] ?> peserta)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Stats:</label>
                    <div id="eventStats" class="small text-muted">Pilih event untuk melihat statistik</div>
                </div>
            </div>

            <!-- Participants Table -->
            <div id="participantsContainer" style="display: none;">
                <h5 id="participantsTitle">Daftar Peserta</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Mahasiswa</th>
                                <th>NIM</th>
                                <th>Fakultas/Jurusan</th>
                                <th>Status</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="participantsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user"></i> Detail Peserta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent"></div>
            </div>
        </div>
    </div>

    <!-- Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Update Status</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="statusForm">
                    <div class="modal-body">
                        <input type="hidden" id="participantId" name="participant_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Peserta:</label>
                            <p id="participantName" class="fw-bold"></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status:</label>
                            <select class="form-select" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="accepted">Diterima</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="reasonGroup" style="display: none;">
                            <label class="form-label">Alasan Penolakan:</label>
                            <textarea class="form-control" name="reason" rows="3" placeholder="Opsional: berikan alasan penolakan"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        let participants = [];
        
        // Load participants when event selected
        function loadParticipants() {
            const select = document.getElementById('eventSelect');
            const eventId = select.value;
            const eventName = select.options[select.selectedIndex]?.dataset.name || '';
            
            if (!eventId) {
                document.getElementById('participantsContainer').style.display = 'none';
                return;
            }
            
            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_participants&event_id=${eventId}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    participants = data.participants;
                    renderParticipants(eventName);
                    updateStats();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
        
        // Render participants table
        function renderParticipants(eventName) {
            document.getElementById('participantsTitle').textContent = `Peserta Event: ${eventName}`;
            document.getElementById('participantsContainer').style.display = 'block';
            
            const tbody = document.getElementById('participantsTableBody');
            tbody.innerHTML = participants.map((p, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td>
                        <div class="fw-bold">${p.nama_lengkap}</div>
                        <small class="text-muted">${p.email}</small>
                    </td>
                    <td>${p.nim}</td>
                    <td>
                        <div>${p.fakultas}</div>
                        <small class="text-muted">${p.jurusan}</small>
                    </td>
                    <td><span class="status-${p.verification_status}">${getStatusText(p.verification_status)}</span></td>
                    <td>${formatDate(p.registered_at)}</td>
                    <td>
                        <button class="btn btn-info btn-sm me-1" onclick="showDetail(${p.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-warning btn-sm" onclick="updateStatus(${p.id}, '${p.nama_lengkap}', '${p.verification_status}')">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }
        
        // Show participant detail
        function showDetail(participantId) {
            const p = participants.find(participant => participant.id == participantId);
            document.getElementById('detailContent').innerHTML = `
                <table class="table table-borderless">
                    <tr><td><strong>Nama:</strong></td><td>${p.nama_lengkap}</td></tr>
                    <tr><td><strong>NIM:</strong></td><td>${p.nim}</td></tr>
                    <tr><td><strong>Email:</strong></td><td>${p.email}</td></tr>
                    <tr><td><strong>Fakultas:</strong></td><td>${p.fakultas}</td></tr>
                    <tr><td><strong>Jurusan:</strong></td><td>${p.jurusan}</td></tr>
                    <tr><td><strong>Angkatan:</strong></td><td>${p.angkatan}</td></tr>
                    <tr><td><strong>Status:</strong></td><td><span class="status-${p.verification_status}">${getStatusText(p.verification_status)}</span></td></tr>
                    <tr><td><strong>Daftar:</strong></td><td>${formatDate(p.registered_at)}</td></tr>
                    ${p.rejected_reason ? `<tr><td><strong>Alasan Ditolak:</strong></td><td>${p.rejected_reason}</td></tr>` : ''}
                </table>
            `;
            new bootstrap.Modal(document.getElementById('detailModal')).show();
        }
        
        // Update participant status
        function updateStatus(participantId, name, currentStatus) {
            document.getElementById('participantId').value = participantId;
            document.getElementById('participantName').textContent = name;
            document.querySelector('[name="status"]').value = currentStatus;
            
            // Show/hide reason field
            document.querySelector('[name="status"]').onchange = function() {
                document.getElementById('reasonGroup').style.display = this.value === 'rejected' ? 'block' : 'none';
            };
            
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }
        
        // Submit status update
        document.getElementById('statusForm').onsubmit = function(e) {
            e.preventDefault();
            
            const participantId = document.getElementById('participantId').value;
            const status = document.querySelector('[name="status"]').value;
            const reason = document.querySelector('[name="reason"]').value;
            
            console.log('Submitting:', { participantId, status, reason });
            
            // Send as form data
            const params = new URLSearchParams();
            params.append('action', 'update_status');
            params.append('participant_id', participantId);
            params.append('status', status);
            params.append('reason', reason);
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            })
            .then(r => r.json())
            .then(data => {
                console.log('Response:', data);
                if (data.success) {
                    alert(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
                    loadParticipants(); // Reload
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan sistem!');
            });
        };
        
        // Update statistics
        function updateStats() {
            const stats = participants.reduce((acc, p) => {
                acc[p.verification_status] = (acc[p.verification_status] || 0) + 1;
                return acc;
            }, {});
            
            document.getElementById('eventStats').innerHTML = `
                Total: ${participants.length} | 
                Pending: ${stats.pending || 0} | 
                Diterima: ${stats.accepted || 0} | 
                Ditolak: ${stats.rejected || 0}
            `;
        }
        
        // Helper functions
        function getStatusText(status) {
            return { pending: 'Pending', accepted: 'Diterima', rejected: 'Ditolak' }[status];
        }
        
        function formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
            });
        }
        
        console.log('Participants page loaded!');
    </script>
</body>
</html>