<!-- ORIGINAL VERSION OF MY INBOX PAGE TO SEE MESSAGES THAT WERE SENT -->
<!--THIS PAGE IS NOT FUNCTIONAL, USED FOR TESTING-->

<?php
// session_start();

// //Check if the user is logged in before allowing them to view their sent messages
// if(!isset($_SESSION['user_id'])) {
//   die("You must be logged in.");   //If they're not logged in, kill the sesssion
// }

// //Get the session ID
// $my_id = $_SESSION['user_id']; 

// //Connect to the db
// $conn = new mysqli("localhost", "root", "", "projectx_db");
// if($conn->connect_error) {
//   die("Failed to connect to the database: " . $conn->connect_error);
// }

// //Get all messages where the sender_id = me
// $sql = "SELECT m.id, m.receiver_id, m.content, m.created_at, u.username AS receiver_name
//         FROM messages m 
//         Join users u ON m.receiver_id = u.id
//         WHERE m.sender_id = '$my_id'
//         ORDER BY m.created_at DESC";
// $result = $conn->query($sql);
?>


<!--Front-end-->
<!DOCTYPE html>
<html>

<head>
  <title>Sent Messages</title>
</head>

<body>
  <!-- <h1>Sent messages</h1>  -->
  <?php
  //Loop through the messages
  // if($result && $result->num_rows > 0) {
  //   while($row = $result->fetch_assoc()) {
  //     echo "<div style='border:1px solid #ccc; margin:10px; padding:10px; ' > ";
  //     echo "<p> <b>To:</b> " . $row['receiver_name'] . "</p>";
  //     echo "<p> <b>Message:</b> " . $row['content'] . "</p>";
  //     echo "<p> <i>Sent at:</i> " . $row['created_at'] . "</i></p>";
  //     echo "</div>";
  //   }
  // } else {
  //   echo "<p>No sent messages found.</p>";
  // }
  ?>
</body>

</html>
<?php
$conn->close();
?>

<?php
// Testing Sending a Message
//require_once 'db_connection.php'; // database connection

// Simulate sending a message from user_id = 1 to user_id = 2
// $sender_id = 1;
// $receiver_id = 2;
// $content = "Test message for unit testing";
// $created_at = date('Y-m-d H:i:s');

// // Insert into database
// $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, created_at) VALUES (?, ?, ?, ?)");
// $stmt->bind_param("iiss", $sender_id, $receiver_id, $content, $created_at);

// if ($stmt->execute()) {
//     echo "Message Sent Successfully!";
// } else {
//     echo "Failed to send message: " . $stmt->error;
// }

// $stmt->close();
// $conn->close();
?>