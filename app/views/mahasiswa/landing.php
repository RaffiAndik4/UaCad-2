<?php
// app/views/mahasiswa/landing.php
$mahasiswa_data = $mahasiswa_data ?? ['nama_lengkap' => 'Mahasiswa'];
$upcoming_events = $upcoming_events ?? [];
$recommended_events = $recommended_events ?? [];
$ongoing_events = $ongoing_events ?? [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UACAD - Campus Events</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; }
        
        /* Header */
        .top-header { background: white; padding: 12px 0; border-bottom: 1px solid #e2e8f0; }
        .navbar-brand { font-size: 24px; font-weight: 700; color: #1e293b !important; }
        .user-dropdown { display: flex; align-items: center; gap: 8px; }
        
        /* Hero Section */
        .hero-section { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); padding: 60px 0; border-radius: 0 0 24px 24px; margin-bottom: 48px; }
        .hero-content h1 { font-size: 3rem; font-weight: 700; color: #1e293b; margin-bottom: 20px; }
        .hero-content p { font-size: 1.1rem; color: #374151; margin-bottom: 30px; line-height: 1.6; }
        .hero-image { text-align: center; }
        .hero-illustration { max-width: 400px; width: 100%; }
        
        /* Event Cards */
        .event-card { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: all 0.3s ease; margin-bottom: 20px; }
        .event-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .event-image { height: 200px; background: linear-gradient(45deg, #667eea, #764ba2); position: relative; }
        .event-category { position: absolute; top: 12px; left: 12px; background: #fbbf24; color: #1e293b; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .event-content { padding: 20px; }
        .event-title { font-size: 1.1rem; font-weight: 600; color: #1e293b; margin-bottom: 8px; }
        .event-organizer { color: #6b7280; font-size: 14px; margin-bottom: 12px; }
        .event-meta { display: flex; justify-content: space-between; align-items: center; }
        .event-date { color: #6b7280; font-size: 13px; }
        .btn-register { background: #1e293b; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; }
        .btn-register:hover { background: #374151; color: white; }
        
        /* Section Headers */
        .section-header { margin-bottom: 32px; }
        .section-title { font-size: 2.5rem; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
        .section-subtitle { color: #fbbf24; font-size: 2.5rem; font-weight: 700; }
        
        /* Navigation Tabs */
        .event-tabs { margin-bottom: 32px; }
        .tab-button { background: #f1f5f9; color: #64748b; border: none; padding: 12px 24px; border-radius: 24px; margin-right: 12px; font-weight: 500; transition: all 0.3s ease; }
        .tab-button.active { background: #1e293b; color: white; }
        .tab-count { background: #e2e8f0; color: #64748b; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 8px; }
        .tab-button.active .tab-count { background: #374151; color: white; }
        
        /* Stats */
        .stats-section { background: white; border-radius: 16px; padding: 24px; margin-bottom: 32px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="top-header">
        <div class="container">
            <nav class="navbar navbar-expand-lg">
                <a class="navbar-brand" href="#">UACAD</a>
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-dropdown" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                            <span><?= htmlspecialchars($mahasiswa_data['nama_lengkap']) ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>mahasiswa/dashboard"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>mahasiswa/profile"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>auth/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1>Your studies,<br>your way</h1>
                        <p>With UACAD, your studies are fully in your hands — personalize your schedule, keep track of campus events, and get recommendations that match your academic journey. Experience a smarter, more flexible way to manage your academic life, designed to support your goals and help you stay ahead.</p>
                        <button class="btn btn-dark btn-lg">Recommend Event</button>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <svg class="hero-illustration" viewBox="0 0 400 300" fill="none">
                            <rect x="50" y="50" width="300" height="200" rx="12" fill="#e2e8f0"/>
                            <rect x="70" y="70" width="120" height="80" rx="8" fill="#667eea"/>
                            <rect x="200" y="70" width="120" height="80" rx="8" fill="#fbbf24"/>
                            <circle cx="125" cy="180" r="20" fill="#10b981"/>
                            <circle cx="275" cy="180" r="20" fill="#ef4444"/>
                            <rect x="70" y="220" width="260" height="8" rx="4" fill="#d1d5db"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Stats Section -->
        <div class="stats-section">
            <div class="row text-center">
                <div class="col-md-3">
                    <h3 class="mb-1"><?= count($upcoming_events) ?></h3>
                    <p class="text-muted mb-0">Upcoming Events</p>
                </div>
                <div class="col-md-3">
                    <h3 class="mb-1"><?= count($ongoing_events) ?></h3>
                    <p class="text-muted mb-0">Ongoing Events</p>
                </div>
                <div class="col-md-3">
                    <h3 class="mb-1"><?= count($recommended_events) ?></h3>
                    <p class="text-muted mb-0">Recommended</p>
                </div>
                <div class="col-md-3">
                    <h3 class="mb-1">0</h3>
                    <p class="text-muted mb-0">Registered Events</p>
                </div>
            </div>
        </div>

        <!-- Ongoing Events Section -->
        <div class="section-header">
            <h2 class="section-title">Never miss a moment</h2>
            <h2 class="section-subtitle">On Campus</h2>
            <h2 class="section-title">On Going <span style="color: #1e293b;">Event</span></h2>
        </div>

        <div class="row">
            <?php if (!empty($ongoing_events)): ?>
                <?php foreach (array_slice($ongoing_events, 0, 4) as $event): ?>
                <div class="col-md-3">
                    <div class="event-card">
                        <div class="event-image">
                            <span class="event-category"><?= htmlspecialchars($event['kategori']) ?></span>
                        </div>
                        <div class="event-content">
                            <h5 class="event-title"><?= htmlspecialchars($event['nama_event']) ?></h5>
                            <p class="event-organizer"><?= htmlspecialchars($event['nama_organisasi']) ?></p>
                            <div class="event-meta">
                                <span class="event-date">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    <?= date('M d', strtotime($event['tanggal_mulai'])) ?>
                                </span>
                                <button class="btn-register" onclick="registerEvent(<?= $event['id'] ?>)">
                                    Daftar Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-calendar-times text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">No Ongoing Events</h5>
                    <p class="text-muted">Check back later for new events</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- All Events Section -->
        <div class="section-header mt-5">
            <h2 class="section-title">Where your <span class="section-subtitle">academic</span> vision</h2>
            <h2 class="section-title">becomes a reality</h2>
        </div>

        <!-- Event Tabs -->
        <div class="event-tabs">
            <button class="tab-button active" onclick="showTab('upcoming')">
                Upcoming Event <span class="tab-count"><?= count($upcoming_events) ?></span>
            </button>
            <button class="tab-button" onclick="showTab('recommended')">
                Recommend Event <span class="tab-count"><?= count($recommended_events) ?></span>
            </button>
            <button class="tab-button" onclick="showTab('past')">
                Past Event <span class="tab-count">0</span>
            </button>
        </div>

        <!-- Event Grid -->
        <div id="upcoming-events" class="tab-content">
            <div class="row">
                <?php if (!empty($upcoming_events)): ?>
                    <?php foreach ($upcoming_events as $event): ?>
                    <div class="col-md-3">
                        <div class="event-card">
                            <div class="event-image">
                                <span class="event-category"><?= htmlspecialchars($event['kategori']) ?></span>
                            </div>
                            <div class="event-content">
                                <h5 class="event-title"><?= htmlspecialchars($event['nama_event']) ?></h5>
                                <p class="event-organizer"><?= htmlspecialchars($event['nama_organisasi']) ?></p>
                                <div class="event-meta">
                                    <span class="event-date">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?= date('M d', strtotime($event['tanggal_mulai'])) ?>
                                    </span>
                                    <button class="btn-register" onclick="registerEvent(<?= $event['id'] ?>)">
                                        Daftar Sekarang
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-calendar-plus text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No Upcoming Events</h5>
                        <p class="text-muted">New events will appear here</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="recommended-events" class="tab-content" style="display: none;">
            <div class="row">
                <?php if (!empty($recommended_events)): ?>
                    <?php foreach ($recommended_events as $event): ?>
                    <div class="col-md-3">
                        <div class="event-card">
                            <div class="event-image">
                                <span class="event-category"><?= htmlspecialchars($event['kategori']) ?></span>
                            </div>
                            <div class="event-content">
                                <h5 class="event-title"><?= htmlspecialchars($event['nama_event']) ?></h5>
                                <p class="event-organizer"><?= htmlspecialchars($event['nama_organisasi']) ?></p>
                                <div class="event-meta">
                                    <span class="event-date">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?= date('M d', strtotime($event['tanggal_mulai'])) ?>
                                    </span>
                                    <button class="btn-register" onclick="registerEvent(<?= $event['id'] ?>)">
                                        Daftar Sekarang
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-star text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No Recommendations</h5>
                        <p class="text-muted">Complete your profile to get personalized recommendations</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="past-events" class="tab-content" style="display: none;">
            <div class="col-12 text-center py-5">
                <i class="fas fa-history text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3">No Past Events</h5>
                <p class="text-muted">Your event history will appear here</p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-events').style.display = 'block';
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
        
        function registerEvent(eventId) {
            if (confirm('Daftar untuk event ini?')) {
                fetch('<?= BASE_URL ?>mahasiswa/landing', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=register&event_id=${eventId}`
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan sistem');
                });
            }
        }
    </script>
</body>
</html>