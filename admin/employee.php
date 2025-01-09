<?php
session_start();

if (!isset($_SESSION['a_id'])) {
    header("Location: ../admin/login.php");
    exit();
}

include '../db/db_conn.php';

// Fetch user info
$adminId = $_SESSION['a_id'];
$sql = "SELECT a_id, firstname, middlename, lastname, birthdate, email, role, position, department, phone_number, address, pfp FROM admin_register WHERE a_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$adminInfo = $result->fetch_assoc();

// Fetch employee data
$sql = "SELECT e_id, firstname, lastname, email, department, phone_number, address FROM employee_register WHERE role='Employee'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <link href="../css/styles.css" rel="stylesheet" />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href="../css/calendar.css" rel="stylesheet"/>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .btn {
            transition: transform 0.3s ease;
            border-radius: 50px;
        }

        .btn:hover {
            transform: translateY(-4px); /* Raise effect on hover */
        }
    </style>
</head>
<body class="sb-nav-fixed bg-black">
    <nav class="sb-topnav navbar navbar-expand navbar-dark border-bottom border-1 border-warning bg-dark">
        <a class="navbar-brand ps-3 text-muted" href="../admin/dashboard.php">Microfinance</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars text-light"></i></button>
            <div class="d-flex ms-auto me-0 me-md-3 my-2 my-md-0 align-items-center">
                <div class="text-light me-3 p-2 rounded shadow-sm bg-gradient" id="currentTimeContainer" 
                    style="background: linear-gradient(45deg, #333333, #444444); border-radius: 5px;">
                    <span class="d-flex align-items-center">
                        <span class="pe-2">
                            <i class="fas fa-clock"></i> 
                            <span id="currentTime">00:00:00</span>
                        </span>
                        <button class="btn btn-outline-warning btn-sm ms-2" type="button" onclick="toggleCalendar()">
                            <i class="fas fa-calendar-alt"></i>
                            <span id="currentDate">00/00/0000</span>
                        </button>
                    </span>
                </div>
                <form class="d-none d-md-inline-block form-inline">
                    <div class="input-group">
                        <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                        <button class="btn btn-warning" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion bg-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu ">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading text-center text-muted">Your Profile</div>
                        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                            <li class="nav-item dropdown text">
                                <a class="nav-link dropdown-toggle text-light d-flex justify-content-center ms-4" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="<?php echo (!empty($adminInfo['pfp']) && $adminInfo['pfp'] !== 'defaultpfp.png') 
                                        ? htmlspecialchars($adminInfo['pfp']) 
                                        : '../img/defaultpfp.jpg'; ?>" 
                                        class="rounded-circle border border-light" width="120" height="120" alt="Profile Picture" />
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="../admin/profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="#!">Settings</a></li>
                                    <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                                    <li><hr class="dropdown-divider" /></li>
                                    <li><a class="dropdown-item" href="../admin/logout.php" onclick="confirmLogout(event)">Logout</a></li>
                                </ul>
                            </li>
                            <li class="nav-item text-light d-flex ms-3 flex-column align-items-center text-center">
                                <span class="big text-light mb-1">
                                    <?php
                                        if ($adminInfo) {
                                        echo htmlspecialchars($adminInfo['firstname'] . ' ' . $adminInfo['middlename'] . ' ' . $adminInfo['lastname']);
                                        } else {
                                        echo "Admin information not available.";
                                        }
                                    ?>
                                </span>      
                                <span class="big text-light">
                                    <?php
                                        if ($adminInfo) {
                                        echo htmlspecialchars($adminInfo['role']);
                                        } else {
                                        echo "User information not available.";
                                        }
                                    ?>
                                </span>
                            </li>
                        </ul>
                        <div class="sb-sidenav-menu-heading text-center text-muted border-top border-1 border-warning mt-3">Admin Dashboard</div>
                        <a class="nav-link text-light" href="../admin/dashboard.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseTAD" aria-expanded="false" aria-controls="collapseTAD">
                            <div class="sb-nav-link-icon"><i class="fa fa-address-card"></i></div>
                            Time and Attendance
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseTAD" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../admin/attendance.php">Attendance Report</a>
                                <a class="nav-link text-light" href="../admin/tad_timesheet.php">Timesheet</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLM" aria-expanded="false" aria-controls="collapseLM">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar-times"></i></div>
                            Leave Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../admin/leave_requests.php">Leave Requests</a>
                                <a class="nav-link text-light" href="../admin/leave_history.php">Leave History</a>
                                <a class="nav-link text-light"  href="../admin/leave_allocation.php">Set Leave</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePM" aria-expanded="false" aria-controls="collapsePM">
                            <div class="sb-nav-link-icon"><i class="fas fa-line-chart"></i></div>
                            Performance Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../admin/evaluation.php">Evaluation</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSR" aria-expanded="false" aria-controls="collapseSR">
                            <div class="sb-nav-link-icon"><i class="fa fa-address-card"></i></div>
                            Social Recognition
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseSR" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../admin/awardee.php">Awardee</a>
                                <a class="nav-link text-light" href="../admin/recognition.php">Generate Certificate</a>
                            </nav>
                        </div>
                        <div class="sb-sidenav-menu-heading text-center text-muted border-top border-1 border-warning">Account Management</div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                            Accounts
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../admin/calendar.php">Calendar</a>
                                <a class="nav-link text-light" href="../admin/admin.php">Admin Accounts</a>
                                <a class="nav-link text-light" href="../admin/employee.php">Employee Accounts</a>
                            </nav>
                        </div>
                        <div class="collapse" id="collapsePages" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                        </div>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-black text-light border-top border-1 border-warning">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($adminInfo['role']); ?></div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
        <main class="bg-black">
            <div class="container-fluid position-relative px-4">
                <h1 class="mb-4 text-light">Employee Account Management</h1>
            </div>
            <div class="container" id="calendarContainer" 
                style="position: fixed; top: 9%; right: 0; z-index: 1050; 
                width: 700px; display: none;">
                <div class="row">
                    <div class="col-md-12">
                        <div id="calendar" class="p-2"></div>
                    </div>
                </div>
            </div>               
            <div class="container mt-5">
                <table class="table table-bordered table-dark">
                    <thead class="thead-light">
                        <tr class="text-center text-light">
                            <th>Employee ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="text-center text-light">
                                    <td><?php echo htmlspecialchars($row['e_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td class='d-flex justify-content-around'>
                                        <button class="btn btn-success btn-sm" 
                                                onclick="fillUpdateForm(<?php echo $row['e_id']; ?>, '<?php echo htmlspecialchars($row['firstname']); ?>', '<?php echo htmlspecialchars($row['lastname']); ?>', '<?php echo htmlspecialchars($row['email']); ?>', '<?php echo htmlspecialchars($row['department']); ?>', '<?php echo htmlspecialchars($row['phone_number']); ?>', '<?php echo htmlspecialchars($row['address']); ?>')">Update</button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteEmployee(<?php echo $row['e_id']; ?>)">Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center">No records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
   <!-- Button trigger modal -->
<div class="d-flex justify-content-center mt-4 mb-0">
    <a class="btn btn-primary text-light" href="#" data-bs-toggle="modal" data-bs-target="#createEmployeeModal">Create Employee</a>
</div>

<!-- Modal -->
<div class="modal fade" id="createEmployeeModal" tabindex="-1" aria-labelledby="createEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content bg-dark">
      <div class="modal-header border-bottom border-1 border-warning">
        <h5 class="modal-title text-light" id="createEmployeeModalLabel">Create Employee Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Form inside the modal -->
        <form id="registrationForm" action="../db/registeremployee_db.php" method="POST">
          <div class="row mb-3">
            <div class="col-md-6">
              <div class="form-floating mb-3 mb-md-0">
                <input class="form-control" id="inputFirstName" type="text" name="firstname" placeholder="Enter your first name" required />
                <label for="inputFirstName">First name</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating"> 
                <input class="form-control" id="inputLastName" type="text" name="lastname" placeholder="Enter your last name" required />
                <label for="inputLastName">Last name</label>
              </div>
            </div>
          </div>
          <div class="form-floating mb-3">
            <input class="form-control" id="inputEmail" type="email" name="email" placeholder="name@example.com" required />
            <label for="inputEmail">Email address</label>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <div class="form-floating mb-3 mb-md-0">
                <input class="form-control" id="inputPassword" type="password" name="password" placeholder="Create a password" required />
                <label for="inputPassword">Password</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating mb-3 mb-md-0">
                <input class="form-control" id="inputPasswordConfirm" type="password" name="confirm_password" placeholder="Confirm password" required />
                <label for="inputPasswordConfirm">Confirm Password</label>
              </div>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <div class="form-floating mb-3 mb-md-0">
                <input type="hidden" id="inputRoleHidden" name="role" value="Employee">
                <input class="form-control" type="text" id="inputRole" value="Employee" disabled>
                <label for="inputRole">Role</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating mb-3 mb-md-0">
                <select id="inputDepartment" name="department" class="form-select" required>
                  <option value="" disabled selected></option>
                  <option value="Finance Department">Finance Department</option>
                  <option value="Administration Department">Administration Department</option>
                  <option value="Sales Department">Sales Department</option>
                  <option value="Credit Department">Credit Department</option>
                  <option value="Human Resource Department">Human Resource Department</option>
                  <option value="IT Department">IT Department</option>
                </select>
                <label for="inputDepartment">Select Department</label>
              </div>          
            </div>
          </div>
          <div class="form-floating mt-3">
            <select id="inputPosition" name="position" class="form-select" required>
              <option value="" disabled selected>Select department first.</option>
            </select>
            <label for="inputPosition">Select Position</label>
          </div> 
          <div class="mt-4 mb-0">
            <div class="d-grid">
              <button class="btn btn-primary btn-block" type="submit">Create Account</button>
            </div>
            <div class="text-center">
              <div class="text-center mt-2 mb-2">
                <a class="btn border-secondary w-100 text-light" href="../admin/employee.php">Back</a>
              </div>
            </div>  
          </div>
        </form>
      </div>
      <div class="modal-footer border-top border-1 border-warning">
        <p class="small text-center text-muted mt-1">Human Resource 2</p>
      </div>
    </div>
  </div>
</div>

<!-- Update Employee Modal -->
<div class="modal fade" id="updateEmployeeModal" tabindex="-1" aria-labelledby="updateEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header d-flex justify-content-center">
                <h5 class="modal-title" id="updateEmployeeModalLabel">Update Employee Account</h5>
            </div>
            <div class="modal-body">
                <form id="updateForm" onsubmit="updateEmployee(event)">
                    <input type="hidden" name="e_id" id="updateId">
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" class="form-control" name="firstname" placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" class="form-control" name="lastname" placeholder="Last Name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select class="form-control" name="department" required>
                            <option value="" disabled selected></option>
                            <option value="Finance Department">Finance Department</option>
                            <option value="Administration Department">Administration Department</option>
                            <option value="Sales Department">Sales Department</option>
                            <option value="Credit Department">Credit Department</option>
                            <option value="Human Resource Department">Human Resource Department</option>
                            <option value="IT Department">IT Department</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" class="form-control" name="phone_number" placeholder="Phone Number" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" class="form-control" name="address" placeholder="Address" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal after Update -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Success</h5>
                <button type="button" class="close text-light" data-dismiss="modal" aria-label="Close" onclick="closeSuccessModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Employee Information updated successfully.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="closeSuccessModal()">Close</button>
            </div>
        </div>
    </div>
</div>




<!-- Modal for Confirming Deletion -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-light" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close text-light bg-dark" data-dismiss="modal" aria-label="Close" onclick="closeModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-light">
                <p> <center>Are you sure you want to delete this employee?</p></center>
            </div>
            <div class="modal-footer">
                <!-- Switched the positions of Cancel and Delete buttons -->
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>
</div>

            </main>
            <footer class="py-4 bg-dark text-light mt-auto border-top border-warning">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2024</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms & Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <script>
        //CALENDAR 
        let calendar;
            function toggleCalendar() {
                const calendarContainer = document.getElementById('calendarContainer');
                    if (calendarContainer.style.display === 'none' || calendarContainer.style.display === '') {
                        calendarContainer.style.display = 'block';
                        if (!calendar) {
                            initializeCalendar();
                         }
                        } else {
                            calendarContainer.style.display = 'none';
                        }
            }

            function initializeCalendar() {
                const calendarEl = document.getElementById('calendar');
                    calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        height: 440,  
                        events: {
                        url: '../db/holiday.php',  
                        method: 'GET',
                        failure: function() {
                        alert('There was an error fetching events!');
                        }
                        }
                    });

                    calendar.render();
            }

            document.addEventListener('DOMContentLoaded', function () {
                const currentDateElement = document.getElementById('currentDate');
                const currentDate = new Date().toLocaleDateString(); 
                currentDateElement.textContent = currentDate; 
            });

            document.addEventListener('click', function(event) {
                const calendarContainer = document.getElementById('calendarContainer');
                const calendarButton = document.querySelector('button[onclick="toggleCalendar()"]');

                    if (!calendarContainer.contains(event.target) && !calendarButton.contains(event.target)) {
                        calendarContainer.style.display = 'none';
                        }
            });
        //CALENDAR END

        //TIME 
        function setCurrentTime() {
            const currentTimeElement = document.getElementById('currentTime');
            const currentDateElement = document.getElementById('currentDate');

            const currentDate = new Date();
    
            currentDate.setHours(currentDate.getHours() + 0);
                const hours = currentDate.getHours();
                const minutes = currentDate.getMinutes();
                const seconds = currentDate.getSeconds();
                const formattedHours = hours < 10 ? '0' + hours : hours;
                const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
                const formattedSeconds = seconds < 10 ? '0' + seconds : seconds;

            currentTimeElement.textContent = `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
            currentDateElement.textContent = currentDate.toLocaleDateString();
        }
        setCurrentTime();
        setInterval(setCurrentTime, 1000);
        //TIME END

        //UPDATE MODAL
// Handle the update process
function updateEmployee(event) {
    event.preventDefault();  // Prevent form from submitting normally

    const form = document.getElementById('updateForm');
    const formData = new FormData(form);

    // Send the form data to the server using fetch
    fetch('../db/update_employee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show the success modal if the update was successful
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();

            // Close the update modal
            closeUpdateModal();
        } else {
            // Show error message if the update failed
            alert('Error updating employee information');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the employee information.');
    });
}

// Close the update modal
function closeUpdateModal() {
    const updateModal = bootstrap.Modal.getInstance(document.getElementById('updateEmployeeModal'));
    if (updateModal) {
        updateModal.hide();
    }
}

// Close the success modal after update
function closeSuccessModal() {
    const successModal = bootstrap.Modal.getInstance(document.getElementById('successModal'));
    if (successModal) {
        successModal.hide();
    }
}

// Function to open the update modal with employee data (example)
function openUpdateModal(employee) {
    document.getElementById('updateId').value = employee.e_id;
    document.querySelector('input[name="firstname"]').value = employee.firstname;
    document.querySelector('input[name="lastname"]').value = employee.lastname;
    document.querySelector('input[name="email"]').value = employee.email;
    document.querySelector('select[name="department"]').value = employee.department;
    document.querySelector('input[name="phone_number"]').value = employee.phone_number;
    document.querySelector('input[name="address"]').value = employee.address;

    // Show the update modal
    const modal = new bootstrap.Modal(document.getElementById('updateEmployeeModal'));
    modal.show();
}

// Function to fill the form and show the modal when "Update" button is clicked
function fillUpdateForm(e_id, firstname, lastname, email, department, phone_number, address) {
    const employee = {
        e_id,
        firstname,
        lastname,
        email,
        department,
        phone_number,
        address
    };
    
    openUpdateModal(employee); // Open the modal with the provided employee data
}

// Handling modal button click directly with HTML event attributes
document.querySelector("#updateEmployeeModal .btn-primary").addEventListener("click", function () {
    updateEmployee(event); // Trigger the update function on button click
});

document.querySelector("#updateEmployeeModal .btn-secondary").addEventListener("click", function () {
    closeUpdateModal(); // Close the update modal on "Close" button click
});

document.querySelector("#successModal .btn-primary").addEventListener("click", function () {
    closeSuccessModal(); // Close the success modal on "Close" button click
});


        let employeeIdToDelete = null;

function deleteEmployee(id) {
    // Set the employee ID to be deleted when the button is clicked
    employeeIdToDelete = id;

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();

    // Add the click event for the "Delete" button inside the modal
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    confirmDeleteBtn.onclick = function() {
        // Proceed with the deletion if the user confirms
        const formData = new FormData();
        formData.append('e_id', employeeIdToDelete);

        // Send the POST request to delete the employee
        fetch('../db/delete_employee.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.success || data.error);
            if (data.success) {
                location.reload(); // Reload the page if deletion is successful
            }
            closeModal(); // Hide the modal after completion
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the employee.');
            closeModal(); // Hide the modal in case of an error
        });
    };
}

// Function to close the modal
function closeModal() {
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
    modal.hide();
}




        document.getElementById('updateForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('../db/update_employee.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.success || data.error);
                if (data.success) {
                    closeModal();
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the employee.');
            });
        };
        
        const positionsByDepartment = {
            "Finance Department": ["Financial Controller", "Accountant", "Credit Analyst", "Supervisor", "Staff"],
            "Administration Department": ["Facilities Manager", "Operations Manager", "Customer Service Representative", "Supervisor", "Staff"],
            "Sales Department": ["Sales Manager", "Sales Representative", "Marketing Coordinator", "Supervisor", "Staff"],
            "Credit Department": ["Loan Officer", "Loan Collection Officer", "Credit Risk Analyst", "Supervisor", "Staff"],
            "Human Resource Department": ["HR Manager", "Recruitment Specialists", "Training Coordinator", "Supervisor", "Staff"],
            "IT Department": ["IT Manager", "Network Administrator", "System Administrator", "IT Support Specialist", "Supervisor", "Staff"]
        };

        function filterPositions() {
            const departmentSelect = document.getElementById("inputDepartment");
            const positionSelect = document.getElementById("inputPosition");
            const selectedDepartment = departmentSelect.value;

            // Clear the previous options in the position dropdown
            positionSelect.innerHTML = '<option value="" disabled selected></option>';

            // Populate the position dropdown with positions relevant to the selected department
            if (positionsByDepartment[selectedDepartment]) {
                positionsByDepartment[selectedDepartment].forEach(position => {
                    const option = document.createElement("option");
                    option.value = position;
                    option.textContent = position;
                    positionSelect.appendChild(option);
                });
            }
        }

        // Attach event listener to department dropdown
        document.getElementById("inputDepartment").addEventListener("change", filterPositions);
        //UPDATE MODAL END
    </script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../js/admin.js"></script>
</body>
</html>

<?php
$conn->close();
?>