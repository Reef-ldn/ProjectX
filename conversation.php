<!--This page handles viewing message they have sent to others-->

<?php
  session_start();

  //Check if the user is logged in before allowing them to view their message history
  if(!isset($_SESSION['user_id'])) {
    die("You must be logged in.");   //If they're not logged in, kill the sesssion
  }

  //Get the session ID
  $my_id = $_SESSION['user_id']; 
  $other_id = $_GET['other_id'] ?? 0; //The user user we want a conversation with 


  //Connect to the db
  $conn = new mysqli("localhost", "root", "", "projectx_db");
  if($conn->connect_error) {
    die("Failed to connect to the database: " . $conn->connect_error);
  }

  //Get all messages where the sender_id = me
  $sql = "SELECT m.id, m.sender_id, m.receiver_id, m.content, m.created_at, s.username 
                AS sender_name, r.username AS receiver_name
          FROM messages m 
          Join users s ON m.sender_id = s.id
          Join users r ON m.receiver_id = r.id
          WHERE (m.sender_id = '$my_id' AND m.receiver_id='$other_id')
            OR (m.sender_id = '$other_id' AND m.receiver_id='$my_id')
          ORDER BY m.created_at ASC";
  $result = $conn->query($sql);

  ?>

  
<!--Front-end-->
<!DOCTYPE html>
  <html>

  <head>
    <title>Conversations</title>
  </head>
  
  <body>
    <h1>Conversation with user <?php echo $other_id; ?> </h1> 

    <?php
      //Loop through the messages
      if($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          echo "<div style='border:1px solid #ccc; margin:10px; padding:10px; ' > ";
          echo "<p> <b>From:</b> " . $row['sender_name'] . "</p>";  //The user that sent the message
          echo "<p> <b>To:</b> " . $row['receiver_name'] . "</p>";  //who the message went to
          echo "<p> <b>Message:</b> " . $row['content'] . "</p>";   //the message
          echo "<p> <i>Sent at: </i> " . $row['created_at'] . "</i></p>"; //when it was sent
          echo "</div>";
        }
      } else {
          echo "<p>No sent messages found between you and user $other_id.</p>";
      }
      $conn->close();
    ?>

    <!--Form to send a new messsage in this convo--> 
    <h2>Send a new message </h2>
    <form action = "send_message.php" method="POST">
      <input type="hidden" name="receiver_id" value="<?php echo $other_id; ?>">
      <textarea name="content" rows="3" cols="40"></textarea> <br><br>
      <button type = "submit">Send</button>
    </form>
    
  </body>
</html>
