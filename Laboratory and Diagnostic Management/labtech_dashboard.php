<?php
include '../../SQL/config.php';
require_once '../Laboratory and Diagnostic Management/test_class.php';

//log in 
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
$labdiagnostic_tests = new labdiagnostic_tests($conn);

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {

        $labdiagnostic_tests->test_code = $_POST['test_code'];
        $labdiagnostic_tests->test_name = $_POST['test_name'];
        $labdiagnostic_tests->description = $_POST['description'];
        $labdiagnostic_tests->category = $_POST['category'];
        $labdiagnostic_tests->preparation_instructions = $_POST['preparation_instructions'];
        $labdiagnostic_tests->estimated_duration = $_POST['estimated_duration'];
        $labdiagnostic_tests->is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($labdiagnostic_tests->create()) {
            $message = "Test created successfully.";
        } else {
            $error = "Unable to create test.";
        }

    } elseif (isset($_POST['update'])) {

        $labdiagnostic_tests->test_id = $_POST['test_id'];
        $labdiagnostic_tests->test_code = $_POST['test_code'];
        $labdiagnostic_tests->test_name = $_POST['test_name'];
        $labdiagnostic_tests->description = $_POST['description'];
        $labdiagnostic_tests->category = $_POST['category'];
        $labdiagnostic_tests->preparation_instructions = $_POST['preparation_instructions'];
        $labdiagnostic_tests->estimated_duration = $_POST['estimated_duration'];
        $labdiagnostic_tests->is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($labdiagnostic_tests->update()) {
            $message = "Test updated successfully.";
        } else {
            $error = "Unable to update test.";
        }

    } elseif (isset($_POST['delete'])) {

        $labdiagnostic_tests->test_id = $_POST['test_id'];

        if ($labdiagnostic_tests->delete()) {
            $message = "Test deleted successfully.";
        } else {
            $error = "Unable to delete test.";
        }
    }
}

// Get all tests
$result = $labdiagnostic_tests->getAll();
$labdiagnostic_tests = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $labdiagnostic_tests[] = $row;
    }
}

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
    <link rel="stylesheet" href="assets/CSS/labtestform.css">
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
                        <a href="#" class="sidebar-link">Test Available</a>
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

        <?php if (!empty($message)): ?>
            <div class="alert success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
    <div class="card">
      <h3>Add New Test</h3>
        <form id="testForm" method="POST">
            <input type="hidden" id="test_id" name="test_id"><!--auto-->
                
        <div class="form-group">
            <label for="test_code">Test Code:</label>
            <input type="text" id="test_code" name="test_code">
        </div>

        <div class="form-group">
             <label for="test_name">Test Name:</label>
             <input type="text" id="test_name" name="test_name">
        </div>
                   
        <div class="form-group">
              <label for="description">Description:</label>
              <input type="text" id="description" name="description">
        </div>
                
        <div class="form-group">
              <label for="category">Category:</label>
              <input type="text" id="category" name="category">
        </div>
                
        <div class="form-group">
               <label for="preparation_instructions">Preparation Instructions:</label>
               <input type="text" id="preparation_instructions" name="preparation_instructions">
                
        <div class="form-group">
               <label for="estimated_duration">Estimated Duration (minutes):</label>
               <input type="number" id="estimated_duration" name="estimated_duration" min="15">
        </div>
                
        <div class="form-group checkbox">
               <input type="checkbox" id="is_active" name="is_active" checked>
               <label for="is_active">Active</label>
        </div>
                
        <div class="form-buttons">
                <button type="submit" name="create" id="createBtn">Create Test</button>
                <button type="submit" name="update" id="updateBtn" style="display: none;">Update Test</button>
                <button type="button" id="cancelBtn" style="display: none;">Cancel</button>
        </div>
    </form>
  </div>
        
        <div class="card">
            <h2>Test List</h2>
            <div class="table-responsive">
                <table id="testsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>CODE</th>
                            <th>NAME</th>
                            <th>CATEGORY</th>
                            <th>DURATION</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                   <tbody>
                        <?php foreach ($labdiagnostic_tests as $row): ?>
                            <tr>
                                <td><?php echo $row['test_id']; ?></td>
                                <td><?php echo $row['test_code']; ?></td>
                                <td><?php echo $row['test_name']; ?></td>
                                <td><?php echo $row['category']; ?></td>
                                <td><?php echo $row['estimated_duration']; ?></td>
                                <td><?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?></td>
                                <td>
                                <button class="btn-view" data-id="<?php echo $row['test_id']; ?>">View</button>
                                <button class="btn-edit" data-id="<?php echo $row['test_id']; ?>">Edit</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="test_id" value="<?php echo $row['test_id']; ?>">
                                        <button type="submit" name="delete" class="btn-delete" onclick="return confirm('Are you sure you want to delete this test?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
   <!-- Modal for viewing test details -->
