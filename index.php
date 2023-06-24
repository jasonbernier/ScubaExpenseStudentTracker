<?php
// Initialize session
session_start();

// Database configuration
$servername = "localhost";
$username = "";
$password = "";
$dbname = "";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to check if a user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Function to register a new user
function registerUser($email, $password)
{
    global $conn;

    // Validate and sanitize input
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $password = filter_var($password, FILTER_SANITIZE_STRING);

    // Check if the email already exists
    $check_query = "SELECT * FROM users WHERE email = '$email'";
    $check_result = $conn->query($check_query);

    if ($check_result->num_rows > 0) {
        echo "Email already exists.";
        return;
    }

    // Insert the user into the database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $insert_query = "INSERT INTO users (email, password) VALUES ('$email', '$hashed_password')";

    if ($conn->query($insert_query) === TRUE) {
        echo "User registered successfully.";
    } else {
        echo "Error: " . $insert_query . "<br>" . $conn->error;
    }
}

// Function to log in a user
function loginUser($email, $password)
{
    global $conn;

    // Validate and sanitize input
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $password = filter_var($password, FILTER_SANITIZE_STRING);

    // Check if the email exists in the database
    $check_query = "SELECT * FROM users WHERE email = '$email'";
    $check_result = $conn->query($check_query);

    if ($check_result->num_rows == 0) {
        echo "Email not found.";
        return;
    }

    // Verify the password
    $user = $check_result->fetch_assoc();
    $hashed_password = $user['password'];

    if (password_verify($password, $hashed_password)) {
        // Password is correct, log in the user
        $_SESSION['user_id'] = $user['id'];
        echo "Logged in successfully.";
    } else {
        echo "Incorrect password.";
    }
}

// Function to log out the current user
function logoutUser()
{
    // Destroy the session and unset the session variables
    session_destroy();
    $_SESSION = array();
    echo "Logged out successfully.";
}

// Function to add a paycheck
function addPaycheck($date, $amount)
{
    global $conn;

    // Validate and sanitize input
    $date = filter_var($date, FILTER_SANITIZE_STRING);
    $amount = filter_var($amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // Check if the user is logged in
    if (!isLoggedIn()) {
        echo "Please log in to add a paycheck.";
        return;
    }

    $user_id = $_SESSION['user_id'];

    // Insert the paycheck into the database
    $insert_query = "INSERT INTO paychecks (user_id, date, amount) VALUES ($user_id, '$date', $amount)";

    if ($conn->query($insert_query) === TRUE) {
        echo "Paycheck added successfully.";
    } else {
        echo "Error: " . $insert_query . "<br>" . $conn->error;
    }
}

// Function to add an expense
function addExpense($date, $amount, $description)
{
    global $conn;

    // Validate and sanitize input
    $date = filter_var($date, FILTER_SANITIZE_STRING);
    $amount = filter_var($amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $description = filter_var($description, FILTER_SANITIZE_STRING);

    // Check if the user is logged in
    if (!isLoggedIn()) {
        echo "Please log in to add an expense.";
        return;
    }

    $user_id = $_SESSION['user_id'];

    // Insert the expense into the database
    $insert_query = "INSERT INTO expenses (user_id, date, amount, description) VALUES ($user_id, '$date', $amount, '$description')";

    if ($conn->query($insert_query) === TRUE) {
        echo "Expense added successfully.";
    } else {
        echo "Error: " . $insert_query . "<br>" . $conn->error;
    }
}

// Function to add a student
function addStudent($name, $dives, $location)
{
    global $conn;

    // Validate and sanitize input
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $dives = filter_var($dives, FILTER_SANITIZE_NUMBER_INT);
    $location = filter_var($location, FILTER_SANITIZE_STRING);

    // Check if the user is logged in
    if (!isLoggedIn()) {
        echo "Please log in to add a student.";
        return;
    }

    $user_id = $_SESSION['user_id'];

    // Insert the student into the database
    $insert_query = "INSERT INTO students (user_id, name, dives, location) VALUES ($user_id, '$name', $dives, '$location')";

    if ($conn->query($insert_query) === TRUE) {
        echo "Student added successfully.";
    } else {
        echo "Error: " . $insert_query . "<br>" . $conn->error;
    }
}

// Function to get paychecks by user
function getPaychecksByUser($user_id)
{
    global $conn;

    $select_query = "SELECT * FROM paychecks WHERE user_id = $user_id";
    $result = $conn->query($select_query);

    $paychecks = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $paychecks[] = $row;
        }
    }

    return $paychecks;
}

// Function to get expenses by user
function getExpensesByUser($user_id)
{
    global $conn;

    $select_query = "SELECT * FROM expenses WHERE user_id = $user_id";
    $result = $conn->query($select_query);

    $expenses = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $expenses[] = $row;
        }
    }

    return $expenses;
}

// Function to get students by user
function getStudentsByUser($user_id)
{
    global $conn;

    $select_query = "SELECT * FROM students WHERE user_id = $user_id";
    $result = $conn->query($select_query);

    $students = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }

    return $students;
}

// Process user registration
if (isset($_POST['register'])) {
    $reg_email = $_POST['reg_email'];
    $reg_password = $_POST['reg_password'];
    registerUser($reg_email, $reg_password);
}

// Process user login
if (isset($_POST['login'])) {
    $login_email = $_POST['login_email'];
    $login_password = $_POST['login_password'];
    loginUser($login_email, $login_password);
}

// Process user logout
if (isset($_POST['logout'])) {
    logoutUser();
}

// Process adding a paycheck
if (isLoggedIn() && isset($_POST['add_paycheck'])) {
    $paycheck_date = $_POST['paycheck_date'];
    $paycheck_amount = $_POST['paycheck_amount'];
    addPaycheck($paycheck_date, $paycheck_amount);
}

