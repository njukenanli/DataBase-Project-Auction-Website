# Contribution History #
When committing each time, please add a note of the changes here. This would become a reference point of mutual evaluation...

Sixth Commit
----------
Karina: modify header.php to make it able to choose between buyer and seller. modify login_result.php to make it more consice and safe and add hash_password function.

Finish all the TODO comments @ browse.php, listing.php and watchlist_fuc.php

Users can now browse, select and sort all the auctions, see details of an auction, and add/remove items to/from watching list. 

TODO: Add email functions. Add functions to automatically end an auction when the deadline arrives. 

2024.11.09 20:00

Fifth Commit
----------
Jiayi:

Fourth Commit
----------
York Tseng:

Third Commit
----------
York Tseng: upload the ER diagram and the logical design.

2024.11.02 18:45

Second Commit 
----------
Karina: Implement the codes to create our database and tables.

@ auction/data/

2024.10.31 19:00

First Commit 
----------
Karina: Create the project on GitHub. Commit the starter code from moodle.

2024.10.16 17:00


# Setup #

## Database ##
Follow the instructions in **auction/data/** to initialize the database.

## Excution ##
Environemtns with PHP and SQL is required. We used XAMPP as the development env.

Run **auction/index.php** to start the program.

# Program Outline #

## Introduction ##
This is a mock auction program running at the server end. 
The main languages involved are **PHP, SQL, CSS/HTML/JS**.

Our program design strictly follows the Entity-Relationship design and follows the 1st, 2nd and 3rd database normalization requirements, the ERD and logical design details of which are as follows.

## Entity-Relationship Diagram ##
![ER Diagram](./materials/Database_ER_diagram.png)

## Logical Design ##
![Logical Design](./materials/Logical_Design.png)