<div id="testModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Test Details</h2>
        <div id="testDetails">
            <!-- Details will be loaded here -->
        </div>
    </div>
</div>
</div>
<script>
    // JavaScript for handling edit functionality
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.btn-edit');
        const viewButtons = document.querySelectorAll('.btn-view');
        const testForm = document.getElementById('testForm');
        const createBtn = document.getElementById('createBtn');
        const updateBtn = document.getElementById('updateBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const modal = document.getElementById('testModal');
        const closeBtn = document.querySelector('.close');
        
        // Add event listeners to all edit buttons
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const testId = this.getAttribute('data-id');
                fetchTestDetails(testId, 'edit');
            });
        });

        // Add event listeners to all view buttons
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const testId = this.getAttribute('data-id');
                fetchTestDetails(testId, 'view');
            });
        });
        
        // Close modal when clicking X
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
        
        // Cancel button functionality
        cancelBtn.addEventListener('click', function() {
            resetForm();
        });
        
        // Function to fetch test details for editing or viewing
        function fetchTestDetails(testId, mode) {
            // Using to get test details
            fetch(`get_test.php?id=${testId}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        if (mode === 'edit') {
                            // Populate form with test details for editing
                            document.getElementById('test_id').value = data.test_id;
                            document.getElementById('test_code').value = data.test_code;
                            document.getElementById('test_name').value = data.test_name;
                            document.getElementById('description').value = data.description;
                            document.getElementById('category').value = data.category;
                            document.getElementById('preparation_instructions').value = data.preparation_instructions;
                            document.getElementById('estimated_duration').value = data.estimated_duration;
                            document.getElementById('is_active').checked = data.is_active == 1;
                            
                            // Show update and cancel buttons, hide create button
                            createBtn.style.display = 'none';
                            updateBtn.style.display = 'inline-block';
                            cancelBtn.style.display = 'inline-block';
                            
                            // Scroll to form
                            testForm.scrollIntoView({ behavior: 'smooth' });
                        } else if (mode === 'view') {
                            // Display details in modal for viewing
                            const detailsDiv = document.getElementById('testDetails');
                            detailsDiv.innerHTML = `
                                <div class="detail-row">
                                    <span class="detail-label">Test ID:</span>
                                    <span>${data.test_id}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Test Code:</span>
                                    <span>${data.test_code}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Test Name:</span>
                                    <span>${data.test_name}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Description:</span>
                                    <span>${data.description || 'N/A'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Category:</span>
                                    <span>${data.category}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Preparation Instructions:</span>
                                    <span>${data.preparation_instructions || 'N/A'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Estimated Duration:</span>
                                    <span>${data.estimated_duration} minutes</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Status:</span>
                                    <span>${data.is_active ? 'Active' : 'Inactive'}</span>
                                </div>
                            `;
                            modal.style.display = 'block';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching test details:', error);
                    alert('Failed to load test details. Please try again.');
                });
        }
        
        // Function to reset form to create mode
        function resetForm() {
            testForm.reset();
            document.getElementById('test_id').value = '';
            createBtn.style.display = 'inline-block';
            updateBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
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