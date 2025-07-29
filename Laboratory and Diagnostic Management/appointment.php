<?php
include '../../SQL/config.php';
require_once '../Laboratory and Diagnostic Management/appointment_class.php';
require_once '../Laboratory and Diagnostic Management/test_class.php';
if (!isset($_SESSION['labtech']) || $_SESSION['labtech'] !== true) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo "User ID is not set in session.";
    exit();
}

$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "No user found.";
    exit();
}

//main content
$labdiagnostic_appointments = new labdiagnostic_appointments($conn);

//Get all appointments for listing
$appointments = $labdiagnostic_appointments->getAll()->get_result()->fetch_all(MYSQLI_ASSOC);

//Get patients,dr.,and test id
$patients = $conn->query("SELECT patient_id, name FROM patients")->fetch_all(MYSQLI_ASSOC);
$doctors = $conn->query("SELECT doctor_id, name FROM doctors")->fetch_all(MYSQLI_ASSOC);
$labdiagnostic_tests = $conn->query("SELECT test_id, test_name FROM labdiagnostic_tests")->fetch_all(MYSQLI_ASSOC);

//handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $labdiagnostic_appointments->test_id = $_POST['test_id'];
        $labdiagnostic_appointments->patient_id = $_POST['patient_id'];
        $labdiagnostic_appointments->doctor_id = $_POST['doctor_id'];
        $labdiagnostic_appointments->scheduled_datetime = $_POST['scheduled_datetime'];
        $labdiagnostic_appointments->end_datetime = $_POST['end_datetime'];
        $labdiagnostic_appointments->status = $_POST['status'];
        $labdiagnostic_appointments->notes = $_POST['notes'];

        if ($labdiagnostic_appointments->create()) {
            $message = "Appointment created successfully.";
            // Refresh appointments list
            $appointments = $labdiagnostic_appointments->getAll()->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $error = "Unable to create appointment.";
        }
    } 
    elseif (isset($_POST['update'])) {
        $labdiagnostic_appointments->appointment_id = $_POST['appointment_id'];
        $labdiagnostic_appointments->test_id = $_POST['test_id'];
        $labdiagnostic_appointments->patient_id = $_POST['patient_id'];
        $labdiagnostic_appointments->doctor_id = $_POST['doctor_id'];
        $labdiagnostic_appointments->scheduled_datetime = $_POST['scheduled_datetime'];
        $labdiagnostic_appointments->end_datetime = $_POST['end_datetime'];
        $labdiagnostic_appointments->status = $_POST['status'];
        $labdiagnostic_appointments->notes = $_POST['notes'];

        if ($labdiagnostic_appointments->update()) {
            $message = "Appointment updated successfully.";
            // Refresh appointments list
            $appointments = $labdiagnostic_appointments->getAll()->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $error = "Unable to update appointment.";
        }
    } 
    elseif (isset($_POST['delete'])) {
        $labdiagnostic_appointments->appointment_id = $_POST['appointment_id'];

        if ($labdiagnostic_appointments->delete()) {
            $message = "Appointment deleted successfully.";
            // Refresh appointments list
            $appointments = $labdiagnostic_appointments->getAll()->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $error = "Unable to delete appointment.";
        }
    }
}

// Get all appointments for listing (if not already refreshed above)
if (!isset($appointments)) {
    $appointments = $labdiagnostic_appointments->getAll()->get_result()->fetch_all(MYSQLI_ASSOC);
}



// Get appointment statistics by status
$statusResult = $labdiagnostic_appointments->getCountByStatus()->get_result();
$statusStats = [
    'booked' => 0,
    'completed' => 0,
    'cancelled' => 0,
    'no_show' => 0
];

if ($statusResult) {
    foreach ($statusResult->fetch_all(MYSQLI_ASSOC) as $row) {
        $statusStats[$row['status']] = $row['count'];
    }
    $statusResult->free(); 
}

