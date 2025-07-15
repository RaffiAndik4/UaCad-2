<?php
// app/views/mahasiswa/jadwal.php
$mahasiswa_data = $mahasiswa_data ?? ['nama_lengkap' => 'Mahasiswa'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kuliah - UACAD</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: 260px; background: linear-gradient(180deg, #10b981 0%, #059669 100%); z-index: 1000; }
        .sidebar .logo { text-align: center; color: white; font-size: 24px; font-weight: 700; margin: 24px 0 40px 0; }
        .sidebar .nav-link { color: rgba(255,255,255,0.85); padding: 14px 24px; text-decoration: none; display: flex; align-items: center; transition: all 0.2s ease; margin: 2px 12px 2px 0; border-radius: 0 12px 12px 0; font-weight: 500; font-size: 14px; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.15); color: white; transform: translateX(4px); text-decoration: none; }
        .sidebar .nav-link.active { background: rgba(255,255,255,0.25); color: white; font-weight: 600; }
        .sidebar .nav-link i { margin-right: 12px; width: 18px; text-align: center; }
        .main-content { margin-left: 260px; padding: 24px; min-height: 100vh; }
        .page-header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 32px; border-radius: 16px; margin-bottom: 24px; }
        .calendar-container { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        
        /* FullCalendar Customization */
        .fc { font-family: 'Inter', sans-serif; }
        .fc-toolbar { margin-bottom: 20px; }
        .fc-toolbar-title { font-size: 1.5rem; font-weight: 600; color: #1e293b; }
        .fc-button-primary { background: #10b981; border-color: #10b981; }
        .fc-button-primary:hover { background: #059669; border-color: #059669; }
        .fc-event { border-radius: 6px; border: none; padding: 2px 6px; }
        .fc-event-kuliah { background: #3b82f6; }
        .fc-event-tugas { background: #f59e0b; }
        .fc-event-ujian { background: #ef4444; }
        .fc-event-kegiatan { background: #8b5cf6; }
        .fc-daygrid-event { margin: 1px 0; }
        
        /* Modal Styling */
        .modal-header { background: #10b981; color: white; }
        .modal-header .btn-close { filter: invert(1); }
        .form-label { font-weight: 600; color: #374151; }
        .form-control, .form-select { border: 2px solid #e2e8f0; border-radius: 8px; }
        .form-control:focus, .form-select:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
        
        /* Quick Actions */
        .quick-actions { background: white; border-radius: 12px; padding: 20px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .quick-btn { background: #f8fafc; border: 2px solid #e2e8f0; color: #64748b; padding: 12px 16px; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; }
        .quick-btn:hover { background: #10b981; color: white; border-color: #10b981; text-decoration: none; }
        
        /* Legend */
        .legend { background: white; border-radius: 12px; padding: 16px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .legend-item { display: flex; align-items: center; margin-bottom: 8px; }
        .legend-color { width: 16px; height: 16px; border-radius: 4px; margin-right: 8px; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo"><i class="fas fa-graduation-cap"></i> UACAD</div>
        <nav class="nav flex-column">
            <a href="<?= BASE_URL ?>mahasiswa/landing" class="nav-link"><i class="fas fa-home"></i> Home</a>
            <a href="<?= BASE_URL ?>mahasiswa/dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="<?= BASE_URL ?>mahasiswa/kegiatan" class="nav-link"><i class="fas fa-calendar-check"></i> Kegiatan</a>
            <a href="<?= BASE_URL ?>mahasiswa/jadwal" class="nav-link active"><i class="fas fa-calendar-alt"></i> Jadwal</a>
            <a href="<?= BASE_URL ?>mahasiswa/aspirasi" class="nav-link"><i class="fas fa-lightbulb"></i> Aspirasi</a>
            <a href="<?= BASE_URL ?>mahasiswa/profile" class="nav-link"><i class="fas fa-user"></i> Profil</a>
            <a href="<?= BASE_URL ?>auth/logout" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-calendar-alt me-2"></i>Jadwal Kuliah</h1>
                    <p class="mb-0 opacity-90">Kelola jadwal kuliah dan kegiatan akademik Anda</p>
                </div>
                <div>
                    <button class="btn btn-light me-2" onclick="importJadwal()">
                        <i class="fas fa-upload"></i> Import Excel
                    </button>
                    <button class="btn btn-outline-light" onclick="exportJadwal()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="row">
                <div class="col-md-3">
                    <a href="#" class="quick-btn d-block text-center" onclick="addNewEvent('kuliah')">
                        <i class="fas fa-chalkboard-teacher d-block mb-2" style="font-size: 1.5rem;"></i>
                        <span>Tambah Kuliah</span>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="#" class="quick-btn d-block text-center" onclick="addNewEvent('tugas')">
                        <i class="fas fa-tasks d-block mb-2" style="font-size: 1.5rem;"></i>
                        <span>Tambah Tugas</span>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="#" class="quick-btn d-block text-center" onclick="addNewEvent('ujian')">
                        <i class="fas fa-file-alt d-block mb-2" style="font-size: 1.5rem;"></i>
                        <span>Tambah Ujian</span>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="#" class="quick-btn d-block text-center" onclick="addNewEvent('kegiatan')">
                        <i class="fas fa-star d-block mb-2" style="font-size: 1.5rem;"></i>
                        <span>Tambah Kegiatan</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="legend">
            <h6 class="mb-3">Jenis Kegiatan:</h6>
            <div class="row">
                <div class="col-md-3">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #3b82f6;"></div>
                        <span>Kuliah</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #f59e0b;"></div>
                        <span>Tugas</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #ef4444;"></div>
                        <span>Ujian</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #8b5cf6;"></div>
                        <span>Kegiatan</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Add/Edit Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="eventForm">
                    <div class="modal-body">
                        <input type="hidden" id="eventId" name="id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Judul <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="eventTitle" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jenis <span class="text-danger">*</span></label>
                                    <select class="form-select" id="eventType" name="type" required>
                                        <option value="">Pilih Jenis</option>
                                        <option value="kuliah">Kuliah</option>
                                        <option value="tugas">Tugas</option>
                                        <option value="ujian">Ujian</option>
                                        <option value="kegiatan">Kegiatan</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="eventDate" name="date" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Waktu Mulai</label>
                                    <input type="time" class="form-control" id="eventStartTime" name="start_time">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Waktu Selesai</label>
                                    <input type="time" class="form-control" id="eventEndTime" name="end_time">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Mata Kuliah</label>
                                    <input type="text" class="form-control" id="eventSubject" name="subject">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Dosen/PIC</label>
                                    <input type="text" class="form-control" id="eventLecturer" name="lecturer">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Lokasi</label>
                                    <input type="text" class="form-control" id="eventLocation" name="location">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ruangan</label>
                                    <input type="text" class="form-control" id="eventRoom" name="room">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="eventDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="eventRecurring" name="recurring">
                                <label class="form-check-label" for="eventRecurring">
                                    Jadwal berulang (mingguan)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="deleteBtn" onclick="deleteEvent()" style="display: none;">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Jadwal dari Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="importForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">File Excel (.xlsx)</label>
                            <input type="file" class="form-control" name="excel_file" accept=".xlsx,.xls" required>
                            <div class="form-text">Format: Nama Mata Kuliah | Hari | Jam Mulai | Jam Selesai | Ruangan | Dosen</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        let calendar;
        let currentEvent = null;

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                editable: true,
                droppable: true,
                
                // Load events from database
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetch('<?= BASE_URL ?>mahasiswa/jadwal', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=get_events'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            successCallback(data.events);
                        } else {
                            failureCallback(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading events:', error);
                        failureCallback('Error loading events');
                    });
                },
                
                // Handle date selection
                select: function(arg) {
                    openEventModal(null, arg.startStr);
                },
                
                // Handle event click
                eventClick: function(arg) {
                    currentEvent = arg.event;
                    openEventModal(arg.event);
                },
                
                // Handle event drag/drop
                eventDrop: function(arg) {
                    updateEventDate(arg.event);
                },
                
                // Handle event resize
                eventResize: function(arg) {
                    updateEventDate(arg.event);
                }
            });
            
            calendar.render();
        });

        function openEventModal(event = null, selectedDate = null) {
            const modal = new bootstrap.Modal(document.getElementById('eventModal'));
            const form = document.getElementById('eventForm');
            const deleteBtn = document.getElementById('deleteBtn');
            
            // Reset form
            form.reset();
            
            if (event) {
                // Edit mode
                document.getElementById('modalTitle').textContent = 'Edit Jadwal';
                document.getElementById('eventId').value = event.id;
                document.getElementById('eventTitle').value = event.title;
                document.getElementById('eventType').value = event.extendedProps.type;
                document.getElementById('eventDate').value = event.startStr.split('T')[0];
                document.getElementById('eventStartTime').value = event.extendedProps.start_time || '';
                document.getElementById('eventEndTime').value = event.extendedProps.end_time || '';
                document.getElementById('eventSubject').value = event.extendedProps.subject || '';
                document.getElementById('eventLecturer').value = event.extendedProps.lecturer || '';
                document.getElementById('eventLocation').value = event.extendedProps.location || '';
                document.getElementById('eventRoom').value = event.extendedProps.room || '';
                document.getElementById('eventDescription').value = event.extendedProps.description || '';
                deleteBtn.style.display = 'block';
            } else {
                // Add mode
                document.getElementById('modalTitle').textContent = 'Tambah Jadwal';
                if (selectedDate) {
                    document.getElementById('eventDate').value = selectedDate;
                }
                deleteBtn.style.display = 'none';
            }
            
            modal.show();
        }

        function addNewEvent(type) {
            openEventModal();
            document.getElementById('eventType').value = type;
        }

        // Handle form submission
        document.getElementById('eventForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', document.getElementById('eventId').value ? 'update_event' : 'create_event');
            
            fetch('<?= BASE_URL ?>mahasiswa/jadwal', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    calendar.refetchEvents();
                    bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan sistem', 'danger');
            });
        });

        function deleteEvent() {
            if (confirm('Yakin ingin menghapus jadwal ini?')) {
                const eventId = document.getElementById('eventId').value;
                
                fetch('<?= BASE_URL ?>mahasiswa/jadwal', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete_event&id=${eventId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        calendar.refetchEvents();
                        bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Terjadi kesalahan sistem', 'danger');
                });
            }
        }

        function updateEventDate(event) {
            const formData = new FormData();
            formData.append('action', 'update_event_date');
            formData.append('id', event.id);
            formData.append('start', event.startStr);
            formData.append('end', event.endStr);
            
            fetch('<?= BASE_URL ?>mahasiswa/jadwal', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Jadwal berhasil dipindahkan', 'success');
                } else {
                    showAlert(data.message, 'danger');
                    calendar.refetchEvents(); // Revert if failed
                }
            })
            .catch(error => {
                console.error('Error:', error);
                calendar.refetchEvents(); // Revert on error
            });
        }

        function importJadwal() {
            const modal = new bootstrap.Modal(document.getElementById('importModal'));
            modal.show();
        }

        // Handle import form
        document.getElementById('importForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'import_excel');
            
            fetch('<?= BASE_URL ?>mahasiswa/jadwal', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    calendar.refetchEvents();
                    bootstrap.Modal.getInstance(document.getElementById('importModal')).hide();
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan sistem', 'danger');
            });
        });

        function exportJadwal() {
            window.open('<?= BASE_URL ?>mahasiswa/jadwal?action=export', '_blank');
        }

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alert);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>