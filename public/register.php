<?php
include './scripts/db.php';

$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Hash the password before storing it
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Check if email already exists
    $checkEmailStmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    if (!$checkEmailStmt) {
        die("Prepare failed: " . $conn->error);
    }
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();

    // Check if username already exists
    $checkUsernameStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    if (!$checkUsernameStmt) {
        die("Prepare failed: " . $conn->error);
    }
    $checkUsernameStmt->bind_param("s", $username);
    $checkUsernameStmt->execute();
    $checkUsernameStmt->store_result();

    // If email or username already exists, show error message
    if ($checkEmailStmt->num_rows > 0) {
        $message = "Email ID already exists";
        $toastClass = "#007bff"; // Primary color
    } elseif ($checkUsernameStmt->num_rows > 0) {
        $message = "Username already exists";
        $toastClass = "#007bff"; // Primary color
    } else {
        // Prepare and bind for inserting new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sss", $username, $email, $hashedPassword);
        if ($stmt->execute()) {
            $message = "Account created successfully";
            $toastClass = "#28a745"; // Success color
        } else {
            $message = "Error: " . $stmt->error;
            $toastClass = "#dc3545"; // Danger color
        }

        // Close user creation statement
        $stmt->close();
    }

    $checkEmailStmt->close();
    $checkUsernameStmt->close();
    $conn->close();

    header("Location: login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="shortcut icon" href="https://cdn-icons-png.flaticon.com/512/295/295128.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Registration</title>
</head>

<body class="bg-light">
    <div class="container p-5 d-flex flex-column align-items-center">
        <?php if ($message): ?>
            <div class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
                style="background-color: <?php echo $toastClass; ?>;">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $message; ?>
                    </div>
                    <button type="button" class="btn-close
                    btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        <form method="post" class="form-control mt-5 p-4" style="height:auto; width:380px;
            box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
            rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
            <div class="row text-center">
                <i class="fa fa-user-circle-o fa-3x mt-1 mb-2" style="color: green;"></i>
                <h5 class="p-4" style="font-weight: 700;">Create Your Account</h5>
            </div>
            <div class="mb-2">
                <label for="username"><i class="fa fa-user"></i> User Name</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-2 mt-2">
                <label for="email"><i class="fa fa-envelope"></i> Email</label>
                <input type="text" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-2 mt-2">
                <label for="password"><i class="fa fa-lock"></i> Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="mb-2 mt-3">
                <button type="submit" class="btn btn-success
                bg-success" style="font-weight: 600;">Create
                    Account</button>
            </div>
            <div class="mb-2 mt-4">
                <p class="text-center" style="font-weight: 600; 
                color: navy;">I have an Account <a href="./login.php" style="text-decoration: none;">Login</a></p>
            </div>
        </form>
    </div>
    <script>
        let toastElList = [].slice.call(document.querySelectorAll('.toast'))
        let toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, { delay: 3000 });
        });
        toastList.forEach(toast => toast.show());
    </script>
</body>

</html>