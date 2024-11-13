<?php include_once("header.php") ?>

<?php
// Start session if it hasn't been started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user has seller privileges
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] !== 'seller') {
    die("Only sellers can create auctions.");
}
?>

<div class="container">
    <!-- Create auction form -->
    <div style="max-width: 800px; margin: 10px auto">
        <h2 class="my-3">Create New Auction</h2>
        <div class="card">
            <div class="card-body">
                <!-- Note: This form does not perform any dynamic/client-side/JavaScript-based data validation. It only checks after the form has been submitted. -->
                <form method="post" action="create_auction_result.php" enctype="multipart/form-data">
                    <div class="form-group row">
                        <label for="auctionTitle" class="col-sm-2 col-form-label text-right">Title of Auction</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="auctionTitle" name="auctionTitle" placeholder="e.g. Black mountain bike">
                            <small id="titleHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> A short description of the item you're selling, which will display in listings.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="auctionDetails" class="col-sm-2 col-form-label text-right">Details</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" id="auctionDetails" name="auctionDetails" rows="4"></textarea>
                            <small id="detailsHelp" class="form-text text-muted">Full details of the listing to help bidders decide if it's what they're looking for.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="auctionCategory" class="col-sm-2 col-form-label text-right">Category</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="auctionCategory" name="auctionCategory">
                                <option selected>Choose...</option>
                                <option value="china">China</option>
                                <option value="painting">Painting</option>
                                <option value="sculpture">Sculpture</option>
                            </select>
                            <small id="categoryHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Select a category for this item.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="auctionStartPrice" class="col-sm-2 col-form-label text-right">Starting Price</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">£</span>
                                </div>
                                <input type="number" class="form-control" id="auctionStartPrice" name="auctionStartPrice">
                            </div>
                            <small id="startBidHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Initial bid amount.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="auctionReservePrice" class="col-sm-2 col-form-label text-right">Reserve Price</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">£</span>
                                </div>
                                <input type="number" class="form-control" id="auctionReservePrice" name="auctionReservePrice">
                            </div>
                            <small id="reservePriceHelp" class="form-text text-muted">Optional. Auctions that end below this price will not go through. This value is not displayed in the auction listing.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="auctionEndDate" class="col-sm-2 col-form-label text-right">End Time</label>
                        <div class="col-sm-10">
                            <input type="datetime-local" class="form-control" id="auctionEndDate" name="auctionEndDate">
                            <small id="endDateHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Set the end time for the auction.</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="auctionImage" class="col-sm-2 col-form-label text-right">Upload Image</label>
                        <div class="col-sm-10">
                            <input type="file" class="form-control" id="auctionImage" name="auctionImage" accept="image/*">
                            <small class="form-text text-muted">Upload an image for the auction item (optional).</small>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary form-control">Create Auction</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once("footer.php") ?>
