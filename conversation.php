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
$sql = "SELECT m.id, m.sender_id, m.receiver_id, m.content, m.created_at, m.shared_post_id,
               sender.username AS sender_name, receiver.username 
               AS receiver_name, p.post_type, p.file_path, p.text_content AS post_text
        FROM messages m
        LEFT JOIN users AS sender ON m.sender_id = sender.id
        LEFT JOIN users AS receiver ON m.receiver_id = receiver.id
        LEFT JOIN posts p ON m.shared_post_id = p.id
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

  <!--Navbar stylesheet-->
  <link rel="stylesheet" href="/ProjectX/css/navbar.css">



  <style>
    html,
    body {
      height: 100%;
      margin: 0;
      padding: 0;
      color: white;
      font-family: sans-serif;
    }

    body {
      display: flex;
      align-items: center;
      justify-content: center;
      background-image: url('/ProjectX/uploads/people-soccer-stadium.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-attachment: fixed;
      background-position: center;
      height: 100vh;
      margin: 0;
      padding: 0;
      color: white;
      font-family: sans-serif;
      position: relative;
    }

    .bg-blur-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      backdrop-filter: blur(4px);
      background-color: rgba(0, 0, 0, 0.4);
      z-index: 1;
    }

    .main-wrapper {
      z-index: 2;
      width: 100%;
      max-width: 700px;
      height: 90vh;
      display: flex;
      flex-direction: column;
      background: rgba(30, 30, 30, 0.92);
      border-radius: 16px;
      box-shadow: 0 0 20px rgba(0, 255, 100, 0.2);
      overflow: hidden;
    }

    .chat-container {
      width: 100%;
      max-width: 700px;
      margin: 0 auto;
      background: rgba(30, 30, 30, 0.9);
      color: white;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0, 255, 100, 0.1);
      overflow: hidden;
    }

    .chat-header {
      background: #009e42;
      padding: 15px;
      display: flex;
      align-items: center;
      color: white;
    }

    .chat-header img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 10px;
    }

    .chat-messages {
      flex: 1;
      overflow-y: auto;
      padding: 15px;
    }


    .message {
      display: flex;
      margin-bottom: 10px;
      align-items: flex-end;
    }

    .msg-content p {
      margin: 0;
      padding: 5px 0 0 0;
    }

    .msg-content small {
      display: block;
      margin-top: 5px;
      font-size: 0.75rem;
      opacity: 0.7;
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
      background: rgba(30, 30, 30, 0.9);
      color: #e9ecef;
    }

    .message-form textarea::placeholder {
      color: #ccc;
      opacity: 1;
    }

    .message-form textarea {
      flex: 1;
      resize: none;
      padding: 10px;
      border-radius: 20px;
      border: 1px solid #ccc;
      margin-right: 10px;
      background-color: rgba(30, 30, 30, 0.9);
      color: white;

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

    .text-muted {
      color: #ccc;
    }
  </style>


</head>
<div class="bg-blur-overlay"></div>

<div class="main-wrapper">
  <!-- Chat Header -->
  <div class="chat-header">
    <img src="<?php echo $other_profile_pic; ?>" alt="Profile">
    <span><?php echo $other_username; ?></span>
  </div>

  <!-- Chat Messages -->
  <div class="chat-messages">
    <?php
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $isSender = ($row['sender_id'] == $my_id);
        $msgClass = $isSender ? "sent" : "received";
        echo "<div class='message $msgClass'>";
        if (!$isSender) {
          echo "<img src='$other_profile_pic' width='30' height='30' class='rounded-circle me-2'>";
        }
        echo "<div class='msg-content'>";
        if (!empty($row['content'])) {
          echo "<p>" . htmlspecialchars($row['content']) . "</p>";
        }

        if (!empty($row['shared_post_id'])) {
          echo "<div class='card mt-2' style='background-color: #f1f1f1; color: black;'>
                  <div class='card-body p-2'>";
          if ($row['post_type'] === 'image') {
            echo "<img src='{$row['file_path']}' class='img-fluid rounded' style='max-height:200px;'>";
          } elseif ($row['post_type'] === 'video') {
            echo "<video class='w-100' style='max-height:200px;' controls>
                    <source src='{$row['file_path']}' type='video/mp4'>
                  </video>";
          } elseif ($row['post_type'] === 'text') {
            echo "<p class='mb-0'>" . htmlspecialchars($row['post_text']) . "</p>";
          }
          echo "<a href='view_post.php?post_id={$row['shared_post_id']}' class='btn btn-sm btn-outline-success mt-2'>View Post</a>
                </div>
              </div>";
        }

        echo "<br><small>" . date('H:i', strtotime($row['created_at'])) . "</small></div></div>";
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

<script>
  const chatMessages = document.querySelector('.chat-messages');
  if (chatMessages) {
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }
</script>


</html>