// Process adding an expense
if (isLoggedIn() && isset($_POST['add_expense'])) {
    $expense_date = $_POST['expense_date'];
    $expense_amount = $_POST['expense_amount'];
    $expense_description = $_POST['expense_description'];
    addExpense($expense_date, $expense_amount, $expense_description);
}

// Process adding a student
if (isLoggedIn() && isset($_POST['add_student'])) {
    $student_name = $_POST['student_name'];
    $student_dives = $_POST['student_dives'];
    $student_location = $_POST['student_location'];
    addStudent($student_name, $student_dives, $student_location);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Scuba Instruction Tracker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* Basic mobile-friendly styling */
        body {
            font-family: Arial, sans-serif;
            padding: 10px;
            max-width: 800px;
            margin: 0 auto;
        }

        h2 {
            margin-top: 20px;
        }

        .column {
            float: left;
            width: 33.33%;
            padding: 10px;
            box-sizing: border-box;
        }

        .row:after {
            content: "";
            display: table;
            clear: both;
        }

        .results {
            margin-top: 20px;
            text-align: center;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input[type="date"],
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 5px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="row">
        <div class="column">
            <?php if (!isLoggedIn()) { ?>
                <h2>User Registration</h2>
                <form method="post" action="">
                    <label>Email:</label>
                    <input type="email" name="reg_email" required>

                    <label>Password:</label>
                    <input type="password" name="reg_password" required>

                    <input type="submit" name="register" value="Register">
                </form>
            <?php } ?>
        </div>

        <div class="column">
            <?php if (!isLoggedIn()) { ?>
                <h2>User Login</h2>
                <form method="post" action="">
                    <label>Email:</label>
                    <input type="email" name="login_email" required>

                    <label>Password:</label>
                    <input type="password" name="login_password" required>

                    <input type="submit" name="login" value="Login">
                </form>
            <?php } ?>
        </div>

        <div class="column">
            <?php if (isLoggedIn()) { ?>
                <h2>User Logout</h2>
                <form method="post" action="">
                    <input type="submit" name="logout" value="Logout">
                </form>
            <?php } ?>
        </div>
    </div>

    <?php if (isLoggedIn()) { ?>
        <div class="row">
            <div class="column">
                <h2>Add Paycheck</h2>
                <form method="post" action="">
                    <label>Date:</label>
                    <input type="date" name="paycheck_date" required>

                    <label>Amount:</label>
                    <input type="text" name="paycheck_amount" required>

                    <input type="submit" name="add_paycheck" value="Add Paycheck">
                </form>
            </div>

            <div class="column">
                <h2>Add Expense</h2>
                <form method="post" action="">
                    <label>Date:</label>
                    <input type="date" name="expense_date" required>

                    <label>Amount:</label>
                    <input type="text" name="expense_amount" required>

                    <label>Description:</label>
                    <input type="text" name="expense_description" required>

                    <input type="submit" name="add_expense" value="Add Expense">
                </form>
            </div>

            <div class="column">
                <h2>Add Student</h2>
                <form method="post" action="">
                    <label>Name:</label>
                    <input type="text" name="student_name" required>

                    <label>Number of Dives:</label>
                    <input type="text" name="student_dives" required>

                    <label>Location:</label>
                    <select name="student_location" required>
                        <option value="Pool">Pool</option>
                        <option value="Open Water">Open Water</option>
                    </select>

                    <input type="submit" name="add_student" value="Add Student">
                </form>
            </div>
        </div>

        <div class="row">
            <div class="column">
                <h2>List Paychecks</h2>
                <form method="post" action="">
                    <input type="submit" name="list_paychecks" value="List Paychecks">
                </form>
                <div class="results">
                    <?php
                    if (isLoggedIn() && isset($_POST['list_paychecks'])) {
                        $user_id = $_SESSION['user_id'];
                        $paychecks = getPaychecksByUser($user_id);

                        if (!empty($paychecks)) {
                            echo "<ul>";
                            foreach ($paychecks as $paycheck) {
                                echo "<li>Date: " . $paycheck['date'] . ", Amount: $" . $paycheck['amount'] . "</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<p>No paychecks found.</p>";
                        }
                    }
                    ?>
                </div>
            </div>

            <div class="column">
                <h2>List Expenses</h2>
                <form method="post" action="">
                    <input type="submit" name="list_expenses" value="List Expenses">
                </form>
                <div class="results">
                    <?php
                    if (isLoggedIn() && isset($_POST['list_expenses'])) {
                        $user_id = $_SESSION['user_id'];
                        $expenses = getExpensesByUser($user_id);

                        if (!empty($expenses)) {
                            echo "<ul>";
                            foreach ($expenses as $expense) {
                                echo "<li>Date: " . $expense['date'] . ", Amount: $" . $expense['amount'] . ", Description: " . $expense['description'] . "</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<p>No expenses found.</p>";
                        }
                    }
                    ?>
                </div>
            </div>

            <div class="column">
                <h2>List Students</h2>
                <form method="post" action="">
                    <input type="submit" name="list_students" value="List Students">
                </form>
                <div class="results">
                    <?php
                    if (isLoggedIn() && isset($_POST['list_students'])) {
                        $user_id = $_SESSION['user_id'];
                        $students = getStudentsByUser($user_id);

                        if (!empty($students)) {
                            echo "<ul>";
                            foreach ($students as $student) {
                                echo "<li>Name: " . $student['name'] . ", Dives: " . $student['dives'] . ", Location: " . $student['location'] . "</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<p>No students found.</p>";
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php
    // Close the database connection
    $conn->close();
    ?>
</body>
</html>
