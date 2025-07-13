<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Event - UACAD</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #1e293b;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(102, 126, 234, 0.15);
        }
        
        .sidebar .logo {
            text-align: center;
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin: 24px 0 40px 0;
            padding: 0 20px;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 14px 24px;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            margin: 2px 12px 2px 0;
            border-radius: 0 12px 12px 0;
            font-weight: 500;
            font-size: 14px;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(4px);
        }
        
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.25);
            color: white;
            font-weight: 600;
        }
        
        .sidebar .nav-link i {
            margin-right: 12px;
            width: 18px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 24px;
            min-height: 100vh;
        }
        
        .header {
            background: white;
            padding: 24px 28px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 28px;
            border: 1px solid #e2e8f0;
        }
        
        .page-title {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .page-title h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            color: #64748b;
            font-size: 14px;
        }
        
        .stat-draft i { color: #f59e0b; }
        .stat-active i { color: #10b981; }
        .stat-finished i { color: #6366f1; }
        .stat-cancelled i { color: #ef4444; }
        
        .content-tabs {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #f1f5f9;
            background: #f8fafc;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #64748b;
            font-weight: 600;
            padding: 16px 24px;
        }
        
        .nav-tabs .nav-link.active {
            background: white;
            color: #667eea;
            border-bottom: 3px solid #667eea;
        }
        
        .tab-content {
            padding: 24px;
        }
        
        .event-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.2s ease;
        }
        
        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        
        .event-header {
            display: flex;
            justify-content: between;
            align-items: start;
            margin-bottom: 12px;
        }
        
        .event-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .event-meta {
            display: flex;
            gap: 20px;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 12px;
        }
        
        .event-description {
            color: #64748b;
            margin-bottom: 16px;
        }
        
        .event-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-sm-custom {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-aktif {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-selesai {
            background: #e0e7ff;
            color: #3730a3;
        }
        
        .status-dibatalkan {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .event-poster {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 16px;
        }
        
        .modal-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .participants-badge {
            background: #e0f2fe;
            color: #0277bd;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-university"></i> UACAD
        </div>
        <nav class="nav flex-column">
            <a href="<?= BASE_URL ?>organisasi/dashboard" class="nav-link">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="<?= BASE_URL ?>organisasi/events" class="nav-link active">
                <i class="fas fa-calendar-check"></i> Kelola Event
            </a>
            <a href="<?= BASE_URL ?>organisasi/participants" class="nav-link">
                <i class="fas fa-users"></i> Peserta
            </a>
            <a href="<?= BASE_URL ?>organisasi/analytics" class="nav-link">
                <i class="fas fa-chart-line"></i> Analitik
            </a>
            <a href="<?= BASE_URL ?>organisasi/profile" class="nav-link">
                <i class="fas fa-building"></i> Profil
            </a>
            <a href="<?= BASE_URL ?>auth/logout" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="page-title">
                <div>
                    <h1>Kelola Event</h1>
                    <p class="text-muted">Kelola semua event organisasi Anda dengan mudah</p>
                </div>
                <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#createEventModal">
                    <i class="fas fa-plus"></i> Buat Event Baru
                </button>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="stats-overview">
            <div class="stat-card stat-draft">
                <i class="fas fa-edit"></i>
                <div class="number" id="stat-draft">0</div>
                <div class="label">Draft</div>
            </div>
            <div class="stat-card stat-active">
                <i class="fas fa-play"></i>
                <div class="number" id="stat-active">0</div>
                <div class="label">Aktif</div>
            </div>
            <div class="stat-card stat-finished">
                <i class="fas fa-check"></i>
                <div class="number" id="stat-finished">0</div>
                <div class="label">Selesai</div>
            </div>
            <div class="stat-card stat-cancelled">
                <i class="fas fa-times"></i>
                <div class="number" id="stat-cancelled">0</div>
                <div class="label">Dibatalkan</div>
            </div>
        </div>

        <!-- Content Tabs -->
        <div class="content-tabs">
            <ul class="nav nav-tabs" id="eventTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-events" type="button">
                        <i class="fas fa-play me-2"></i>Event Aktif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft-events" type="button">
                        <i class="fas fa-edit me-2"></i>Draft
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="finished-tab" data-bs-toggle="tab" data-bs-target="#finished-events" type="button">
                        <i class="fas fa-chart-bar me-2"></i>Rekapan Event Selesai
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-events" type="button">
                        <i class="fas fa-list me-2"></i>Semua Event
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="eventTabContent">
                <!-- Active Events -->
                <div class="tab-pane fade show active" id="active-events" role="tabpanel">
                    <div id="active-events-list">
                        <!-- Dynamic content akan dimuat di sini -->
                    </div>
                </div>

                <!-- Draft Events -->
                <div class="tab-pane fade" id="draft-events" role="tabpanel">
                    <div id="draft-events-list">
                        <!-- Dynamic content akan dimuat di sini -->
                    </div>
                </div>

                <!-- Finished Events - Rekapan -->
                <div class="tab-pane fade" id="finished-events" role="tabpanel">
                    <div class="mb-3">
                        <h5>Rekapan Event Selesai</h5>
                        <p class="text-muted">Lihat statistik dan hasil dari event yang telah selesai</p>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="finishedEventsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Event</th>
                                    <th>Tanggal</th>
                                    <th>Peserta</th>
                                    <th>Kapasitas</th>
                                    <th>Tingkat Kehadiran</th>
                                    <th>Rating</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="finished-events-table-body">
                                <!-- Dynamic content akan dimuat di sini -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- All Events -->
                <div class="tab-pane fade" id="all-events" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover" id="allEventsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Poster</th>
                                    <th>Event</th>
                                    <th>Kategori</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Peserta</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="all-events-table-body">
                                <!-- Dynamic content akan dimuat di sini -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Event Modal -->
    <div class="modal fade" id="createEventModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle"></i> <span id="modal-title">Buat Event Baru</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
                </div>
                <form id="eventForm" enctype="multipart/form-data">
                    <input type="hidden" id="event-id" name="event_id">
                    <div class="modal-body">
                        <div id="modal-alert"></div>
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Nama Event <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="event-name" name="nama_event" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" id="event-category" name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="seminar">Seminar</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="kompetisi">Kompetisi</option>
                                    <option value="webinar">Webinar</option>
                                    <option value="pelatihan">Pelatihan</option>
                                    <option value="expo">Expo</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea class="form-control" id="event-description" name="deskripsi" rows="3"></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="event-start" name="tanggal_mulai" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Selesai</label>
                                <input type="datetime-local" class="form-control" id="event-end" name="tanggal_selesai">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Lokasi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="event-location" name="lokasi" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Kapasitas <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="event-capacity" name="kapasitas" min="1" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Upload Poster</label>
                            <input type="file" class="form-control" id="event-poster" name="poster_event" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">Format: JPG, PNG, PDF (Max: 5MB)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded">
                                        <input class="form-check-input" type="radio" name="status" id="status-draft" value="draft" checked>
                                        <label class="form-check-label fw-bold" for="status-draft">
                                            <i class="fas fa-edit text-warning"></i> Draft
                                        </label>
                                        <br><small class="text-muted">Belum dipublikasi</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded">
                                        <input class="form-check-input" type="radio" name="status" id="status-active" value="aktif">
                                        <label class="form-check-label fw-bold" for="status-active">
                                            <i class="fas fa-play text-success"></i> Aktif
                                        </label>
                                        <br><small class="text-muted">Siap menerima pendaftaran</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-save"></i> <span id="submit-text">Simpan Event</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Event Detail Modal -->
    <div class="modal fade" id="eventDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle"></i> Detail Event
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
                </div>
                <div class="modal-body" id="event-detail-content">
                    <!-- Dynamic content -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap5.min.js"></script>

    <script>
        let events = []; // Store all events
        
        // Load events when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadEvents();
            setupEventHandlers();
        });
        
        function loadEvents() {
            // Sample data - dalam implementasi nyata, ambil dari server
            events = [
                {
                    id: 1,
                    nama_event: 'Workshop Web Development',
                    kategori: 'workshop',
                    deskripsi: 'Belajar membuat website modern dengan HTML, CSS, dan JavaScript',
                    tanggal_mulai: '2025-07-25 09:00:00',
                    tanggal_selesai: '2025-07-25 17:00:00',
                    lokasi: 'Lab Komputer A',
                    kapasitas: 50,
                    status: 'aktif',
                    poster: 'workshop_poster.jpg',
                    peserta_terdaftar: 35,
                    peserta_hadir: 32,
                    rating: 4.5
                },
                {
                    id: 2,
                    nama_event: 'Seminar AI & Machine Learning',
                    kategori: 'seminar',
                    deskripsi: 'Membahas perkembangan terkini dalam bidang AI',
                    tanggal_mulai: '2025-07-28 13:00:00',
                    tanggal_selesai: '2025-07-28 16:00:00',
                    lokasi: 'Aula Utama',
                    kapasitas: 100,
                    status: 'draft',
                    poster: null,
                    peserta_terdaftar: 0,
                    peserta_hadir: 0,
                    rating: 0
                },
                {
                    id: 3,
                    nama_event: 'Kompetisi Programming 2025',
                    kategori: 'kompetisi',
                    deskripsi: 'Lomba programming tingkat universitas',
                    tanggal_mulai: '2025-06-15 08:00:00',
                    tanggal_selesai: '2025-06-15 18:00:00',
                    lokasi: 'Lab Programming',
                    kapasitas: 75,
                    status: 'selesai',
                    poster: 'programming_contest.jpg',
                    peserta_terdaftar: 68,
                    peserta_hadir: 65,
                    rating: 4.8
                }
            ];
            
            updateStats();
            renderActiveEvents();
            renderDraftEvents();
            renderFinishedEvents();
            renderAllEvents();
        }
        
        function updateStats() {
            const stats = {
                draft: events.filter(e => e.status === 'draft').length,
                aktif: events.filter(e => e.status === 'aktif').length,
                selesai: events.filter(e => e.status === 'selesai').length,
                dibatalkan: events.filter(e => e.status === 'dibatalkan').length
            };
            
            document.getElementById('stat-draft').textContent = stats.draft;
            document.getElementById('stat-active').textContent = stats.aktif;
            document.getElementById('stat-finished').textContent = stats.selesai;
            document.getElementById('stat-cancelled').textContent = stats.dibatalkan;
        }
        
        function renderActiveEvents() {
            const activeEvents = events.filter(e => e.status === 'aktif');
            const container = document.getElementById('active-events-list');
            
            if (activeEvents.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times text-muted" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h5>Belum Ada Event Aktif</h5>
                        <p class="text-muted">Event yang sudah dipublikasi akan muncul di sini</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = activeEvents.map(event => `
                <div class="event-card">
                    <div class="d-flex">
                        ${event.poster ? `
                            <img src="${BASE_URL}uploads/posters/${event.poster}" 
                                 alt="Poster" class="event-poster">
                        ` : `
                            <div class="event-poster d-flex align-items-center justify-content-center bg-light">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                        `}
                        <div class="flex-grow-1">
                            <div class="event-header">
                                <div>
                                    <div class="event-title">${event.nama_event}</div>
                                    <div class="event-meta">
                                        <span><i class="fas fa-tag"></i> ${event.kategori}</span>
                                        <span><i class="fas fa-calendar"></i> ${formatDate(event.tanggal_mulai)}</span>
                                        <span><i class="fas fa-map-marker-alt"></i> ${event.lokasi}</span>
                                        <span><i class="fas fa-users"></i> ${event.peserta_terdaftar}/${event.kapasitas}</span>
                                    </div>
                                </div>
                                <span class="status-badge status-${event.status}">${event.status}</span>
                            </div>
                            <div class="event-description">${event.deskripsi}</div>
                            <div class="event-actions">
                                <button class="btn btn-sm btn-outline-primary btn-sm-custom" onclick="viewEvent(${event.id})">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                                <button class="btn btn-sm btn-outline-warning btn-sm-custom" onclick="editEvent(${event.id})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-info btn-sm-custom" onclick="viewParticipants(${event.id})">
                                    <i class="fas fa-users"></i> Peserta
                                </button>
                                <button class="btn btn-sm btn-outline-success btn-sm-custom" onclick="duplicateEvent(${event.id})">
                                    <i class="fas fa-copy"></i> Duplikat
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-sm-custom" onclick="deleteEvent(${event.id})">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function renderDraftEvents() {
            const draftEvents = events.filter(e => e.status === 'draft');
            const container = document.getElementById('draft-events-list');
            
            if (draftEvents.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-edit text-muted" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h5>Belum Ada Draft Event</h5>
                        <p class="text-muted">Event yang belum dipublikasi akan muncul di sini</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = draftEvents.map(event => `
                <div class="event-card">
                    <div class="d-flex">
                        ${event.poster ? `
                            <img src="${BASE_URL}uploads/posters/${event.poster}" 
                                 alt="Poster" class="event-poster">
                        ` : `
                            <div class="event-poster d-flex align-items-center justify-content-center bg-light">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                        `}
                        <div class="flex-grow-1">
                            <div class="event-header">
                                <div>
                                    <div class="event-title">${event.nama_event}</div>
                                    <div class="event-meta">
                                        <span><i class="fas fa-tag"></i> ${event.kategori}</span>
                                        <span><i class="fas fa-calendar"></i> ${formatDate(event.tanggal_mulai)}</span>
                                        <span><i class="fas fa-map-marker-alt"></i> ${event.lokasi}</span>
                                        <span><i class="fas fa-users"></i> ${event.kapasitas} peserta</span>
                                    </div>
                                </div>
                                <span class="status-badge status-${event.status}">${event.status}</span>
                            </div>
                            <div class="event-description">${event.deskripsi}</div>
                            <div class="event-actions">
                                <button class="btn btn-sm btn-success btn-sm-custom" onclick="publishEvent(${event.id})">
                                    <i class="fas fa-play"></i> Publikasi
                                </button>
                                <button class="btn btn-sm btn-outline-warning btn-sm-custom" onclick="editEvent(${event.id})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-primary btn-sm-custom" onclick="viewEvent(${event.id})">
                                    <i class="fas fa-eye"></i> Preview
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-sm-custom" onclick="deleteEvent(${event.id})">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function renderFinishedEvents() {
            const finishedEvents = events.filter(e => e.status === 'selesai');
            const tbody = document.getElementById('finished-events-table-body');
            
            if (finishedEvents.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-chart-bar text-muted" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                            <br>Belum ada event yang selesai
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = finishedEvents.map(event => {
                const attendanceRate = event.peserta_hadir > 0 ? 
                    ((event.peserta_hadir / event.peserta_terdaftar) * 100).toFixed(1) : 0;
                
                return `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                ${event.poster ? `
                                    <img src="${BASE_URL}uploads/posters/${event.poster}" 
                                         alt="Poster" style="width: 40px; height: 30px; object-fit: cover; border-radius: 4px; margin-right: 12px;">
                                ` : `
                                    <div style="width: 40px; height: 30px; background: #f1f5f9; border-radius: 4px; margin-right: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image text-muted" style="font-size: 12px;"></i>
                                    </div>
                                `}
                                <div>
                                    <div class="fw-bold">${event.nama_event}</div>
                                    <small class="text-muted">${event.kategori}</small>
                                </div>
                            </div>
                        </td>
                        <td>${formatDate(event.tanggal_mulai)}</td>
                        <td>
                            <span class="participants-badge">${event.peserta_terdaftar} terdaftar</span>
                            <br><small class="text-muted">${event.peserta_hadir} hadir</small>
                        </td>
                        <td>${event.kapasitas}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress" style="width: 60px; height: 8px; margin-right: 8px;">
                                    <div class="progress-bar ${attendanceRate >= 80 ? 'bg-success' : attendanceRate >= 60 ? 'bg-warning' : 'bg-danger'}" 
                                         style="width: ${attendanceRate}%"></div>
                                </div>
                                <small>${attendanceRate}%</small>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                ${Array.from({length: 5}, (_, i) => `
                                    <i class="fas fa-star ${i < Math.floor(event.rating) ? 'text-warning' : 'text-muted'}" style="font-size: 12px;"></i>
                                `).join('')}
                                <span class="ms-2 small">${event.rating}/5</span>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary btn-sm" onclick="viewReport(${event.id})" title="Lihat Laporan">
                                    <i class="fas fa-chart-line"></i>
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="exportReport(${event.id})" title="Export PDF">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn btn-outline-info btn-sm" onclick="viewParticipants(${event.id})" title="Daftar Peserta">
                                    <i class="fas fa-users"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        
        function renderAllEvents() {
            const tbody = document.getElementById('all-events-table-body');
            
            tbody.innerHTML = events.map(event => `
                <tr>
                    <td>
                        ${event.poster ? `
                            <img src="${BASE_URL}uploads/posters/${event.poster}" 
                                 alt="Poster" style="width: 50px; height: 35px; object-fit: cover; border-radius: 6px;">
                        ` : `
                            <div style="width: 50px; height: 35px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                        `}
                    </td>
                    <td>
                        <div class="fw-bold">${event.nama_event}</div>
                        <small class="text-muted">${event.lokasi}</small>
                    </td>
                    <td><span class="badge bg-secondary">${event.kategori}</span></td>
                    <td>${formatDate(event.tanggal_mulai)}</td>
                    <td><span class="status-badge status-${event.status}">${event.status}</span></td>
                    <td>
                        <div>${event.peserta_terdaftar || 0}/${event.kapasitas}</div>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar bg-primary" style="width: ${((event.peserta_terdaftar || 0) / event.kapasitas * 100)}%"></div>
                        </div>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary btn-sm" onclick="viewEvent(${event.id})" title="Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-outline-warning btn-sm" onclick="editEvent(${event.id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="deleteEvent(${event.id})" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
            
            // Initialize DataTable
            if ($.fn.DataTable.isDataTable('#allEventsTable')) {
                $('#allEventsTable').DataTable().destroy();
            }
            
            $('#allEventsTable').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                },
                columnDefs: [
                    { orderable: false, targets: [0, 6] }
                ]
            });
        }
        
        function setupEventHandlers() {
            // Event form submission
            document.getElementById('eventForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const submitText = document.getElementById('submit-text');
                const isEdit = document.getElementById('event-id').value;
                
                // Show loading
                submitBtn.disabled = true;
                submitText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                
                // Simulate API call
                setTimeout(() => {
                    const alertContainer = document.getElementById('modal-alert');
                    
                    if (isEdit) {
                        // Update existing event
                        const eventIndex = events.findIndex(e => e.id == isEdit);
                        if (eventIndex !== -1) {
                            updateEventFromForm(events[eventIndex], formData);
                        }
                        
                        alertContainer.innerHTML = `
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> Event berhasil diperbarui!
                            </div>
                        `;
                    } else {
                        // Create new event
                        const newEvent = createEventFromForm(formData);
                        events.push(newEvent);
                        
                        alertContainer.innerHTML = `
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> Event berhasil dibuat!
                            </div>
                        `;
                    }
                    
                    // Update displays
                    updateStats();
                    renderActiveEvents();
                    renderDraftEvents();
                    renderFinishedEvents();
                    renderAllEvents();
                    
                    // Reset form and close modal after delay
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('createEventModal')).hide();
                        this.reset();
                        document.getElementById('event-id').value = '';
                        alertContainer.innerHTML = '';
                    }, 1500);
                    
                    // Restore button
                    submitBtn.disabled = false;
                    submitText.innerHTML = isEdit ? 'Update Event' : 'Simpan Event';
                    
                }, 1000);
            });
            
            // Reset modal when closed
            document.getElementById('createEventModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('eventForm').reset();
                document.getElementById('event-id').value = '';
                document.getElementById('modal-title').textContent = 'Buat Event Baru';
                document.getElementById('submit-text').textContent = 'Simpan Event';
                document.getElementById('modal-alert').innerHTML = '';
            });
        }
        
        function createEventFromForm(formData) {
            return {
                id: events.length + 1,
                nama_event: formData.get('nama_event'),
                kategori: formData.get('kategori'),
                deskripsi: formData.get('deskripsi'),
                tanggal_mulai: formData.get('tanggal_mulai'),
                tanggal_selesai: formData.get('tanggal_selesai'),
                lokasi: formData.get('lokasi'),
                kapasitas: parseInt(formData.get('kapasitas')),
                status: formData.get('status'),
                poster: formData.get('poster_event')?.name || null,
                peserta_terdaftar: 0,
                peserta_hadir: 0,
                rating: 0
            };
        }
        
        function updateEventFromForm(event, formData) {
            event.nama_event = formData.get('nama_event');
            event.kategori = formData.get('kategori');
            event.deskripsi = formData.get('deskripsi');
            event.tanggal_mulai = formData.get('tanggal_mulai');
            event.tanggal_selesai = formData.get('tanggal_selesai');
            event.lokasi = formData.get('lokasi');
            event.kapasitas = parseInt(formData.get('kapasitas'));
            event.status = formData.get('status');
            
            if (formData.get('poster_event')?.name) {
                event.poster = formData.get('poster_event').name;
            }
        }
        
        function editEvent(id) {
            const event = events.find(e => e.id === id);
            if (!event) return;
            
            // Fill form
            document.getElementById('event-id').value = event.id;
            document.getElementById('event-name').value = event.nama_event;
            document.getElementById('event-category').value = event.kategori;
            document.getElementById('event-description').value = event.deskripsi;
            document.getElementById('event-start').value = event.tanggal_mulai.replace(' ', 'T');
            document.getElementById('event-end').value = event.tanggal_selesai ? event.tanggal_selesai.replace(' ', 'T') : '';
            document.getElementById('event-location').value = event.lokasi;
            document.getElementById('event-capacity').value = event.kapasitas;
            
            // Set status
            document.querySelector(`input[name="status"][value="${event.status}"]`).checked = true;
            
            // Update modal
            document.getElementById('modal-title').textContent = 'Edit Event';
            document.getElementById('submit-text').textContent = 'Update Event';
            
            // Show modal
            new bootstrap.Modal(document.getElementById('createEventModal')).show();
        }
        
        function viewEvent(id) {
            const event = events.find(e => e.id === id);
            if (!event) return;
            
            const attendanceRate = event.peserta_hadir > 0 ? 
                ((event.peserta_hadir / event.peserta_terdaftar) * 100).toFixed(1) : 0;
            
            document.getElementById('event-detail-content').innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        ${event.poster ? `
                            <img src="${BASE_URL}uploads/posters/${event.poster}" 
                                 alt="Poster" class="img-fluid rounded mb-3">
                        ` : `
                            <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height: 200px;">
                                <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                        `}
                        <div class="text-center">
                            <span class="status-badge status-${event.status} fs-6">${event.status}</span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h4>${event.nama_event}</h4>
                        <p class="text-muted mb-3">${event.deskripsi}</p>
                        
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <strong><i class="fas fa-tag text-primary"></i> Kategori:</strong><br>
                                ${event.kategori}
                            </div>
                            <div class="col-sm-6">
                                <strong><i class="fas fa-map-marker-alt text-danger"></i> Lokasi:</strong><br>
                                ${event.lokasi}
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <strong><i class="fas fa-calendar text-success"></i> Tanggal Mulai:</strong><br>
                                ${formatDateTime(event.tanggal_mulai)}
                            </div>
                            <div class="col-sm-6">
                                <strong><i class="fas fa-calendar-check text-info"></i> Tanggal Selesai:</strong><br>
                                ${event.tanggal_selesai ? formatDateTime(event.tanggal_selesai) : 'Tidak ditentukan'}
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <strong><i class="fas fa-users text-warning"></i> Kapasitas:</strong><br>
                                ${event.kapasitas} peserta
                            </div>
                            <div class="col-sm-6">
                                <strong><i class="fas fa-user-check text-success"></i> Terdaftar:</strong><br>
                                ${event.peserta_terdaftar} peserta
                            </div>
                        </div>
                        
                        ${event.status === 'selesai' ? `
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <strong><i class="fas fa-user-friends text-info"></i> Hadir:</strong><br>
                                    ${event.peserta_hadir} peserta (${attendanceRate}%)
                                </div>
                                <div class="col-sm-6">
                                    <strong><i class="fas fa-star text-warning"></i> Rating:</strong><br>
                                    ${event.rating}/5 ⭐
                                </div>
                            </div>
                        ` : ''}
                        
                        <div class="mt-4">
                            <button class="btn btn-primary btn-sm me-2" onclick="editEvent(${event.id})">
                                <i class="fas fa-edit"></i> Edit Event
                            </button>
                            ${event.status === 'aktif' ? `
                                <button class="btn btn-info btn-sm me-2" onclick="viewParticipants(${event.id})">
                                    <i class="fas fa-users"></i> Lihat Peserta
                                </button>
                            ` : ''}
                            ${event.status === 'selesai' ? `
                                <button class="btn btn-success btn-sm me-2" onclick="viewReport(${event.id})">
                                    <i class="fas fa-chart-line"></i> Lihat Laporan
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
        }
        
        function deleteEvent(id) {
            const event = events.find(e => e.id === id);
            if (!event) return;
            
            if (confirm(`Yakin ingin menghapus event "${event.nama_event}"?`)) {
                events = events.filter(e => e.id !== id);
                
                // Update displays
                updateStats();
                renderActiveEvents();
                renderDraftEvents();
                renderFinishedEvents();
                renderAllEvents();
                
                // Show success message
                showToast('Event berhasil dihapus!', 'success');
            }
        }
        
        function publishEvent(id) {
            const event = events.find(e => e.id === id);
            if (!event) return;
            
            if (confirm(`Publikasi event "${event.nama_event}"?`)) {
                event.status = 'aktif';
                
                // Update displays
                updateStats();
                renderActiveEvents();
                renderDraftEvents();
                renderAllEvents();
                
                showToast('Event berhasil dipublikasi!', 'success');
            }
        }
        
        function duplicateEvent(id) {
            const event = events.find(e => e.id === id);
            if (!event) return;
            
            const newEvent = {
                ...event,
                id: events.length + 1,
                nama_event: event.nama_event + ' (Copy)',
                status: 'draft',
                peserta_terdaftar: 0,
                peserta_hadir: 0,
                rating: 0
            };
            
            events.push(newEvent);
            
            // Update displays
            updateStats();
            renderDraftEvents();
            renderAllEvents();
            
            showToast('Event berhasil diduplikasi!', 'success');
        }
        
        function viewParticipants(id) {
            // Redirect to participants page
            window.location.href = `${BASE_URL}organisasi/participants?event=${id}`;
        }
        
        function viewReport(id) {
            // Redirect to report page
            window.location.href = `${BASE_URL}organisasi/reports?event=${id}`;
        }
        
        function exportReport(id) {
            // Download PDF report
            showToast('Laporan sedang diunduh...', 'info');
            // Implement PDF export logic here
        }
        
        // Utility functions
        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }
        
        function formatDateTime(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        function showToast(message, type = 'info') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(toast);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
        
        // Set BASE_URL for JavaScript
        const BASE_URL = '<?= BASE_URL ?>';
        
        console.log('Events page loaded successfully!');
    </script>
</body>
</html>