// Get current date for view of calendar
$currentDate = date('Y-m-d');
$currentMonth = date('Y-m');
$daysInMonth = date('t', strtotime($currentMonth));
$firstDayOfMonth = date('N', strtotime($currentMonth . '-01')); 
    

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS | Laboratory and Diagnostic Management</title>
    <link rel="shortcut icon" href="assets/image/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="assets/CSS/super.css">
    <link rel="stylesheet" href="assets/CSS/appointment.css">
</head>
<style>
    </style>
<body>
    <div class="d-flex">
        <!----- Sidebar ----->
        <aside id="sidebar" class="sidebar-toggle">

            <div class="sidebar-logo mt-3">
                <img src="assets/image/logo-dark.png" width="90px" height="20px">
            </div>

            <div class="menu-title">Navigation</div>

            <!----- Sidebar Navigation ----->
        
            <li class="sidebar-item">
                <a href="admin_dashboard.php" class="sidebar-link" data-bs-toggle="#" data-bs-target="#"
                    aria-expanded="false" aria-controls="auth">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cast" viewBox="0 0 16 16">
                        <path d="m7.646 9.354-3.792 3.792a.5.5 0 0 0 .353.854h7.586a.5.5 0 0 0 .354-.854L8.354 9.354a.5.5 0 0 0-.708 0" />
                        <path d="M11.414 11H14.5a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .5.5h3.086l-1 1H1.5A1.5 1.5 0 0 1 0 10.5v-7A1.5 1.5 0 0 1 1.5 2h13A1.5 1.5 0 0 1 16 3.5v7a1.5 1.5 0 0 1-1.5 1.5h-2.086z" />
                    </svg>
                    <span style="font-size: 18px;">Dashboard</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse" data-bs-target="#labtech"
                    aria-expanded="true" aria-controls="auth">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-building"
                        viewBox="0 0 16 16" style="margin-bottom: 7px;">
                        <path
                            d="M4 2.5a.5.5 0
             0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zM4 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM7.5 5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z" />
                        <path
                            d="M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1zm11 0H3v14h3v-2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V15h3z" />
                    </svg>
                    <span style="font-size: 18px;">Laboratory and Diagnostic Management</span>
                </a>

                <ul id="labtech" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a href="../Laboratory and Diagnostic Management/labtech_dashboard.php" class="sidebar-link">Test Available</a>
                    </li>
                    <li class="sidebar-item">
                        <a href="../Laboratory and Diagnostic Management/appointment.php" class="sidebar-link">Appointment</a>
                    </li>
                    <li class="sidebar-item">
                        <a href="payroll.php" class="sidebar-link">Payroll</a>
                    </li>
                </ul>
            </li>
        </aside>
        <!----- End of Sidebar ----->
        <!----- Main Content ----->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <button class="toggler-btn" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30px" height="30px" fill="currentColor" class="bi bi-list-ul"
                            viewBox="0 0 16 16">
                            <path fill-rule="evenodd"
                                d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
                        </svg>
                    </button>
                </div>
                <div class="logo">
                    <div class="dropdown d-flex align-items-center">
                        <span class="username ml-1 me-2"><?php echo $user['fname']; ?> <?php echo $user['lname']; ?></span><!-- Display the logged-in user's name -->
                        <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="min-width: 200px; padding: 10px; border-radius: 5px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); background-color: #fff; color: #333;">
                            <li style="margin-bottom: 8px; font-size: 14px; color: #555;">
                                <span>Welcome <strong style="color: #007bff;"><?php echo $user['lname']; ?></strong>!</span>
                            </li>
                            <li>
                                <a class="dropdown-item" href="../logout.php" style="font-size: 14px; color: #007bff; text-decoration: none; padding: 8px 12px; border-radius: 4px; transition: background-color 0.3s ease;">
                                    Logout
                                </a>
                            </li>
                        </ul>

                    </div>
                </div>
            </div>
            <!-- START CODING HERE -->
