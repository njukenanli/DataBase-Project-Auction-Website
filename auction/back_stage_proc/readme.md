# Excution #
This is the back-stage process codes of this program. 

Before allowing user to visit this website, you should first start 

    transaction.php

independently at the back stage. 

This process allows the system to end an auction automatically at the set end time of this auction, either to inform the successful buyer and the seller to make a deal, or to tell the seller that the auction is cancelled due to bids not high enough.

# Testing #

For our developers to test, after starting *AMPP server, just visit the 

	auction/back_stage_proc/transaction.php 

in an independent page in the browser first, and don't close it so that it can keep running.

Then **at another page in the browser**, run 

	index.py 

to start the auction program.

This makes two processes (transaction.php and user browsing functions) run at the same time.