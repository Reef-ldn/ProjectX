<!-- This script allows users to view all the messages that have been sent to the logged in user-->

<?php
  session_start();

  //Check if the user is logged in before allowing them to view their inbox.
  if(!isset($_SESSION['user_id'])) {
    die("You must be logged in to view your inbox.");   //If they're not logged in, kill the sesssion
  }

  //Get the session ID
  $my_id = $_SESSION['user_id'];          //The ID of the user that sent the text

  //Connect to the db
  $conn = new mysqli("localhost", "root", "", "projectx_db");
  if($conn->connect_error) {
    die("Failed to connect to the database: " . $conn->connect_error);
  }

  //Select all messages where the receiver_id = me
  $sql = "SELECT m.id, m.sender_id, m.content, m.created_at, u.username AS sender_name
          FROM messages m
          JOIN users u ON m.sender_id = u.id
          WHERE m.receiver_id = '$my_id' 
          ORDER BY m.created_at DESC";
  $result = $conn->query($sql);

?>

<!--Front-end-->
<!DOCTYPE html>
  <html>

    <head>
      <title>My Inbox</title>
    </head>
  
    <body>
      <h1>Inbox</h1> 
      <?php
        //Loop through the messages
        if($result && $result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
            echo "div style='border:1px solid #ccc; margin:10px; padding:10px; ' > ";
            echo "<p> <b>From:</b> " . $row['sender_name'] . "</p>";
            echo "<p> <b>Message:</b> " . $row['content'] . "</p>";
            echo "<p> <i>Sent at:</i> " . $row['created_at'] . "</i></p>";
            echo "</div>";
          }
        } else {
          echo "<p>No messages found.</p>";
        }
      ?>
    </body>
  </html>
<?php
  $conn->close();
?>

  