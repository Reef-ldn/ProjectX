<!-- This page displays a feed of all the uploaded content from users--> 

<?php
  session_start();  //Check the user is logged in;

  //connect to the db
  $conn = new mysqli("localhost", "root", "", "projectx_db");
  if($conn->connect_error) {
    die("Failed to connect to the database: " . $conn->connect_error);
  }

  
  
  
  //Fetch all videos from newest to oldest (LIFO)
  $sql = "SELECT p.id, p.post_type, p.file_path, p.text_content, p.created_at, u.username,
          (SELECT COUNT(*) FROM likes l where l.post_id = p.id) AS like_count
          from posts p
          JOIN users u ON p.user_id = u.id 
          ORDER BY p.created_at DESC"
  ;
  //The "JOIN" gives the foreigner key of the user's name from the user's table
  //The select count is a sub query of likes and acts as a like counter.
      //for each row in 'posts', the 'likes' table is also checked to see how many rows there is 
      // for posts with that id and  keeps a count of it
      //This count result is the 'like_count'

  $result = $conn->query($sql);
?>
  

  
<!DOCTYPE html>
 <html>

  <head>
    <title>Feed</title>
  </head>
  
  <body>
    <h1>Feed<h1> 

    <?php
    if($result-> num_rows >0) {          
      //Display each post - Read each line
      while($row = $result->fetch_assoc()) {
        //creates the video box
        echo "<div style = 'border:1px solid #ccc;
                margin-bottom:10px;
                padding:10px;'>";

        //Show the title (If there is one)
        if(!empty($row['title'])) {
          echo"<h3>" . $row['title'] . "</h3>";
        }

        echo "<p>Uploaded by: " .  $row['username'] . " at " . $row['created_at'] . "</p>";
        
        //If it's a text post
        if($row['post_type'] == "text") {
          echo "<p>" . $row['text_content'] . "</p>";
        }
        //if it's an image post
        else if($row['post_type'] == "image") {
          echo "<p>" . $row['text_content'] . "</p>"; //uses text_content as a caption (2 birds with 1 stone)
          echo "<img src='" . $row['file_path'] . "' width='400'/>" ;
        }
        //if it's a video
        else if($row['post_type'] == "video") {
          echo "<p>" . $row['text_content'] . "</p>"; //uses text_content as a caption
          echo "<video width='400' controls> 
                <source src = ' " . $row['file_path'] . " ' type = 'video/mp4' >
                Your browser does not support the video tag.
                </video> ";
        }

        echo "</div>";   

        
        //Displaying the Like count
        echo "<p>Likes: " . $row ['like_count'] . "</p>"; //Show the like count next to the video (How many likes are in the 'like_count' section of that likes column)

        //Like Button 
        echo "<a href='likes.php?post_id=" . $row['id'] . "'>Like</a>";    //Clicking this button will call the 'likes.php' script

        //The Comments Form
        echo "<form action = 'comments.php'    method = 'POST'>" ;
        echo "<input type='hidden' name='post_id'  value='" . $row['id'] . "' /> ";
        echo "<textarea name = 'comment_text'   placeholder ='Write a comment here...' > </textarea> <br>";
        echo "<button type = 'submit' > Comment </button> " ; 
        echo "</form>";

        //Displaying the commentts
        $postID = $row['id']; //The current post
        $commentSql = "SELECT c.comment_text, c.created_at, u.username
                        FROM comments c
                        JOIN users u ON c.user_id = u.id
                        WHERE c.post_id = '$postID'
                        ORDER BY c.created_at ASC";

        $commentResult = $conn->query($commentSql);

        while($cRow = $commentResult->fetch_assoc()) {
          echo "<p> <b>" . $cRow['username'] . ":</b> " . $cRow['comment_text'] . 
                " <i>(" . $cRow['created_at'] . ")</i></p>";
        }

      } 
    } else {
      echo "Feed is Empty.";
    }
    $conn->close();
    ?>
    
  </body>

</html>