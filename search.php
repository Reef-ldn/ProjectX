<?php
session_start();
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$loggedUserId = $_SESSION['user_id'] ?? null;
$q = trim($_GET['q'] ?? '');

$users = $posts = $media = [];

if ($q !== '') {
  // Search Users
  $userSql = "SELECT id, username, name, profile_pic FROM users 
              WHERE username LIKE ? OR name LIKE ?";
  $stmtUser = $conn->prepare($userSql);
  $searchTerm = "%{$q}%";
  $stmtUser->bind_param("ss", $searchTerm, $searchTerm);
  $stmtUser->execute();
  $users = $stmtUser->get_result()->fetch_all(MYSQLI_ASSOC);

  // Search Text Posts
  $postSql = "SELECT id, text_content, created_at FROM posts 
              WHERE post_type = 'text' AND text_content LIKE ?";
  $stmtPost = $conn->prepare($postSql);
  $stmtPost->bind_param("s", $searchTerm);
  $stmtPost->execute();
  $posts = $stmtPost->get_result()->fetch_all(MYSQLI_ASSOC);

  // Search Media Posts
  $mediaSql = "SELECT id, post_type, file_path, text_content, created_at FROM posts 
               WHERE post_type IN ('image', 'video') AND 
               (text_content LIKE ? OR file_path LIKE ?)";
  $stmtMedia = $conn->prepare($mediaSql);
  $stmtMedia->bind_param("ss", $searchTerm, $searchTerm);
  $stmtMedia->execute();
  $media = $stmtMedia->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Search Results</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
    <!--Navbar stylesheet-->
    <link rel="stylesheet" href="/ProjectX/css/navbar.css">

  
  <style>
    body {
      color: #eee;
      padding: 20px;
      background-image: url('/ProjectX/uploads/people-soccer-stadium.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      background-attachment: fixed;
    }

    .bg-blur-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      backdrop-filter: blur(4px);
      background-color: rgba(0, 0, 0, 0.3);
      z-index: 1;
    }

    .search-section {
      margin-bottom: 40px;
    }

    .search-section h3 {
      margin-bottom: 20px;
      border-bottom: 2px solid #444;
      padding-bottom: 8px;
    }

    .container {
      background-color: rgba(30, 30, 30, 0.7);
      padding: 40px;
      border-radius: 10px;
      max-width: 900px;
    }

    .container-2 {
      background-color: rgba(30, 30, 30, 0.7);
      justify-content: center;
      align-items: center;

      padding: 20px 0px 20px 0px;

      border-radius: 10px;
    }

    .result-item {
      padding: 10px;
      border-bottom: 1px solid #333;
    }

    .result-item:hover {
      background-color: #1a1a1a;
    }

    .profile-pic {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 12px;
    }

    .media-thumb {
      max-width: 100px;
      max-height: 100px;
      object-fit: cover;
      margin-right: 10px;
    }

    a {
      color: #00ff88;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    input.form-control {
      background-color: #222;
      color: #eee;
      border: 1px solid #444;
    }

    input.form-control::placeholder {
      color: #aaa;
    }

    input.form-control:focus {
      background-color: #222;
      color: #fff;
      border-color: #00ff88;
      box-shadow: none;
    }
  </style>
</head>

<body>
  <div class="bg-blur-overlay"></div>


  <!-- Top search bar -->
  <div class="main-content position-relative z-2">

   <!--Nav Bar-->
   <?php 
    // $currentPage = 'profile';
    include 'navbar.php'; ?>
  
    <!--container-->
    <div class="container mt-5">

      <!-- Search Bar -->
      <form action="search.php" method="GET" class="d-flex justify-content-center mb-4">
        <input type="text" name="q" class="form-control me-2" placeholder="Search users, posts, or media..."
          value="<?php echo htmlspecialchars($q); ?>">
        <button type="submit" class="btn btn-success">Search</button>
      </form>

      <h1 class="mb-4">Search Results for "<?php echo htmlspecialchars($q); ?>"</h1>

      <?php if ($q === ''): ?>
        <p>Please enter a search term.</p>
      <?php else: ?>

        <div class="search-section">
          <h3>Users</h3>
          <?php if (count($users) > 0): ?>
            <?php foreach ($users as $u): ?>
              <div class="result-item d-flex align-items-center">
                <img src="<?php echo $u['profile_pic'] ?: 'uploads/profile_pics/Footballer_shooting_b&w.jpg'; ?>"
                  class="profile-pic">
                <div>
                  <a href="profile.php?user_id=<?php echo $u['id']; ?>">
                    <strong>@<?php echo strtolower($u['username']); ?></strong> — <?php echo $u['name']; ?>
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No users found.</p>
          <?php endif; ?>
        </div>

        <div class="search-section">
          <h3>Posts</h3>
          <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $p): ?>
              <div class="result-item">
                <a href="view_post.php?post_id=<?php echo $p['id']; ?>">
                  <?php echo substr($p['text_content'], 0, 100); ?>...
                </a>
                <br><small class="text-muted"><?php echo date('M d, Y H:i', strtotime($p['created_at'])); ?></small>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No text posts found.</p>
          <?php endif; ?>
        </div>

        <div class="search-section">
          <h3>Media</h3>
          <?php if (count($media) > 0): ?>
            <?php foreach ($media as $m): ?>
              <div class="result-item d-flex align-items-center">
                <?php if ($m['post_type'] == 'image'): ?>
                  <img src="<?php echo $m['file_path']; ?>" class="media-thumb" alt="Image">
                <?php elseif ($m['post_type'] == 'video'): ?>
                  <video class="media-thumb" muted>
                    <source src="<?php echo $m['file_path']; ?>" type="video/mp4">
                  </video>
                <?php endif; ?>
                <div>
                  <a href="view_post.php?post_id=<?php echo $m['id']; ?>">
                    <?php echo !empty($m['text_content']) ? substr($m['text_content'], 0, 60) . '...' : ucfirst($m['post_type']) . " Post"; ?>
                  </a>
                  <br><small class="text-muted"><?php echo date('M d, Y H:i', strtotime($m['created_at'])); ?></small>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No media found.</p>
          <?php endif; ?>
        </div>

      <?php endif; ?>
    </div>
  </div>

</body>

</html>
<?php $conn->close(); ?>