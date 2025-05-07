==============================
 NextXI - README
==============================

Project Title:
--------------
NextXI – A Football-Themed Social Networking Platform  
By Sheriff Adisa (220041681)


Description:
-------------
NextXI is a web-based social platform designed to connect football enthusiasts. Users can register, create profiles, post multimedia content, like and comment on posts, chat with other users, and track personal football achievements like trophies and previous teams.

The platform mimics the experience of a social media site, tailored to the football world.

Features:
---------
- User registration and login
- Profile editing with profile picture uploads
- Multimedia post upload (image, video, or text)
- Like & comment on posts
- Highlight posts feature
- Direct messaging between users
- Follower system (follow/unfollow)
- Search functionality (users, posts, and media)
- View shared posts in messages
- Trophy and previous team tracking (on profile)

Technologies Used:
------------------
- PHP (Server-side scripting)
- MySQL (Database)
- HTML/CSS/JavaScript (Frontend)
- Bootstrap (Styling)
- XAMPP (Local Hosting)

Installation Instructions:
--------------------------
1. Download and install **XAMPP** if not already installed.
2. Place the **ProjectX** folder into the `htdocs` directory.
3. Start **Apache** and **MySQL** in XAMPP.
4. Open **phpMyAdmin**, import the `projectx_database.sql` file to create the required database.
5. Open your browser and navigate to:  
   `http://localhost/ProjectX/register.php` to create a new user.

Default Image Paths:
--------------------
Ensure the  default image is placed in the appropriate folder:
- `uploads/profile_pics/Footballer_shooting_b&w.jpg`  

Credentials:
------------
There are no default users — create an account via `register.php`.

Additional Notes:
-----------------
- The “nested comments” feature is partially implemented. Replies are saved in the DB but not persistently rendered on refresh.
- The like button is powered by JavaScript/AJAX for a dynamic user experience.
- If profile pictures are not uploaded, a fallback image is used.

Author:
-------
Sheriff Adisa  
BSc Computer Science – Aston University  
Final Year Project – 2024/2025