<div class="container">
        <h1>Appointment</h1>

        <?php if (!empty($message)): ?>
            <div class="alert success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="dashboard-ngani">
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Booked</h3>
                    <div class="stat-value"><?php echo $statusStats['booked'] ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Completed</h3>
                    <div class="stat-value"><?php echo $statusStats['completed'] ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Cancelled</h3>
                    <div class="stat-value"><?php echo $statusStats['cancelled'] ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <h3>No Show</h3>
                    <div class="stat-value"><?php echo $statusStats['no_show'] ?? 0; ?></div>
                </div>
            </div>
        </div>
        
        <div class="tabs">
            <button class="tab-btn active" data-tab="list">View Appointment</button>
            <button class="tab-btn" data-tab="calendar">Calendar View</button>
            <button class="tab-btn" data-tab="new">New Appointment</button>
        </div>
        
        <div class="tab-content">
            <!--View appointment here-->
            <div class="tab-pane active" id="list">
                <div class="card">
                    <h2>Appointment List</h2>
                    <div class="filters">
                        <div class="filter-group">
                            <label for="status-filter">Status:</label>
                            <select id="status-filter">
                                <option value="">All</option>
                                <option value="booked">Booked</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="no_show">No Show</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="date-filter">Date:</label>
                            <input type="date"id="date-filter">
                   </div>
                        <button id="reset-filters">Reset Filters</button>
                </div>
                    <div class="table-responsive">
                        <table id="appointmentsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Test</th>
                                    <th>Scheduled Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <!--appointment table backend-->
                            <?php if (!empty($appointments)): ?>
                                    <?php foreach ($appointments as $row): ?>
                                        <tr data-status="<?php echo $row['status']; ?>" 
                                        data-date="<?php echo date('Y-m-d', strtotime($row['scheduled_datetime'])); ?>"
                                        data-notes="<?php echo htmlspecialchars($row['notes']); ?>"
                                        data-patient-name="<?php echo htmlspecialchars($row['patient_name']); ?>"
                                        data-doctor-name="<?php echo htmlspecialchars($row['doctor_name']); ?>">
                                            <td><?php echo $row['appointment_id']; ?></td>
                                            <td><?php echo $row['test_name'] ?? 'Unknown'; ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($row['scheduled_datetime'])); ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($row['end_datetime'])); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $row['status']; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn-view" data-id="<?php echo $row['appointment_id']; ?>">View</button>
                                                <button class="btn-edit" data-id="<?php echo $row['appointment_id']; ?>">Edit</button>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                                    <button type="submit" name="delete" class="btn-delete" onclick="return confirm('Are you sure you want to delete this appointment?')">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                   
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Calendar View Tab-->
            <div class="tab-pane" id="calendar">
                <div class="card">
                    <h2>Calendar View</h2>
                    <div class="calendar-controls">
                        <button id="prev-month">&lt; Prev</button>
                        <h3 id="current-month"><?php echo date('F Y'); ?></h3>
                        <button id="next-month">Next &gt;</button>
            </div>
            <div class="calendar">
                <div class="weekdays">
                    <div>Sunday</div>
                    <div>Monday</div>
                    <div>Tuesday</div>
                    <div>Wednesday</div>
                    <div>Thursday</div>
                    <div>Friday</div>
                    <div>Saturday</div>
            </div>
                 <div class="days" id="calendar-days">
                    <!--calendar generate by js here-->
                 </div>
            </div>
            <div id="day-appointments" class="day-appointments">
                <h3>Appoinment for <span id="select-date"><?php echo date('F d, Y'); ?></span></h3>
                <div id="day-appointment-list">
                <!--Day appointment loaded here-->
                <p class="no-appointment">No Appointment for this date.</p>
               </div>
            </div>
        </div>
    </div>
              
            
            <!-- New Appointment Tab -->
            <div class="tab-pane" id="new">
                <div class="card">
                    <h2>Schedule New Appointment</h2>
                    <form id="appointmentForm" method="POST">
                        <input type="hidden" id="appointment_id" name="appointment_id">
                        
            <div class="form-group">
                <label for="patient_id">Patient: </label>
                <select id="patient_id" name="patient_id" required>
                    <option value="">Select Patient</option>
                <?php foreach ($patients as $patients): ?>
                    <option value="<?php echo $patients['patient_id']; ?>">
                <?php echo $patients['name']; ?></option>
                <?php endforeach; ?>
     </select>
