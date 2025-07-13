<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Event - UACAD</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body style="background-color: #f8fafc;">
    <div class="container-fluid">
        <div class="row">
            <!-- Simple Sidebar -->
            <div class="col-md-2 bg-primary text-white p-3" style="min-height: 100vh;">
                <h5><i class="fas fa-university"></i> UACAD</h5>
                <hr>
                <div class="nav flex-column">
                    <a href="<?= BASE_URL ?>organisasi/dashboard" class="nav-link text-white">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>organisasi/events" class="nav-link text-white">
                        <i class="fas fa-calendar"></i> Event
                    </a>
                    <a href="#" class="nav-link text-white bg-secondary rounded">
                        <i class="fas fa-chart-line"></i> Laporan
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2>Laporan Event</h2>
                        <?php if (isset($event)): ?>
                            <p class="text-muted">Event: <?= htmlspecialchars($event['nama_event']) ?></p>
                        <?php else: ?>
                            <p class="text-muted">Rekapan semua event selesai</p>
                        <?php endif; ?>
                    </div>
                    <a href="<?= BASE_URL ?>organisasi/events" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Event
                    </a>
                </div>
                
                <?php if (isset($event) && isset($report_data)): ?>
                    <!-- Single Event Report -->
                    <div class="row mb-4">
                        <!-- Summary Cards -->
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-users text-primary" style="font-size: 2rem;"></i>
                                    <h4 class="mt-2"><?= $report_data['total_registered'] ?></h4>
                                    <p class="text-muted">Total Pendaftar</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-user-check text-success" style="font-size: 2rem;"></i>
                                    <h4 class="mt-2"><?= $report_data['total_attended'] ?></h4>
                                    <p class="text-muted">Yang Hadir</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-percentage text-warning" style="font-size: 2rem;"></i>
                                    <h4 class="mt-2"><?= $report_data['attendance_rate'] ?>%</h4>
                                    <p class="text-muted">Tingkat Kehadiran</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-star text-warning" style="font-size: 2rem;"></i>
                                    <h4 class="mt-2"><?= $report_data['rating_average'] ?>/5</h4>
                                    <p class="text-muted">Rating Rata-rata</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Demographics Chart -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-pie"></i> Peserta per Fakultas</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="facultyChart" style="height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Registration Timeline -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-line"></i> Timeline Pendaftaran</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="timelineChart" style="height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Event Details -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle"></i> Detail Event</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Nama Event:</strong></td>
                                            <td><?= htmlspecialchars($event['nama_event']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Kategori:</strong></td>
                                            <td><?= htmlspecialchars($event['kategori']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal:</strong></td>
                                            <td><?= date('d M Y H:i', strtotime($event['tanggal_mulai'])) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Lokasi:</strong></td>
                                            <td><?= htmlspecialchars($event['lokasi']) ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Kapasitas:</strong></td>
                                            <td><?= $event['kapasitas'] ?> peserta</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jumlah Feedback:</strong></td>
                                            <td><?= $report_data['feedback_count'] ?> feedback</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tingkat Kepuasan:</strong></td>
                                            <td><?= $report_data['satisfaction_rate'] ?>%</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td><span class="badge bg-success">Selesai</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <button class="btn btn-primary me-2" onclick="exportPDF()">
                            <i class="fas fa-download"></i> Export PDF
                        </button>
                        <button class="btn btn-success me-2" onclick="exportExcel()">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                        <a href="<?= BASE_URL ?>organisasi/participants?event=<?= $event['id'] ?>" class="btn btn-info">
                            <i class="fas fa-users"></i> Lihat Peserta
                        </a>
                    </div>
                    
                <?php elseif (isset($all_reports)): ?>
                    <!-- All Reports Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-bar"></i> Rekapan Semua Event Selesai</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($all_reports)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Event</th>
                                                <th>Tanggal</th>
                                                <th>Peserta</th>
                                                <th>Kehadiran</th>
                                                <th>Rating</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($all_reports as $report): ?>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong><?= htmlspecialchars($report['event']['nama_event']) ?></strong>
                                                        <br><small class="text-muted"><?= htmlspecialchars($report['event']['kategori']) ?></small>
                                                    </div>
                                                </td>
                                                <td><?= date('d M Y', strtotime($report['event']['tanggal_mulai'])) ?></td>
                                                <td>
                                                    <span class="badge bg-primary"><?= $report['summary']['total_registered'] ?> terdaftar</span>
                                                    <br><small><?= $report['summary']['total_attended'] ?> hadir</small>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar bg-success" style="width: <?= $report['summary']['attendance_rate'] ?>%"></div>
                                                    </div>
                                                    <small><?= $report['summary']['attendance_rate'] ?>%</small>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="me-2"><?= $report['summary']['rating_average'] ?>/5</span>
                                                        <div>
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fas fa-star <?= $i <= $report['summary']['rating_average'] ? 'text-warning' : 'text-muted' ?>" style="font-size: 12px;"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="<?= BASE_URL ?>organisasi/reports?event=<?= $report['event']['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-chart-line text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">Belum Ada Laporan</h5>
                                    <p class="text-muted">Event yang telah selesai akan muncul di sini</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- No Event Selected -->
                    <div class="text-center py-5">
                        <i class="fas fa-chart-line text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Pilih Event</h4>
                        <p class="text-muted">Silakan pilih event dari halaman event untuk melihat laporan</p>
                        <a href="<?= BASE_URL ?>organisasi/events" class="btn btn-primary">
                            <i class="fas fa-calendar"></i> Lihat Event
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if (isset($report_data)): ?>
        // Faculty Chart
        const facultyCtx = document.getElementById('facultyChart').getContext('2d');
        const facultyData = <?= json_encode($report_data['demographics']['by_faculty']) ?>;
        
        new Chart(facultyCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(facultyData),
                datasets: [{
                    data: Object.values(facultyData),
                    backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Timeline Chart
        const timelineCtx = document.getElementById('timelineChart').getContext('2d');
        const timelineData = <?= json_encode($report_data['timeline']) ?>;
        
        new Chart(timelineCtx, {
            type: 'line',
            data: {
                labels: timelineData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                }),
                datasets: [{
                    label: 'Pendaftaran Harian',
                    data: timelineData.map(item => item.registrations),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
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
        <?php endif; ?>
        
        function exportPDF() {
            alert('Fitur export PDF sedang dalam pengembangan');
            // Implement PDF export
        }
        
        function exportExcel() {
            alert('Fitur export Excel sedang dalam pengembangan');
            // Implement Excel export
        }
    </script>
</body>
</html>