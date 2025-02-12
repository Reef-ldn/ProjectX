<!-- This page displays a feed of all the uploaded content from users--> 

<?php
  session_start();  //Check the user is logged in;

  //connect to the db
  $conn = new mysqli("localhost", "root", "", "projectx_db");
  if($conn->connect_error) {
    die("Failed to connect to the database: " . $conn->connect_error);
  }

  //Fetch all videos from newest to oldest (LIFO)
  $sql = "SELECT v.id, v.video_path, v.title, v.created_at, u.username,
  (SELECT COUNT(*) FROM likes l 
  where l.video_id = v.id
  ) AS like_count 
  from videos v
  JOIN users u on v.user_id = u.id 
  ORDER BY v.created_at DESC"
  ;
  //The "JOIN" gives the foreigner key of the user's name from the user's table
  //The select count is a sub query of likes and acts as a like counter.
      //for each row in 'videos', the 'likes' table is also checked to see how many rows the video id and count it
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
      //Display each video - Read each line
      while($row = $result->fetch_assoc()) {
        //shows the video
        echo "<div style = 'border:1px solid #ccc;
                margin-bottom;
                padding:10px;'>";
        echo "<h3>" . $row['title'] . "</h3>";
        echo "<p>Uploaded by: " .  $row['username'] . " at " . $row['created_at'] . "</p>";
        
        //shows the video
        echo "<video width='400' controls>
            <source src = ' " . $row['video_path'] . " ' type = 'video/mp4' >
            Your browser does not support the video tag.
            </video> ";
            echo "</div>";   
        
        //Displaying the Like count
        echo "<p>Likes: " . $row ['like_count'] . "</p>"; //Show the like count next to the video (How many likes are in the 'like_count' section of that likes column)

        //Like Button 
        echo "<a href='likes.php?video_id=" . $row['id'] . "'>Like</a>";    //Clicking this button will call the 'likes.php' script

        //The Comments Form
        echo "<form action = 'comments.php'    method = 'POST'>" ;
        echo "<input type='hidden' name='video_id'  value='" . $row['id'] . "' /> ";
        echo "<textarea name = 'comment_text'   placeholder ='Write a comment here...' > </textarea> <br>";
        echo "<button type = 'submit' > Comment </button> " ; 
        echo "</form>";

        //Displaying the commentts
        $videoID = $row['id']; //The current video
        $commentSql = "SELECT c.comment_text, c.created_at, u.username
                        FROM comments c
                        JOIN users u ON c.user_id = u.id
                        WHERE c.video_id = '$videoID'
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