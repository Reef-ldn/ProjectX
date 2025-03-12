<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    die("Please log in to see comments!");
}

$postID = (int) $_GET['post_id'];

// Connect to DB
$conn = new mysqli("localhost", "root", "", "projectx_db");
if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//Fetch details about the post
$postSql = "SELECT p.id, p.text_content, p.file_path, p.post_type, p.created_at, u.username
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = $postID";
$postRes = $conn->query($postSql);
$postRow = $postRes->fetch_assoc();

// Fetch comments
$commentSql = "SELECT c.id, c.comment_text, c.created_at, u.username
               FROM comments c
               JOIN users u ON c.user_id = u.id
               WHERE c.post_id='$postID'
               ORDER BY c.created_at ASC";
$commentRes = $conn->query($commentSql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Comments for Post #<?php echo $postID; ?></title>
  <!-- Bootstrap / styling -->
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
    rel="stylesheet"
  >
</head>
<body class="p-3">

<h1>Comments for Post #<?php echo $postID; ?></h1>

<!-- Display  details about the post -->
<?php if($postRow): ?>
  <h2>Posted by: <?php echo $postRow['username']; ?> on <?php echo $postRow['created_at']; ?></h2>
  <?php if($postRow['post_type'] === 'text'): ?>
    <p><?php echo $postRow['text_content']; ?></p>
  <?php elseif($postRow['post_type'] === 'image'): ?>
    <p><?php echo $postRow['text_content']; ?></p>
    <img src="<?php echo $postRow['file_path']; ?>" width="400">
  <?php else: ?>
    <p><?php echo $postRow['text_content']; ?></p>
    <video width="400" controls>
      <source src="<?php echo $postRow['file_path']; ?>" type="video/mp4">
    </video>
  <?php endif; ?>
<?php endif; ?>

<hr>

<!-- Display existing comments -->
<h3>All Comments</h3>
<?php if($commentRes && $commentRes->num_rows > 0): ?>
  <?php while($cRow = $commentRes->fetch_assoc()): ?>
    <div class="mb-2">
      <strong><?php echo $cRow['username']; ?></strong> 
      <small class="text-muted">
        (<?php echo $cRow['created_at']; ?>)
      </small>
      <p><?php echo $cRow['comment_text']; ?></p>
    </div>
  <?php endwhile; ?>
<?php else: ?>
  <p>No comments yet!</p>
<?php endif; ?>

<hr>

<!-- Comment submission form -->
<h3>Leave a Comment</h3>
<form action="comments.php" method="POST">
  <input type="hidden" name="post_id" value="<?php echo $postID; ?>">
  <textarea name="comment_text" class="form-control mb-2" placeholder="Write a comment..."></textarea>
  <button type="submit" class="btn btn-primary">Post Comment</button>
</form>

<!-- Possibly a link back to feed -->
<br>
<a href="feed.php" class="btn btn-secondary">Back to Feed</a>

<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>

<?php
$conn->close();
?>