</div>
             <div class="form-group">
                <label for="doctor_id">Doctor: </label>
                <select id="doctor_id" name="doctor_id" required>
                    <option value="">Select Doctor</option>
                <?php foreach ($doctors as $doctors): ?>
                    <option value="<?php echo $doctors['doctor_id']; ?>">
                        <?php echo $doctors['name']; ?></option>
                        <?php endforeach; ?>
     </select>
</div>
                        <div class="form-group">
                            <label for="test_id">Test:</label>
                            <select id="test_id" name="test_id" required>
                                <option value="">Select Test</option>
                               <?php foreach ($labdiagnostic_tests as $test): ?>
                                <option value="<?php echo $test['test_id']; ?>">
                                    <?php echo $test['test_name']; ?></option>
                                    <?php endforeach; ?>
     </select>
</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="scheduled_datetime">Start Date & Time:</label>
                                <input type="datetime-local" id="scheduled_datetime" name="scheduled_datetime" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="end_datetime">End Date & Time:</label>
                                <input type="datetime-local" id="end_datetime" name="end_datetime" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select id="status" name="status" required>
                                <option value="booked">Booked</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="no_show">No Show</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Notes:</label>
                            <input type="text" id="notes" name="notes"></input>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" name="create" id="createBtn">Schedule Appointment</button>
                            <button type="submit" name="update" id="updateBtn" style="display: none;">Update Appointment</button>
                            <button type="button" id="cancelBtn" style="display: none;">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!--modal for viewing-->
        <div id="appointmentModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Appointment Details</h2>
                <div id="appointmentDetails">
                    <!--loaded here-->
            </div>
        </div>
    </div>       
</div>

