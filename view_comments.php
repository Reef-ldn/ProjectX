<?php

//Ensure the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
  die("Please log in to see comments!");
}

$postID = (int) $_GET['post_id'];

// Connect to DB
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
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
$commentSql = "SELECT c.id, c.comment_text, c.created_at, c.user_id,  u.username
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

  <!--Bootstrap CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <!--Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</head>

<body class="p-3">

  <h1>Comments for Post #<?php echo $postID; ?></h1>

  <!-- Display  details about the post -->
  <?php if ($postRow): ?>
    <h2>Posted by: <?php echo $postRow['username']; ?> on <?php echo $postRow['created_at']; ?></h2>
    <?php if ($postRow['post_type'] === 'text'): ?>
      <p><?php echo $postRow['text_content']; ?></p>
    <?php elseif ($postRow['post_type'] === 'image'): ?>
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
  <?php if ($commentRes && $commentRes->num_rows > 0): ?>
    <?php while ($cRow = $commentRes->fetch_assoc()): ?>
      <div class="mb-2 border rounded p-2 position-relative">
        <div class="d-flex justify-content-between">
          <div>
            <strong><?php echo $cRow['username']; ?></strong>
            <small class="text-muted">(<?php echo $cRow['created_at']; ?>)</small>
          </div>

          <!--3 Dot dropdown-->
          <div class="dropdown">
            <button class="btn btn-sm text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if ($cRow['user_id'] == $_SESSION['user_id']): ?>
                <li><a class="dropdown-item edit-comment" href="#" data-id="<?php echo $cRow['id']; ?>">Edit</a></li>
                <li><a class="dropdown-item delete-comment text-danger" href="#"
                    data-id="<?php echo $cRow['id']; ?>">Delete</a></li>
              <?php endif; ?>
              <li><a class="dropdown-item report-comment" href="#" data-id="<?php echo $cRow['id']; ?>">Report</a></li>
            </ul>
          </div>
        </div>



        <p><?php echo $cRow['comment_text']; ?></p>

        <!--Replies-->
        <div class="ms-4 mt-2 reply-container" data-parent-id="<?php echo $cRow['id']; ?>">
          <!-- Replies will go here -->
        </div>
        <!-- Reply form -->
        <form class="reply-form ms-4 mt-1" data-parent-id="<?php echo $cRow['id']; ?>">
          <input type="hidden" name="post_id" value="<?php echo $postID; ?>">
          <input type="hidden" name="parent_id" value="<?php echo $cRow['id']; ?>">
          <input type="text" name="reply_text" class="form-control form-control-sm mb-1" placeholder="Reply...">
          <button class="btn btn-sm btn-secondary" type="submit">Reply</button>
        </form>
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



  <br>



  <!--Bootstrap JavaScript-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>


  <script>
    document.querySelectorAll('.delete-comment').forEach(btn => {
      btn.addEventListener('click', async e => {
        e.preventDefault();
        const id = btn.dataset.id;
        if (!confirm("Delete comment?")) return;

        const res = await fetch('delete_comment.php', {
          method: 'POST',
          body: new URLSearchParams({ comment_id: id })
        });
        const data = await res.json();
        if (data.status === 'success') {
          btn.closest('.mb-2').remove();
        }
      });
    });

    document.querySelectorAll('.edit-comment').forEach(btn => {
      btn.addEventListener('click', e => {
        e.preventDefault();
        const id = btn.dataset.id;
        const textEl = document.querySelector(`.comment-text[data-id='${id}']`);
        const oldText = textEl.textContent;
        const newText = prompt("Edit comment:", oldText);
        if (!newText || newText === oldText) return;

        fetch('edit_comment.php', {
          method: 'POST',
          body: new URLSearchParams({ comment_id: id, comment_text: newText })
        }).then(res => res.json()).then(data => {
          if (data.status === 'success') textEl.textContent = newText;
        });
      });
    });

    document.querySelectorAll('.reply-form').forEach(form => {
      form.addEventListener('submit', async e => {
        e.preventDefault();
        const replyText = form.querySelector('input[name="reply_text"]').value;
        if (!replyText) return;

        const formData = new FormData(form);
        const res = await fetch('reply_comment.php', {
          method: 'POST',
          body: formData
        });

        const data = await res.json();
        if (data.status === 'success') {
          const container = form.previousElementSibling;
          const newReply = document.createElement('p');
          newReply.classList.add('ms-2');
          newReply.textContent = 'You: ' + replyText;
          container.appendChild(newReply);
          form.reset();
        }
      });
    });
  </script>

</body>

</html>

<?php
$conn->close();
?>