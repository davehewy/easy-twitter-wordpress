Easy Twitter
=================================

A small plugin which uses PHP and basic caching through a database to store your latest tweets. Currently only configured fetch a singular tweet from any username.

Usage guidelines
------------------

You can use the plugin anywhere in your Wordpress themes by simply using the shortcode.

``` 
[eztwitter]
```

Parameters it currently accepts are:

***username*** - your twitter username

***tweetcount*** - the amount of tweets to retrieve

Example
-------------

In any wordpress post or page (using Wordpress post / page editor).

```
[eztwitter username='Daveheward' tweetcount='1']
```
Using PHP
```
<?php echo do_shortcode('[eztwitter username="Daveheward" tweetCount="1"]')?>
````
