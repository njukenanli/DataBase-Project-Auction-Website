<?php
$item_id = $_GET['item_id'];
$buyer_id = $_GET['buyer_id'];
?>
<html>
  <form id="enquiryForm" method="post" action="process_enquiry.php">
  Before this auction ends, you can make enquiry to the seller about this item.
  <br><br>
  <input type="hidden" name="item_id" value=<?php echo $item_id;?>>
  <input type="hidden" name="buyer_id" value=<?php echo $buyer_id;?>>
  Enquiry (no more than 200 characters) :<br>
    <div class="input-group" style="width: 50%; float: left;">
      <textarea class="form-control" id="enquiry" name="enquiry" rows="5" maxlength="200"></textarea>
    </div>
  <br><br><br><br><br>
  <div class="col-md-1 px-0">
    <button type="submit" class="btn btn-primary">Make Enquiry</button>
  </div>
</html>