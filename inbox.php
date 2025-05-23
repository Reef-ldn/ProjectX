<!-- This script allows users to view all the messages that have been sent to the logged in user-->

<?php
session_start();

//Check if the user is logged in before allowing them to view their inbox.
if (!isset($_SESSION['user_id'])) {
  die("You must be logged in to view your inbox.");   //If they're not logged in, kill the sesssion
}

//Get the session ID
$my_id = $_SESSION['user_id'];  //Logged in user's ID

//Connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Failed to connect to the database: " . $conn->connect_error);
}

/*
 This SQL query finds a list of all users the current user has had conversations with.
- checks messages where this user was either the sender or receiver.
- grabs the other user's ID, username, profile picture, and the time of the last message.
- groups results by user and shows the most recent messages first.
*/
//Select all messages where the receiver_id = me
$sql = "SELECT m.id, m.sender_id, m.content, m.created_at, u.username AS sender_name
          FROM messages m
          JOIN users u ON m.sender_id = u.id  /*Join users table and messages table*/
          WHERE m.receiver_id = '$my_id' 
          ORDER BY m.created_at DESC";
$result = $conn->query($sql);

// Get list of users the logged-in user has conversations with
$sql = "SELECT u.id, u.username, u.profile_pic, MAX(m.created_at) 
            as last_msg_time, SUBSTRING_INDEX(GROUP_CONCAT(m.content 
            ORDER BY m.created_at DESC), ',', 1) 
            AS last_msg /*Last Message preview (When it was sent too)*/
        FROM messages m
        JOIN users u ON u.id = IF(m.sender_id = '$my_id', m.receiver_id, m.sender_id)
        WHERE m.sender_id = '$my_id' OR m.receiver_id = '$my_id'
        GROUP BY u.id, u.username, u.profile_pic
        ORDER BY last_msg_time DESC";   /*Last message sent/receieved goes at the top*/
$users = $conn->query($sql);

?>

<!--Frontend-->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Inbox</title>

  <!--Bootstrap CSS-->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!--Font Awesome Icons-->
  <script src="https://kit.fontawesome.com/22c727220d.js" crossorigin="anonymous"></script>

  <!--Navbar stylesheet-->
  <link rel="stylesheet" href="/ProjectX/css/navbar.css">

  <style>
    body {
      background-image: url('/ProjectX/uploads/people-soccer-stadium.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      background-attachment: fixed;
      color: white;
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

    /* Makes the inbox take up the full screen height */
    .inbox-wrapper {
      display: flex;
      height: 100vh;
      overflow: hidden;
      background-color: rgba(30, 30, 30, 0.88);
      border-radius: 16px;
      box-shadow: 0 0 20px rgba(0, 255, 100, 0.1);
      margin: 40px auto;
      max-width: 1100px;
      color: black;
    }

    /* Left Panel: List of users messaged */
    .sidebar {
      width: 30%;
      background: white;
      border-right: 1px solid #dee2e6;
      overflow-y: auto;
    }

    /* Sidebar heading */
    .sidebar h4 {
      padding: 20px;
      border-bottom: 1px solid #ccc;
      text-align: center;
    }

    /* User container for side bar */
    .user-info {
      display: flex;
      align-items: center;
    }

    /* Each user item (in the list) */
    .user-item {
      padding: 15px;
      border-bottom: 1px solid #eee;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: 0.2s;
    }

    /* Profile pic for side bar */
    .user-item img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 10px;
    }

    .user-item:hover {
      background-color: #f1f1f1;
    }

    /* When a conversation is selected */
    .user-item.active {
      background-color: #009e42;
      color: white;
    }

    /* Message Preview in the side bar */
    .user-item.active .msg-preview {
      color: white;
    }

    .msg-preview {
      font-size: 0.875rem;
      color: #6c757d;
      margin-top: 4px;
    }

    /* Right Panel - Chat conversation iframe */
    .chat-viewer {
      width: 70%;
      background: #fff;
    }

    /* The iframe where conversations are shown */
    iframe {
      border: none;
      width: 100%;
      height: 100%;
      min-height: 100vh;
    }

    /* Chat viewer for smaller screens */
    @media (max-width: 768px) {

      /* Hide sidebar completely */
      .sidebar {
        display: none !important;
      }

      /* Expand chat view to take full width */
      .chat-viewer {
        width: 100%;
        display: block;
        position: relative;
        z-index: 2;
        background-color: rgba(0, 0, 0, 0.6);
        color: white;
      }
    }
  </style>
</head>

<body>
  <div class="bg-blur-overlay"></div> <!--Background-->

  <!--Wrapper-->
  <div class="main-content-wrapper position-relative z-2">

    <!--Inbox Wrapper-->
    <div class="inbox-wrapper mt-5">

      <!--Nav Bar-->
      <?php
      $currentPage = 'inbox';
      include 'navbar.php'; ?>

      <!-- Left panel: List of Users messaged -->
      <div class="sidebar">
        <h4 class="text-center">Inbox</h4>

        <?php if ($users && $users->num_rows > 0): ?>
          <!--Loop through each user that has been messaged-->
          <?php while ($row = $users->fetch_assoc()): ?>
            <!--Clicking a user loads their chat on the right through an iFrame-->
            <a href="conversation.php?other_id=<?php echo $row['id']; ?>" target="chatFrame"
              class="text-decoration-none text-dark">
              <!-- User's details on the left -->
              <div class="user-item">
                <div class="user-info">
                  <div>
                    <!--profile pic-->
                    <img src="<?php echo !empty($row['profile_pic'])
                      ? $row['profile_pic']
                      : 'uploads/profile_pics/Footballer_shooting_b&w.jpg'; ?>" alt="">
                    <!--username-->
                    <strong><?php echo $row['username']; ?></strong>
                    <!--Show a preview of the last message in the chat-->
                    <div class="msg-preview">
                      <?php echo htmlspecialchars(substr($row['last_msg'], 0, 30)) . '...'; ?>
                    </div>
                  </div>
                </div>
              </div> <!--User's Details-->
            </a>
          <?php endwhile; ?>
        <?php else: ?>
          <!--if no conversations are found-->
          <p class="text-muted text-center mt-4">No conversations found.</p>
        <?php endif; ?>
      </div>

      <!-- Right panel: Chat Viewer - shows all convos -->
      <div class="chat-viewer">
        <!-- blank iframe at first. When a user is clicked, the conversation.php loads here -->
        <iframe name="chatFrame" src="" title="Conversation"></iframe>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Script handles highlighting chats in the left panel -->
  <script>
    // Select all the user rows
    const userItems = document.querySelectorAll('.user-item');

    //For each user
    userItems.forEach(item => {
      item.addEventListener('click', () => {    //Listen for when they're clicked on
        const userId = item.getAttribute('data-user-id');   //Get their ID

        //Chat highlighting
        userItems.forEach(i => i.classList.remove('active')); // Remove green highlight from all items
        item.classList.add('active'); // Highlight the chat that was clicked

        // On phones, go to the chat directly
        if (window.innerWidth <= 768) {
          window.location.href = `conversation.php?other_id=${userId}`;
        } else {
          // On PC or larger screens, load the chat into an iframe of conversation.php
          document.querySelector('iframe[name="chatFrame"]').src = `conversation.php?other_id=${userId}`;
        }
      });
    });
  </script>
</body>

</html>
<?php $conn->close(); ?>