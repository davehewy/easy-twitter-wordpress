<?php

	// When we uninstall just remove the options and the cache out of good practise.
	
	delete_option('eztw_username');
	delete_option('eztw_tweet_count');
	delete_option('eztw_use_cache');
	
	// Best way to cache stuff.
	delete_option('eztw_cache_timer');
	delete_option('eztw_cached_tweet');
	delete_option('eztw_cached_tweet_date');
	

?>