<script>
 document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Add active class to current button and pane
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
            
            // If calendar tab, initialize calendar
            if (tabId === 'calendar') {
                generateCalendar();
            }
        });
    });
    
    // Edit and view functionality
    const appointmentForm = document.getElementById('appointmentForm');
    const createBtn = document.getElementById('createBtn');
    const updateBtn = document.getElementById('updateBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const modal = document.getElementById('appointmentModal');
    const closeModal = document.querySelector('.close');
    
    //Function to fetch appointment details
    function formatDateTimeForInput(dateString) {
    const date = new Date(dateString);
    return date.toISOString().slice(0, 16);  //YYYY-MM-DDTHH:MM
}

    function fetchAppointmentDetails(appointmentId, action) {
        // Find the appointment in the table
        const rows = document.querySelectorAll('#appointmentsTable tbody tr');
        let appointmentData = null;
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells[0].textContent === appointmentId) {
                appointmentData = {
                    appointment_id: cells[0].textContent,
                    test_name: cells[1].textContent,
                    patient_name: row.getAttribute('data-patient-name'),
                    doctor_name: row.getAttribute('data-doctor-name'),
                    scheduled_datetime: cells[2].textContent,
                    end_datetime: cells[3].textContent,
                    status: cells[4].querySelector('.status-badge').textContent.trim().toLowerCase(),
                    notes:row.dataset.notes 
                };
            }
        });
        
        if (appointmentData) {
            if (action === 'edit') {
                // Populate form for editing
                document.getElementById('appointment_id').value = appointmentId;
                
                // Populate patient dropdown
                const patientSelect = document.getElementById('patient_id');
                for (let i = 0; i < patientSelect.options.length; i++) {
                    if (patientSelect.options[i].text === appointmentData.patient_name) {
                        patientSelect.selectedIndex = i;
                        break;
                    }
                }
                
                // Populate doctor dropdown
                const doctorSelect = document.getElementById('doctor_id');
                for (let i = 0; i < doctorSelect.options.length; i++) {
                    if (doctorSelect.options[i].text === appointmentData.doctor_name) {
                        doctorSelect.selectedIndex = i;
                        break;
                    }
                }
                
                // Populate test dropdown
                const testSelect = document.getElementById('test_id');
                for (let i = 0; i < testSelect.options.length; i++) {
                    if (testSelect.options[i].text === appointmentData.test_name) {
                        testSelect.selectedIndex = i;
                        break;
                    }
                }
                
                // Set status dropdown
                const statusSelect = document.getElementById('status');
                statusSelect.value = appointmentData.status;
                
                // Set notes/date and time
                document.getElementById('scheduled_datetime').value = formatDateTimeForInput(appointmentData.scheduled_datetime);
                document.getElementById('end_datetime').value =  formatDateTimeForInput(appointmentData.end_datetime);        
                document.getElementById('notes').value = appointmentData.notes || '';
                
                // Show update and cancel buttons, hide create button
                createBtn.style.display = 'none';
                updateBtn.style.display = 'inline-block';
                cancelBtn.style.display = 'inline-block';
                
            } else if (action === 'view') {
                // Show modal with appointment details
                const detailsHTML = `
                    <div class="detail-row">
                        <strong>Test:</strong> ${appointmentData.test_name}
                    </div>
                    <div class="detail-row">
                        <strong>Patient:</strong> ${appointmentData.patient_name || 'N/A'}
                    </div>
                    <div class="detail-row">
                        <strong>Doctor:</strong> ${appointmentData.doctor_name || 'N/A'}
                    </div>
                    <div class="detail-row">
                        <strong>Scheduled Date:</strong> ${appointmentData.scheduled_datetime}
                    </div>
                    <div class="detail-row">
                        <strong>End Date:</strong> ${appointmentData.end_datetime}
                    </div>
                    <div class="detail-row">
                        <strong>Status:</strong> <span class="status-badge status-${appointmentData.status}">${appointmentData.status.charAt(0).toUpperCase() + appointmentData.status.slice(1)}</span>
                    </div>
                    <div class="detail-row">
                        <strong>Notes:</strong>
                        <p>${appointmentData.notes || 'No notes'}</p>
                    </div>
                `;
                
                document.getElementById('appointmentDetails').innerHTML = detailsHTML;
                modal.style.display = 'block';
            }
        }
    }
    
    // Function to reset form to create mode
    function resetForm() {
        appointmentForm.reset();
        document.getElementById('appointment_id').value = '';
        createBtn.style.display = 'inline-block';
        updateBtn.style.display = 'none';
        cancelBtn.style.display = 'none';
    }
    
    // Add event listeners to edit and view buttons
    function attachButtonListeners() {
        
        const editButtons = document.querySelectorAll('.btn-edit');
        const viewButtons = document.querySelectorAll('.btn-view');
        
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const appointmentId = this.getAttribute('data-id');
                fetchAppointmentDetails(appointmentId, 'edit');
                
                // Switch to new appointment tab
                tabButtons.forEach(btn => {
                    if (btn.getAttribute('data-tab') === 'new') {
                        btn.click();
                    }
                });
            });
        });
        
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const appointmentId = this.getAttribute('data-id');
                fetchAppointmentDetails(appointmentId, 'view');
            });
        });
    }
    
    // Initial attachment of button listeners
    attachButtonListeners();
    
    // Cancel button functionality
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            resetForm();
        });
    }
    
    // Close modal when clicking the X
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Filter functionality
    const statusFilter = document.getElementById('status-filter');
    const dateFilter = document.getElementById('date-filter');
    const resetFiltersBtn = document.getElementById('reset-filters');
    
    function applyFilters() {
        const statusValue = statusFilter ? statusFilter.value : '';
        const dateValue = dateFilter ? dateFilter.value : '';
        
        const rows = document.querySelectorAll('#appointmentsTable tbody tr');
        
        rows.forEach(row => {
            let showRow = true;
            
            if (statusValue && row.getAttribute('data-status') !== statusValue) {
                showRow = false;
            }
            
            if (dateValue && row.getAttribute('data-date') !== dateValue) {
                showRow = false;
            }
            
            row.style.display = showRow ? '' : 'none';
        });
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', applyFilters);
    }
    
    if (dateFilter) {
        dateFilter.addEventListener('change', applyFilters);
    }
    
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            if (statusFilter) statusFilter.value = '';
            if (dateFilter) dateFilter.value = '';
            applyFilters();
        });
    }
    
    // Calendar functionality
    let currentDate = new Date();
    
    function generateCalendar() {
        const calendarDays = document.getElementById('calendar-days');
        const currentMonthElement = document.getElementById('current-month');
        
        if (!calendarDays || !currentMonthElement) return;
        
        
        // Clear previous calendar
        calendarDays.innerHTML = '';
        
        // Set current month display
        currentMonthElement.textContent = currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });
        
        // Get first day of month and number of days
        const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
        const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
        
        // Calculate first day of week (0 = Sunday)
        let firstDayIndex = firstDay.getDay();
        
        // Add empty cells for days before first day of month
        for (let i = 0; i < firstDayIndex; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.classList.add('day', 'empty');
            calendarDays.appendChild(emptyDay);
        }
        
        // Add days of month
        for (let i = 1; i <= lastDay.getDate(); i++) {
            const dayElement = document.createElement('div');
            dayElement.classList.add('day');
            
            // Check if this is today
            const dayDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), i);
            if (dayDate.toDateString() === new Date().toDateString()) {
                dayElement.classList.add('today');
            }
            
            // Add date number
            dayElement.textContent = i;
            
            // Add click event to show appointments for this day
            dayElement.addEventListener('click', function() {
                const selectedDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), i);
                showAppointmentsForDay(selectedDate);
                
                // Remove selected class from all days
                document.querySelectorAll('.day').forEach(day => {
                    day.classList.remove('selected');
                });
                
                // Add selected class to clicked day
                this.classList.add('selected');
            });
            
            calendarDays.appendChild(dayElement);
        }
    }
    
    function showAppointmentsForDay(date) {
        const selectedDateElement = document.getElementById('select-date');
        const appointmentsList = document.getElementById('day-appointment-list');
        
        if (!selectedDateElement || !appointmentsList) return;
        
        // Format date for display
        selectedDateElement.textContent = date.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        // Format date for comparison
        const dateString = date.toISOString().split('T')[0];
        
        // Find appointments for this day
        const rows = document.querySelectorAll('#appointmentsTable tbody tr');
        let appointmentsHTML = '';
        let hasAppointments = false;
        
        rows.forEach(row => {
            if (row.getAttribute('data-date') === dateString) {
                hasAppointments = true;
                const cells = row.querySelectorAll('td');
                appointmentsHTML += `
                    <div class="day-appointment">
                        <div class="appointment-time">${cells[2].textContent}</div>
                        <div class="appointment-details">
                            <div class="appointment-test">${cells[1].textContent}</div>
                            <div class="appointment-status">${cells[4].innerHTML}</div>
                        </div>
                        <div class="appointment-actions">
                            <button class="btn-view" data-id="${cells[0].textContent}">View</button>
                        </div>
                    </div>
                `;
            }
        });
        
        if (hasAppointments) {
            appointmentsList.innerHTML = appointmentsHTML;
            
            // Add event listeners to new view buttons
            appointmentsList.querySelectorAll('.btn-view').forEach(button => {
                button.addEventListener('click', function() {
                    const appointmentId = this.getAttribute('data-id');
                    fetchAppointmentDetails(appointmentId, 'view');
                });
            });
        } else {
            appointmentsList.innerHTML = '<p class="no-appointments">No appointments for this date.</p>';
        }
    }
    
    // Month navigation
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    
    if (prevMonthBtn) {
        prevMonthBtn.addEventListener('click', function() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            generateCalendar();
        });
    }
    
    if (nextMonthBtn) {
        nextMonthBtn.addEventListener('click', function() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            generateCalendar();

            
        });
    }
});


</script>
   

    <!----- End of Main Content ----->

    <script>
        const toggler = document.querySelector(".toggler-btn");
        toggler.addEventListener("click", function() {
            document.querySelector("#sidebar").classList.toggle("collapsed");
        });
    </script>
    <script src="assets/Bootstrap/all.min.js"></script>
    <script src="assets/Bootstrap/bootstrap.bundle.min.js"></script>
    <script src="assets/Bootstrap/fontawesome.min.js"></script>
    <script src="assets/Bootstrap/jq.js"></script>

</body>

</html>