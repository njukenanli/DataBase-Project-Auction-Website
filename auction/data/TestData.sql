INSERT INTO Buyer (email, password)
VALUES ('example1@example.com', '$2y$10$mekZWXnyyEKoaO3MkGZsd.uviTyLBfoVdoce7djHPIHqPPPa9xNeW');
INSERT INTO Seller (email, password)
VALUES ('example1@example.com', '$2y$10$mekZWXnyyEKoaO3MkGZsd.uviTyLBfoVdoce7djHPIHqPPPa9xNeW');
INSERT INTO Buyer (email, password)
VALUES ('example2@example.com', '$2y$10$mekZWXnyyEKoaO3MkGZsd.uviTyLBfoVdoce7djHPIHqPPPa9xNeW');
INSERT INTO Seller (email, password)
VALUES ('example2@example.com', '$2y$10$mekZWXnyyEKoaO3MkGZsd.uviTyLBfoVdoce7djHPIHqPPPa9xNeW');

INSERT INTO Category (name)
VALUES ("china");
INSERT INTO Category (name)
VALUES ("painting");
INSERT INTO Category (name)
VALUES ("sculpture");

INSERT INTO Item (title, description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('china', 'Delicate china.', 1, 1, 10.0, 12.0, "2026-03-04");
INSERT INTO Item (title, description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('painting', 'Delicate painting.', 1, 2, 5.0, 8.0, "2026-03-08");
INSERT INTO Item (title, description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('sculpture', 'Modern sculpture.', 1, 3, 20.0, 22.0, "2026-09-08");
INSERT INTO Item (title, description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('china', 'Qing-dynasty china.', 1, 1, 13.0, 14.0, "2026-03-04");
INSERT INTO Item (title, description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('painting', 'Chinese painting.', 1, 2, 10.0, 12.0, "2026-03-08");
INSERT INTO Item (title, description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('sculpture', 'Italian sculpture.', 1, 3, 20.0, 22.0, "2026-09-08");
INSERT INTO Item (title, description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('painting', 'French painting.', 1, 2, 20.0, 22.0, "2022-09-08");
INSERT INTO Item (title, description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('painting', 'Austria painting.', 1, 2, 20.0, 22.0, "2022-09-08");
INSERT INTO Item (title, description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('china', 'Janpanese china.', 1, 1, 20.0, 22.0, "2022-09-08");

INSERT INTO Bid (bid_time, buyer_ID, item_ID, bid_price)
VALUES ("2024-10-04", 1, 1, 24.0);
INSERT INTO Bid (bid_time, buyer_ID, item_ID, bid_price)
VALUES ("2024-10-05", 2, 1, 25.0);
INSERT INTO Bid (bid_time, buyer_ID, item_ID, bid_price)
VALUES ("2024-10-05", 1, 2, 10.0);
INSERT INTO Bid (bid_time, buyer_ID, item_ID, bid_price)
VALUES ("2022-10-05", 1, 2, 10.0);
INSERT INTO Bid (bid_time, buyer_ID, item_ID, bid_price)
VALUES ("2022-06-05", 2, 7, 10.0);
INSERT INTO Bid (bid_time, buyer_ID, item_ID, bid_price)
VALUES ("2022-06-05", 1, 7, 30.0);
INSERT INTO Bid (bid_time, buyer_ID, item_ID, bid_price)
VALUES ("2022-06-05", 2, 8, 30.0);

INSERT INTO Watch (buyer_ID, item_ID)
VALUES (1, 5)