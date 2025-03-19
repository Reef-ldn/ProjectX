<!--This page handles viewing message they have sent to others-->

<?php
session_start();

//Check if the user is logged in before allowing them to view their message history
if (!isset($_SESSION['user_id'])) {
  die("You must be logged in.");   //If they're not logged in, kill the sesssion
}

//Get the session ID
$my_id = $_SESSION['user_id'];
$other_id = $_GET['other_id'] ?? 0; //The user user we want a conversation with 


//Connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Failed to connect to the database: " . $conn->connect_error);
}


// Fetch the other user's name & profile pic
$userSql = "SELECT id, username, profile_pic FROM users WHERE id = '$other_id'";
$userRes = $conn->query($userSql);
if ($userRes && $userRes->num_rows > 0) {
  $userRow = $userRes->fetch_assoc();
  $other_username = $userRow['username'];
  $other_profile_pic = $userRow['profile_pic'] ?? 'uploads/profile_pics/default_profile_pic.jpg';
} else {
  $other_username = "Unknown User";
  $other_profile_pic = 'uploads/profile_pics/default_profile_pic.jpg';
}


//Get all messages where the sender_id = me
$sql = "SELECT m.id, m.sender_id, m.receiver_id, m.content, m.created_at,
               sender.username AS sender_name, receiver.username AS receiver_name
        FROM messages m
        LEFT JOIN users AS sender ON m.sender_id = sender.id
        LEFT JOIN users AS receiver ON m.receiver_id = receiver.id
        WHERE (m.sender_id = '$my_id' AND m.receiver_id='$other_id')
           OR (m.sender_id = '$other_id' AND m.receiver_id='$my_id')
        ORDER BY m.created_at ASC";


$result = $conn->query($sql);

?>


<!--Front-end-->
<!DOCTYPE html>
<html>

<head>
  <title>Chat with <?php echo $other_username; ?></title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <script src="https://kit.fontawesome.com/22c727220d.js" crossorigin="anonymous"></script>

  <style>
    body {
      background-color: #f8f9fa;
    }

    .chat-container {
      max-width: 600px;
      margin: 20px auto;
      background: white;
      border-radius: 10px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .chat-header {
      background: #009e42;
      color: white;
      padding: 15px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .chat-header img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 10px;
    }

    .chat-messages {
      max-height: 400px;
      overflow-y: auto;
      padding: 15px;
    }

    .message {
      display: flex;
      margin-bottom: 10px;
      align-items: flex-end;
    }

    .message .msg-content {
      padding: 10px 15px;
      border-radius: 15px;
      max-width: 70%;
      word-wrap: break-word;
    }

    .message.sent {
      justify-content: flex-end;
    }

    .message.sent .msg-content {
      background: #009e42;
      color: white;
      border-bottom-right-radius: 0;
    }

    .message.received .msg-content {
      background: #e9ecef;
      color: black;
      border-bottom-left-radius: 0;
    }

    .message-form {
      display: flex;
      padding: 10px;
      border-top: 1px solid #ddd;
      background: white;
    }

    .message-form textarea {
      flex: 1;
      resize: none;
      padding: 10px;
      border-radius: 20px;
      border: 1px solid #ddd;
      margin-right: 10px;
    }

    .send-btn {
      background: #009e42;
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 50%;
    }

    .send-btn:hover {
      background: #007a34;
    }
  </style>


</head>

<body>

  <div class="chat-container">
    <!-- Chat Header -->
    <div class="chat-header">
      <div class="d-flex align-items-center">
        <!--Back Button-->
        <a href="inbox.php" class="btn btn-light btn-sm me-3">
          <i class="fas fa-arrow-left"></i>
        </a>
        <img src="<?php echo $other_profile_pic; ?>" alt="Profile">
        <span><?php echo $other_username; ?></span>
      </div>
    </div>

    <!-- Chat Messages -->
    <div class="chat-messages">


      <?php

      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $isSender = ($row['sender_id'] == $my_id);
          $msgClass = $isSender ? "sent" : "received";

          echo "<div class='message $msgClass'>";


          // Show profile picture for received messages
          if (!$isSender) {
            echo "<img src='$other_profile_pic' width='30' height='30' class='rounded-circle me-2'>";
          }
          echo "<div class='msg-content'>" . htmlspecialchars($row['content']) . "<br>
          <small>" . date('H:i', strtotime($row['created_at'])) . "</small>
          </div>";
          echo "</div>";
        }
      } else {
        echo "<p class='text-center text-muted'>No messages yet.</p>";
      }


      ?>
    </div>

    <!-- Message Form -->
    <form class="message-form" action="send_message.php" method="POST">
      <input type="hidden" name="receiver_id" value="<?php echo $other_id; ?>">
      <textarea name="content" rows="1" class="form-control" placeholder="Type a message..."></textarea>
      <button type="submit" class="send-btn"><i class="fas fa-paper-plane"></i></button>
    </form>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- <script>
    const chatMessages = document.querySelector('.chat-messages');
    if (chatMessages) {
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }
  </script> -->

</body>

</html>