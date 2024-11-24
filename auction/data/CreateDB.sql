CREATE TABLE Seller (
    user_ID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(60) NOT NULL UNIQUE,
    password VARCHAR(60) NOT NULL
);

CREATE TABLE Buyer (
    user_ID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(60) NOT NULL UNIQUE,
    password VARCHAR(60) NOT NULL
);

CREATE TABLE Category (
    category_ID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL
);

CREATE TABLE Item (
    item_ID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(30) NOT NULL,
    description VARCHAR(60),
    seller_ID INT(6) UNSIGNED,
    category_ID INT(6) UNSIGNED,
    starting_price DECIMAL(10,2) NOT NULL,
    reserve_price DECIMAL(10,2) NOT NULL,
    end_date TIMESTAMP NOT NULL,
    image_path VARCHAR(255),
    processed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (seller_ID) REFERENCES Seller(user_ID),
    FOREIGN KEY (category_ID) REFERENCES Category(category_ID)
);

CREATE TABLE Bid (
    bid_ID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bid_time TIMESTAMP NOT NULL,
    buyer_ID INT(6) UNSIGNED,
    item_ID INT(6) UNSIGNED,
    bid_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (buyer_ID) REFERENCES Buyer(user_ID),
    FOREIGN KEY (item_ID) REFERENCES Item(item_ID)
);

CREATE TABLE Watch (
    watch_ID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    buyer_ID INT(6) UNSIGNED,
    item_ID INT(6) UNSIGNED,
    FOREIGN KEY (buyer_ID) REFERENCES Buyer(user_ID),
    FOREIGN KEY (item_ID) REFERENCES Item(item_ID)
);

CREATE TABLE Comment (
    item_ID INT(6) UNSIGNED PRIMARY KEY,
    comment VARCHAR(200) DEFAULT 'No comment yet...',
    rating DECIMAL(2,1) DEFAULT -1.0,
    FOREIGN KEY (item_ID) REFERENCES Item(item_id)
);

CREATE TABLE Enquiry (
    enquiry_ID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enquiry_time TIMESTAMP NOT NULL,
    item_ID INT(6) UNSIGNED,
    buyer_ID INT(6) UNSIGNED,
    enquiry VARCHAR(200) NOT NULL,
    answer VARCHAR(200) DEFAULT 'No answer yet...',
    FOREIGN KEY (item_ID) REFERENCES Item(item_id),
    FOREIGN KEY (buyer_ID) REFERENCES Buyer(user_ID)
)