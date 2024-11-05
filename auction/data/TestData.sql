INSERT INTO Buyer (email, password)
VALUES ('example1@example.com', '000000000000000000000000000000000000000000000000000000000000');
INSERT INTO Seller (email, password)
VALUES ('example1@example.com', '000000000000000000000000000000000000000000000000000000000000');
INSERT INTO Buyer (email, password)
VALUES ('example2@example.com', '000000000000000000000000000000000000000000000000000000000000');
INSERT INTO Seller (email, password)
VALUES ('example2@example.com', '000000000000000000000000000000000000000000000000000000000000');

INSERT INTO Category (name)
VALUES ("china");
INSERT INTO Category (name)
VALUES ("painting");
INSERT INTO Category (name)
VALUES ("sculpture");

INSERT INTO Item (description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('Delicate china.', 1, 1, 10.0, 12.0, "2026-03-04");
INSERT INTO Item (description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('Delicate painting.', 1, 2, 5.0, 8.0, "2026-03-08");
INSERT INTO Item (description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('Modern sculpture.', 1, 3, 20.0, 22.0, "2026-09-08");
INSERT INTO Item (description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('Qing-dynasty china.', 1, 1, 13.0, 14.0, "2026-03-04");
INSERT INTO Item (description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('Chinese painting.', 1, 2, 10.0, 12.0, "2026-03-08");
INSERT INTO Item (description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('Italian sculpture.', 1, 3, 20.0, 22.0, "2026-09-08");
INSERT INTO Item (description, seller_ID, category_ID, starting_price, reserve_price, end_date)
VALUES ('French painting.', 1, 2, 20.0, 22.0, "2022-09-08");

INSERT INTO Bid (bid_time, buyer_ID, item_ID, bid_price)
VALUES ("2024-10-04", 1, 1, 24.0);
INSERT INTO Bid (bid_time, buyer_ID, item_ID, bid_price)
VALUES ("2024-10-05", 2, 1, 25.0);
INSERT INTO Bid (bid_time, buyer_ID, item_ID, bid_price)
VALUES ("2024-10-05", 1, 2, 10.0);
INSERT INTO Bid (bid_time, buyer_ID, item_ID, bid_price)
VALUES ("2022-10-05", 1, 2, 10.0);

INSERT INTO Comment (Item_ID, comment)
VALUES (7, "Very honest seller. The work is authentic!");

INSERT INTO Watch (buyer_ID, item_ID)
VALUES (1, 5)