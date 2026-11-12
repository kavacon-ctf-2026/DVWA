<?php
if (!defined('DVWA_WEB_PAGE_TO_ROOT')) {
    define('DVWA_WEB_PAGE_TO_ROOT', '../../../');
}

// Get current user's ID using the same function as the rest of DVWA
$currentUser = dvwaCurrentUser();
$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT user_id FROM users WHERE user = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $currentUser);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_row = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : false;
$current_user_id = $user_row ? intval($user_row['user_id']) : 0;
mysqli_stmt_close($stmt);

$html = "";
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    if (!preg_match('/^\d+$/', $_GET['user_id'])) {
        $html .= "<p>Invalid user ID format. Please enter a number.</p>";
    } else {
        $id = intval($_GET['user_id']);

        // Only allow users to access their own profile
        if ($id != $current_user_id) {
            $html .= "<p>Access denied. You can only view your own profile.</p>";
        } else {
            $stmt2 = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT first_name, last_name, user_id, avatar FROM users WHERE user_id = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt2, 'i', $id);
            mysqli_stmt_execute($stmt2);
            $result2 = mysqli_stmt_get_result($stmt2);

            if ($result2 && mysqli_num_rows($result2) > 0) {
                $row = mysqli_fetch_assoc($result2);
                $html .= "
                    <div class=\"profile-info\">
                        <h3>User Profile</h3>
                        <p>User ID: " . htmlspecialchars($row['user_id'], ENT_QUOTES, 'UTF-8') . "</p>
                        <p>Name: " . htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8') . " " .
                    htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8') . "</p>
                        <p>Avatar: " . htmlspecialchars($row['avatar'], ENT_QUOTES, 'UTF-8') . "</p>
                    </div>";
            } else {
                $html .= "<p>No user found.</p>";
            }
            mysqli_stmt_close($stmt2);
        }
    }
}
?>
