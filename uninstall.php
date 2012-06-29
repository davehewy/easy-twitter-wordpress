<?php

	// When we uninstall just remove the options and the cache out of good practise.
	
	delete_option('eztw_username');
	delete_option('eztw_tweet_count');
	delete_option('eztw_use_cache');
	
	// Best way to cache stuff.
	
	

?>