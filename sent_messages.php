<!--This page handles viewing message they have sent to others-->

<?php
  session_start();

  //Check if the user is logged in before allowing them to view their message history
  if(!isset($_SESSION['user_id'])) {
    die("You must be logged in.");   //If they're not logged in, kill the sesssion
  }

  //Get the session ID
  $my_id = $_SESSION['user_id']; 

  //Connect to the db
  $conn = new mysqli("localhost", "root", "", "projectx_db");
  if($conn->connect_error) {
    die("Failed to connect to the database: " . $conn->connect_error);
  }

  //Get all messages where the sender_id = me
  $sql = "SELECT m.id, m.receiver_id, m.content, m.created_at, u.username AS receiver_name
          FROM messages m 
          Join users u ON m.receiver_id = u.id
          WHERE m.sender_id = '$my_id'
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
            echo "<div style='border:1px solid #ccc; margin:10px; padding:10px; ' > ";
            echo "<p> <b>To:</b> " . $row['receiver_name'] . "</p>";
            echo "<p> <b>Message:</b> " . $row['content'] . "</p>";
            echo "<p> <i>Sent at:</i> " . $row['created_at'] . "</i></p>";
            echo "</div>";
          }
        } else {
          echo "<p>No sent messages found.</p>";
        }
      ?>
    </body>
  </html>
<?php
  $conn->close();
?>
