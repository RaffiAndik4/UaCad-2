<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Peserta - UACAD</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
                        <i class="fas fa-users"></i> Peserta
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2>Kelola Peserta</h2>
                        <?php if (isset($event)): ?>
                            <p class="text-muted">Event: <?= htmlspecialchars($event['nama_event']) ?></p>
                        <?php endif; ?>
                    </div>
                    <a href="<?= BASE_URL ?>organisasi/events" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Event
                    </a>
                </div>
                
                <?php if (isset($event)): ?>
                    <!-- Event Info -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5><?= htmlspecialchars($event['nama_event']) ?></h5>
                                    <p class="text-muted"><?= htmlspecialchars($event['deskripsi']) ?></p>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <strong>Tanggal:</strong> <?= date('d M Y H:i', strtotime($event['tanggal_mulai'])) ?>
                                        </div>
                                        <div class="col-sm-6">
                                            <strong>Lokasi:</strong> <?= htmlspecialchars($event['lokasi']) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="mb-2">
                                        <span class="badge bg-primary fs-6">
                                            <?= isset($participants) ? count($participants) : 0 ?>/<?= $event['kapasitas'] ?> Peserta
                                        </span>
                                    </div>
                                    <div>
                                        <span class="badge bg-<?= $event['status'] == 'aktif' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($event['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Participants Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-users"></i> Daftar Peserta</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($participants) && !empty($participants)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Nama</th>
                                                <th>NIM</th>
                                                <th>Fakultas</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Tanggal Daftar</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($participants as $index => $participant): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars($participant['nama']) ?></td>
                                                <td><?= htmlspecialchars($participant['nim']) ?></td>
                                                <td><?= htmlspecialchars($participant['fakultas']) ?></td>
                                                <td><?= htmlspecialchars($participant['email']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $participant['status'] == 'hadir' ? 'success' : 'warning' ?>">
                                                        <?= ucfirst($participant['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('d M Y H:i', strtotime($participant['tanggal_daftar'])) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewDetail(<?= $participant['id'] ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($participant['status'] != 'hadir'): ?>
                                                    <button class="btn btn-sm btn-outline-success" onclick="markAttendance(<?= $participant['id'] ?>)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-user-plus text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">Belum Ada Peserta</h5>
                                    <p class="text-muted">Peserta yang mendaftar akan muncul di sini</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- No Event Selected -->
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Pilih Event</h4>
                        <p class="text-muted">Silakan pilih event dari halaman event untuk melihat peserta</p>
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
        function viewDetail(participantId) {
            alert('Fitur detail peserta - ID: ' + participantId);
        }
        
        function markAttendance(participantId) {
            if (confirm('Tandai peserta ini sebagai hadir?')) {
                alert('Peserta ditandai hadir - ID: ' + participantId);
                // Implement AJAX call here
                location.reload();
            }
        }
    </script>
</body>
</html>